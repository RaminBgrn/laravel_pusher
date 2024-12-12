<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetMessageRequest;
use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\StoreMessageRequest;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatMessageController extends Controller
{
    public function index(GetMessageRequest $request){
        $data = $request->validated();
        $chatId = $data['chat_id'];
        $currentPage = $data['page'];
        $pageSize = $data['page_size'] ?? 15;

        $message = ChatMessage::where('chat_id' , $chatId)
        ->with('user')
        ->latest('created_at')
        ->simplePaginate(
            $pageSize,
            ['*'],
            'page',
            $currentPage
        );
        return $this->success($message->getCollection());
    }

    public function store(StoreMessageRequest $request){
        $data = $request->validated();
        $data['user_id'] = auth()->user()->id;

        $chatMessage = ChatMessage::create($data);
        $chatMessage->load('user');

        // TODO send notification to pusher

        return $this->success($chatMessage , 'Message has been send successfully');
    }
}
