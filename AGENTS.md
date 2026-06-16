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

# API Standards

Use RESTful endpoints.

Examples:

```text
POST   /api/login
POST   /api/register

GET    /api/offers
POST   /api/offers

GET    /api/candidates

POST   /api/analyses

POST   /api/chat

POST   /api/compare
```

Return JSON responses only.

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
