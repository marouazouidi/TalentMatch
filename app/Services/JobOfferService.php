<?php

namespace App\Services;

use App\Models\JobOffer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class JobOfferService
{
    public function list(int $userId): Collection
    {
        return JobOffer::where('user_id', $userId)
            ->withCount('analyses')
            ->latest()
            ->get();
    }

    public function find(int $userId, int $id): Model
    {
        return JobOffer::where('user_id', $userId)
            ->findOrFail($id);
    }

    public function create(int $userId, array $data): Model
    {
        return JobOffer::create(array_merge($data, ['user_id' => $userId]));
    }

    public function update(int $userId, int $id, array $data): Model
    {
        $offer = $this->find($userId, $id);
        $offer->update($data);

        return $offer->fresh();
    }

    public function delete(int $userId, int $id): void
    {
        $offer = $this->find($userId, $id);
        $offer->delete();
    }
}
