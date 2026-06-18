## ADDED Requirements

### Requirement: User can submit a candidate for analysis

The system SHALL allow an authenticated user to submit a candidate name and CV text for AI analysis against a specific job offer they own. A Candidate record SHALL be created and linked to an Analysis record with status "pending".

#### Scenario: Successful candidate submission
- **WHEN** an authenticated user submits a candidate name, CV text, and selects a valid job offer they own
- **THEN** the system creates a Candidate record, creates an Analysis record with status "pending", dispatches an async analysis job, and redirects with a success message

#### Scenario: Submission with missing candidate name
- **WHEN** an authenticated user submits without a candidate name
- **THEN** the system returns a validation error and does not create the candidate or analysis

#### Scenario: Submission with empty CV text
- **WHEN** an authenticated user submits with empty CV text
- **THEN** the system returns a validation error and does not create the candidate or analysis

#### Scenario: Submission for non-existent job offer
- **WHEN** an authenticated user submits for a job offer that does not exist
- **THEN** the system returns a 404 response and does not create the candidate or analysis

#### Scenario: Submission for another user's job offer
- **WHEN** an authenticated user submits for a job offer owned by another user
- **THEN** the system returns a 403 Forbidden response and does not create the candidate or analysis

### Requirement: Candidate information is persisted

The system SHALL persist candidate information independently from the AI analysis.

#### Scenario: Candidate record created
- **WHEN** a candidate is submitted
- **THEN** the system stores the candidate name and CV text in the candidates table

#### Scenario: Multiple analyses for the same candidate
- **WHEN** the same candidate is analyzed against different job offers
- **THEN** each analysis is stored separately and linked to the appropriate job offer

### Requirement: AI analysis runs asynchronously via queue

The system SHALL dispatch an AnalyseCandidateJob when a candidate is submitted. The job SHALL update the analysis status through pending, processing, completed, or failed states.

#### Scenario: Successful analysis
- **WHEN** the AnalyseCandidateJob runs and the AI returns a valid structured response
- **THEN** the analysis status is set to "completed", all AI fields are persisted, and the analysis is available for viewing

#### Scenario: AI returns invalid response
- **WHEN** the AnalyseCandidateJob runs and the AI returns a response outside the defined schema
- **THEN** the analysis status is set to "failed", the raw payload MAY be stored for debugging, and the analysis is not marked complete

#### Scenario: AI timeout
- **WHEN** the AnalyseCandidateJob runs and the AI provider times out
- **THEN** the analysis status is set to "failed" and an appropriate error is recorded

#### Scenario: Queue failure
- **WHEN** the AnalyseCandidateJob throws an unexpected exception
- **THEN** the analysis status is set to "failed" and the job is released to the failed jobs table for retry

### Requirement: AI returns structured analysis with schema validation

The AI SHALL return a JSON response conforming to a defined schema. The system SHALL validate the response against the schema and reject any response that does not match.

Schema fields:
- extracted_skills (array of strings)
- years_experience (integer)
- education_level (string)
- languages (array of strings)
- matching_score (integer, 0-100)
- strengths (array of strings)
- weaknesses (array of strings)
- missing_skills (array of strings)
- recommendation (enum: interview, pending, reject)
- justification (string)

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

### Requirement: User can view analysis details

The system SHALL display a complete analysis page showing the candidate name, job offer title, matching score, recommendation, extracted skills, languages, years of experience, strengths, weaknesses, missing skills, and justification.

#### Scenario: Viewing own analysis
- **WHEN** an authenticated user navigates to an analysis detail page for a job offer they own
- **THEN** the system displays all analysis fields including candidate name, matching score, recommendation, extracted skills, languages, strengths, weaknesses, missing skills, and justification

#### Scenario: Viewing another user's analysis
- **WHEN** an authenticated user attempts to view an analysis for a job offer owned by another user
- **THEN** the system returns a 403 Forbidden response

#### Scenario: Viewing analysis with pending status
- **WHEN** an authenticated user views an analysis with status "pending"
- **THEN** the system displays a message that analysis is in progress

#### Scenario: Viewing analysis with failed status
- **WHEN** an authenticated user views an analysis with status "failed"
- **THEN** the system displays a message that analysis failed and optionally a retry option

### Requirement: User can view candidates ranked by matching score

The system SHALL allow sorting analyses by matching score in descending order per job offer.

#### Scenario: Ranking analyses
- **WHEN** an authenticated user views analyses for a job offer they own
- **THEN** the system displays analyses ordered by matching_score descending

#### Scenario: Ranking with equal scores
- **WHEN** multiple analyses have the same matching score
- **THEN** the system orders them by creation date descending as a secondary sort
