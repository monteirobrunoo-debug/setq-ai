<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * leads — buyer who liked a demo enough to leave contact info.
 *
 * Captured via the slider that pops on the chat page after the user
 * has either sent ≥3 messages or spent ≥5 minutes in the sandbox.
 *
 * Email is required. Company / use-case are optional. The full chat
 * transcript is attached so the sales team has context before reaching
 * out — explicitly disclosed in the form.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $t) {
            $t->id();

            // What they entered
            $t->string('email', 200)->index();
            $t->string('company', 200)->nullable();
            $t->text('use_case')->nullable();

            // Demo context
            $t->string('agent', 32)->index();
            $t->string('session_id', 64)->index();
            $t->json('transcript')->nullable();          // last 10 turns of the chat

            // Tracking / debugging
            $t->ipAddress('ip')->nullable();
            $t->string('user_agent', 512)->nullable();
            $t->string('referrer', 512)->nullable();

            // Workflow flags
            $t->boolean('notified')->default(false);     // email sent to sales?
            $t->timestamp('notified_at')->nullable();
            $t->string('status', 32)->default('new');    // new | contacted | converted | dropped

            $t->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
