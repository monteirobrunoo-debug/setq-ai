<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * usage_logs — one row per Anthropic API call (chat turn).
 *
 * Captures token counts + USD cost so the /admin/analytics dashboard
 * can answer: "how many demos ran today, how much did they cost,
 * which agent burns the most tokens, who's just window-shopping?"
 *
 * One row = one POST /demo/{agent}/stream that completed.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $t) {
            $t->id();

            // Session tracking — same id as the cookie, so we can join sessions
            $t->string('session_id', 64)->index();
            $t->string('agent', 32)->index();           // assistant|operations|growth|insights
            $t->ipAddress('ip')->nullable();
            $t->string('user_agent', 512)->nullable();

            // Anthropic token + cost (cost computed server-side from model rate)
            $t->string('model', 64)->nullable();        // e.g. claude-sonnet-4-5-20250929
            $t->unsignedInteger('input_tokens')->default(0);
            $t->unsignedInteger('output_tokens')->default(0);
            $t->unsignedInteger('cache_read_tokens')->default(0);
            $t->decimal('cost_usd', 8, 6)->default(0);  // 6 decimals = sub-cent precision

            // Diagnostics
            $t->unsignedInteger('latency_ms')->nullable();
            $t->boolean('errored')->default(false);
            $t->string('error_msg', 255)->nullable();

            $t->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};
