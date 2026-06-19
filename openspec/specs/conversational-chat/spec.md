# Conversational Chat

## Purpose

Authenticated HR agents can interact with an AI assistant directly from the analysis detail page. The assistant provides contextual answers about candidate evaluations, suggests interview questions, and maintains conversation history. Conversations are automatically created per analysis and persisted in the database.

## Requirements

### Requirement: User can open an AI conversation from analysis page

The system SHALL allow an authenticated user to open an AI chat conversation from the analysis detail page. The button SHALL only be visible when the analysis status is "completed".

#### Scenario: View conversation for completed analysis
- **WHEN** an authenticated user views a completed analysis for a job offer they own
- **THEN** the system displays an "Open AI Assistant" button that navigates to the conversation view

#### Scenario: No button for pending analysis
- **WHEN** an authenticated user views a pending or processing analysis
- **THEN** the system does NOT display the "Open AI Assistant" button

#### Scenario: No button for another user's analysis
- **WHEN** an authenticated user views a completed analysis for a job offer owned by another user
- **THEN** the system returns a 403 Forbidden response

### Requirement: Conversation is created automatically

The system SHALL automatically create a conversation when a user opens the AI assistant for the first time on an analysis.

#### Scenario: First conversation access
- **WHEN** an authenticated user opens the AI assistant for an analysis that has no conversation
- **THEN** the system creates a new conversation linked to the analysis and displays an empty chat history

#### Scenario: Existing conversation access
- **WHEN** an authenticated user opens the AI assistant for an analysis that already has conversations
- **THEN** the system displays the existing conversation and its messages

### Requirement: User can send messages and receive AI responses

The system SHALL allow the user to send messages to the AI assistant and receive contextual responses. Messages SHALL be stored in the database.

#### Scenario: Send message and receive AI response
- **WHEN** an authenticated user sends a message in a conversation belonging to their analysis
- **THEN** the system saves the user message with role "user", sends the conversation context to the AI, saves the AI response with role "assistant", and returns the response

#### Scenario: Message with empty content
- **WHEN** an authenticated user submits a message with empty content
- **THEN** the system returns a validation error and does not send the message to the AI

#### Scenario: Message exceeding max length
- **WHEN** an authenticated user submits a message longer than 2000 characters
- **THEN** the system returns a validation error and does not send the message to the AI

### Requirement: AI responds with HR context

The AI SHALL answer HR questions using the candidate CV, analysis results, and job offer information provided as context. The AI SHALL NOT invent information not present in the provided context.

#### Scenario: AI answers about strengths
- **WHEN** a user asks "What are this candidate's strengths?"
- **THEN** the AI responds using the strengths and extracted skills from the analysis context

#### Scenario: AI suggests interview questions
- **WHEN** a user asks "What interview questions should I ask?"
- **THEN** the AI suggests questions based on the job requirements, candidate profile, and identified skill gaps

#### Scenario: AI maintains conversation history
- **WHEN** a user sends a follow-up message referencing a previous question
- **THEN** the AI responds with awareness of the prior conversation context

### Requirement: Conversation authorization is enforced

The system SHALL restrict access to conversations based on analysis ownership.

#### Scenario: View own conversation
- **WHEN** an authenticated user views a conversation linked to their own analysis
- **THEN** the system displays the conversation messages

#### Scenario: View another user's conversation
- **WHEN** an authenticated user attempts to view a conversation linked to another user's analysis
- **THEN** the system returns a 403 Forbidden response
