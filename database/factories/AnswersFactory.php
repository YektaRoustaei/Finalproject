<?php

namespace Database\Factories;

use App\Models\Answers;
use App\Models\Seeker;
use App\Models\JobPosting;
use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnswersFactory extends Factory
{
    protected $model = Answers::class;

    public function definition()
    {
        return [
            'seeker_id' => Seeker::factory(),
            'job_id' => JobPosting::factory(),
            'question_id' => Questionnaire::factory(),
            'answer' => $this->faker->sentence,
        ];
    }
}
