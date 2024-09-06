<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Skill;
use App\Models\JobSkill;


class SkillTest extends TestCase
{
    use RefreshDatabase;


    public function test_fetch_all_skills(): void
    {

        $skills = Skill::factory()->count(3)->create();


        $response = $this->getJson('/api/skills');


        $response->assertStatus(200);


        $response->assertJson($skills->toArray());
    }

    public function test_fetch_all_skills_empty(): void
    {

        $response = $this->getJson('/api/skills');


        $response->assertStatus(200);


        $response->assertExactJson([]);
    }

    public function test_store_new_skill(): void
    {

        $skillData = ['name' => 'Laravel'];


        $response = $this->postJson('/api/skills', $skillData);


        $response->assertStatus(201);

        $response->assertJson([
            'name' => 'Laravel',
        ]);


        $this->assertDatabaseHas('skills', [
            'name' => 'Laravel',
        ]);
    }

    public function test_store_skill_duplicate_name(): void
    {

        $existingSkill = Skill::factory()->create(['name' => 'Laravel']);


        $response = $this->postJson('/api/skills', ['name' => 'Laravel']);


        $response->assertStatus(422);

        $response->assertJsonValidationErrors('name');
    }

    public function test_store_skill_invalid_data(): void
    {

        $response = $this->postJson('/api/skills', ['name' => '']);


        $response->assertStatus(422);


        $response->assertJsonValidationErrors('name');
    }

    public function test_fetch_all_job_skills(): void
    {

        $jobSkills = \App\Models\JobSkill::factory()->count(3)->create();


        $response = $this->getJson('/api/jobskills');


        $response->assertStatus(200);


        $response->assertJson($jobSkills->toArray());
    }

    public function test_fetch_all_job_skills_empty(): void
    {

        $response = $this->getJson('/api/jobskills');


        $response->assertStatus(200);


        $response->assertExactJson([]);
    }
}
