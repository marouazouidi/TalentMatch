## Context

The candidate analysis feature produces static evaluations. HR users cannot ask follow-up questions or explore candidate context interactively. A lightweight AI conversation system is needed — one chat session per analysis, synchronous request/response, no real-time infrastructure.

The Laravel AI SDK is already installed with Groq as the default provider. The `CandidateAnalysisAgent` exists for structured analysis but is intentionally not reused here — this feature uses a plain prompt-only agent with no structured output, tools, or memory.

## Goals / Non-Goals

**Goals:**
- Allow one or more chat conversations per analysis
- Persist messages in the database
- Provide contextual AI responses grounded in the candidate, job offer, and analysis
- Keep the implementation simple — synchronous, no queue for chat
- Enforce authorization via existing ownership patterns

**Non-Goals:**
- Real-time features (WebSockets, polling, SSE)
- Streaming AI responses
- Multi-agent system
- Tools / function calling / MCP
- File uploads or attachments
- Events or listeners
- Voice or multimedia messages

## Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Relationship | `Analysis hasMany Conversation` | HR may want multiple chat threads per analysis |
| Conversation creation | Lazy — auto-created on first `sendMessage` | Avoids empty orphan conversations cluttering the DB |
| Agent pattern | `ConversationAgent` implements `Agent` only (no `HasStructuredOutput`) | This is free-form Q&A, not structured extraction; the existing `CandidateAnalysisAgent` is for structured output only |
| Provider | Default from `config/ai.php` (Groq) | No hardcoded model or provider; respects existing configuration |
| Context | Build prompt from JobOffer + Candidate + Analysis + last 20 messages | Token-safe window that preserves recent discussion while avoiding overflow |
| Message flow | Save user msg → build context → call agent → save AI response → return | Simple synchronous flow; no additional queue infrastructure needed |
| Auth | Policy checks `$user->id === $analysis->jobOffer->user_id` | Identical to existing `AnalysisPolicy` pattern |
| Chat UI | In-page panel on analysis detail page | Keeps analysis context visible alongside the chat |
| Visibility | Button shown only when `AnalysisStatus::Completed` | No point chatting before analysis is done |

## Risks / Trade-offs

| Risk | Mitigation |
|------|------------|
| AI provides hallucinated answers about the candidate | Prompt explicitly restricts AI to the provided context; agent instructions enforce HR-only scope |
| Message context window grows too large | Cap history to last 20 messages in prompt construction |
| Empty AI response from provider | Retry once in service; return user-friendly error if still empty |
| AI timeout during synchronous request | Service catches exceptions and returns error message; no queue needed for chat |
| User sends very long messages | Form request enforces max 2000 character limit |
