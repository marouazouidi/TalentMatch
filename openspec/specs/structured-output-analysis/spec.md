# Structured Output Analysis

## Purpose

Define a reusable structured output contract for AI responses — the JSON schema definition (field names, types, constraints, enums), format rules (pure JSON only, no surrounding text), forward compatibility (extra fields silently ignored), and schema versioning for contract evolution. Any domain feature that consumes AI structured output conforms to this contract.

## Requirements

### Requirement: AI returns valid structured JSON only

The AI SHALL return ONLY a valid JSON object conforming to the defined schema. No explanatory text, markdown formatting, or any content outside the JSON object SHALL be present in the response.

#### Scenario: Response is pure JSON
- **WHEN** the AI returns a response
- **THEN** the response SHALL be a single valid JSON object with no surrounding text

#### Scenario: Response with surrounding text is rejected
- **WHEN** the AI returns text outside the JSON object
- **THEN** the consuming system SHALL reject the response before further processing

### Requirement: Response conforms to defined JSON Schema

The AI response SHALL conform to the following JSON schema. Every field SHALL match its declared type and constraints. Additional fields beyond the schema SHALL be silently ignored.

- `extracted_skills`: array of strings
- `years_experience`: integer, value >= 0
- `education_level`: string
- `languages`: array of strings
- `matching_score`: integer, value between 0 and 100 inclusive
- `strengths`: array of strings
- `weaknesses`: array of strings
- `missing_skills`: array of strings
- `recommendation`: string, one of `"interview"`, `"pending"`, `"reject"`
- `justification`: string

#### Scenario: All fields match schema
- **WHEN** the AI response includes all required fields with correct types and values within constraints
- **THEN** the response SHALL be accepted as valid

#### Scenario: Field has wrong type
- **WHEN** any field does not match its declared type
- **THEN** the consuming system SHALL reject the response

#### Scenario: Value violates constraint
- **WHEN** a field value falls outside its declared constraint (e.g., matching_score < 0 or > 100)
- **THEN** the consuming system SHALL reject the response

#### Scenario: Enum value is invalid
- **WHEN** the recommendation field contains a value not in `["interview", "pending", "reject"]`
- **THEN** the consuming system SHALL reject the response

#### Scenario: Required field is missing
- **WHEN** the AI response is missing any required schema field
- **THEN** the consuming system SHALL reject the response

#### Scenario: Extra fields are silently ignored
- **WHEN** the AI returns additional fields beyond the defined schema
- **THEN** the consuming system SHALL ignore extra fields and accept the valid fields

### Requirement: Schema includes version field

The AI response SHALL include an integer `schema_version` field to support backward-compatible contract evolution.

#### Scenario: version field present
- **WHEN** the AI returns a response
- **THEN** the response SHALL include a `schema_version` field with an integer value

#### Scenario: version mismatch is handled
- **WHEN** the AI returns a `schema_version` not recognized by the consumer
- **THEN** the consuming system MAY attempt best-effort parsing with the current known schema
