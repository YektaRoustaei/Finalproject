<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppliedJob>
 */
class AppliedJobFactory extends Factory
{
    protected $model = \App\Models\AppliedJob::class;
    public function definition(): array
    {
        return [
            'job_id' => \App\Models\JobPosting::factory(),
            'seeker_id' => \App\Models\Seeker::factory(),
        ];
    }
}
