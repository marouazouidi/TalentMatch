## Context

The project currently has only Laravel Breeze authentication scaffolded. No job offer feature exists. Job offers are the foundational entity — all downstream features (candidate submission, AI analysis, comparison, chat) depend on them. This design covers the full CRUD lifecycle of job offers scoped to authenticated users.

## Goals / Non-Goals

**Goals:**
- Full web CRUD for job offers (create, read, update, delete)
- Each user owns and accesses only their own offers
- Thin controllers delegating to a service layer
- Form request validation for all inputs
- Blade UI consistent with Breeze styling (TailwindCSS)
- Feature tests covering all CRUD operations and authorization

**Non-Goals:**
- AI analysis integration (future change)
- Candidate submission (future change)
- Pagination or search (can be added later)
- Soft deletes or restore (simple hard delete for now)

## Decisions

1. **Service Layer Pattern**: All CRUD business logic lives in `JobOfferService`, not the controller. Controllers only validate requests, call the service, and return responses. This follows the project convention established in AGENTS.md.

2. **Form Requests**: `StoreJobOfferRequest` and `UpdateJobOfferRequest` handle validation independently. This keeps validation rules centralized and testable. Rules: `title` (required, string, max:255), `description` (required, string), `required_skills` (required, array), `minimum_experience` (required, integer, min:0).

3. **Policy-based Authorization**: `JobOfferPolicy` gates all mutating operations (update, delete) using the `user_id` foreign key. `view` and `viewAny` are also gated. This aligns with Laravel conventions and keeps auth logic declarative.


5. **Blade Views**: Standard Breeze-styled views using TailwindCSS utility classes. Layout extends `layouts.app`. Forms use existing Breeze form components (`x-input`, `x-button`, etc.) for consistency.

6. **Database Casting**: `required_skills` column stores JSON in MySQL but is cast to `array` on the Eloquent model. This allows clean array manipulation in PHP.

## Risks / Trade-offs

- **No pagination**: Lists could grow large over time. Mitigation → Low risk at launch; pagination can be added later with minimal refactoring since the index view uses a simple loop.
- **Hard delete**: Offers with analyses would cascade-delete related records. Mitigation → The migration includes `foreignId('user_id')->constrained()->cascadeOnDelete()` for user deletes, but offers use hard delete. Soft deletes can be added later if needed.
- **No search/filter**: HR agents with many offers may struggle to find specific ones. Mitigation → Acceptable initial scope; search can be added as a follow-up feature.
- **Single table**: All offer data in one table is simple but could become a bottleneck. Mitigation → Appropriate for the expected scale of individual HR agents; normalization can be introduced if needed.
