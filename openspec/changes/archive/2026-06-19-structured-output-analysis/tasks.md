## 1. Schema Definition

- [x] 1.1 Create `App\Data\CandidateAnalysisSchema` Laravel AI SDK structured output class with all 10 fields and strict types
- [x] 1.2 Define field-level validation rules (matching_score 0-100, recommendation enum, array types, integer types, string types)

## 2. Job Integration

- [x] 2.1 Update `AnalyseCandidateJob` to use `CandidateAnalysisSchema` structured output class for AI response parsing
- [x] 2.2 Add post-parsing range validation for matching_score (0-100) and recommendation enum values
- [x] 2.3 Ensure raw AI response payload is stored before validation failure for debugging
- [x] 2.4 Implement lifecycle status transitions: set "processing" on job start, "completed" on success, "failed" on error
- [x] 2.5 Configure retry behavior: retries for transient errors, no retry for validation failures (use non-retryable exception)

## 3. Testing

- [x] 3.1 Write tests for valid AI response with all correct field types and values
- [x] 3.2 Write tests for matching_score out of range rejection
- [x] 3.3 Write tests for recommendation invalid value rejection
- [x] 3.4 Write tests for missing required field rejection
- [x] 3.5 Write tests for extra unexpected fields being ignored
- [x] 3.6 Write tests for raw payload being stored on invalid response
- [x] 3.7 Write tests for lifecycle status transitions (pending → processing → completed/failed)
- [x] 3.8 Write tests for retry behavior (transient error retry, validation error no retry)

## 4. Final Checks

- [x] 4.1 Run full test suite and fix any failures
- [x] 4.2 Run `vendor/bin/pint --format agent` to fix code style
