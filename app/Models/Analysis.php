<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use App\Enums\Recommendation;
use Database\Factories\AnalysisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Analysis extends Model
{
    /** @use HasFactory<AnalysisFactory> */
    use HasFactory;

    protected $fillable = [
        'job_offer_id',
        'candidate_id',
        'payload',
        'extracted_skills',
        'years_experience',
        'education_level',
        'languages',
        'matching_score',
        'strengths',
        'weaknesses',
        'missing_skills',
        'recommendation',
        'justification',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'extracted_skills' => 'array',
            'languages' => 'array',
            'strengths' => 'array',
            'weaknesses' => 'array',
            'missing_skills' => 'array',
            'recommendation' => Recommendation::class,
            'status' => AnalysisStatus::class,
        ];
    }

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
