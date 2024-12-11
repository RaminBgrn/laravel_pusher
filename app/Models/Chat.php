<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = "chats";
    protected $guarded = ['id'];

    public function participants()
    {
        return $this->hasMany(ChatParticipant::class, 'chat_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_id');
    }

    public function lastMessage()
    {
        return $this->hasOne(ChatMessage::class, 'chat_id')->latest('created_at');
    }
}
