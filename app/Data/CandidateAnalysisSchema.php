<?php

namespace App\Data;

use Illuminate\Contracts\JsonSchema\JsonSchema;

class CandidateAnalysisSchema
{
    public const int CURRENT_VERSION = 1;

    public static function definition(JsonSchema $schema): array
    {
        return [
            'schema_version' => $schema->integer()->min(1)->max(self::CURRENT_VERSION)->required(),
            'extracted_skills' => $schema->array()->items($schema->string())->required(),
            'years_experience' => $schema->integer()->min(0)->required(),
            'education_level' => $schema->string()->required(),
            'languages' => $schema->array()->items($schema->string())->required(),
            'matching_score' => $schema->integer()->min(0)->max(100)->required(),
            'strengths' => $schema->array()->items($schema->string())->required(),
            'weaknesses' => $schema->array()->items($schema->string())->required(),
            'missing_skills' => $schema->array()->items($schema->string())->required(),
            'recommendation' => $schema->string()->enum(['interview', 'pending', 'reject'])->required(),
            'justification' => $schema->string()->required(),
        ];
    }

    public static function validate(array $data, int $analysisId): void
    {
        $required = [
            'extracted_skills', 'years_experience', 'education_level',
            'languages', 'matching_score', 'strengths', 'weaknesses',
            'missing_skills', 'recommendation', 'justification', 'schema_version',
        ];

        foreach ($required as $field) {
            if (! array_key_exists($field, $data)) {
                throw new \RuntimeException("Missing required field: {$field}");
            }
        }

        if (! is_int($data['schema_version']) || $data['schema_version'] !== self::CURRENT_VERSION) {
            throw new \RuntimeException('schema_version must be '.self::CURRENT_VERSION);
        }

        if (! is_array($data['extracted_skills'])) {
            throw new \RuntimeException('extracted_skills must be an array');
        }

        if (! is_int($data['years_experience']) || $data['years_experience'] < 0) {
            throw new \RuntimeException('years_experience must be a non-negative integer');
        }

        if (! is_string($data['education_level'])) {
            throw new \RuntimeException('education_level must be a string');
        }

        if (! is_array($data['languages'])) {
            throw new \RuntimeException('languages must be an array');
        }

        if (! is_int($data['matching_score']) || $data['matching_score'] < 0 || $data['matching_score'] > 100) {
            throw new \RuntimeException('matching_score must be an integer between 0 and 100');
        }

        if (! is_array($data['strengths'])) {
            throw new \RuntimeException('strengths must be an array');
        }

        if (! is_array($data['weaknesses'])) {
            throw new \RuntimeException('weaknesses must be an array');
        }

        if (! is_array($data['missing_skills'])) {
            throw new \RuntimeException('missing_skills must be an array');
        }

        if (! in_array($data['recommendation'], ['interview', 'pending', 'reject'], true)) {
            throw new \RuntimeException('recommendation must be one of: interview, pending, reject');
        }

        if (! is_string($data['justification'])) {
            throw new \RuntimeException('justification must be a string');
        }
    }
}
