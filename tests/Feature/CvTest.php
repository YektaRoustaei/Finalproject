<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Seeker;
use App\Models\CurriculumVitae;
use Illuminate\Database\Schema\Blueprint;
use App\Models\SeekerSkill;
use App\Models\Skill;
use Laravel\Sanctum\Sanctum;
use App\Models\Education;
use App\Models\JobExperience;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Schema;


class CvTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_curriculum_vitae(): void
    {
        $seeker = Seeker::factory()->create();
        $user = $seeker;

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/seeker/cv/create', [
            'skills' => [
                ['name' => 'PHP'],
                ['name' => 'Laravel'],
            ],
            'educations' => [
                [
                    'degree' => 'BSc in Computer Science',
                    'institution' => 'University X',
                    'start_date' => '2015-09-01',
                    'end_date' => '2019-06-01',
                ],
            ],
            'job_experiences' => [
                [
                    'position' => 'Software Engineer',
                    'company_name' => 'Company Y',
                    'start_date' => '2019-07-01',
                    'end_date' => '2021-08-01',
                ],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'curriculum_vitae' => [
                    'id',
                    'seeker_id',
                    'seeker_skills' => [
                        '*' => ['id', 'skill_id', 'curriculum_vitae_id'],
                    ],
                    'educations' => [
                        '*' => ['id', 'curriculum_vitae_id', 'institution', 'degree', 'field_of_study', 'start_date', 'end_date'],
                    ],
                    'job_experiences' => [
                        '*' => ['id', 'position', 'company_name', 'start_date', 'end_date'],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('skills', ['name' => 'PHP']);
        $this->assertDatabaseHas('education', ['degree' => 'BSc in Computer Science']);
        $this->assertDatabaseHas('job_experiences', ['position' => 'Software Engineer']);
    }

    public function test_update_curriculum_vitae(): void
    {
        $seeker = Seeker::factory()->create();

        $user = $seeker;
        $this->actingAs($user, 'sanctum');

        $cv = CurriculumVitae::factory()->create(['seeker_id' => $seeker->id]);

        $response = $this->putJson('/api/seeker/cv/update', [
            'cv_id' => $cv->id,
            'skills' => [
                ['name' => 'PHP'],
                ['name' => 'Laravel'],
            ],
            'educations' => [
                [
                    'degree' => 'BSc in Computer Science',
                    'institution' => 'University X',
                    'start_date' => '2015-09-01',
                    'end_date' => '2019-06-01',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'curriculum_vitae' => [
                    'id',
                    'seeker_id',
                    'seeker_skills' => [
                        '*' => ['id', 'skill_id', 'curriculum_vitae_id'],
                    ],
                    'educations' => [
                        '*' => ['id', 'degree', 'institution', 'start_date', 'end_date'],
                    ],
                ],
            ]);

        $this->assertDatabaseHas('skills', ['name' => 'PHP']);
        $this->assertDatabaseHas('education', ['degree' => 'BSc in Computer Science']);
    }

    public function test_remove_curriculum_vitae(): void
    {
        $user = Seeker::factory()->create();  // Use Seeker as the authenticated user
        $this->actingAs($user, 'sanctum');

        $cv = CurriculumVitae::factory()->create(['seeker_id' => $user->id]);

        $response = $this->deleteJson('/api/seeker/cv/delete', ['id' => $cv->id]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Curriculum Vitae removed']);

        $this->assertDatabaseMissing('curriculum_vitaes', ['id' => $cv->id]);
    }

    public function test_get_curriculum_vitae(): void
    {
        $user = Seeker::factory()->create();
        $this->actingAs($user, 'sanctum');

        $cv = CurriculumVitae::factory()->create(['seeker_id' => $user->id]);

        $response = $this->getJson('/api/seeker/cv/info'); // Update the endpoint if necessary

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Curriculum Vitae found',
                'curriculum_vitae_id' => $cv->id,
            ]);
    }

    public function test_get_curriculum_vitae_not_found(): void
    {
        $seeker = Seeker::factory()->create();
        $this->actingAs($seeker, 'sanctum');

        $response = $this->getJson('/api/seeker/cv/info');

        \Log::info('Response content: ', ['response' => $response->getContent()]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'No Curriculum Vitae found for this user.']);
    }

    public function test_store_curriculum_vitae_unauthorized(): void
    {
        $response = $this->postJson('/api/seeker/cv/create', [
            'skills' => [
                ['name' => 'PHP'],
                ['name' => 'Laravel'],
            ],
            'educations' => [
                [
                    'degree' => 'BSc in Computer Science',
                    'institution' => 'University X',
                    'start_date' => '2015-09-01',
                    'end_date' => '2019-06-01',
                ],
            ],
            'job_experiences' => [
                [
                    'position' => 'Software Engineer',
                    'company_name' => 'Company Y',
                    'start_date' => '2019-07-01',
                    'end_date' => '2021-08-01',
                ],
            ],
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }


    public function test_update_curriculum_vitae_unauthorized(): void
    {
        $cv = CurriculumVitae::factory()->create();

        $response = $this->putJson('/api/seeker/cv/update', [
            'cv_id' => $cv->id,
            'skills' => [
                ['name' => 'PHP'],
                ['name' => 'Laravel'],
            ],
            'educations' => [
                [
                    'degree' => 'BSc in Computer Science',
                    'institution' => 'University X',
                    'start_date' => '2015-09-01',
                    'end_date' => '2019-06-01',
                ],
            ],
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function test_update_curriculum_vitae_with_invalid_data(): void
    {
        $seeker = Seeker::factory()->create();
        $this->actingAs($seeker, 'sanctum');

        $cv = CurriculumVitae::factory()->create(['seeker_id' => $seeker->id]);

        $response = $this->putJson('/api/seeker/cv/update', [
            'cv_id' => $cv->id,
            'skills' => [
                ['name' => '']
            ],
            'educations' => [
                [
                    'degree' => '',
                    'institution' => 'University X',
                    'start_date' => '2015-09-01',
                    'end_date' => '2019-06-01',
                ],
            ],
        ]);

        \Log::info('Response content: ', ['response' => $response->getContent()]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'The educations.0.degree field must be a string.',
            ])
            ->assertJsonFragment([
                'errors' => [
                    'educations.0.degree' => [
                        'The educations.0.degree field must be a string.'
                    ]
                ],
            ]);
    }









}
