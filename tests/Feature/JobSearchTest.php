<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\JobPosting;
use App\Models\City;
use App\Models\Synonym;
use App\Models\Provider;

class JobSearchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_search_jobs_by_title()
    {

        $job = JobPosting::factory()->create(['title' => 'Software Engineer']);


        $response = $this->get('api/search?search_term=&city=Manchester');


        $response->assertStatus(200);
        $response->assertJson([
            'jobs' => [
                [
                    'title' => 'Software Engineer',
                    'id' => $job->id,
                    'salary' => $job->salary,
                    'type' => $job->type,
                    'description' => $job->description,
                    'provider_city' => $job->provider->city->city_name ?? 'Unknown',
                    'provider_name' => $job->provider->company_name,
                    'job_skills' => $job->jobSkills->pluck('skill.name')->toArray(),
                    'matching_skills' => $job->matching_skills ?? [],
                    'matching_skills_count' => $job->matching_skills_count ?? 0,
                    'distance_from_input_city' => $job->distance_from_input_city,
                    'distance_from_seeker_city' => $job->distance_from_seeker_city,
                ]
            ]
        ]);
    }

    /** @test */
    public function it_can_search_jobs_by_city()
    {
        // Arrange
        $city = City::factory()->create(['city_name' => 'San Francisco']);
        $provider = Provider::factory()->create(['city_id' => $city->id]); // Ensure the provider is linked to the city
        $job = JobPosting::factory()->create([
            'provider_id' => $provider->id,  // Link job to the provider
            // Add other necessary fields here
        ]);

        // Act
        $response = $this->get('/api/search?search_term=&city=San Francisco');

        // Debug the response if necessary
        // dd($response->json());

        // Assert
        $response->assertStatus(200);

        $responseData = $response->json();

        // Assert that the response contains pagination information and the jobs
        $this->assertArrayHasKey('current_page', $responseData);
        $this->assertArrayHasKey('total_pages', $responseData);
        $this->assertArrayHasKey('total_jobs', $responseData);

        $this->assertArrayHasKey('jobs', $responseData);
        $this->assertIsArray($responseData['jobs']);
        $this->assertNotEmpty($responseData['jobs']);

        // Check the first job in the response
        $jobData = $responseData['jobs'][0];

        $this->assertEquals($job->id, $jobData['id']);
        $this->assertEquals($job->title, $jobData['title']);
        $this->assertEquals($job->salary, $jobData['salary']);
        $this->assertEquals($job->type, $jobData['type']);
        $this->assertEquals($job->description, $jobData['description']);
    }

    /** @test */
    public function it_can_search_jobs_by_job_type()
    {

        $job = JobPosting::factory()->create(['type' => 'Full Time']);

        $response = $this->get('/api/search?search_term=&city=&job_type=Full Time');




        $response->assertStatus(200);


        $response->assertJson(function ($json) use ($job) {

            $json->has('current_page')
                ->has('total_pages')
                ->has('total_jobs')
                ->has('jobs')
                ->whereType('jobs', 'array')
                ->has('jobs.0')
                ->where('jobs.0.id', $job->id)
                ->where('jobs.0.title', $job->title)
                ->where('jobs.0.salary', $job->salary)
                ->where('jobs.0.type', $job->type)
                ->where('jobs.0.description', $job->description)
                ->where('jobs.0.provider_name', $job->provider->company_name);
        });
    }

    /** @test */
    public function it_can_search_jobs_with_synonyms()
    {

        $synonym = Synonym::factory()->create(['title' => 'Dev', 'synonym1' => 'Developer']);
        $job = JobPosting::factory()->create(['title' => 'Developer']);

        $response = $this->get('api/search?search_term=Dev&city=Manchesterv');

        $response->assertStatus(200);
        $response->assertJson([
            'jobs' => [
                [
                    'title' => 'Developer',
                    'id' => $job->id,
                    'salary' => $job->salary,
                    'type' => $job->type,
                    'description' => $job->description,
                    'provider_name' => $job->provider->company_name,
                ]
            ]
        ]);
    }
}
