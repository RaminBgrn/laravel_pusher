<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetChatRequest;
use App\Http\Requests\StoreChatRequest;
use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(GetChatRequest $request)
    {
        $data = $request->validated();
        $isPrivate = 1;
        if ($request->has('is_private')) {
            $isPrivate = (int)$data['is_private'];
        }

        $chats = Chat::where('is_private', $isPrivate)
            ->hasParticipant(auth()->user()->id)
            ->whereHas('messages')
            ->with('lastMessage.user', 'participants.user')
            ->latest('updated_at')
            ->get();
        return $this->success($chats);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChatRequest $request)
    {
        $data = $this->prepareStoreData($request);
        if ($data['userId'] === $data['otherUserId']) {
            return $this->error('You can not create a chat whit your own ');
        }
        $previousChat = $this->getPreviousChat($data['otherUserId']);

        if ($previousChat === null) {
            $chat = Chat::create($data['data']);
            $chat->participants->createMany([
                [
                    'user_id' => $data['userId']
                ],
                [
                    'user_id' => $data['otherUserId']
                ],
            ]);
            $chat->refresh()->load('lastMessage.user' , 'participant.user');
            return $this->success($chat);
        }
        return $this->success($previousChat->load('lastMessage.user' , 'participant.user'));
    }

    private function getPreviousChat(int $otherUserId)
    {

        $userId = auth()->user()->id;

        return Chat::where('is_private', 1)
            ->whereHas('participants', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereHas('participants', function ($query) use ($otherUserId) {
                $query->where('user_id', $otherUserId);
            })
            ->first();
    }



    private function prepareStoreData(StoreChatRequest $request)
    {
        $data = $request->validated();
        $otherUserid = (int)$data['user_id'];
        unset($data['user_id']);
        $data['created_by'] = auth()->user()->id;
        return [
            'otherUserId' => $otherUserid,
            'userId' => auth()->user()->id,
            'data' => $data,
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Chat $chat)
    {
        $chat->load('lastMessage.user', 'participant.user');
        return $this->success($chat);
    }
}
