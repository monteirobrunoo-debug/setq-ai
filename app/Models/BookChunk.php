<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookChunk extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'domain', 'book_key', 'book_title', 'page_no', 'content',
    ];

    public const DOMAIN_BACK_OFFICE  = 'back_office';   // Operations + Insights
    public const DOMAIN_FRONT_OFFICE = 'front_office';  // Growth + Assistant
}
