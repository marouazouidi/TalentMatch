## Why

HR agents need the ability to create, manage, and organize job offers before they can submit candidates for AI analysis. Without a job offer management system, the core recruitment workflow cannot function. This is the foundational feature that enables all downstream capabilities — candidate submission, AI analysis, comparison, and chat.

## What Changes

- Create `JobOffer` model, migration, and factory
- Add `JobOfferService` for all CRUD business logic
- Add `JobOfferController` with thin CRUD methods
- Add `StoreJobOfferRequest` and `UpdateJobOfferRequest` form requests
- Add `JobOfferPolicy` for user-level authorization (users own their offers)
- Add web routes for job offers
- Create Blade views: offer list, create form, show detail, edit form
- Add `Navigation` link for Job Offers
- Add `JobOfferFactory` and update `DatabaseSeeder`
- Add feature tests for all CRUD operations

## Capabilities

### New Capabilities
- `job-offer-management`: Full CRUD for job offers including creation, listing, viewing details, updating, and deletion — scoped per authenticated user.

### Modified Capabilities
None — this is the first feature built on top of authentication.

## Impact

- **Models**: New `app/Models/JobOffer.php` with `belongsTo(User)` and `hasMany(Analysis)` relationships
- **Database**: New `job_offers` migration with `required_skills` cast as array
- **Controllers**: New `app/Http/Controllers/JobOfferController.php`
- **Services**: New `app/Services/JobOfferService.php` — first service class
- **Routes**: New RESTful routes in `routes/web.php`
- **Views**: New `resources/views/offers/` directory with 4 Blade views
- **Authorization**: New `app/Policies/JobOfferPolicy.php`
- **Validation**: New form requests in `app/Http/Requests/`
- **Tests**: New `tests/Feature/JobOfferTest.php`
- **Navigation**: Updated `navigation.blade.php` with Job Offers link
