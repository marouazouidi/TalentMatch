## Why

Candidate analysis results are currently static — HR users receive a structured evaluation (score, strengths, weaknesses, recommendation) but cannot ask follow-up questions, explore reasoning, or interact with the AI to deepen their understanding of a candidate's fit.

## What Changes

- Add `conversations` and `messages` database tables linked to analyses
- Create `Conversation` and `Message` Eloquent models with `hasMany`/`belongsTo` relationships
- Create `MessageRole` enum (`User`, `Assistant`) with Eloquent casting
- Create `ConversationAgent` — a stateless Laravel AI SDK agent (no structured output, no tools, no memory)
- Create `ConversationService` — builds AI context from JobOffer, Candidate, Analysis, and conversation history
- Create `ConversationController` with `show()` and `sendMessage()` actions
- Create `ConversationPolicy` enforcing ownership via `analysis.jobOffer.user_id`
- Add two routes: show conversation and send message
- Add "Open AI Assistant" button and chat panel to the analysis detail page (visible only when `AnalysisStatus::Completed`)
- Add form request validation for message content (required, max 2000 chars)

## Capabilities

### New Capabilities
- `conversational-chat`: AI-powered chat per candidate analysis — users send messages, AI responds with HR-specific guidance grounded in the candidate evaluation, job offer context, and conversation history

### Modified Capabilities
- `candidate-analysis`: Add "Open AI Assistant" button on the analysis detail page when analysis is completed

## Impact

- New migration: `conversations` table (analysis_id FK, title, timestamps)
- New migration: `messages` table (conversation_id FK, role, content, timestamps)
- New models: `App\Models\Conversation`, `App\Models\Message`
- New enum: `App\Enums\MessageRole`
- New agent: `App\Agents\ConversationAgent`
- New service: `App\Services\ConversationService`
- New controller: `App\Http\Controllers\ConversationController`
- New policy: `App\Policies\ConversationPolicy`
- New form request: `App\Http\Requests\StoreMessageRequest`
- New view partial: `resources/views/analyses/partials/chat-panel.blade.php`
- Modified: `app/Models/Analysis.php` (add `hasMany conversations`)
- Modified: `routes/web.php` (add 2 conversation routes)
- Modified: `resources/views/analyses/show.blade.php` (add assistant button + chat panel)
- Modified: `app/Providers/AppServiceProvider.php` (register ConversationPolicy)
- No changes to existing migrations, CandidateAnalysisAgent, AnalyseCandidateJob, or AI schema validation
