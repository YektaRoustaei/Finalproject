<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function testCategoryListCanBeRetrieved()
    {
        $category = Category::factory()->create([
            'title' => 'Test Category',
        ]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJson([
                [
                    'title' => 'Test Category',
                ]
            ]);
    }
}
