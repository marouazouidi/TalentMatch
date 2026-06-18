<?php

use App\Models\Analysis;
use App\Models\JobOffer;
use App\Models\User;

uses()->group('job-offer');

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

// ─── Authentication ───

test('unauthenticated user is redirected to login for index', function (): void {
    $this->get(route('offers.index'))->assertRedirect(route('login'));
});

test('unauthenticated user is redirected to login for create', function (): void {
    $this->get(route('offers.create'))->assertRedirect(route('login'));
});

test('unauthenticated user is redirected to login for store', function (): void {
    $this->post(route('offers.store'), [])->assertRedirect(route('login'));
});

test('unauthenticated user is redirected to login for show', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);
    $this->get(route('offers.show', $offer))->assertRedirect(route('login'));
});

test('unauthenticated user is redirected to login for edit', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);
    $this->get(route('offers.edit', $offer))->assertRedirect(route('login'));
});

test('unauthenticated user is redirected to login for update', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);
    $this->put(route('offers.update', $offer), [])->assertRedirect(route('login'));
});

test('unauthenticated user is redirected to login for destroy', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);
    $this->delete(route('offers.destroy', $offer))->assertRedirect(route('login'));
});

// ─── List ───

test('user can view their own job offers list', function (): void {
    $offers = JobOffer::factory()
        ->count(3)
        ->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user)
        ->get(route('offers.index'))
        ->assertOk();

    expect($response->viewData('offers'))->toHaveCount(3);
    $response->assertSee($offers->first()->title);
});

test('empty state shows no job offers message', function (): void {
    $this->actingAs($this->user)
        ->get(route('offers.index'))
        ->assertOk()
        ->assertSee('No job offers yet.');
});

test('user cannot see other users offers in their list', function (): void {
    JobOffer::factory()->count(3)->create(['user_id' => $this->user->id]);
    JobOffer::factory()->count(2)->create(['user_id' => User::factory()->create()->id]);

    $response = $this->actingAs($this->user)
        ->get(route('offers.index'))
        ->assertOk();

    expect($response->viewData('offers'))->toHaveCount(3);
});

// ─── Create ───

test('create page is displayed', function (): void {
    $this->actingAs($this->user)
        ->get(route('offers.create'))
        ->assertOk();
});

test('user can create a job offer', function (): void {
    $this->actingAs($this->user)
        ->post(route('offers.store'), [
            'title' => 'Senior PHP Developer',
            'description' => 'We need a senior PHP developer.',
            'required_skills' => ['PHP', 'Laravel', 'MySQL'],
            'minimum_experience' => 5,
        ])
        ->assertRedirect(route('offers.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('job_offers', [
        'title' => 'Senior PHP Developer',
        'user_id' => $this->user->id,
    ]);
});

// ─── Validation (Store) ───

test('store validates required fields', function (): void {
    $this->actingAs($this->user)
        ->post(route('offers.store'), [])
        ->assertSessionHasErrors(['title', 'description', 'required_skills', 'minimum_experience']);
});

test('store validates title max length', function (): void {
    $this->actingAs($this->user)
        ->post(route('offers.store'), [
            'title' => str_repeat('a', 256),
            'description' => 'Valid description.',
            'required_skills' => ['PHP'],
            'minimum_experience' => 1,
        ])
        ->assertSessionHasErrors(['title']);
});

test('store validates required_skills must be array', function (): void {
    $this->actingAs($this->user)
        ->post(route('offers.store'), [
            'title' => 'Test',
            'description' => 'Test description.',
            'required_skills' => 'not-an-array',
            'minimum_experience' => 1,
        ])
        ->assertSessionHasErrors(['required_skills']);
});

test('store validates minimum_experience must be integer', function (): void {
    $this->actingAs($this->user)
        ->post(route('offers.store'), [
            'title' => 'Test',
            'description' => 'Test description.',
            'required_skills' => ['PHP'],
            'minimum_experience' => 'not-an-integer',
        ])
        ->assertSessionHasErrors(['minimum_experience']);
});

test('store validates minimum_experience cannot be negative', function (): void {
    $this->actingAs($this->user)
        ->post(route('offers.store'), [
            'title' => 'Test',
            'description' => 'Test description.',
            'required_skills' => ['PHP'],
            'minimum_experience' => -1,
        ])
        ->assertSessionHasErrors(['minimum_experience']);
});

// ─── Show ───

test('user can view their own job offer detail', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('offers.show', $offer))
        ->assertOk()
        ->assertSee($offer->title)
        ->assertSee($offer->description);
});

test('user cannot view another users job offer', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($this->user)
        ->get(route('offers.show', $offer))
        ->assertForbidden();
});

// ─── Edit ───

test('edit page is displayed for own offer', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('offers.edit', $offer))
        ->assertOk()
        ->assertSee($offer->title);
});

test('user cannot edit another users job offer', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($this->user)
        ->get(route('offers.edit', $offer))
        ->assertForbidden();
});

test('user cannot update another users job offer', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($this->user)
        ->put(route('offers.update', $offer), [
            'title' => 'Hacked',
            'description' => 'Hacked description.',
            'required_skills' => ['Hack'],
            'minimum_experience' => 1,
        ])
        ->assertForbidden();
});

// ─── Update ───

test('user can update their own job offer', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->put(route('offers.update', $offer), [
            'title' => 'Updated Title',
            'description' => $offer->description,
            'required_skills' => $offer->required_skills,
            'minimum_experience' => $offer->minimum_experience,
        ])
        ->assertRedirect(route('offers.index'))
        ->assertSessionHas('status');

    expect($offer->fresh()->title)->toBe('Updated Title');
});

test('update validates required fields', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->put(route('offers.update', $offer), [])
        ->assertSessionHasErrors(['title', 'description', 'required_skills', 'minimum_experience']);
});

test('update validates minimum_experience cannot be negative', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->put(route('offers.update', $offer), [
            'title' => 'Test',
            'description' => 'Test description.',
            'required_skills' => ['PHP'],
            'minimum_experience' => -1,
        ])
        ->assertSessionHasErrors(['minimum_experience']);
});

// ─── Delete ───

test('user can delete their own job offer', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->delete(route('offers.destroy', $offer))
        ->assertRedirect(route('offers.index'))
        ->assertSessionHas('status');

    expect(JobOffer::find($offer->id))->toBeNull();
});

test('user cannot delete another users job offer', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($this->user)
        ->delete(route('offers.destroy', $offer))
        ->assertForbidden();

    expect(JobOffer::find($offer->id))->not->toBeNull();
});

// ─── Candidate Count ───

test('index shows zero candidate count when no analyses exist', function (): void {
    JobOffer::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user)
        ->get(route('offers.index'))
        ->assertOk()
        ->assertSee('0');
});

test('index shows analyzed candidate count for each offer', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);
    Analysis::factory()->count(2)->create(['job_offer_id' => $offer->id]);

    $this->actingAs($this->user)
        ->get(route('offers.index'))
        ->assertOk()
        ->assertSee('2');
});

test('candidate count only counts own offer analyses', function (): void {
    $offer = JobOffer::factory()->create(['user_id' => $this->user->id]);
    $otherOffer = JobOffer::factory()->create(['user_id' => User::factory()->create()->id]);

    Analysis::factory()->count(3)->create(['job_offer_id' => $offer->id]);
    Analysis::factory()->count(5)->create(['job_offer_id' => $otherOffer->id]);

    $response = $this->actingAs($this->user)
        ->get(route('offers.index'))
        ->assertOk();

    $offers = $response->viewData('offers');
    expect($offers->firstWhere('id', $offer->id)->analyses_count)->toBe(3);
    expect($offers->firstWhere('id', $otherOffer->id))->toBeNull();
});
