<?php

namespace Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Seeker;
use App\Models\JobPosting;
use App\Models\SavedJob;

class SavedJobTest extends TestCase
{
    use RefreshDatabase;

    public function testSaveJob()
    {
        $response = $this->postJson('/api/seeker/jobs/save', [
            'job_id' => '1',
            'seeker_id' => '1',
        ]);
        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function testUnSaveJob()
    {
        $response = $this->postJson('/api/seeker/jobs/unsave', [
            'job_id' => '1',
            'seeker_id' => '1',
        ]);
        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function testSaveJobWithInvalidJobId()
    {
        $seeker = Seeker::factory()->create();
        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/save', [
                'job_id' => '1', // Invalid job ID for testing purposes
            ]);
        $response->assertStatus(422);
        $this->assertArrayHasKey('errors', $response->json());
        $this->assertCount(1, $response->json()['errors']);
    }

    public function testUnSaveJobWithInvalidJobId()
    {
        $seeker = Seeker::factory()->create();
        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/unsave', [
                'job_id' => '1', // Invalid job ID for testing purposes
            ]);
        $response->assertStatus(422);
        $this->assertArrayHasKey('errors', $response->json());
        $this->assertCount(1, $response->json()['errors']);
    }

    public function testSaveJobWithValidJobId()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();
        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/save', [
                'job_id' => $job->id,
            ]);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Job Saved successfully']);

        $this->assertDatabaseHas('saved_jobs', [
            'job_id' => $job->id,
            'seeker_id' => $seeker->id,
        ]);
    }

    public function testUnSaveJobWithValidJobId()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();
        $savedJob = SavedJob::factory()->create([
            'seeker_id' => $seeker->id,
            'job_id' => $job->id,
        ]);

        $this->assertDatabaseHas('saved_jobs', [
            'job_id' => $job->id,
            'seeker_id' => $seeker->id,
        ]);

        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/unsave', [
                'job_id' => $job->id,
            ]);
        $response->assertStatus(200)
            ->assertJson(['message' => 'Job Unsaved successfully']);

        $this->assertDatabaseMissing('saved_jobs', [
            'job_id' => $job->id,
            'seeker_id' => $seeker->id,
        ]);
    }

    public function testJobAlreadySaved()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();
        SavedJob::factory()->create([
            'seeker_id' => $seeker->id,
            'job_id' => $job->id,
        ]);

        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/save', [
                'job_id' => $job->id,
            ]);

        $response->assertStatus(409)
            ->assertJson(['message' => 'Job has already been saved']);
    }

    public function testJobNotSavedYet()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();

        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/save', [
                'job_id' => $job->id,
            ]);

        $response->assertStatus(200);
    }

    public function testJobNotSavedBefore()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();

        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/unsave', [
                'job_id' => $job->id,
            ]);

        $response->assertStatus(409)
            ->assertJson(['message' => 'Job not saved before']);
    }

    public function testJobSavedBefore()
    {
        $seeker = Seeker::factory()->create();
        $job = JobPosting::factory()->create();
        SavedJob::factory()->create([
            'seeker_id' => $seeker->id,
            'job_id' => $job->id,
        ]);

        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/unsave', [
                'job_id' => $job->id,
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Job Unsaved successfully']);
    }
}
