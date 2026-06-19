## Why

The current AnalyseCandidateJob relies on AI returning valid structured JSON, but the contract between AI and backend has gaps — field types, value ranges, enum values, and error handling rules are not explicitly codified as a formal specification. This leads to fragile parsing and silent failures. A strict, validated structured output specification ensures deterministic AI response processing, schema enforcement, and consistent error handling.

## What Changes

- Formalize the AI structured output JSON schema contract as a standalone specification
- Define strict validation rules for each field (types, ranges, enums)
- Specify error handling behavior for invalid AI responses
- Codify storage rules for raw AI response (payload) vs structured fields
- Define analysis lifecycle flow (pending → processing → completed / failed)
- Specify queue integration behavior (status transitions managed by job)
- Define retry strategy for transient vs validation failures
- Update AnalyseCandidateJob to validate against the formal schema

## Capabilities

### New Capabilities
- `structured-output-schema`: Formal specification for the AI response JSON schema, field-level validation rules, error handling for invalid responses, payload storage rules, analysis lifecycle (pending → processing → completed → failed), queue integration, and retry strategy

### Modified Capabilities
- `candidate-analysis`: Update the "AI returns structured analysis with schema validation" requirement to reference the formal schema specification

## Impact

- AnalyseCandidateJob: updated validation logic, lifecycle management, retry handling
- AI prompt: updated to reference formal schema
- Test suite: additional schema validation edge cases, lifecycle transitions, retry behavior
