<?php

use App\Data\CandidateAnalysisSchema;
use App\Enums\AnalysisStatus;
use App\Jobs\AnalyseCandidateJob;
use App\Models\Analysis;
use App\Models\Candidate;
use App\Models\JobOffer;
use App\Models\User;
use App\Services\CandidateAnalysisService;
use Illuminate\Support\Facades\Queue;

class AnalyseCandidateJobTestStub extends AnalyseCandidateJob
{
    protected function callAi(Analysis $analysis): array
    {
        return [
            'schema_version' => 1,
            'extracted_skills' => ['PHP'],
            'years_experience' => 5,
            'education_level' => "Bachelor's",
            'languages' => ['English'],
            'matching_score' => 85,
            'strengths' => ['Team player'],
            'weaknesses' => ['Public speaking'],
            'missing_skills' => ['Docker'],
            'recommendation' => 'interview',
            'justification' => 'Good match.',
        ];
    }
}

class AnalyseCandidateJobTestStubInvalid extends AnalyseCandidateJob
{
    protected function callAi(Analysis $analysis): array
    {
        return [
            'extracted_skills' => ['PHP'],
            'years_experience' => 5,
            'education_level' => "Bachelor's",
            'languages' => ['English'],
            'matching_score' => 85,
            'strengths' => ['Team player'],
            'weaknesses' => ['Public speaking'],
            'missing_skills' => ['Docker'],
            'recommendation' => 'interview',
            // missing: justification, schema_version
        ];
    }
}

uses()->group('candidate-analysis');

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->offer = JobOffer::factory()->create(['user_id' => $this->user->id]);
});

// ─── Authentication ───

test('unauthenticated user cannot access submission form', function (): void {
    $this->get(route('candidates.create', $this->offer))
        ->assertRedirect(route('login'));
});

test('unauthenticated user cannot submit a candidate', function (): void {
    $this->post(route('candidates.store', $this->offer), [])
        ->assertRedirect(route('login'));
});

test('unauthenticated user cannot view analysis', function (): void {
    $analysis = Analysis::factory()->create(['job_offer_id' => $this->offer->id]);
    $this->get(route('analyses.show', $analysis))
        ->assertRedirect(route('login'));
});

// ─── Submission ───

test('user can submit a candidate for analysis', function (): void {
    Queue::fake();

    $this->actingAs($this->user)
        ->post(route('candidates.store', $this->offer), [
            'candidate_name' => 'John Doe',
            'cv_text' => 'Experienced PHP developer with 5 years of Laravel experience.',
            'job_offer_id' => $this->offer->id,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('candidates', [
        'name' => 'John Doe',
    ]);

    $this->assertDatabaseHas('analyses', [
        'job_offer_id' => $this->offer->id,
        'status' => 'pending',
    ]);
});

test('submitting same candidate for same offer is idempotent', function (): void {
    Queue::fake();

    $this->actingAs($this->user)
        ->post(route('candidates.store', $this->offer), [
            'candidate_name' => 'Jane Doe',
            'cv_text' => 'Same CV.',
            'job_offer_id' => $this->offer->id,
        ])
        ->assertRedirect();

    $this->actingAs($this->user)
        ->post(route('candidates.store', $this->offer), [
            'candidate_name' => 'Jane Doe',
            'cv_text' => 'Same CV.',
            'job_offer_id' => $this->offer->id,
        ])
        ->assertRedirect();

    $this->assertDatabaseCount('candidates', 1);
    $this->assertDatabaseCount('analyses', 1);
});

test('submission validates candidate_name is required', function (): void {
    $this->actingAs($this->user)
        ->post(route('candidates.store', $this->offer), [
            'cv_text' => 'Some CV text.',
            'job_offer_id' => $this->offer->id,
        ])
        ->assertSessionHasErrors(['candidate_name']);
});

test('submission validates cv_text is required', function (): void {
    $this->actingAs($this->user)
        ->post(route('candidates.store', $this->offer), [
            'candidate_name' => 'John Doe',
            'job_offer_id' => $this->offer->id,
        ])
        ->assertSessionHasErrors(['cv_text']);
});

test('submission validates job_offer_id exists', function (): void {
    $this->actingAs($this->user)
        ->post(route('candidates.store', $this->offer), [
            'candidate_name' => 'John Doe',
            'cv_text' => 'Some CV text.',
            'job_offer_id' => 99999,
        ])
        ->assertSessionHasErrors(['job_offer_id']);
});

test('cannot submit for another user job offer', function (): void {
    $otherOffer = JobOffer::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($this->user)
        ->post(route('candidates.store', $otherOffer), [
            'candidate_name' => 'John Doe',
            'cv_text' => 'Some CV text.',
            'job_offer_id' => $otherOffer->id,
        ])
        ->assertForbidden();
});

// ─── Show Analysis Details ───

test('user can view their own analysis detail', function (): void {
    $analysis = Analysis::factory()
        ->completed()
        ->create(['job_offer_id' => $this->offer->id]);

    $this->actingAs($this->user)
        ->get(route('analyses.show', $analysis))
        ->assertOk()
        ->assertSee($analysis->candidate->name)
        ->assertSee($analysis->matching_score)
        ->assertSee($analysis->recommendation->name);
});

test('user cannot view another user analysis', function (): void {
    $otherOffer = JobOffer::factory()->create(['user_id' => User::factory()->create()->id]);
    $analysis = Analysis::factory()->create(['job_offer_id' => $otherOffer->id]);

    $this->actingAs($this->user)
        ->get(route('analyses.show', $analysis))
        ->assertForbidden();
});

test('analysis shows in progress message when pending', function (): void {
    $analysis = Analysis::factory()->pending()->create(['job_offer_id' => $this->offer->id]);

    $this->actingAs($this->user)
        ->get(route('analyses.show', $analysis))
        ->assertOk()
        ->assertSee('in progress');
});

test('analysis shows failed message when failed', function (): void {
    $analysis = Analysis::factory()->failed()->create(['job_offer_id' => $this->offer->id]);

    $this->actingAs($this->user)
        ->get(route('analyses.show', $analysis))
        ->assertOk()
        ->assertSee('failed');
});

// ─── Candidate Ranking ───

test('analyses are ranked by matching score descending', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);

    $low = Analysis::factory()->create(['job_offer_id' => $offer->id, 'matching_score' => 30]);
    $high = Analysis::factory()->create(['job_offer_id' => $offer->id, 'matching_score' => 90]);
    $mid = Analysis::factory()->create(['job_offer_id' => $offer->id, 'matching_score' => 60]);

    $service = app(CandidateAnalysisService::class);
    $analyses = $service->listByOffer($this->user->id, $offer->id);

    expect($analyses->pluck('id')->toArray())->toBe([
        $high->id,
        $mid->id,
        $low->id,
    ]);
});

test('ranking uses created_at as secondary sort for equal scores', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);

    $older = Analysis::factory()->create([
        'job_offer_id' => $offer->id,
        'matching_score' => 70,
        'created_at' => now()->subDay(),
    ]);
    $newer = Analysis::factory()->create([
        'job_offer_id' => $offer->id,
        'matching_score' => 70,
        'created_at' => now(),
    ]);

    $service = app(CandidateAnalysisService::class);
    $analyses = $service->listByOffer($this->user->id, $offer->id);

    expect($analyses->pluck('id')->toArray())->toBe([
        $newer->id,
        $older->id,
    ]);
});

// ─── Schema Validation ───

function validSchemaData(): array
{
    return [
        'schema_version' => 1,
        'extracted_skills' => ['PHP', 'Laravel'],
        'years_experience' => 5,
        'education_level' => "Bachelor's",
        'languages' => ['English', 'French'],
        'matching_score' => 85,
        'strengths' => ['Team player'],
        'weaknesses' => ['Public speaking'],
        'missing_skills' => ['Docker'],
        'recommendation' => 'interview',
        'justification' => 'Strong match.',
    ];
}

test('valid schema passes validation', function (): void {
    CandidateAnalysisSchema::validate(validSchemaData(), 0);

    expect(true)->toBeTrue();
});

test('schema validation rejects missing field', function (): void {
    $data = validSchemaData();
    unset($data['justification']);

    expect(fn () => CandidateAnalysisSchema::validate($data, 0))
        ->toThrow(RuntimeException::class, 'Missing required field');
});

test('schema validation rejects out of range matching_score', function (): void {
    $data = validSchemaData();
    $data['matching_score'] = 150;

    expect(fn () => CandidateAnalysisSchema::validate($data, 0))
        ->toThrow(RuntimeException::class, 'matching_score');
});

test('schema validation rejects invalid recommendation', function (): void {
    $data = validSchemaData();
    $data['recommendation'] = 'invalid_value';

    expect(fn () => CandidateAnalysisSchema::validate($data, 0))
        ->toThrow(RuntimeException::class, 'recommendation');
});

test('schema validation rejects field type mismatch', function (): void {
    $data = validSchemaData();
    $data['matching_score'] = 'eighty-five';

    expect(fn () => CandidateAnalysisSchema::validate($data, 0))
        ->toThrow(RuntimeException::class, 'matching_score');
});

test('schema validation rejects invalid schema_version', function (): void {
    $data = validSchemaData();
    $data['schema_version'] = 99;

    expect(fn () => CandidateAnalysisSchema::validate($data, 0))
        ->toThrow(RuntimeException::class, 'schema_version');
});

test('schema validation accepts extra unexpected fields', function (): void {
    $data = validSchemaData();
    $data['extra_field'] = 'should be ignored';

    CandidateAnalysisSchema::validate($data, 0);

    expect(true)->toBeTrue();
});

test('schema validation rejects negative years_experience', function (): void {
    $data = validSchemaData();
    $data['years_experience'] = -1;

    expect(fn () => CandidateAnalysisSchema::validate($data, 0))
        ->toThrow(RuntimeException::class, 'years_experience');
});

// ─── Persistence ───

test('candidate information is persisted independently', function (): void {
    $this->actingAs($this->user)
        ->post(route('candidates.store', $this->offer), [
            'candidate_name' => 'Jane Smith',
            'cv_text' => 'Expert React developer.',
            'job_offer_id' => $this->offer->id,
        ]);

    $this->assertDatabaseHas('candidates', [
        'name' => 'Jane Smith',
        'cv_text' => 'Expert React developer.',
    ]);
});

test('same candidate can be analyzed against multiple job offers', function (): void {
    $candidate = Candidate::factory()->create();

    $offer2 = JobOffer::factory()->create(['user_id' => $this->user->id]);

    $analysis1 = Analysis::factory()->create([
        'job_offer_id' => $this->offer->id,
        'candidate_id' => $candidate->id,
    ]);
    $analysis2 = Analysis::factory()->create([
        'job_offer_id' => $offer2->id,
        'candidate_id' => $candidate->id,
    ]);

    expect($candidate->analyses)->toHaveCount(2);
    expect($analysis1->job_offer_id)->not->toBe($analysis2->job_offer_id);
});

// ─── Job Behavior ───

test('job completes with valid AI response', function (): void {
    $analysis = Analysis::factory()->create([
        'job_offer_id' => $this->offer->id,
        'candidate_id' => Candidate::factory(),
        'status' => AnalysisStatus::Pending,
    ]);

    $job = new AnalyseCandidateJobTestStub($analysis->id);
    $job->handle();

    $analysis->refresh();

    expect($analysis->status)->toBe(AnalysisStatus::Completed);
    expect($analysis->extracted_skills)->toBe(['PHP']);
    expect($analysis->matching_score)->toBe(85);
    expect($analysis->recommendation->value)->toBe('interview');
});

test('job sets status to failed on validation error', function (): void {
    $analysis = Analysis::factory()->create([
        'job_offer_id' => $this->offer->id,
        'candidate_id' => Candidate::factory(),
        'status' => AnalysisStatus::Pending,
    ]);

    $job = new AnalyseCandidateJobTestStubInvalid($analysis->id);
    $job->handle();

    $analysis->refresh();

    expect($analysis->status)->toBe(AnalysisStatus::Failed);
});

test('job stores raw payload before validation failure', function (): void {
    $analysis = Analysis::factory()->create([
        'job_offer_id' => $this->offer->id,
        'candidate_id' => Candidate::factory(),
        'status' => AnalysisStatus::Pending,
    ]);

    $job = new AnalyseCandidateJobTestStubInvalid($analysis->id);
    $job->handle();

    $analysis->refresh();

    expect($analysis->payload)->not->toBeNull();
    expect($analysis->status)->toBe(AnalysisStatus::Failed);
});

test('job skips if analysis is already completed', function (): void {
    $analysis = Analysis::factory()->completed()->create([
        'job_offer_id' => $this->offer->id,
        'candidate_id' => Candidate::factory(),
    ]);

    $originalStatus = $analysis->status;

    $job = new AnalyseCandidateJobTestStub($analysis->id);
    $job->handle();

    $analysis->refresh();

    expect($analysis->status)->toBe($originalStatus);
});

test('analyse candidate job is dispatched on submission', function (): void {
    Queue::fake();

    $this->actingAs($this->user)
        ->post(route('candidates.store', $this->offer), [
            'candidate_name' => 'Job Test',
            'cv_text' => 'Testing job dispatch.',
            'job_offer_id' => $this->offer->id,
        ]);

    Queue::assertPushed(AnalyseCandidateJob::class);
});

test('job transitions through processing to completed', function (): void {
    $analysis = Analysis::factory()->create([
        'job_offer_id' => $this->offer->id,
        'candidate_id' => Candidate::factory(),
        'status' => AnalysisStatus::Pending,
    ]);

    $job = new AnalyseCandidateJobTestStub($analysis->id);

    $analysis->refresh();
    expect($analysis->status)->toBe(AnalysisStatus::Pending);

    $job->handle();

    $analysis->refresh();
    expect($analysis->status)->toBe(AnalysisStatus::Completed);
});
