<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = "chat_messages";
    protected $guarded = ['id'];
    protected $touches = ['chat'];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
