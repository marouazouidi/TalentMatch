<?php

namespace App\Models;

use Database\Factories\CandidateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    /** @use HasFactory<CandidateFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'cv_text',
    ];

    public function analyses(): HasMany
    {
        return $this->hasMany(Analysis::class);
    }
}
