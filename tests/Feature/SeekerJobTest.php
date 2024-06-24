<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
        $response->assertStatus(200);
    }
    public function testApplyJobWithDuplicatedJobId()
    {
        $seeker = \App\Models\Seeker::factory()->create();
        $job = \App\Models\JobPosting::factory()->create();
        $appliedJob = \App\Models\AppliedJob::factory()->create([
            'seeker_id' => $seeker->id,
            'job_id' => $job->id,
        ]);
        $response = $this->actingAs($seeker, 'sanctum')
            ->postJson('/api/seeker/jobs/apply', [
                'job_id' => $job->id,
            ]);
        $response->assertStatus(400)
            ->assertJson(['error' => 'You have already applied for this job']);
    }

}
