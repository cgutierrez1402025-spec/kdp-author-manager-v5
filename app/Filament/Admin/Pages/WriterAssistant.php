<?php

namespace App\Filament\Admin\Pages;

use App\Jobs\ProcessOllamaChat;
use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Models\Prompt;
use App\Models\Source;
use App\Models\Work;
use App\Services\Ollama\OllamaClient;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;

class WriterAssistant extends Page
{
    protected static string $view = 'filament.admin.writer-assistant';

    protected static ?string $title = 'Asistente del escritor';

    protected static ?string $navigationLabel = 'Asistente del escritor';

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Inteligencia artificial';

    protected static ?int $navigationSort = 1;

    public ?int $selectedWorkId = null;

    public ?int $conversationId = null;

    public ?int $activeAssistantMessageId = null;

    public ?int $notifiedMessageId = null;

    public string $model = '';

    public string $systemPrompt = '';

    public string $prompt = '';

    public string $temperature = '0.7';

    public string $numContext = '4096';

    public string $numPredict = '512';

    public bool $thinking = false;

    public string $ollamaStatus = 'Sin comprobar';

    public array $availableModels = [];

    public function mount(): void
    {
        $this->model = (string) config('services.ollama.default_model', 'qwen2.5:7b');
        $this->selectedWorkId = $this->works()->first()?->id;
        $this->checkOllama();
    }

    public function checkOllama(): void
    {
        try {
            $client = app(OllamaClient::class);
            $client->version();
            $this->availableModels = collect($client->models())
                ->mapWithKeys(fn (array $model): array => [
                    $model['name'] => $model['name'].' ('.number_format(($model['size'] ?? 0) / 1_000_000_000, 1).' GB)',
                ])
                ->all();
            $this->ollamaStatus = 'Conectado';

            if ($this->availableModels !== [] && ! array_key_exists($this->model, $this->availableModels)) {
                $this->model = array_key_first($this->availableModels);
            }
        } catch (\Throwable $exception) {
            $this->availableModels = [];
            $this->ollamaStatus = 'No disponible';
        }
    }

    public function sendMessage(): void
    {
        Validator::make([
            'work_id' => $this->selectedWorkId,
            'model' => $this->model,
            'prompt' => $this->prompt,
        ], [
            'work_id' => ['required', 'integer'],
            'model' => ['required', 'string', 'max:255'],
            'prompt' => ['required', 'string', 'max:50000'],
        ])->validate();

        $work = $this->works()->findOrFail($this->selectedWorkId);
        $conversation = $this->conversationForWork($work);
        $sequence = (int) $conversation->messages()->max('sequence') + 1;
        $options = array_filter([
            'temperature' => (float) $this->temperature,
            'num_ctx' => (int) $this->numContext,
            'num_predict' => (int) $this->numPredict,
        ], fn (mixed $value): bool => $value !== 0);

        if ($this->thinking) {
            $options['think'] = true;
        }

        $userMessage = $conversation->messages()->create([
            'role' => 'user',
            'content' => trim($this->prompt),
            'status' => 'completed',
            'sequence' => $sequence,
            'metadata' => ['ollama_options' => $options],
            'completed_at' => now(),
        ]);

        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'status' => 'pending',
            'sequence' => $sequence + 1,
        ]);

        $conversation->update([
            'status' => 'processing',
            'last_message_at' => now(),
        ]);

        ProcessOllamaChat::dispatch($assistantMessage)->onQueue('ollama');

        $this->conversationId = $conversation->id;
        $this->activeAssistantMessageId = $assistantMessage->id;
        $this->prompt = '';
        $this->notifiedMessageId = null;

        Notification::make()
            ->title('Petición enviada')
            ->body('Puedes seguir trabajando en el resto de la aplicación mientras Ollama responde.')
            ->success()
            ->send();
    }

    public function refreshExecution(): void
    {
        if (! $this->activeAssistantMessageId) {
            return;
        }

        $message = $this->activeAssistantMessage();

        if (! $message || ! in_array($message->status, ['completed', 'failed'], true)) {
            return;
        }

        if ($this->notifiedMessageId === $message->id) {
            return;
        }

        $this->notifiedMessageId = $message->id;
        Notification::make()
            ->title($message->status === 'completed' ? 'Respuesta disponible' : 'La petición ha fallado')
            ->body($message->status === 'completed' ? 'Ollama ha terminado de generar el resultado.' : $message->content)
            ->color($message->status === 'completed' ? 'success' : 'danger')
            ->send();
    }

    public function savePrompt(): void
    {
        $assistant = $this->completedAssistantMessage();
        $userMessage = $this->userMessageFor($assistant);

        if (! $assistant || ! $userMessage) {
            return;
        }

        Prompt::create([
            'work_id' => $assistant->conversation->work_id,
            'title' => 'Conversación con Ollama',
            'prompt_text' => $userMessage->content,
            'purpose' => 'writer_assistant',
            'result_text' => $assistant->content,
            'rating' => 5,
            'reused' => false,
            'generated_final_content' => false,
        ]);

        Notification::make()->title('Prompt guardado')->success()->send();
    }

    public function saveAsSource(): void
    {
        $assistant = $this->completedAssistantMessage();
        $userMessage = $this->userMessageFor($assistant);

        if (! $assistant || ! $userMessage) {
            return;
        }

        $prompt = Prompt::firstOrCreate([
            'work_id' => $assistant->conversation->work_id,
            'prompt_text' => $userMessage->content,
        ], [
            'title' => 'Conversación con Ollama',
            'purpose' => 'writer_assistant',
            'result_text' => $assistant->content,
            'rating' => 5,
            'reused' => false,
            'generated_final_content' => false,
        ]);

        Source::create([
            'work_id' => $assistant->conversation->work_id,
            'prompt_id' => $prompt->id,
            'title' => 'Contenido generado con Ollama',
            'source_type' => 'generated_ai',
            'origin' => 'ollama_local',
            'summary' => str($assistant->content)->limit(500)->toString(),
            'generated_content' => $assistant->content,
            'rights_status' => 'Revisión humana pendiente',
            'notes' => 'Modelo: '.$assistant->conversation->model,
            'metadata' => [
                'conversation_id' => $assistant->conversation->id,
                'message_id' => $assistant->id,
            ],
        ]);

        Notification::make()->title('Resultado guardado como fuente')->success()->send();
    }

    public function newConversation(): void
    {
        $this->conversationId = null;
        $this->activeAssistantMessageId = null;
        $this->notifiedMessageId = null;
    }

    public function getWorksProperty(): Collection
    {
        return $this->works();
    }

    public function getCurrentConversationProperty(): ?AiConversation
    {
        return $this->conversationId
            ? AiConversation::query()
                ->whereKey($this->conversationId)
                ->where('user_id', auth()->id())
                ->with('messages')
                ->first()
            : null;
    }

    public function getActiveAssistantMessageProperty(): ?AiMessage
    {
        return $this->activeAssistantMessage();
    }

    public function activeAssistantMessage(): ?AiMessage
    {
        return $this->activeAssistantMessageId
            ? AiMessage::query()
                ->whereKey($this->activeAssistantMessageId)
                ->whereHas('conversation', fn ($query) => $query->where('user_id', auth()->id()))
                ->first()
            : null;
    }

    protected function completedAssistantMessage(): ?AiMessage
    {
        $message = $this->activeAssistantMessage();

        return $message?->status === 'completed' ? $message->load('conversation') : null;
    }

    protected function userMessageFor(?AiMessage $assistant): ?AiMessage
    {
        return $assistant?->conversation
            ->messages()
            ->where('role', 'user')
            ->where('sequence', '<', $assistant->sequence)
            ->latest('sequence')
            ->first();
    }

    protected function works(): Collection
    {
        return Work::query()
            ->when(! auth()->user()?->canViewAllAuthorData(), fn ($query) => $query->where('user_id', auth()->id()))
            ->orderBy('title_public')
            ->get(['id', 'title_public']);
    }

    protected function conversationForWork(Work $work): AiConversation
    {
        if ($this->conversationId) {
            $existing = AiConversation::query()
                ->whereKey($this->conversationId)
                ->where('user_id', auth()->id())
                ->where('work_id', $work->id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return AiConversation::create([
            'user_id' => auth()->id(),
            'work_id' => $work->id,
            'title' => 'Asistente · '.$work->title_public,
            'provider' => 'ollama',
            'model' => $this->model,
            'system_prompt' => $this->systemPrompt ?: null,
            'last_message_at' => now(),
        ]);
    }
}