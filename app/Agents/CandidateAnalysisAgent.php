<?php

namespace App\Agents;

use App\Data\CandidateAnalysisSchema;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

class CandidateAnalysisAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are an expert HR recruitment assistant. Analyze the given candidate CV against the job offer details. Extract the candidate\'s skills, experience, education, and languages from the CV. Evaluate how well the candidate fits the job requirements, required skills, and minimum experience. Provide a matching score from 0 to 100, justify your score, list strengths and weaknesses, identify any missing skills, and give a recommendation (interview, pending, or reject). Return only valid JSON matching the specified schema exactly.';
    }

    public function schema(JsonSchema $schema): array
    {
        return CandidateAnalysisSchema::definition($schema);
    }
}
