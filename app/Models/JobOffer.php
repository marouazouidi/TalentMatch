<?php

namespace App\Models;

use Database\Factories\JobOfferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'description', 'required_skills', 'minimum_experience', 'user_id'])]
class JobOffer extends Model
{
    /** @use HasFactory<JobOfferFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class, 'job_offer_id');
    }
}
