<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Seeker;
use App\Models\JobPosting;
use App\Models\AppliedJob;
use App\Models\Provider;
use App\Models\SavedJob;
use App\Models\CurriculumVitae;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\Skill;

use App\Models\JobSkill;
use App\Models\Category;


class InfoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the SeekerInfo retrieval.
     */
    public function test_seeker_info_retrieval(): void
    {
        $city = City::factory()->create();
        $seeker = Seeker::factory()->create(['city_id' => $city->id]);

        $jobPosting = JobPosting::factory()->create();

        $appliedJob = AppliedJob::factory()->create([
            'seeker_id' => $seeker->id,
            'job_id' => $jobPosting->id,
        ]);

        $savedJob = SavedJob::factory()->create([
            'seeker_id' => $seeker->id,
            'job_id' => $jobPosting->id,
        ]);

        $curriculumVitae = CurriculumVitae::factory()->create(['seeker_id' => $seeker->id]);

        Sanctum::actingAs($seeker, ['*']);

        $response = $this->getJson('/api/seeker/get-info');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'first_name',
            'last_name',
            'email',
            'address',
            'phonenumber',
            'saved_jobs',
            'applied_jobs' => [
                '*' => [
                    'job_id',
                    'status',
                    'curriculum_vitae_id',
                    'cover_letter_id',
                    'created_at',
                ],
            ],
            'curriculum_vitae' => [
                '*' => [
                    'educations' => [
                        '*' => [
                            'start_date',
                            'end_date',
                        ],
                    ],
                    'jobExperiences' => [
                        '*' => [
                            'start_date',
                            'end_date',
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertJson([
            'first_name' => $seeker->first_name,
            'last_name' => $seeker->last_name,
            'email' => $seeker->email,
            'address' => $city->city_name,
            'phonenumber' => $seeker->phonenumber,
        ]);
    }

    public function test_get_all_seekers_no_search(): void
    {
        $city = City::factory()->create();
        $seeker = Seeker::factory()->create(['city_id' => $city->id]);

        CurriculumVitae::factory()->create(['seeker_id' => $seeker->id]);

        $response = $this->getJson('/api/seeker/all');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'first_name' => $seeker->first_name,
            'last_name' => $seeker->last_name,
            'email' => $seeker->email,
            'address' => $city->city_name,
            'phonenumber' => $seeker->phonenumber,
        ]);
    }

    public function test_get_all_seekers_with_search(): void
    {
        $city = City::factory()->create();

        $seeker = Seeker::factory()->create([
            'city_id' => $city->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $otherSeeker = Seeker::factory()->create([
            'city_id' => $city->id,
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);

        CurriculumVitae::factory()->create(['seeker_id' => $seeker->id]);
        CurriculumVitae::factory()->create(['seeker_id' => $otherSeeker->id]);

        $response = $this->getJson('/api/seeker/all?search=John');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $response->assertJsonMissing([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
        ]);
    }


    public function test_get_all_seekers_no_seekers(): void
    {
        $response = $this->getJson('/api/seeker/all');

        $response->assertStatus(200);

        $response->assertJson([]);
    }

    public function test_get_all_seekers_with_partial_match(): void
    {
        $city = City::factory()->create();

        $seeker = Seeker::factory()->create([
            'city_id' => $city->id,
            'first_name' => 'Johnathan',
        ]);

        $otherSeeker = Seeker::factory()->create([
            'city_id' => $city->id,
            'first_name' => 'Jane',
        ]);

        CurriculumVitae::factory()->create(['seeker_id' => $seeker->id]);
        CurriculumVitae::factory()->create(['seeker_id' => $otherSeeker->id]);

        $response = $this->getJson('/api/seeker/all?search=John');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'first_name' => 'Johnathan',
        ]);

        $response->assertJsonMissing([
            'first_name' => 'Jane',
        ]);
    }



    /** provider tests */


    public function test_get_all_providers_no_search(): void
    {
        $city = City::factory()->create();
        $provider = Provider::factory()->create([
            'city_id' => $city->id,
        ]);

        JobPosting::factory()->create([
            'provider_id' => $provider->id,
        ]);

        $response = $this->getJson('/api/provider/all');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'company_name' => $provider->company_name,
            'address' => $city->city_name,
            'telephone' => $provider->telephone,
            'email' => $provider->email,
        ]);
    }

    public function test_get_all_providers_with_search(): void
    {
        $city = City::factory()->create();
        $provider = Provider::factory()->create([
            'city_id' => $city->id,
            'company_name' => 'Tech Innovations',
        ]);

        $otherProvider = Provider::factory()->create([
            'city_id' => $city->id,
            'company_name' => 'Creative Solutions',
        ]);

        JobPosting::factory()->create([
            'provider_id' => $provider->id,
        ]);
        JobPosting::factory()->create([
            'provider_id' => $otherProvider->id,
        ]);

        $response = $this->getJson('/api/provider/all?search=Tech');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'company_name' => 'Tech Innovations',
        ]);

        $response->assertJsonMissing([
            'company_name' => 'Creative Solutions',
        ]);
    }

    public function test_get_all_providers_with_partial_match(): void
    {
        $city = City::factory()->create();
        $provider = Provider::factory()->create([
            'city_id' => $city->id,
            'company_name' => 'Tech Innovations',
        ]);

        $otherProvider = Provider::factory()->create([
            'city_id' => $city->id,
            'company_name' => 'Innovative Tech',
        ]);

        JobPosting::factory()->create([
            'provider_id' => $provider->id,
        ]);
        JobPosting::factory()->create([
            'provider_id' => $otherProvider->id,
        ]);

        $response = $this->getJson('/api/provider/all?search=Tech');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'company_name' => 'Tech Innovations',
        ]);
        $response->assertJsonFragment([
            'company_name' => 'Innovative Tech',
        ]);
    }
}

