## Context

TalentMatch currently supports job offer management (CRUD). The next milestone is AI-powered candidate pre-screening. An authenticated HR user selects a job offer, enters a candidate name and CV text, and the system asynchronously analyzes the candidate against the job offer using AI. The analysis result is persisted with a structured schema and displayed to the user.

The `Analysis` model exists minimally with only `job_offer_id`. It must be expanded to include all schema fields and status tracking. A `Candidate` model must be created.

## Goals / Non-Goals

**Goals:**
- Candidate submission form with name and CV text, linked to a job offer
- Asynchronous AI analysis via Laravel queue (database driver)
- Structured AI response with validation (schema enforcement, retry on invalid)
- Analysis detail view with all AI-generated fields
- Candidate ranking by matching score (descending) per job offer
- Authorization scoping analyses to the owning user's job offers
- Status tracking: pending → processing → completed / failed

**Non-Goals:**
- Conversational agent or candidate comparison (future change)
- Multiple CV formats or file uploads (text-only for now)
- Batch candidate submission
- Real-time progress via WebSockets or polling (simple page reload)
- AI provider configuration UI (hard-coded for now)

## Decisions

1. **Separate Candidate model** rather than embedding in Analysis. Candidates can exist independently and be re-analyzed against different offers in the future.

### Database Relationships

```
Candidate
  ├── id (PK)
  ├── name
  ├── cv_text
  └── hasMany Analysis

Analysis
  ├── id (PK)
  ├── job_offer_id (FK → job_offers.id)
  ├── candidate_id (FK → candidates.id)
  ├── payload (nullable JSON)
  ├── extracted_skills (nullable JSON)
  ├── years_experience (nullable integer)
  ├── education_level (nullable string)
  ├── languages (nullable JSON)
  ├── matching_score (nullable integer)
  ├── strengths (nullable JSON)
  ├── weaknesses (nullable JSON)
  ├── missing_skills (nullable JSON)
  ├── recommendation (nullable string)
  ├── justification (nullable text)
  ├── status (string, default: pending)
  ├── belongsTo JobOffer
  └── belongsTo Candidate

JobOffer
  └── hasMany Analysis
```

**Entity Relationship Summary:**

- `Candidate` **hasMany** `Analysis` — one candidate can be analyzed against multiple job offers
- `Analysis` **belongsTo** `Candidate` — each analysis is linked to exactly one candidate
- `JobOffer` **hasMany** `Analysis` — one offer can receive multiple candidate analyses
- `Analysis` **belongsTo** `JobOffer` — each analysis targets exactly one job offer
- `Analysis` is always linked to both a `Candidate` and a `JobOffer`; neither foreign key may be null

### Migration: Add candidate_id to analyses

A new migration (`update_analyses_table`) must add `candidate_id` as a foreign key:

- `$table->foreignId('candidate_id')->constrained()->cascadeOnDelete()`

The existing `job_offer_id` foreign key is already present from the job-offers-crud change.

### Model Relationships

Candidate model:
- `$this->hasMany(Analysis::class)`

Analysis model (updates):
- `$this->belongsTo(Candidate::class)`
- `$this->belongsTo(JobOffer::class)` — already exists

2. **Status column on Analysis** with enum: `pending`, `processing`, `completed`, `failed`. The UI checks status and shows appropriate messaging. Queue job updates status as it progresses.

3. **AnalyseCandidateJob** handles the full async flow: mark processing, call AI, validate response, save results, handle failures. This keeps the controller thin.

4. **Structured AI output via `laravel/ai`** SDK with response validation using a schema definition. Invalid responses (mismatched types, missing fields, out-of-range values) cause the analysis to fail with a stored error.

5. **Database queue driver** matches the existing Laravel setup. No Redis dependency needed at this stage.

6. **matching_score stored as integer** (0-100) with validation on save. The AI schema enforces this at the output level.

7. **recommendation uses enum**: `interview`, `pending`, `reject`. Stored as string column with a cast to the enum type in the model.

8. **Blade views** consistent with existing Breeze styling: a submission form (select offer, enter name, paste CV text) and an analysis detail view.

## Risks / Trade-offs

- **AI timeout**: LLM calls may hang. Mitigation → Job timeout configuration; failed status after timeout.
- **Invalid AI response**: AI may return malformed JSON or out-of-schema data. Mitigation → Schema validation before persistence; failed analysis on mismatch.
- **Empty CV**: AI may produce nonsensical results on minimal input. Mitigation → Front-end validation requires non-empty CV; handle gracefully on analysis page.
- **Duplicate candidate submissions**: Same candidate may be submitted multiple times. Mitigation → No deduplication for now; user can see all submissions.
- **Queue failure**: Worker may crash mid-analysis. Mitigation → Failed jobs table for retry/debug; status stuck at "processing" requires manual intervention.
- **No real-time feedback**: User must refresh to see analysis result. Mitigation → Acceptable for initial scope; polling can be added later.
