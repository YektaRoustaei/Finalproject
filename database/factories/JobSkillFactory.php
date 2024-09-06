<?php

namespace Database\Factories;

use App\Models\JobSkill;
use App\Models\JobPosting;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobSkillFactory extends Factory
{
    protected $model = JobSkill::class;

    public function definition(): array
    {
        return [
            'job_posting_id' => JobPosting::factory(), // Create a JobPosting when creating a JobSkill
            'skill_id' => Skill::factory(), // Create a Skill when creating a JobSkill
        ];
    }
}


