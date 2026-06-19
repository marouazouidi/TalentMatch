## 1. Database

- [ ] 1.1 Create migration for `conversations` table (id, analysis_id FK, title nullable, timestamps)
- [ ] 1.2 Create migration for `messages` table (id, conversation_id FK, role string, content text, timestamps)

## 2. Enum & Models

- [ ] 2.1 Create `App\Enums\MessageRole` backed string enum (User, Assistant)
- [ ] 2.2 Create `App\Models\Conversation` with fillable, casts, relationships
- [ ] 2.3 Create `App\Models\Message` with fillable, MessageRole cast, relationships
- [ ] 2.4 Add `hasMany(Conversation::class)` relationship to `App\Models\Analysis`

## 3. AI Agent

- [ ] 3.1 Create `App\Agents\ConversationAgent` implementing `Agent` only (no tools, no memory, no structured output)

## 4. Service Layer

- [ ] 4.1 Create `App\Services\ConversationService` with `findOrCreateConversation()` and `sendMessage()`

## 5. Authorization & Validation

- [ ] 5.1 Create `App\Policies\ConversationPolicy` (view/create based on analysis ownership)
- [ ] 5.2 Create `App\Http\Requests\StoreMessageRequest` (content required, string, max:2000)
- [ ] 5.3 Register `ConversationPolicy` in `AppServiceProvider`

## 6. Controller & Routes

- [ ] 6.1 Create `App\Http\Controllers\ConversationController` with `show()` and `sendMessage()`
- [ ] 6.2 Add conversation routes to `routes/web.php` (show conversation, send message)

## 7. Views

- [ ] 7.1 Create `resources/views/analyses/partials/chat-panel.blade.php` chat UI
- [ ] 7.2 Add "Open AI Assistant" button and chat panel include to `analyses/show.blade.php`

## 8. Testing

- [ ] 8.1 Write feature tests for conversation creation, message sending, authorization, validation, and AI response persistence

## 9. Final Checks

- [ ] 9.1 Run `vendor/bin/pint --format agent` to fix code style
- [ ] 9.2 Run `php artisan test --compact` to verify all tests pass
- [ ] 9.3 Run `php artisan route:list` to verify new routes are registered
