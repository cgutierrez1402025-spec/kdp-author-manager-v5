<x-filament-panels::page>
    <div wire:poll.3s="refreshExecution" class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(18rem,1fr)]">
            <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Conversación editorial</h2>
                        <p class="mt-1 text-sm text-gray-500">La petición se procesa en segundo plano y no bloquea el resto del panel.</p>
                    </div>
                    <button type="button" wire:click="newConversation" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200">
                        Nueva conversación
                    </button>
                </div>

                <div class="min-h-96 space-y-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-950/40">
                    @forelse ($this->currentConversation?->messages ?? [] as $message)
                        <article class="rounded-lg p-4 {{ $message->role === 'user' ? 'ml-8 bg-primary-50 dark:bg-primary-950/30' : 'mr-8 bg-white shadow-sm dark:bg-gray-900' }}">
                            <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                {{ $message->role === 'user' ? 'Tú' : 'Ollama' }}
                                @if ($message->status !== 'completed')
                                    · {{ $message->status === 'processing' ? 'Procesando...' : ucfirst($message->status) }}
                                @endif
                            </div>
                            <div class="whitespace-pre-wrap text-sm leading-6 text-gray-800 dark:text-gray-100">{{ $message->content ?: 'Generando respuesta...' }}</div>
                            @if ($message->thinking_content)
                                <details class="mt-3 text-xs text-gray-500">
                                    <summary>Mostrar pensamiento del modelo</summary>
                                    <div class="mt-2 whitespace-pre-wrap">{{ $message->thinking_content }}</div>
                                </details>
                            @endif
                        </article>
                    @empty
                        <div class="flex min-h-80 items-center justify-center text-center text-sm text-gray-500">
                            Selecciona una obra y escribe una petición para iniciar la conversación.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    <textarea wire:model="prompt" rows="5" maxlength="50000" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800" placeholder="Escribe una petición para tu asistente editorial..."></textarea>
                    <div class="mt-3 flex justify-end">
                        <button type="button" wire:click="sendMessage" wire:loading.attr="disabled" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500 disabled:opacity-50">
                            Enviar a Ollama
                        </button>
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-950 dark:text-white">Conexión local</h2>
                        <button type="button" wire:click="checkOllama" class="text-xs font-medium text-primary-600 hover:text-primary-500">Comprobar</button>
                    </div>
                    <p class="mt-2 text-sm {{ $ollamaStatus === 'Conectado' ? 'text-success-600' : 'text-gray-500' }}">{{ $ollamaStatus }}</p>
                </section>

                <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <h2 class="text-sm font-semibold text-gray-950 dark:text-white">Configuración</h2>
                    <div class="mt-4 space-y-4">
                        <label class="block text-sm"><span class="font-medium text-gray-700 dark:text-gray-200">Obra</span>
                            <select wire:model="selectedWorkId" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800">
                                @foreach ($this->works as $work)<option value="{{ $work->id }}">{{ $work->title_public }}</option>@endforeach
                            </select>
                        </label>
                        <label class="block text-sm"><span class="font-medium text-gray-700 dark:text-gray-200">Modelo</span>
                            <select wire:model="model" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800">
                                @forelse ($availableModels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@empty<option value="{{ $model }}">{{ $model }}</option>@endforelse
                            </select>
                        </label>
                        <label class="block text-sm"><span class="font-medium text-gray-700 dark:text-gray-200">Prompt de sistema</span>
                            <textarea wire:model="systemPrompt" rows="3" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800" placeholder="Rol y criterios del asistente..."></textarea>
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block text-sm"><span class="font-medium text-gray-700 dark:text-gray-200">Temperatura</span><input wire:model="temperature" type="number" min="0" max="2" step="0.1" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800"></label>
                            <label class="block text-sm"><span class="font-medium text-gray-700 dark:text-gray-200">Tokens máximos</span><input wire:model="numPredict" type="number" min="1" max="100000" class="mt-1 w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800"></label>
                        </div>
                        <label class="flex items-center gap-2 text-sm"><input wire:model="thinking" type="checkbox" class="rounded border-gray-300 text-primary-600"> Activar pensamiento del modelo</label>
                    </div>
                </section>

                @if ($this->activeAssistantMessage?->status === 'completed')
                    <section class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <h2 class="text-sm font-semibold text-gray-950 dark:text-white">Conservar resultado</h2>
                        <div class="mt-4 space-y-2">
                            <button type="button" wire:click="savePrompt" class="w-full rounded-lg bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200">Guardar prompt y respuesta</button>
                            <button type="button" wire:click="saveAsSource" class="w-full rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white hover:bg-primary-500">Guardar como fuente</button>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</x-filament-panels::page>