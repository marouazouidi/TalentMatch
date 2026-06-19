<?php

namespace App\Services;

use App\Agents\ConversationAgent;
use App\Models\Analysis;
use App\Models\Conversation;
use App\Models\Message;

class ConversationService
{
    public function findOrCreateConversation(Analysis $analysis): Conversation
    {
        return $analysis->conversations()->firstOrCreate(
            [],
            ['title' => 'AI Assistant']
        );
    }

    public function sendMessage(Conversation $conversation, string $content): Message
    {
        $userMessage = $conversation->messages()->create([
            'role' => 'user',
            'content' => $content,
        ]);

        $response = $this->generateResponse($conversation, $content);

        $assistantMessage = $conversation->messages()->create([
            'role' => 'assistant',
            'content' => $response,
        ]);

        return $assistantMessage;
    }

    protected function generateResponse(Conversation $conversation, string $userMessage): string
    {
        $context = $this->buildContext($conversation, $userMessage);

        $agent = new ConversationAgent;

        try {
            $response = $agent->prompt($context);

            $text = $response->text;

            if (empty(trim($text))) {
                $response = $agent->prompt($context);

                $text = $response->text;
            }

            return empty(trim($text))
                ? 'I apologize, but I was unable to generate a response. Please try asking your question again.'
                : $text;
        } catch (\Throwable $e) {
            return 'I apologize, but an error occurred while processing your request. Please try again later.';
        }
    }

    private function buildContext(Conversation $conversation, string $userMessage): string
    {
        $analysis = $conversation->analysis;
        $candidate = $analysis->candidate;
        $jobOffer = $analysis->jobOffer;

        $context = "Job Offer: {$jobOffer->title}\n";
        $context .= "Description: {$jobOffer->description}\n\n";
        $context .= "Candidate CV:\n{$candidate->cv_text}\n\n";
        $context .= "Analysis Results:\n";
        $context .= "- Matching Score: {$analysis->matching_score}/100\n";
        $context .= "- Recommendation: {$analysis->recommendation->value}\n";

        if (! empty($analysis->strengths)) {
            $context .= '- Strengths: '.implode(', ', $analysis->strengths)."\n";
        }

        if (! empty($analysis->weaknesses)) {
            $context .= '- Weaknesses: '.implode(', ', $analysis->weaknesses)."\n";
        }

        if (! empty($analysis->missing_skills)) {
            $context .= '- Missing Skills: '.implode(', ', $analysis->missing_skills)."\n";
        }

        $context .= "\nPrevious Conversation:\n";

        $previousMessages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->take(20)
            ->get();

        foreach ($previousMessages as $msg) {
            $role = $msg->role === 'user' ? 'User' : 'Assistant';
            $context .= "{$role}: {$msg->content}\n";
        }

        $context .= "\nCurrent question from user:\n{$userMessage}";

        return $context;
    }
}
