<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'candidate_name' => ['required', 'string', 'max:255'],
            'cv_text' => ['required', 'string', 'min:1'],
            'job_offer_id' => ['required', 'integer', 'exists:job_offers,id'],
        ];
    }
}
