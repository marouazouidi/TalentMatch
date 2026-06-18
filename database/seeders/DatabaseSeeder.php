<?php

namespace Database\Seeders;

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

        $user->jobOffers()->createMany(
            JobOffer::factory()->count(5)->make()->toArray()
        );
    }
}
