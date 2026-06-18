<?php

namespace Database\Seeders;

use App\Models\Analysis;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('test1234'),
        ]);

        $offers = $user->jobOffers()->createMany(
            JobOffer::factory()->count(5)->make()->toArray()
        );

        foreach ($offers as $offer) {
            Analysis::factory()
                ->count(rand(1, 3))
                ->create(['job_offer_id' => $offer->id]);
        }
    }
}
