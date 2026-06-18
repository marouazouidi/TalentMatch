## Why

HR agents need to submit candidate CVs for AI-powered analysis against job offers. Without this feature, the platform is limited to job offer management with no recruitment intelligence. This is the core value proposition of TalentMatch — automated candidate pre-screening.

## What Changes

- Create `Candidate` model and migration for storing candidate name and CV text
- Create `Analysis` model updates with AI analysis schema fields, status tracking, and payload storage
- Create `AnalyseCandidateJob` that dispatches AI analysis asynchronously
- Create `CandidateAnalysisService` for AI interaction with structured output validation
- Add `candidate-submission` form request with validation rules
- Create `CandidateAnalysisController` with submission, detail view, and ranking endpoints
- Create Blade views for candidate submission form and analysis detail display
- Add web routes for candidate submission and analysis viewing
- Add authorization policy scoping analyses to the owning user's job offers
- Create feature tests covering submission, analysis viewing, ranking, validation, and authorization

## Capabilities

### New Capabilities

- `candidate-analysis`: Submit candidates with CV text for AI analysis against a job offer, view analysis results with AI-generated scores and recommendations, and rank candidates by matching score.

### Modified Capabilities

- `job-offer-management`: Add analyzed candidate count to list view (already implemented).

## Impact

- **Models**: New `Candidate` model; update `Analysis` model with schema fields and status
- **Database**: New `candidates` migration; update `analyses` migration with schema fields
- **Controllers**: New `CandidateAnalysisController`
- **Services**: New `CandidateAnalysisService`
- **Jobs**: New `AnalyseCandidateJob`
- **Queue**: Database queue driver required for async processing
- **Routes**: New web routes for candidate submission and analysis viewing
- **Views**: New candidate submission form and analysis detail views
- **Authorization**: New policy scoping analyses to user's job offers
- **Validation**: New form request for candidate submission
- **Dependencies**: `laravel/ai` package for structured AI output
- **Tests**: New feature tests for the full submission-to-analysis flow
