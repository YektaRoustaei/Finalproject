<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Seeker; // Adjust to your actual seeker model
use App\Models\JobPosting;
use App\Models\JobAlert;

class JobAlertTest extends TestCase
{
    use RefreshDatabase;

    protected $seeker;
    protected $jobPosting;

    public function setUp(): void
    {
        parent::setUp();

        $this->seeker = Seeker::factory()->create();
        $this->jobPosting = JobPosting::factory()->create();
    }

    /**
     * Test if unauthenticated users receive a 401 error.
     */
    public function test_mark_not_interested_unauthenticated()
    {
        $response = $this->postJson('/api/jobs/not-interested');

        $response->assertStatus(404)
        ->assertJson([
            'message' => 'The route api/jobs/not-interested could not be found.',
        ]);
    }

    /**
     * Test if job_id is required and valid.
     */

}
