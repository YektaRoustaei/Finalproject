<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\JobPosting;
use App\Models\Provider;
use App\Models\Skill; // Make sure this import is here
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log; // Import the Log facade
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProviderJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_can_create_job_successfully()
    {
        $provider = Provider::factory()->create();
        $category = Category::factory()->create();

        Sanctum::actingAs($provider, ['*']);

        $data = [
            'title' => 'Test Job',
            'description' => 'Test Description',
            'salary' => 50000,
            'type' => 'Full-time',
            'category_ids' => [$category->id],
            'cover_letter' => true,
            'question' => false,
        ];

        $response = $this->postJson('api/provider/jobs/create', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'job' => [
                    'id',
                    'title',
                    'description',
                    'salary',
                    'type',
                    'created_at',
                    'updated_at',
                    'categories' => [
                        '*' => ['id', 'title'],
                    ],
                    'skills' => [],
                ],
            ]);


        $this->assertDatabaseHas('job_postings', [
            'title' => 'Test Job',
        ]);


        $this->assertDatabaseHas('job_categories', [
            'job_id' => JobPosting::first()->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_provider_cannot_create_job_with_missing_data()
    {
        $provider = Provider::factory()->create();

        Sanctum::actingAs($provider, ['*']);

        $data = [];

        $response = $this->postJson('api/provider/jobs/create', $data);


        $response->assertStatus(422);


        $response->assertJsonStructure([
            'errors' => [
                '*' => [
                    'field',
                    'message',
                ],
            ],
        ]);


        $errors = $response->json('errors');
        $this->assertCount(6, $errors);


        $fields = ['title', 'description', 'salary', 'type', 'cover_letter', 'question'];
        foreach ($fields as $field) {
            $this->assertTrue(
                collect($errors)->contains(fn ($error) => $error['field'] === $field),
                "Missing error for field: $field"
            );
        }
    }
    public function test_middleware_rejects_invalid_category_ids()
    {
        $provider = Provider::factory()->create();

        Sanctum::actingAs($provider, ['*']);


        $data = [
            'title' => 'Test Job',
            'description' => 'Test Description',
            'salary' => 50000,
            'type' => 'Full-time',
            'category_ids' => [999],
            'cover_letter' => true,
            'question' => false,
        ];

        $response = $this->postJson('api/provider/jobs/create', $data);

        $response->assertStatus(422)
        ->assertJsonStructure([
            'errors' => [
                '*' => [
                    'message'
                ]
            ]
        ]);

        $errors = $response->json('errors');


        $this->assertTrue(
            collect($errors)->contains(fn ($error) => $error['message'] === 'The selected category_ids.0 is invalid.'),
            'Expected validation error for invalid category ID not found'
        );
    }

    public function test_provider_can_update_job_successfully()
    {
        $provider = Provider::factory()->create();
        $category = Category::factory()->create();
        $job = JobPosting::factory()->create([
            'provider_id' => $provider->id,
        ]);

        Sanctum::actingAs($provider, ['*']);


        $newCategory = Category::factory()->create();
        $newSkills = Skill::factory()->count(2)->create()->pluck('name')->toArray(); // Get skill names instead of IDs

        $data = [
            'title' => 'Updated Job Title',
            'description' => 'Updated Description',
            'salary' => 60000,
            'type' => 'Part-time',
            'expiry_date' => '2025-01-01',
            'cover_letter' => false,
            'question' => true,
            'category_ids' => [$newCategory->id],
            'jobskills' => $newSkills, // Use skill names
        ];

        $response = $this->putJson("api/updatejob/{$job->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'job' => [
                    'id',
                    'title',
                    'description',
                    'salary',
                    'type',
                    'expiry_date',
                    'cover_letter',
                    'question',
                    'created_at',
                    'updated_at',
                    'categories' => [
                        '*' => ['id', 'title'],
                    ],
                    'skills' => [
                        '*' => ['id', 'name']
                    ],
                ],
            ]);


        $this->assertDatabaseHas('job_postings', [
            'id' => $job->id,
            'title' => 'Updated Job Title',
        ]);


        $this->assertDatabaseHas('job_categories', [
            'job_id' => $job->id,
            'category_id' => $newCategory->id,
        ]);


        foreach ($newSkills as $skillName) {
            $this->assertDatabaseHas('job_skills', [
                'job_posting_id' => $job->id, // Ensure you're using the correct column name
                'skill_id' => Skill::where('name', $skillName)->first()->id,
            ]);
        }
    }

    public function test_provider_can_delete_job_successfully()
    {
        $provider = Provider::factory()->create();
        $job = JobPosting::factory()->create([
            'provider_id' => $provider->id,
        ]);

        Sanctum::actingAs($provider, ['*']);


        $response = $this->deleteJson("api/deletejob/{$job->id}");


        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Job deleted'
            ]);


        $this->assertDatabaseMissing('job_postings', [
            'id' => $job->id,
        ]);


        $this->assertDatabaseMissing('job_categories', [
            'job_id' => $job->id,
        ]);

        $this->assertDatabaseMissing('job_skills', [
            'job_posting_id' => $job->id,
        ]);
    }

    public function test_provider_cannot_create_job_without_token()
    {

        $category = Category::factory()->create();


        $data = [
            'title' => 'Test Job',
            'description' => 'Test Description',
            'salary' => 50000,
            'type' => 'Full-time',
            'category_ids' => [$category->id],
            'cover_letter' => true,
            'question' => false,
        ];


        $response = $this->postJson('api/provider/jobs/create', $data);


        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
            ]);
    }


    public function test_provider_can_create_questionnaire_successfully()
    {
        $provider = Provider::factory()->create();
        $job = JobPosting::factory()->create([
            'provider_id' => $provider->id,
            'question' => true,
        ]);

        Sanctum::actingAs($provider, ['*']);

        $questionsData = [
            'job_id' => $job->id,
            'questions' => [
                [
                    'question' => 'What is your preferred programming language?',
                    'answer_type' => 'string',
                    'min_value' => 0,
                    'max_value' => 255,
                ],
                [
                    'question' => 'Rate your experience from 1 to 10',
                    'answer_type' => 'int',
                    'min_value' => 1,
                    'max_value' => 10,
                ],
            ],
        ];

        $response = $this->postJson('api/provider/jobs/question/create', $questionsData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'job_id',
                        'question',
                        'answer_type',
                        'min_value',
                        'max_value',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        foreach ($questionsData['questions'] as $question) {
            $this->assertDatabaseHas('questionnaires', [
                'job_id' => $job->id,
                'question' => $question['question'],
            ]);
        }
    }


    public function test_provider_cannot_create_questionnaire_with_empty_questions_array()
    {
        $provider = Provider::factory()->create();
        $job = JobPosting::factory()->create([
            'provider_id' => $provider->id,
            'question' => true,
        ]);

        Sanctum::actingAs($provider, ['*']);

        $questionsData = [
            'job_id' => $job->id,
            'questions' => [],
        ];

        $response = $this->postJson('api/provider/jobs/question/create', $questionsData);


        $response->assertStatus(422)
            ->assertJsonStructure([
                'errors' => [
                    'questions',
                ],
            ]);


        $this->assertContains(
            'The questions field is required.',
            $response->json('errors.questions')
        );
    }

    public function test_provider_cannot_create_questionnaire_without_question_field()
    {
        $provider = Provider::factory()->create();
        $job = JobPosting::factory()->create([
            'provider_id' => $provider->id,
            'question' => true,
        ]);

        Sanctum::actingAs($provider, ['*']);

        $questionsData = [
            'job_id' => $job->id,
            'questions' => [
                [
                    'answer_type' => 'string',
                    'min_value' => 0,
                    'max_value' => 255,
                ],
            ],
        ];

        $response = $this->postJson('api/provider/jobs/question/create', $questionsData);

        // Assert status 422
        $response->assertStatus(422);

        // Extract and assert errors
        $errors = $response->json('errors');
        $this->assertArrayHasKey('questions.0.question', $errors);
        $this->assertContains(
            'The questions.0.question field is required.',
            $errors['questions.0.question']
        );
    }







}
