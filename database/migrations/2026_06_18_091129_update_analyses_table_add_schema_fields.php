<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->foreignId('candidate_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->json('payload')->nullable();
            $table->json('extracted_skills')->nullable();
            $table->integer('years_experience')->nullable();
            $table->string('education_level')->nullable();
            $table->json('languages')->nullable();
            $table->integer('matching_score')->nullable();
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('missing_skills')->nullable();
            $table->string('recommendation')->nullable();
            $table->text('justification')->nullable();
            $table->string('status')->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analyses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('candidate_id');
        });

        Schema::table('analyses', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
