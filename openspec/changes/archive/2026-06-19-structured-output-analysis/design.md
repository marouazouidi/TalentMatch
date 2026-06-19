## Context

The AnalyseCandidateJob currently attempts to parse AI responses into a structured format but lacks a formal, codified schema contract. Validation rules are ad-hoc, and error handling for mismatched types, out-of-range values, and extra fields is inconsistent. This design formalizes the contract with a Laravel AI SDK structured output definition, ensuring deterministic parsing and validation.

## Goals / Non-Goals

**Goals:**
- Define a formal Laravel AI SDK structured output schema class
- Implement field-level type, range, and enum validation
- Store raw AI response payload for debugging
- Ignore unexpected extra fields from AI responses
- Codify analysis lifecycle with proper status transitions
- Define retry behavior separating transient from validation errors
- Ensure idempotency — no duplicate analyses for same candidate + offer
- Guarantee concurrency safety in job processing
- Handle AI timeouts gracefully with deterministic failure
- Introduce schema versioning for backward-compatible evolution
- Maintain backward compatibility with existing Analysis model fields

**Non-Goals:**
- Changing the Analysis model schema or database migrations
- Modifying the AI prompt logic
- Adding new AI providers or SDKs

## Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Schema definition | Laravel AI SDK `StructuredOutput` with class | Leverages existing SDK, avoids custom validation code, native schema enforcement |
| Invalid response handling | Set status to "failed", store raw payload | Preserves debugging data without corrupting structured fields |
| Extra fields handling | Ignore silently | Prevents breaking on minor AI response changes; schema class handles this via strict typing |
| Validation timing | Inside AnalyseCandidateJob before persist | Single responsibility: job validates and persists in one atomic unit |
| Lifecycle management | Status transitions in AnalyseCandidateJob | Job owns lifecycle — updates to processing on start, completed on success, failed on error |
| Retry strategy | Laravel job retry with `$tries` and `backoff` | Transient errors retry automatically; validation failures throw non-retryable exception |
| Idempotency | Unique index on (candidate_id, job_offer_id) + firstOrCreate | Prevents duplicate analysis records at database level; idempotent job logic via atomic status check |
| Concurrency safety | Database-level row lock (lockForUpdate) on status check | Prevents race conditions when multiple workers pick up the same job; status update uses WHERE clause to avoid overwrites |
| Timeout handling | Job `$timeout` property + AI client timeout | Laravel kills timed-out jobs; AI client timeout releases connection early; both paths set status to "failed" |
| Schema versioning | Version field in structured output class | Enables forward compatibility — old versions are accepted, new unknown versions are logged and processed with best-effort parsing |

## Additional Design Specifications

### Idempotency

AnalyseCandidateJob MUST be idempotent: re-executing the same job MUST NOT create duplicate records or overwrite valid data.

#### Detection Strategy
- A unique database index on `(candidate_id, job_offer_id)` in the analyses table prevents duplicate creation
- On job start, atomically check current status before processing — if status is already "completed", skip execution
- The `CandidateAnalysisService::submit()` method SHALL use `firstOrCreate` to prevent duplicate submission at the service layer

#### Edge Cases
- If a job retries after a transient failure, the existing analysis record is reused (not duplicated)
- If duplicate submission is attempted, the service returns the existing analysis record with a meaningful error or redirect

### Concurrency Safety

Multiple queue workers MAY pick up the same analysis job. The system MUST prevent race conditions.

#### Locking Strategy
- When the job starts and sets status to "processing", use a database row lock (`SELECT ... FOR UPDATE`) to prevent concurrent status overwrites
- Status updates SHALL use `where('status', 'processing')->update(...)` to ensure only the expected transition occurs
- If a stale job attempts to update a completed analysis, the WHERE clause prevents the overwrite

#### Failure Scenarios
- Two workers both read "pending" simultaneously: the first to acquire the row lock succeeds; the second's update affects zero rows and is safely ignored
- A worker crashes after setting "processing": the job retries (per retry config) and checks current status — if still "processing" from crashed worker, it resumes

### Timeout Handling

AI calls and job execution MUST have defined timeout boundaries.

#### Configuration
- AnalyseCandidateJob `$timeout = 120` seconds (AI response window)
- AI client HTTP timeout set to 90 seconds (slightly under job timeout to surface failure before job kill)
- Job `$maxExceptions` allows Laravel to retry on timeout exceptions up to configured `$tries`

#### Behavior
- If the AI provider exceeds 90 seconds: the HTTP client throws, the job catches and sets status to "failed"
- If the job exceeds 120 seconds: Laravel kills the process and releases to retry; subsequent attempts also time out and exhaust retries, leaving status as "failed"
- On timeout, the raw error message is stored in the payload field

### Schema Versioning

The structured output contract SHALL include a version field to support future schema evolution.

#### Schema Contract
- Add a `schema_version` field (integer) to the `CandidateAnalysisSchema` class, initially set to `1`
- The version is validated as required and must match an expected version
- When the schema evolves, increment the version and update validation accordingly

#### Backward Compatibility
- Old analyses with `schema_version: 1` remain valid and viewable — versioning is metadata, not a data migration
- When the job encounters an unknown version, it logs a warning and attempts best-effort parsing with the current schema
- Schema version is stored as part of the analysis payload for debugging

#### Evolution Rules
- Adding new optional fields: bump minor (conceptual), old versions still parse
- Changing field types or removing fields: bump major, old analyses still display as-is
- Schema version is independent of the Analysis model migration version

### Observability

The system SHALL log key events for debugging and monitoring.

#### Logged Events
- AI raw response body BEFORE validation (at DEBUG level, truncated to 4KB to avoid log flooding)
- Validation failures with specific reason (field name, expected type/range, actual value)
- Retry attempts with attempt number and remaining retries
- Duplicate detection events (existing analysis returned)
- Timeout events with elapsed time and provider endpoint

#### Log Format
- All logs SHALL include `analysis_id` and `job_offer_id` as structured context for filtering
- Use Laravel's logging system with appropriate channels (daily log file in production)
- Sensitive candidate data (CV text) MUST NOT be logged; only the AI response and metadata are logged

## Risks / Trade-offs

| Risk | Mitigation |
|------|------------|
| AI SDK StructuredOutput class may not support all validation rules (e.g., matching_score 0-100 range) | Implement post-parsing validation in job for range checks |
| Schema changes require code deployment | Keep schema definition in a dedicated class for single-point updates |
| Unique index on (candidate_id, job_offer_id) could cause constraint violations under high concurrency | Use `firstOrCreate` — the unique constraint is a safety net, not the primary coordination mechanism |
| Row lock (`SELECT ... FOR UPDATE`) blocks other queries on the same analysis row | Lock contention is minimal since an analysis is processed once; accept brief blocking for correctness |
| Schema versioning adds complexity to the validation pipeline | Version check is a single integer comparison — negligible overhead |
| Logging AI raw response at DEBUG level may include PII or sensitive data | Log only the structured JSON fields, not raw CV text; cap response size at 4KB |
- **Open Question**: Should the CV text be hashed or anonymized before storage?

## Migration Plan

1. Create `App\Data\CandidateAnalysisSchema` structured output class with `schema_version` field
2. Update `AnalyseCandidateJob` to use the schema class with version validation
3. Add post-parsing range validation for matching_score
4. Ensure raw payload is stored before validation failure
5. Implement lifecycle status transitions in job (processing → completed/failed)
6. Configure retry behavior (retry on transient, no retry on validation failure)
7. Add unique index migration for (candidate_id, job_offer_id) in analyses table (if not already present)
8. Implement idempotency in `CandidateAnalysisService::submit()` using `firstOrCreate`
9. Add row lock (`lockForUpdate`) on status check in job start
10. Set job `$timeout = 120` and AI client HTTP timeout = 90 seconds
11. Add structured logging at each lifecycle stage (response, validation, retry, timeout)
12. Update tests to cover idempotency, concurrency, timeouts, schema versioning, and observability
