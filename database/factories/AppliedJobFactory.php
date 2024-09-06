<?php

namespace Database\Factories;

use App\Models\AppliedJob;
use App\Models\JobPosting;
use App\Models\Seeker;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppliedJobFactory extends Factory
{
    protected $model = AppliedJob::class;

    public function definition(): array
    {
        return [
            'job_id' => JobPosting::factory(),
            'seeker_id' => Seeker::factory(),
            'curriculum_vitae_id' => null,
            'cover_letter_id' => null,
            'status' => $this->faker->randomElement(['accepted', 'hold', 'rejected', 'next_step', 'final_step']),
        ];
    }
}
