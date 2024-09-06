<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\JobPosting;
use App\Models\Provider;
use App\Models\User;
use App\Models\Seeker;
use App\Models\CurriculumVitae;
use App\Models\Skill;
use App\Models\SeekerSkill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    protected $seeker;

    protected function setUp(): void
    {
        parent::setUp();


        $this->seeker = Seeker::factory()->create();
        $this->actingAs($this->seeker, 'sanctum');
    }

    /** @test */
    public function it_can_recommend_jobs_based_on_search_term_and_city()
    {

        $city = City::factory()->create(['city_name' => 'London']);
        $provider = Provider::factory()->create(['city_id' => $city->id]);

        $job = JobPosting::factory()->create([
            'provider_id' => $provider->id,
            'title' => 'React Developer',
            'description' => 'Looking for a React developer.',
            'salary' => 90000,
            'type' => 'Full Time',
        ]);

        $skill = Skill::factory()->create(['name' => 'React']);


        $curriculumVitae = \App\Models\CurriculumVitae::factory()->create([
            'seeker_id' => $this->seeker->id,
        ]);


        SeekerSkill::create([
            'seeker_id' => $this->seeker->id,
            'skill_id' => $skill->id,
            'curriculum_vitae_id' => $curriculumVitae->id,
        ]);

        $job->jobSkills()->create(['skill_id' => $skill->id]);


        $response = $this->get('/api/recommend?search_term=React&city=London');


        $response->assertStatus(200)
            ->assertJsonStructure([
                'jobs' => [
                    '*' => [
                        'id',
                        'title',
                        'salary',
                        'type',
                        'description',
                        'provider_city',
                        'provider_name',
                        'job_skills',
                        'matching_skills',
                        'matching_skills_count',
                        'distance_from_input_city',
                        'distance_from_seeker_city',
                    ],
                ],
                'current_page',
                'total_pages',
                'total_jobs',
            ]);

        $responseData = $response->json();


        $this->assertNotEmpty($responseData['jobs']);
        $this->assertEquals('React Developer', $responseData['jobs'][0]['title']);
    }

    /** @test */
    public function it_returns_error_if_seeker_not_authenticated()
    {

        \Illuminate\Support\Facades\Auth::shouldReceive('guard')
            ->with('sanctum')
            ->andReturnSelf();
        \Illuminate\Support\Facades\Auth::shouldReceive('user')
            ->andReturn(null);


        $response = $this->get('/api/recommend?search_term=React&city=London');


        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthenticated.']);
    }

    /** @test */
    public function it_returns_error_if_curriculum_vitae_not_found()
    {

        $seekerWithoutCV = Seeker::factory()->create();
        $this->actingAs($seekerWithoutCV, 'sanctum');


        $response = $this->get('/api/recommend?search_term=React&city=London');


        $response->assertStatus(404)
            ->assertJson(['error' => 'No Curriculum Vitae found.']);
    }

    /** @test */
    public function it_returns_jobs_even_if_city_not_found()
    {

        $curriculumVitae = CurriculumVitae::factory()->create(['seeker_id' => $this->seeker->id]);


        $otherCity = City::factory()->create(['city_name' => 'Manchester']);
        $provider = Provider::factory()->create(['city_id' => $otherCity->id]);

        $job = JobPosting::factory()->create([
            'provider_id' => $provider->id,
            'title' => 'React Developer',
            'description' => 'Looking for a React developer in Manchester.',
            'salary' => 85000,
            'type' => 'Part Time',
        ]);

        $skill = Skill::factory()->create(['name' => 'React']);


        SeekerSkill::create([
            'seeker_id' => $this->seeker->id,
            'skill_id' => $skill->id,
            'curriculum_vitae_id' => $curriculumVitae->id,
        ]);

        $job->jobSkills()->create(['skill_id' => $skill->id]);

        $response = $this->get('/api/recommend?search_term=React&city=NonExistentCity');


        $response->assertStatus(200);
        $responseData = $response->json();


        $this->assertNotEmpty($responseData['jobs']);
        $this->assertEquals('React Developer', $responseData['jobs'][0]['title']);
    }
    /** @test */

    public function it_returns_jobs_even_if_no_jobs_match_search_term()
    {

        $city = City::factory()->create(['city_name' => 'London']);
        $provider = Provider::factory()->create(['city_id' => $city->id]);


        $job = JobPosting::factory()->create([
            'provider_id' => $provider->id,
            'title' => 'Python Developer',
            'description' => 'Looking for a Python developer.',
            'salary' => 95000,
            'type' => 'Full Time',
        ]);

        $skill = Skill::factory()->create(['name' => 'Python']);


        $curriculumVitae = CurriculumVitae::factory()->create(['seeker_id' => $this->seeker->id]);


        SeekerSkill::create([
            'seeker_id' => $this->seeker->id,
            'skill_id' => $skill->id,
            'curriculum_vitae_id' => $curriculumVitae->id,
        ]);

        $job->jobSkills()->create(['skill_id' => $skill->id]);


        $response = $this->get('/api/recommend?search_term=React&city=London');


        $response->assertStatus(200);
        $responseData = $response->json();


        $this->assertEmpty($responseData['jobs']);
    }

    /** @test */
    public function it_returns_jobs_even_if_no_jobs_in_provided_city()
    {

        $city = City::factory()->create(['city_name' => 'London']);
        $provider = Provider::factory()->create(['city_id' => $city->id]);


        $otherCity = City::factory()->create(['city_name' => 'Manchester']);
        $providerInOtherCity = Provider::factory()->create(['city_id' => $otherCity->id]);

        $job = JobPosting::factory()->create([
            'provider_id' => $providerInOtherCity->id,
            'title' => 'React Developer',
            'description' => 'Looking for a React developer in Manchester.',
            'salary' => 85000,
            'type' => 'Part Time',
        ]);

        $skill = Skill::factory()->create(['name' => 'React']);


        $curriculumVitae = CurriculumVitae::factory()->create(['seeker_id' => $this->seeker->id]);


        SeekerSkill::create([
            'seeker_id' => $this->seeker->id,
            'skill_id' => $skill->id,
            'curriculum_vitae_id' => $curriculumVitae->id,
        ]);

        $job->jobSkills()->create(['skill_id' => $skill->id]);


        $response = $this->get('/api/recommend?search_term=React&city=London');


        $response->assertStatus(200);
        $responseData = $response->json();


        $this->assertNotEmpty($responseData['jobs']);
        $this->assertEquals('React Developer', $responseData['jobs'][0]['title']);
    }

    /** @test */
    public function it_handles_pagination_correctly()
    {

        $city = City::factory()->create(['city_name' => 'London']);
        $provider = Provider::factory()->create(['city_id' => $city->id]);

        for ($i = 0; $i < 15; $i++) {
            JobPosting::factory()->create([
                'provider_id' => $provider->id,
                'title' => 'Job ' . $i,
                'description' => 'Description for job ' . $i,
                'salary' => 10000 + $i,
                'type' => 'Full Time',
            ]);
        }

        $curriculumVitae = CurriculumVitae::factory()->create(['seeker_id' => $this->seeker->id]);


        $response = $this->get('/api/recommend?search_term=Job&city=London&page=2');


        $response->assertStatus(200);
        $responseData = $response->json();

        $this->assertEquals(2, $responseData['current_page']);
        $this->assertEquals(2, $responseData['total_pages']);
        $this->assertCount(5, $responseData['jobs']);
    }


}
