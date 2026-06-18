# AGENTS.md

## Project Overview

Project Name: TalentMatch

TalentMatch is an AI-powered recruitment pre-screening platform built with Laravel.

The application allows HR agents to:

* Create and manage job offers
* Submit candidate CVs as text
* Automatically analyze candidates using AI
* Generate structured candidate evaluations
* Calculate a matching score between candidate and job offer
* Receive AI recommendations
* Interact with an AI assistant that can explain analyses, compare candidates, and maintain conversation context

The project follows a service-oriented architecture and uses Laravel AI SDK features such as structured outputs, tools/function calling, and conversation memory.

---

# Technical Stack

## Backend

* Laravel 13
* PHP 8.3+
* MySQL
* Laravel Breeze (Authentication)
* Laravel Queue
* Laravel Jobs
* Laravel AI SDK
* Laravel Debugbar

## Development Workflow

* Git Flow
* OpenSpec 
* AGENTS.md
* Feature branches
* Daily commits with AI usage mention

---

# Core Features

## Authentication

Users can:

* Register
* Login
* Logout

Each user owns their job offers and candidate analyses.

---

## Job Offers Management

Users can:

* Create job offers
* View all their offers
* Update offers
* Delete offers
* View offer details

Each offer contains:

* Title
* Description
* Required skills
* Minimum experience

---

## Candidate Submission

Users can:

* Submit candidate name
* Submit candidate CV as plain text
* Launch AI analysis

---

## AI Candidate Analysis

The AI must compare:

* Candidate CV
* Job Offer Requirements

The AI generates a structured JSON response.

Required schema:

```json
{
  "competences_extraites": [],
  "annees_experience": 0,
  "niveau_etudes": "",
  "langues": [],
  "matching_score": 0,
  "points_forts": [],
  "lacunes": [],
  "competences_manquantes": [],
  "recommandation": "convoquer",
  "justification": ""
}
```

The JSON schema must always be respected.

No free-form responses are allowed.

---

# AI Agent Responsibilities

The AI agent must:

* Explain candidate scores
* Explain recommendations
* Suggest interview questions
* Compare candidates
* Retrieve real data using tools
* Maintain conversation memory

The agent must never invent data.

All answers must be grounded in database information.

---

# Mandatory Tools

The AI agent must use the following tools.

## GetCandidateAnalysisTool

Purpose:

Retrieve a complete candidate analysis from the database.

Input:

```php
candidateAnalysisId
```

Output:

Candidate analysis object.

---

## GetJobRequirementsTool

Purpose:

Retrieve job offer requirements.

Input:

```php
jobOfferId
```

Output:

Job offer object.

---

## CompareCandidatesTool

Purpose:

Compare two candidates analyzed for the same job offer.

Input:

```php
candidateAnalysisId1
candidateAnalysisId2
```

Output:

Comparison result.

---

# Conversation Memory

Conversation history must be persisted.

The assistant must remember:

* Previous questions
* Previous answers
* Current candidate context
* Current job offer context

The assistant must not behave statelessly.

---

# Database Structure

## users

Stores HR accounts.

## job_offers

Stores job opportunities.

## candidates

Stores submitted candidates.

## candidate_analyses

Stores AI analysis results.

Acts as the relationship between:

* Job Offer
* Candidate

Contains:

* Matching score
* Strengths
* Weaknesses
* Missing skills
* Recommendation
* Justification

## conversations

Stores chat sessions.

## messages

Stores conversation messages.

---

# Application Architecture

Controllers must remain thin.

Business logic must never be placed inside controllers.

Controllers should only:

* Validate requests
* Call services
* Return responses

---

# Service Layer

All business logic belongs inside Services.

Examples:

```text
CandidateAnalysisService
ChatService
ComparisonService
```

Services are responsible for:

* AI interactions
* Data transformations
* Business rules

---

# Queue System

AI analysis must run asynchronously.

Never call AI directly inside controllers.

Required flow:

Submit CV
→ Dispatch Job
→ Queue
→ AI Analysis
→ Save Analysis

Use:

```php
AnalyseCandidateJob
```

---

# Validation Rules

Use Form Requests for all incoming data.

Required requests:

```text
StoreJobOfferRequest
UpdateJobOfferRequest
StoreCandidateRequest
ChatRequest
```

Validation must never be placed directly in controllers.

---

# Eloquent Standards

Use Eloquent relationships.

Avoid raw SQL whenever possible.

Use eager loading to prevent N+1 queries.

Example:

```php
JobOffer::with('analyses')->get();
```

Never perform database queries inside loops.

---

# Eloquent Casts

Required casts:

```php
required_skills => array
competences_extraites => array
langues => array
points_forts => array
lacunes => array
competences_manquantes => array
```

Recommendation should use Enum.

---

# Routing

The application uses web routes with Blade views.

All routes are defined in `routes/web.php` using Laravel Breeze authentication.

---

# Error Handling

Handle the following cases:

* Empty CV
* Missing job requirements
* Invalid AI response
* Missing candidate
* Missing offer
* Queue failures

Never expose internal exceptions to clients.

---

# Security Rules

All routes must be protected using authentication middleware except:

```text
login
register
```

Users must access only their own data.

Authorization checks are mandatory.

---

# Code Quality Rules

* Follow PSR-12
* Use meaningful class names
* Use typed properties
* Use typed return values
* Prefer dependency injection
* Keep methods small and focused

Maximum responsibility per class.

---

# Folder Structure

```text
app/
├── Agents
├── Services
├── Jobs
├── Tools
├── Models
├── Http
│   ├── Controllers
│   └── Requests
├── Enums
└── Policies
```

---

# Git Workflow

Use feature branches:

```text
feature/auth
feature/offres-crud
feature/candidates
feature/analyse-ia
feature/agent-conversationnel
```

Commit frequently.

Commit messages must mention AI usage when applicable.

Examples:

```text
feat(ai): implement candidate analysis service using structured output

feat(agent): add candidate comparison tool

refactor(ai): improve matching score prompt

fix(queue): handle invalid AI response
```

---

# Definition of Done

A feature is considered complete when:

* Code is tested manually
* Validation is implemented
* Authorization is implemented
* No N+1 queries
* Proper service layer is used
* Proper Form Requests are used
* Documentation is updated
* Commit is pushed

---

# Non-Goals

The AI must not:

* Invent candidate information
* Invent scores
* Bypass tools
* Ignore memory
* Store invalid JSON
* Execute business logic in controllers
* Run long AI operations synchronously

Always prioritize correctness, maintainability, and traceability.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.


## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>
