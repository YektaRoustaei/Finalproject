<?php

namespace Database\Factories;

use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class QuestionnaireFactory extends Factory
{
    protected $model = Questionnaire::class;

    public function definition()
    {
        return [
            'job_id' => \App\Models\JobPosting::factory(), // Assuming JobPosting factory exists
            'question' => $this->faker->sentence,
            'answer_type' => $this->faker->randomElement(['string', 'int']),
            'min_value' => $this->faker->numberBetween(0, 10),
            'max_value' => $this->faker->optional()->numberBetween(11, 100),
        ];
    }
}
