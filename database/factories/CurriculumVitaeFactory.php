<?php

namespace Database\Factories;

use App\Models\CurriculumVitae;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurriculumVitaeFactory extends Factory
{
    protected $model = CurriculumVitae::class;

    public function definition()
    {
        return [
            'seeker_id' => \App\Models\Seeker::factory(),
            // Add other fields here
        ];
    }
}
