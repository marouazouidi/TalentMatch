<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Analysis;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function __construct(
        private readonly ConversationService $conversationService,
    ) {}

    public function show(Analysis $analysis): View
    {
        $this->authorize('view', $analysis);

        $conversation = $this->conversationService->findOrCreateConversation($analysis);

        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get();

        return view('analyses.partials.chat-panel', compact('conversation', 'messages', 'analysis'));
    }

    public function sendMessage(StoreMessageRequest $request, Conversation $conversation): mixed
    {
        $this->authorize('view', $conversation);

        $message = $this->conversationService->sendMessage(
            $conversation,
            $request->validated('content'),
        );

        return redirect()->back()->with('last_message_id', $message->id);
    }
}
