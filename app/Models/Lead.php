<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    public $timestamps = false;  // only created_at

    protected $fillable = [
        'email', 'company', 'use_case', 'agent', 'session_id',
        'transcript', 'ip', 'user_agent', 'referrer',
        'notified', 'notified_at', 'status',
    ];

    protected $casts = [
        'transcript'  => 'array',
        'notified'    => 'boolean',
        'notified_at' => 'datetime',
        'created_at'  => 'datetime',
    ];

    public const STATUS_NEW       = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_DROPPED   = 'dropped';
}
