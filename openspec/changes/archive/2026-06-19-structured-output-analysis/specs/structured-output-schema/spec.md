## ADDED Requirements

### Requirement: AI returns valid structured JSON only

The AI SHALL return ONLY a valid JSON object conforming to the defined schema. No explanatory text, markdown formatting, or any content outside the JSON object SHALL be present in the response.

#### Scenario: Response is pure JSON
- **WHEN** the AI returns a response
- **THEN** the response SHALL be a single valid JSON object with no surrounding text

#### Scenario: Response with surrounding text is rejected
- **WHEN** the AI returns text outside the JSON object
- **THEN** the system SHALL reject the response and set analysis status to "failed"

### Requirement: Schema defines field-level validation rules

The system SHALL enforce the following JSON schema for AI responses:

- extracted_skills: array of strings, MAY be empty
- years_experience: integer, MUST be >= 0
- education_level: string, MAY be empty
- languages: array of strings, MAY be empty
- matching_score: integer, 0–100 inclusive
- strengths: array of strings, MAY be empty
- weaknesses: array of strings, MAY be empty
- missing_skills: array of strings, MAY be empty
- recommendation: string, MUST be one of: "interview", "pending", "reject"
- justification: string, MAY be empty

#### Scenario: All fields valid
- **WHEN** the AI returns all required fields with correct types and values within range
- **THEN** the system persists all fields and sets status to "completed"

#### Scenario: matching_score out of range
- **WHEN** the AI returns a matching_score less than 0 or greater than 100
- **THEN** the system rejects the response and sets status to "failed"

#### Scenario: Field has wrong type
- **WHEN** the AI returns a field with incorrect type (e.g., string instead of integer for matching_score)
- **THEN** the system rejects the response and sets status to "failed"

#### Scenario: recommendation has invalid value
- **WHEN** the AI returns a recommendation value not in ["interview", "pending", "reject"]
- **THEN** the system rejects the response and sets status to "failed"

#### Scenario: Missing required field
- **WHEN** the AI response is missing any required schema field
- **THEN** the system rejects the response and sets status to "failed"

#### Scenario: Extra unexpected fields are ignored
- **WHEN** the AI returns additional fields beyond the defined schema
- **THEN** the system SHALL ignore extra fields and process the valid schema fields

### Requirement: Analysis status follows lifecycle flow

The system SHALL manage analysis status through a defined lifecycle: pending (initial), processing (job running), completed (success), or failed (error). Each status transition SHALL be explicit and driven by the queue job.

#### Scenario: Initial status is pending
- **WHEN** a candidate analysis is created
- **THEN** the analysis status SHALL be set to "pending"

#### Scenario: Status set to processing when job starts
- **WHEN** the AnalyseCandidateJob begins execution
- **THEN** the analysis status SHALL be updated to "processing"

#### Scenario: Status set to completed after success
- **WHEN** the AI returns a valid response and all fields are persisted
- **THEN** the analysis status SHALL be set to "completed"

#### Scenario: Status set to failed on invalid response
- **WHEN** the AI returns a response that fails schema validation
- **THEN** the analysis status SHALL be set to "failed"

#### Scenario: Status set to failed on job exception
- **WHEN** the AnalyseCandidateJob throws an unexpected exception
- **THEN** the analysis status SHALL be set to "failed"

### Requirement: Queue integration defines async processing behavior

The system SHALL process analyses asynchronously via Laravel Queue Jobs. Status transitions MUST be managed exclusively by the queue job during execution.

#### Scenario: Analysis job dispatched on submission
- **WHEN** a candidate is submitted for analysis
- **THEN** the system dispatches an AnalyseCandidateJob to the queue

#### Scenario: Job updates status to processing
- **WHEN** the AnalyseCandidateJob starts executing
- **THEN** the job MUST set status to "processing" before making the AI call

#### Scenario: Job sets completed after successful persistence
- **WHEN** the AnalyseCandidateJob validates and persists the AI response
- **THEN** the job MUST set status to "completed" after all fields are saved

#### Scenario: Job sets failed on validation error
- **WHEN** the AI response fails schema validation
- **THEN** the job MUST set status to "failed" without persisting structured fields

#### Scenario: Job sets failed on AI provider error
- **WHEN** the AI provider returns an error or times out
- **THEN** the job MUST set status to "failed" and store the raw error if available

### Requirement: Retry behavior for transient failures

The system MAY retry AI processing for transient failures. Persistent validation errors MUST NOT be retried. Retry strategy is handled at the application configuration level.

#### Scenario: Retry on transient AI failure
- **WHEN** the AI provider returns a transient error (timeout, network issue)
- **THEN** the job MAY be retried according to application retry configuration

#### Scenario: No retry on validation failure
- **WHEN** the AI response fails schema validation
- **THEN** the job MUST NOT be retried and status SHALL remain "failed"

#### Scenario: Retry count and backoff are configurable
- **WHEN** the application is deployed
- **THEN** the number of retries and backoff strategy SHALL be defined in queue configuration

### Requirement: Raw AI response is stored separately

The system SHALL store the raw AI response in a payload field for debugging purposes. The raw response SHALL be persisted regardless of validation result.

#### Scenario: Valid response stored
- **WHEN** the AI returns a valid response
- **THEN** the raw JSON string SHALL be stored in the payload field alongside the structured fields

#### Scenario: Invalid response stored
- **WHEN** the AI returns an invalid response
- **THEN** the raw JSON string SHALL be stored in the payload field, but the structured fields SHALL NOT be persisted and status SHALL be set to "failed"

## MODIFIED Requirements

### Requirement: AI returns structured analysis with schema validation

The system SHALL enforce a strict JSON schema for AI responses. The schema validation SHALL check field types, value ranges, and enum values before persisting any analysis data.

#### Scenario: Valid AI response
- **WHEN** the AI returns all required fields with correct types and values within range
- **THEN** the system persists all fields and sets status to "completed"

#### Scenario: matching_score out of range
- **WHEN** the AI returns a matching_score outside 0-100
- **THEN** the system rejects the response and sets status to "failed"

#### Scenario: recommendation has invalid value
- **WHEN** the AI returns a recommendation not in (interview, pending, reject)
- **THEN** the system rejects the response and sets status to "failed"

#### Scenario: Missing required field
- **WHEN** the AI response is missing a required schema field
- **THEN** the system rejects the response and sets status to "failed"

#### Scenario: Field type mismatch
- **WHEN** the AI returns a field with incorrect type
- **THEN** the system rejects the response and sets status to "failed"

#### Scenario: Raw payload stored on invalid response
- **WHEN** the AI returns an invalid response
- **THEN** the raw response is stored in payload but structured fields are not persisted
