## ADDED Requirements

### Requirement: User can create a job offer
The system SHALL allow an authenticated user to create a job offer with title, description, required skills, and minimum experience. The authenticated user SHALL be automatically set as the owner.

#### Scenario: Successful creation
- **WHEN** an authenticated user submits a valid job offer with title, description, required_skills, and minimum_experience
- **THEN** the system creates the offer, associates it with the authenticated user, and redirects to the offer list with a success message

#### Scenario: Creation with invalid data
- **WHEN** an authenticated user submits a job offer with missing required fields or invalid data types
- **THEN** the system returns validation errors and does not create the offer

#### Scenario: Unauthenticated creation attempt
- **WHEN** an unauthenticated user submits a job offer creation request
- **THEN** the system redirects to the login page

### Requirement: User can list their job offers
The system SHALL display a paginated list of job offers owned by the authenticated user.

#### Scenario: Viewing offer list
- **WHEN** an authenticated user navigates to the job offers index page
- **THEN** the system displays all offers owned by that user, ordered by creation date

### Requirement: User can view a single job offer
The system SHALL display the full details of a specific job offer owned by the authenticated user.

#### Scenario: Viewing own offer
- **WHEN** an authenticated user navigates to a job offer detail page they own
- **THEN** the system displays the offer title, description, required skills, and minimum experience

#### Scenario: Viewing another user's offer
- **WHEN** an authenticated user attempts to view a job offer owned by another user
- **THEN** the system returns a 403 Forbidden response

### Requirement: User can update a job offer
The system SHALL allow an authenticated user to update the title, description, required skills, and minimum experience of a job offer they own.

#### Scenario: Successful update
- **WHEN** an authenticated user submits valid updates for a job offer they own
- **THEN** the system updates the offer and redirects to the offer detail page with a success message

#### Scenario: Update with invalid data
- **WHEN** an authenticated user submits invalid update data for a job offer they own
- **THEN** the system returns validation errors and does not update the offer

#### Scenario: Update another user's offer
- **WHEN** an authenticated user attempts to update a job offer owned by another user
- **THEN** the system returns a 403 Forbidden response

### Requirement: User can delete a job offer
The system SHALL allow an authenticated user to delete a job offer they own.

#### Scenario: Successful deletion
- **WHEN** an authenticated user deletes a job offer they own
- **THEN** the system removes the offer and redirects to the offer list with a success message

#### Scenario: Delete another user's offer
- **WHEN** an authenticated user attempts to delete a job offer owned by another user
- **THEN** the system returns a 403 Forbidden response

### Requirement: Offer list displays analyzed candidate count
The system SHALL show, for each job offer in the list, the number of candidates that have an associated analysis. Only candidates with a completed or existing analysis record SHALL be counted.

#### Scenario: Analyzed count visible in list
- **WHEN** an authenticated user views the job offers list
- **THEN** the system displays, for each offer, the count of analyzed candidates associated with that offer

#### Scenario: Zero analyzed candidates
- **WHEN** a job offer has no analyzed candidates
- **THEN** the system displays a count of 0 for that offer

### Requirement: Offer detail page shows analyzed candidates
The system SHALL display all analyzed candidates associated with a job offer on the offer detail page. Each candidate entry SHALL include the candidate name, matching score, recommendation, and analysis status. The system SHALL provide navigation to the candidate analysis details.

#### Scenario: Viewing analyzed candidates on offer detail
- **WHEN** an authenticated user views a job offer detail page they own
- **THEN** the system displays a list of analyzed candidates showing candidate name, matching score, recommendation, and analysis status

#### Scenario: No analyzed candidates
- **WHEN** a job offer has no analyzed candidates
- **THEN** the system displays an appropriate empty state message

#### Scenario: Navigation to candidate analysis
- **WHEN** an authenticated user clicks on an analyzed candidate entry
- **THEN** the system navigates to that candidate's analysis details

### Requirement: User can view analyzed candidates only for their own offers
The system SHALL restrict access to analyzed candidate data based on job offer ownership. An authenticated user SHALL only see analyzed candidates for job offers they own.

#### Scenario: Viewing analyzed candidates for own offer
- **WHEN** an authenticated user views analyzed candidates for a job offer they own
- **THEN** the system displays the candidate data

#### Scenario: Viewing analyzed candidates for another user's offer
- **WHEN** an authenticated user attempts to view analyzed candidates for a job offer owned by another user
- **THEN** the system returns a 403 Forbidden response
