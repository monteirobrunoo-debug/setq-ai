<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageLog extends Model
{
    public $timestamps = false;  // only created_at, set by DB default

    protected $fillable = [
        'session_id', 'agent', 'ip', 'user_agent',
        'model', 'input_tokens', 'output_tokens', 'cache_read_tokens', 'cost_usd',
        'latency_ms', 'errored', 'error_msg',
    ];

    protected $casts = [
        'input_tokens'      => 'integer',
        'output_tokens'     => 'integer',
        'cache_read_tokens' => 'integer',
        'cost_usd'          => 'decimal:6',
        'latency_ms'        => 'integer',
        'errored'           => 'boolean',
        'created_at'        => 'datetime',
    ];

    /**
     * Compute USD cost from token counts + model rate.
     * Anthropic Sonnet 4.5 list price (Apr 2026):
     *   input  $3.00 / 1M tokens
     *   output $15.00 / 1M tokens
     *   cache read $0.30 / 1M tokens
     */
    public static function computeCost(string $model, int $in, int $out, int $cacheRead = 0): float
    {
        // Coarse rates by model family — refine when we add more models
        $rates = match (true) {
            str_contains($model, 'opus')   => ['in' => 15.00, 'out' => 75.00, 'cache' => 1.50],
            str_contains($model, 'haiku')  => ['in' =>  0.80, 'out' =>  4.00, 'cache' => 0.08],
            default /* sonnet */           => ['in' =>  3.00, 'out' => 15.00, 'cache' => 0.30],
        };
        $cost  = ($in        / 1_000_000) * $rates['in'];
        $cost += ($out       / 1_000_000) * $rates['out'];
        $cost += ($cacheRead / 1_000_000) * $rates['cache'];
        return round($cost, 6);
    }
}
