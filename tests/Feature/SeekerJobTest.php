<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Seeker;
use App\Models\JobPosting;
use App\Models\AppliedJob;
use App\Models\Answers;
use App\Models\Questionnaire;



class SeekerJobTest extends TestCase
{
    use RefreshDatabase;
    public function testApplyJob()
    {
        $response = $this->postJson('/api/seeker/jobs/apply', [
            'job_id' => '1',
            'seeker_id' => '1',
        ]);
        $response->assertStatus(401)
        ->assertJson(['error' => 'Unauthorized']);
    }
    public function testApplyJobWithInvalidJobId()
    {
        $seeker = \App\Models\Seeker::factory()->create();
        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/apply', [
                'job_id' => '1',
            ]);
        $response->assertStatus(422);
        $this->assertArrayHasKey('errors', $response->json());
        $this->assertCount(1, $response->json()['errors']);
    }
    public function testApplyJobWithValidJobId()
    {
        $seeker = \App\Models\Seeker::factory()->create();
        $job = \App\Models\JobPosting::factory()->create();

        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/apply', [
                'job_id' => $job->id,
            ]);

        $response->assertStatus(201);
    }
    public function testApplyJobWithDuplicatedJobId()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();
        $appliedJob = AppliedJob::factory()->create([
            'seeker_id' => $seeker->id,
            'job_id' => $job->id,
            'status' => 'hold',
        ]);

        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/apply', [
                'job_id' => $job->id,
            ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'You have already applied for this job']);
    }
    public function testStoreAnswersSuccessfully()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();
        $question = Questionnaire::factory()->create();

        $response = $this->actingAs($seeker, 'sanctum')->postJson('/api/answers/' . $job->id, [
            'job_id' => $job->id,
            'answers' => [
                [
                    'question_id' => $question->id,
                    'answer' => 'Test answer'
                ]
            ]
        ]);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Answers stored successfully']);

        $this->assertDatabaseHas('answers', [
            'seeker_id' => $seeker->id,
            'job_id' => $job->id,
            'question_id' => $question->id,
            'answer' => 'Test answer'
        ]);
    }
    public function testStoreAnswersUnauthorized()
    {
        $job = JobPosting::factory()->create();
        $question = Questionnaire::factory()->create();

        $response = $this->postJson('/api/answers/'. $job->id, [
            'job_id' => $job->id,
            'answers' => [
                [
                    'question_id' => $question->id,
                    'answer' => 'Test answer'
                ]
            ]
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }
    public function testGetAnswersSuccessfully()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();
        $answer = Answers::factory()->create([
            'seeker_id' => $seeker->id,
            'job_id' => $job->id,
        ]);

        $response = $this->actingAs($seeker, 'sanctum')
            ->getJson("/api/getanswers/{$job->id}/{$seeker->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'seeker_id' => $seeker->id,
                'job_id' => $job->id,
                'answer' => $answer->answer,
            ]);
    }
    public function testGetAnswersWhenNoneExist()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();

        $response = $this->actingAs($seeker, 'sanctum')
            ->getJson("/api/getanswers/{$job->id}/{$seeker->id}");

        $response->assertStatus(404)
            ->assertJson(['message' => 'No answers found for this job']);
    }
    public function testStoreCoverLetterSuccessfully()
    {
        $seeker = Seeker::factory()->create();

        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/coverletter/create', [
                'content' => 'This is a test cover letter.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Cover Letter created successfully',
            ]);

        $this->assertDatabaseHas('cover_letters', [
            'seeker_id' => $seeker->id,
            'content' => 'This is a test cover letter.',
        ]);
    }
    public function testStoreCoverLetterUnauthorized()
    {
        $response = $this->postJson('/api/seeker/coverletter/create', [
            'content' => 'This is a test cover letter.',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }
    public function testStoreCoverLetterWithEmptyContent()
    {
        $seeker = Seeker::factory()->create();


        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/coverletter/create', [
                'content' => '',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Cover Letter created successfully',
            ]);


        $this->assertDatabaseHas('cover_letters', [
            'seeker_id' => $seeker->id,
            'content' => null,
        ]);
    }
    public function testStoreCoverLetterWithNullContent()
    {
        $seeker = Seeker::factory()->create();


        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/coverletter/create', [
                'content' => null,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Cover Letter created successfully',
            ]);

        $this->assertDatabaseHas('cover_letters', [
            'seeker_id' => $seeker->id,
            'content' => null,
        ]);
    }





}
