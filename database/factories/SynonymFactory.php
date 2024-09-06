<?php

namespace Database\Factories;

use App\Models\Synonym;
use Illuminate\Database\Eloquent\Factories\Factory;

class SynonymFactory extends Factory
{
    protected $model = Synonym::class;

    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'synonym1' => $this->faker->word,
            'synonym2' => $this->faker->word,
            'synonym3' => $this->faker->word,
            'synonym4' => $this->faker->word,
            'synonym5' => $this->faker->word,
        ];
    }
}

