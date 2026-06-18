## 1. Models & Migrations

- [x] 1.1 Create `Candidate` model with fillable fields (`name`, `cv_text`) and `hasMany Analysis` relationship
- [x] 1.2 Create `create_candidates_table` migration with columns: `id`, `name`, `cv_text` (text), `timestamps`
- [x] 1.3 Update `Analysis` model: add fillable fields (all schema fields), `casts` (payload array, extracted_skills array, languages array, strengths array, weaknesses array, missing_skills array, recommendation enum), `status` enum cast, `belongsTo Candidate` relationship
- [x] 1.4 Create `update_analyses_table` migration: add columns `candidate_id` (FK), `payload` (nullable json), `extracted_skills` (nullable json), `years_experience` (nullable integer), `education_level` (nullable string), `languages` (nullable json), `matching_score` (nullable integer), `strengths` (nullable json), `weaknesses` (nullable json), `missing_skills` (nullable json), `recommendation` (nullable string), `justification` (nullable text), `status` (string, default 'pending')
- [x] 1.5 Create `Recommendation` enum with cases: Interview, Pending, Reject
- [x] 1.6 Create `AnalysisStatus` enum with cases: Pending, Processing, Completed, Failed
- [x] 1.7 Run migrations

## 2. Service Layer

- [x] 2.1 Create `CandidateAnalysisService` with method: `submit(int $userId, array $data)` that creates Candidate, creates Analysis with status "pending", dispatches AnalyseCandidateJob, returns analysis
- [x] 2.2 Create method `find(int $userId, int $analysisId)` that scopes analysis to user's job offers
- [x] 2.3 Create method `listByOffer(int $userId, int $offerId)` that returns analyses sorted by matching_score descending

## 3. Form Request

- [x] 3.1 Create `StoreCandidateRequest` with rules: `candidate_name` (required, string, max:255), `cv_text` (required, string, min:1), `job_offer_id` (required, integer, exists:job_offers,id)

## 4. Queue Job

- [x] 4.1 Create `AnalyseCandidateJob` that receives analysis ID, updates status to "processing", calls AI with job offer requirements and candidate CV, validates AI response against schema, persists results or marks as failed on error
- [x] 4.2 Handle timeout in job configuration

## 5. Controller

- [x] 5.1 Create `CandidateAnalysisController` with methods: `create` (show submission form with job offers list), `store` (validate, call service, redirect), `show` (display analysis detail)
- [x] 5.2 Apply authorization via policy in controller

## 6. Routes

- [x] 6.1 Add web routes: `GET /offers/{offer}/candidates/create` (submission form), `POST /offers/{offer}/candidates` (store submission), `GET /analyses/{analysis}` (show analysis detail)

## 7. Views

- [x] 7.1 Create `candidates/create.blade.php` — form with job offer selector, candidate name input, CV textarea
- [x] 7.2 Create `analyses/show.blade.php` — full detail view with all AI fields, candidate info, matching score visualization, recommendation badge

## 8. Policy & Authorization

- [x] 8.1 Create `AnalysisPolicy` with `view` method checking job offer ownership
- [x] 8.2 Register policy in `AppServiceProvider`

## 9. Factories & Seeders

- [x] 9.1 Create `CandidateFactory` with name and CV text defaults
- [x] 9.2 Update `AnalysisFactory` to include schema fields and status
- [x] 9.3 Update `DatabaseSeeder` to create sample analyses

## 10. Tests

- [x] 10.1 Write tests for candidate submission (success, validation errors, non-existent offer, unauthorized offer)
- [x] 10.2 Write tests for analysis detail view (own analysis, another user's analysis, pending status, failed status)
- [x] 10.3 Write tests for candidate ranking by matching score
- [x] 10.4 Write tests for AnalyseCandidateJob (successful AI response, invalid AI response, AI timeout)
- [x] 10.5 Run tests and fix any failures

## 11. Final Checks

- [x] 11.1 Run `vendor/bin/pint --format agent` to fix code style
- [x] 11.2 Run full test suite to verify nothing is broken
- [x] 11.3 Run `php artisan route:list` to verify all routes registered
