## 1. Model & Migration

- [x] 1.1 Create `JobOffer` model with `fillable`, `casts` (`required_skills => array`), and relationships (`belongsTo User`, `hasMany Analysis`)
- [x] 1.2 Create `create_job_offers_table` migration with columns: `id`, `user_id` (foreign), `title`, `description`, `required_skills` (json), `minimum_experience` (integer), `timestamps`
- [x] 1.3 Run migration

## 2. Service Layer

- [x] 2.1 Create `app/Services/JobOfferService.php` with methods: `list()`, `find($id)`, `create($data)`, `update($id, $data)`, `delete($id)`
- [x] 2.2 All service methods scope queries to the authenticated user

## 3. Form Requests

- [x] 3.1 Create `StoreJobOfferRequest` with rules: `title` (required, string, max:255), `description` (required, string), `required_skills` (required, array), `minimum_experience` (required, integer, min:0)
- [x] 3.2 Create `UpdateJobOfferRequest` with same rules (all required since full resource replacement)

## 4. Controller

- [x] 4.1 Create `JobOfferController` with thin methods: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`
- [x] 4.2 Controller delegates all logic to `JobOfferService`, only handles request/response

## 5. Policy & Authorization

- [x] 5.1 Create `JobOfferPolicy` with `view`, `viewAny`, `create`, `update`, `delete` methods
- [x] 5.2 Register policy in `AppServiceProvider`
- [x] 5.3 Apply `authorize()` calls in controller and/or middleware

## 6. Routes

- [x] 6.1 Add web routes in `routes/web.php`: `GET /offers` (index), `GET /offers/create` (create), `POST /offers` (store), `GET /offers/{offer}` (show), `GET /offers/{offer}/edit` (edit), `PUT /offers/{offer}` (update), `DELETE /offers/{offer}` (destroy)

## 7. Views

- [x] 7.1 Create `resources/views/offers/index.blade.php` — table listing user's offers with create button
- [x] 7.2 Create `resources/views/offers/create.blade.php` — form with title, description, required_skills (multi-input), minimum_experience fields
- [x] 7.3 Create `resources/views/offers/show.blade.php` — full detail view with edit/delete actions
- [x] 7.4 Create `resources/views/offers/edit.blade.php` — pre-filled form for updating an offer
- [x] 7.5 Add "Job Offers" link to `resources/views/layouts/navigation.blade.php`

## 8. Factory & Seeder

- [x] 8.1 Create `JobOfferFactory` with sensible defaults using `User` relationship
- [x] 8.2 Update `DatabaseSeeder` to create sample job offers

## 9. Tests

- [x] 9.1 Create `tests/Feature/JobOfferTest.php` with tests for: creating offers, listing offers, viewing own offers, viewing others' offers (403), updating own offers, updating others' offers (403), deleting own offers, deleting others' offers (403), validation errors, unauthenticated access
- [x] 9.2 Run tests and fix any failures

## 10. Final Checks

- [x] 10.1 Run `vendor/bin/pint --format agent` to fix code style
- [x] 10.2 Run full test suite to verify nothing is broken
- [x] 10.3 Run `php artisan route:list` to verify all routes registered
