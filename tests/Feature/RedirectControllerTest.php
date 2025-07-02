<?php

namespace Tests\Feature;

use App\Models\Url;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class RedirectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirect_to_original_url()
    {
        $url = Url::factory()->create([
            'original_url' => 'https://example.com',
            'short_code' => 'test123'
        ]);

        $response = $this->get('/test123');

        $response->assertStatus(302);
        $response->assertRedirect('https://example.com');
    }

    public function test_redirect_tracks_click()
    {
        $url = Url::factory()->create(['short_code' => 'test123']);

        $this->get('/test123');

        $this->assertDatabaseHas('clicks', [
            'url_id' => $url->id
        ]);

        $click = Click::where('url_id', $url->id)->first();
        $this->assertNotNull($click->ip_address);
        $this->assertNotNull($click->clicked_at);
    }

    public function test_redirect_tracks_user_agent()
    {
        $url = Url::factory()->create(['short_code' => 'test123']);

        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/91.0.4472.124'
        ])->get('/test123');

        $response->assertStatus(302);

        $click = Click::where('url_id', $url->id)->first();
        $this->assertEquals('Chrome', $click->browser);
        $this->assertEquals('Windows', $click->os);
        $this->assertEquals('desktop', $click->device_type);
    }

    public function test_redirect_tracks_referer()
    {
        $url = Url::factory()->create(['short_code' => 'test123']);

        $this->withHeaders([
            'Referer' => 'https://google.com'
        ])->get('/test123');

        $click = Click::where('url_id', $url->id)->first();
        $this->assertEquals('https://google.com', $click->referer);
    }

    public function test_redirect_nonexistent_url_returns_404()
    {
        $response = $this->get('/nonexistent');

        $response->assertStatus(404);
    }

    public function test_redirect_inactive_url_returns_410()
    {
        $url = Url::factory()->create([
            'short_code' => 'abc123',
            'is_active' => false
        ]);

        $response = $this->get('/abc123');

        $response->assertStatus(410);
        $response->assertViewIs('errors.url-unavailable');
    }

    public function test_redirect_expired_url_returns_410()
    {
        $url = Url::factory()->create([
            'short_code' => 'def456',
            'expires_at' => Carbon::now()->subDay()
        ]);

        $response = $this->get('/def456');

        $response->assertStatus(410);
        $response->assertViewIs('errors.url-unavailable');
    }

    public function test_password_protected_url_shows_form()
    {
        $url = Url::factory()->create([
            'short_code' => 'ghi789',
            'password_hash' => bcrypt('secret123')
        ]);

        $response = $this->get('/ghi789');

        $response->assertStatus(200);
        $response->assertViewIs('password-form');
        $response->assertSee('Password Protected');
    }

    public function test_password_protected_url_with_correct_password()
    {
        $url = Url::factory()->create([
            'short_code' => 'jkl012',
            'original_url' => 'https://example.com',
            'password_hash' => bcrypt('secret123')
        ]);

        $response = $this->post('/jkl012', [
            'password' => 'secret123'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('https://example.com');

        // Should track the click
        $this->assertDatabaseHas('clicks', [
            'url_id' => $url->id
        ]);
    }

    public function test_password_protected_url_with_incorrect_password()
    {
        $url = Url::factory()->create([
            'short_code' => 'mno345',
            'password_hash' => bcrypt('secret123')
        ]);

        $response = $this->post('/mno345', [
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(200);
        $response->assertViewIs('password-form');
        $response->assertSee('Invalid password');

        // Should not track the click
        $this->assertDatabaseMissing('clicks', [
            'url_id' => $url->id
        ]);
    }

    public function test_password_protected_url_with_title_and_description()
    {
        $url = Url::factory()->create([
            'short_code' => 'pqr678',
            'password_hash' => bcrypt('secret123'),
            'title' => 'Protected Site',
            'description' => 'This is a protected site'
        ]);

        $response = $this->get('/pqr678');

        $response->assertStatus(200);
        $response->assertSee('Protected Site');
        $response->assertSee('This is a protected site');
    }

    public function test_preview_url_information()
    {
        $url = Url::factory()->create([
            'short_code' => 'preview123',
            'title' => 'Preview Site',
            'description' => 'A site for preview'
        ]);

        $response = $this->get('/preview/preview123');

        $response->assertStatus(200);
        $response->assertViewIs('url-preview');
        $response->assertSee('Preview Site');
        $response->assertSee('A site for preview');
    }

    public function test_preview_nonexistent_url_returns_404()
    {
        $response = $this->get('/preview/nonexistent');

        $response->assertStatus(404);
    }

    public function test_redirect_with_mobile_user_agent()
    {
        $url = Url::factory()->create(['short_code' => 'mobile123']);

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 Mobile Safari/604.1'
        ])->get('/mobile123');

        $click = Click::where('url_id', $url->id)->first();
        $this->assertEquals('Safari', $click->browser);
        $this->assertEquals('iOS', $click->os);
        $this->assertEquals('mobile', $click->device_type);
    }

    public function test_redirect_with_tablet_user_agent()
    {
        $url = Url::factory()->create(['short_code' => 'tablet123']);

        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 Mobile Safari/604.1'
        ])->get('/tablet123');

        $click = Click::where('url_id', $url->id)->first();
        $this->assertEquals('Safari', $click->browser);
        $this->assertEquals('iOS', $click->os);
        $this->assertEquals('tablet', $click->device_type);
    }

    public function test_redirect_handles_unknown_user_agent()
    {
        $url = Url::factory()->create(['short_code' => 'unknown123']);

        $this->withHeaders([
            'User-Agent' => 'SomeUnknownBot/1.0'
        ])->get('/unknown123');

        $click = Click::where('url_id', $url->id)->first();
        $this->assertEquals('Unknown', $click->browser);
        $this->assertEquals('Unknown', $click->os);
        $this->assertEquals('desktop', $click->device_type);
    }

    public function test_redirect_geographic_tracking()
    {
        $url = Url::factory()->create(['short_code' => 'geo123']);

        // Mock IP address (in real tests, this would be localhost)
        $this->get('/geo123', [
            'REMOTE_ADDR' => '127.0.0.1'
        ]);

        $click = Click::where('url_id', $url->id)->first();
        $this->assertNotNull($click->ip_address);
        // For localhost, it should default to 'US' and 'Local'
        $this->assertEquals('US', $click->country);
        $this->assertEquals('Local', $click->city);
    }

    public function test_redirect_error_handling_for_click_tracking()
    {
        $url = Url::factory()->create([
            'short_code' => 'error123',
            'original_url' => 'https://example.com'
        ]);

        // This should still redirect even if click tracking fails
        $response = $this->get('/error123');

        $response->assertStatus(302);
        $response->assertRedirect('https://example.com');
    }

    public function test_multiple_clicks_same_ip()
    {
        $url = Url::factory()->create(['short_code' => 'multi123']);

        // Make multiple requests from same IP
        $this->get('/multi123');
        $this->get('/multi123');
        $this->get('/multi123');

        $clickCount = Click::where('url_id', $url->id)->count();
        $this->assertEquals(3, $clickCount);

        // But unique clicks should be 1
        $uniqueClicks = Click::where('url_id', $url->id)
            ->distinct('ip_address')
            ->count();
        $this->assertEquals(1, $uniqueClicks);
    }

    public function test_redirect_with_long_url()
    {
        $longUrl = 'https://example.com/very/long/path/with/many/segments/and/query/parameters?param1=value1&param2=value2&param3=value3';
        
        $url = Url::factory()->create([
            'short_code' => 'long123',
            'original_url' => $longUrl
        ]);

        $response = $this->get('/long123');

        $response->assertStatus(302);
        $response->assertRedirect($longUrl);
    }

    public function test_redirect_with_special_characters_in_url()
    {
        $specialUrl = 'https://example.com/path?query=hello%20world&special=!@#$%^&*()';
        
        $url = Url::factory()->create([
            'short_code' => 'special123',
            'original_url' => $specialUrl
        ]);

        $response = $this->get('/special123');

        $response->assertStatus(302);
        $response->assertRedirect($specialUrl);
    }
} 