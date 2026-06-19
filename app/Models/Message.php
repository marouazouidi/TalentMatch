<?php

namespace App\Models;

use App\Enums\MessageRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'role' => MessageRole::class,
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
