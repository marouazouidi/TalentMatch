<?php

use App\Agents\ConversationAgent;
use App\Enums\AnalysisStatus;
use App\Models\Analysis;
use App\Models\Conversation;
use App\Models\JobOffer;
use App\Models\User;

uses()->group('conversation');

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->offer = JobOffer::factory()->create(['user_id' => $this->user->id]);
    $this->analysis = Analysis::factory()->create([
        'job_offer_id' => $this->offer->id,
        'status' => AnalysisStatus::Completed,
    ]);
});

// ─── Conversation Creation ───

test('user can view conversation for completed analysis', function (): void {
    $this->actingAs($this->user)
        ->get(route('conversations.show', $this->analysis))
        ->assertOk();
});

test('conversation is created automatically on first access', function (): void {
    $this->actingAs($this->user)
        ->get(route('conversations.show', $this->analysis));

    $this->assertDatabaseHas('conversations', [
        'analysis_id' => $this->analysis->id,
    ]);
});

test('existing conversation is reused on subsequent access', function (): void {
    $this->actingAs($this->user)
        ->get(route('conversations.show', $this->analysis));

    $this->actingAs($this->user)
        ->get(route('conversations.show', $this->analysis));

    $this->assertDatabaseCount('conversations', 1);
});

// ─── Authorization ───

test('user cannot view conversation for another user analysis', function (): void {
    $otherUser = User::factory()->create();
    $otherOffer = JobOffer::factory()->create(['user_id' => $otherUser->id]);
    $otherAnalysis = Analysis::factory()->create([
        'job_offer_id' => $otherOffer->id,
        'status' => AnalysisStatus::Completed,
    ]);

    $this->actingAs($this->user)
        ->get(route('conversations.show', $otherAnalysis))
        ->assertForbidden();
});

// ─── Message Sending ───

test('user can send a message and receive AI response', function (): void {
    ConversationAgent::fake([
        function (string $prompt): string {
            return 'The candidate has strong PHP skills and would be a good fit.';
        },
    ]);

    $conversation = $this->analysis->conversations()->create();

    $this->actingAs($this->user)
        ->post(route('conversations.messages.store', $conversation), [
            'content' => 'What are the strengths?',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'role' => 'user',
        'content' => 'What are the strengths?',
    ]);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'role' => 'assistant',
    ]);
});

test('message validation rejects empty content', function (): void {
    $conversation = $this->analysis->conversations()->create();

    $this->actingAs($this->user)
        ->post(route('conversations.messages.store', $conversation), [
            'content' => '',
        ])
        ->assertSessionHasErrors('content');
});

test('message validation rejects content exceeding max length', function (): void {
    $conversation = $this->analysis->conversations()->create();

    $this->actingAs($this->user)
        ->post(route('conversations.messages.store', $conversation), [
            'content' => str_repeat('a', 2001),
        ])
        ->assertSessionHasErrors('content');
});

test('user cannot send message to another user conversation', function (): void {
    $otherUser = User::factory()->create();
    $otherOffer = JobOffer::factory()->create(['user_id' => $otherUser->id]);
    $otherAnalysis = Analysis::factory()->create([
        'job_offer_id' => $otherOffer->id,
        'status' => AnalysisStatus::Completed,
    ]);
    $otherConversation = $otherAnalysis->conversations()->create();

    $this->actingAs($this->user)
        ->post(route('conversations.messages.store', $otherConversation), [
            'content' => 'Hello',
        ])
        ->assertForbidden();
});

// ─── AI Response ───

test('AI response is persisted as assistant message', function (): void {
    ConversationAgent::fake([
        function (string $prompt): string {
            return 'Based on the analysis, this candidate is well-suited for the role.';
        },
    ]);

    $conversation = $this->analysis->conversations()->create();

    $this->actingAs($this->user)
        ->post(route('conversations.messages.store', $conversation), [
            'content' => 'Is this candidate suitable?',
        ]);

    $this->assertDatabaseHas('messages', [
        'conversation_id' => $conversation->id,
        'role' => 'assistant',
        'content' => 'Based on the analysis, this candidate is well-suited for the role.',
    ]);
});
