<?php

namespace Tests\Feature;

use App\Http\Middleware\Provider\Job\PrepareCreatingJobProcess;
use App\Models\Category;
use App\Models\JobPosting;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
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
            'salary' => '50000',
            'type' => 'Full-time',
            'location' => 'Remote',
            'category_ids' => [$category->id],
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
                         'location',
                         'created_at',
                         'updated_at',
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

    public function test_provider_can_create_job_un1successfully_missing_data()
    {
        $provider = Provider::factory()->create();

        $category = Category::factory()->create();

        Sanctum::actingAs($provider, ['*']);

        $data = []; // Empty array

        $response = $this->postJson('api/provider/jobs/create', $data);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'errors' => [
                         '*' => [
                             'field',
                             'message',
                         ],
                     ],
                 ]);    

        $this->assertCount(4, $response['errors']);         
    }

    public function test_middleware_rejects_invalid_category_ids()
    {
        $request = Request::create('/job/create', 'POST', [
            'title' => 'Test Job',
            'description' => 'Test Description',
            'salary' => '50000',
            'type' => 'Full-time',
            'location' => 'Remote',
            'category_ids' => [999], // Assuming 999 does not exist
        ]);

        $middleware = new PrepareCreatingJobProcess();

        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true], 200);
        });

        $this->assertEquals(422, $response->status());
        $this->assertArrayHasKey('errors', $response->getData(true));
        $this->assertEquals('The selected category_ids.0 is invalid.', $response->getData(true)['errors'][0]['message']);
    }
}
