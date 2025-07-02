<?php

namespace Tests\Feature;

use App\Models\Url;
use App\Models\User;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_correctly()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('welcome');
        $response->assertSee('URL Shortener');
        $response->assertSee('Shorten Your URLs');
    }

    public function test_homepage_shows_statistics()
    {
        // Create some test data
        Url::factory()->count(3)->create();
        Click::factory()->count(5)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('3'); // URLs count
        $response->assertSee('5'); // Clicks count
    }

    public function test_shorten_url_via_post_request()
    {
        $response = $this->postJson('/shorten', [
            'original_url' => 'https://example.com'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'original_url',
                'short_code',
                'short_url'
            ]
        ]);

        $this->assertDatabaseHas('urls', [
            'original_url' => 'https://example.com'
        ]);
    }

    public function test_shorten_url_with_custom_code()
    {
        $response = $this->postJson('/shorten', [
            'original_url' => 'https://example.com',
            'custom_code' => 'custom123'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'short_code' => 'custom123'
            ]
        ]);

        $this->assertDatabaseHas('urls', [
            'original_url' => 'https://example.com',
            'short_code' => 'custom123'
        ]);
    }

    public function test_shorten_url_with_options()
    {
        $response = $this->postJson('/shorten', [
            'original_url' => 'https://example.com',
            'title' => 'Test Title',
            'description' => 'Test Description',
            'password' => 'secret123',
            'expires_at' => Carbon::now()->addDays(30)->format('Y-m-d H:i:s')
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        $this->assertDatabaseHas('urls', [
            'original_url' => 'https://example.com',
            'title' => 'Test Title',
            'description' => 'Test Description'
        ]);
    }

    public function test_shorten_url_rejects_invalid_url()
    {
        $response = $this->postJson('/shorten', [
            'original_url' => 'not-a-valid-url'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['original_url']);
    }

    public function test_shorten_url_rejects_duplicate_custom_code()
    {
        Url::factory()->create(['short_code' => 'duplicate']);

        $response = $this->postJson('/shorten', [
            'original_url' => 'https://example.com',
            'custom_code' => 'duplicate'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['custom_code']);
    }

    public function test_shorten_url_via_form_submission()
    {
        $response = $this->post('/shorten', [
            'original_url' => 'https://example.com'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHas('success');
    }

    public function test_bulk_shorten_urls()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/bulk-shorten', [
            'urls' => [
                'https://example.com',
                'https://google.com',
                'https://github.com'
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'results' => [
                    '*' => [
                        'original_url',
                        'short_code',
                        'short_url'
                    ]
                ]
            ]
        ]);

        $this->assertDatabaseHas('urls', ['original_url' => 'https://example.com']);
        $this->assertDatabaseHas('urls', ['original_url' => 'https://google.com']);
        $this->assertDatabaseHas('urls', ['original_url' => 'https://github.com']);
    }

    public function test_bulk_shorten_requires_authentication()
    {
        $response = $this->postJson('/bulk-shorten', [
            'urls' => ['https://example.com']
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Authentication required for bulk operations'
        ]);
    }

    public function test_bulk_shorten_rejects_invalid_urls()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/bulk-shorten', [
            'urls' => [
                'https://example.com',
                'invalid-url',
                'https://google.com'
            ]
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['urls.1']);

        // No URLs should be created since validation failed
        $this->assertDatabaseMissing('urls', ['original_url' => 'https://example.com']);
        $this->assertDatabaseMissing('urls', ['original_url' => 'https://google.com']);
        $this->assertDatabaseMissing('urls', ['original_url' => 'invalid-url']);
    }

    public function test_show_url_details()
    {
        $url = Url::factory()->create([
            'title' => 'Test URL',
            'description' => 'Test Description'
        ]);
        
        // Create some clicks for this URL
        Click::factory()->count(5)->create(['url_id' => $url->id]);

        $response = $this->get("/info/{$url->short_code}");

        $response->assertStatus(200);
        $response->assertViewIs('url.show');
        $response->assertSee($url->title);
        $response->assertSee($url->description);
        $response->assertSee($url->original_url);
        $response->assertSee('5'); // Click count
    }

    public function test_show_url_details_requires_ownership()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        
        $url = Url::factory()->create(['user_id' => $user->id]);

        // Try to access as different user
        $response = $this->actingAs($otherUser)->get("/info/{$url->short_code}");
        $response->assertStatus(403);

        // Try to access without authentication
        $response = $this->get("/info/{$url->short_code}");
        $response->assertStatus(403);

        // Should work for owner
        $response = $this->actingAs($user)->get("/info/{$url->short_code}");
        $response->assertStatus(200);
    }

    public function test_show_url_details_allows_public_urls()
    {
        $url = Url::factory()->create(['user_id' => null]); // Public URL

        $response = $this->get("/info/{$url->short_code}");
        $response->assertStatus(200);
    }

    public function test_qr_code_generation_in_response()
    {
        $response = $this->postJson('/shorten', [
            'original_url' => 'https://example.com'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'short_url'
            ]
        ]);

        // QR code should be available via the show route
        $data = $response->json('data');
        $shortCode = $data['short_code'];
        
        $showResponse = $this->get("/info/{$shortCode}");
        $showResponse->assertStatus(200);
        $showResponse->assertViewHas('qr_code_url');
    }
} 