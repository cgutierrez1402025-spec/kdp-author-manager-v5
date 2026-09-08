<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('provider')->default('ollama');
            $table->string('model');
            $table->text('system_prompt')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'work_id']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->string('role', 20);
            $table->longText('content')->nullable();
            $table->longText('thinking_content')->nullable();
            $table->string('status')->default('completed');
            $table->unsignedInteger('sequence');
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['ai_conversation_id', 'sequence']);
        });

        if (! Schema::hasColumn('sources', 'prompt_id')) {
            Schema::table('sources', function (Blueprint $table) {
                $table->foreignId('prompt_id')->nullable()->after('work_id')->nullOnDelete();
                $table->string('origin')->nullable()->after('source_type');
                $table->longText('generated_content')->nullable()->after('summary');
                $table->json('metadata')->nullable()->after('generated_content');
            });
        }

        if (! Schema::hasColumn('prompts', 'result_text')) {
            Schema::table('prompts', function (Blueprint $table) {
                $table->longText('result_text')->nullable()->after('prompt_text');
            });
        }
    }

    public function down(): void
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropForeign(['prompt_id']);
            $table->dropColumn(['prompt_id', 'origin', 'generated_content', 'metadata']);
        });

        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};