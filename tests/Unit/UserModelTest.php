<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Url;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123')
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertNotNull($user->password);
    }

    public function test_user_has_many_urls()
    {
        $user = User::factory()->create();
        $urls = Url::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->urls);
        foreach ($user->urls as $url) {
            $this->assertInstanceOf(Url::class, $url);
            $this->assertEquals($user->id, $url->user_id);
        }
    }

    public function test_user_can_generate_api_token()
    {
        $user = User::factory()->create();

        $token = $user->generateApiToken();

        $this->assertIsString($token);
        $this->assertEquals(60, strlen($token));
        $this->assertEquals($token, $user->api_token);
    }

    public function test_user_regenerates_api_token_if_exists()
    {
        $user = User::factory()->create(['api_token' => 'old_token']);
        $oldToken = $user->api_token;

        $newToken = $user->generateApiToken();

        $this->assertNotEquals($oldToken, $newToken);
        $this->assertEquals($newToken, $user->fresh()->api_token);
    }

    public function test_user_total_urls_accessor()
    {
        $user = User::factory()->create();
        Url::factory()->count(5)->create(['user_id' => $user->id]);

        $this->assertEquals(5, $user->total_urls);
    }

    public function test_user_total_clicks_accessor()
    {
        $user = User::factory()->create();
        $urls = Url::factory()->count(3)->create(['user_id' => $user->id]);

        // Create clicks for user's URLs
        Click::factory()->count(4)->create(['url_id' => $urls[0]->id]);
        Click::factory()->count(3)->create(['url_id' => $urls[1]->id]);
        Click::factory()->count(2)->create(['url_id' => $urls[2]->id]);

        $this->assertEquals(9, $user->total_clicks);
    }

    public function test_user_clicks_today_accessor()
    {
        $user = User::factory()->create();
        $url = Url::factory()->create(['user_id' => $user->id]);

        // Create clicks today
        Click::factory()->count(3)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()
        ]);

        // Create clicks yesterday
        Click::factory()->count(2)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::yesterday()
        ]);

        $this->assertEquals(3, $user->clicks_today);
    }

    public function test_user_clicks_this_week_accessor()
    {
        $user = User::factory()->create();
        $url = Url::factory()->create(['user_id' => $user->id]);

        // Create clicks this week
        Click::factory()->count(5)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->startOfWeek()->addDays(2)
        ]);

        // Create clicks last week
        Click::factory()->count(3)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->subWeek()
        ]);

        $this->assertEquals(5, $user->clicks_this_week);
    }

    public function test_user_clicks_this_month_accessor()
    {
        $user = User::factory()->create();
        $url = Url::factory()->create(['user_id' => $user->id]);

        // Create clicks this month
        Click::factory()->count(7)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->startOfMonth()->addDays(10)
        ]);

        // Create clicks last month
        Click::factory()->count(4)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->subMonth()
        ]);

        $this->assertEquals(7, $user->clicks_this_month);
    }

    public function test_user_top_urls_accessor()
    {
        $user = User::factory()->create();
        $url1 = Url::factory()->create(['user_id' => $user->id]);
        $url2 = Url::factory()->create(['user_id' => $user->id]);
        $url3 = Url::factory()->create(['user_id' => $user->id]);

        // Create different numbers of clicks for each URL
        Click::factory()->count(10)->create(['url_id' => $url1->id]);
        Click::factory()->count(5)->create(['url_id' => $url2->id]);
        Click::factory()->count(15)->create(['url_id' => $url3->id]);

        $topUrls = $user->top_urls;

        $this->assertCount(3, $topUrls);
        $this->assertEquals($url3->id, $topUrls[0]->id); // Most clicks first
        $this->assertEquals($url1->id, $topUrls[1]->id);
        $this->assertEquals($url2->id, $topUrls[2]->id); // Least clicks last
    }

    public function test_user_top_urls_limits_to_5()
    {
        $user = User::factory()->create();
        $urls = Url::factory()->count(8)->create(['user_id' => $user->id]);

        // Create clicks for all URLs
        foreach ($urls as $index => $url) {
            Click::factory()->count($index + 1)->create(['url_id' => $url->id]);
        }

        $topUrls = $user->top_urls;

        $this->assertCount(5, $topUrls);
    }

    public function test_user_recent_urls_accessor()
    {
        $user = User::factory()->create();
        
        // Create URLs at different times
        $oldUrl = Url::factory()->create([
            'user_id' => $user->id,
            'created_at' => Carbon::now()->subDays(10)
        ]);
        
        $recentUrl = Url::factory()->create([
            'user_id' => $user->id,
            'created_at' => Carbon::now()->subHours(2)
        ]);

        $recentUrls = $user->recent_urls;

        $this->assertCount(2, $recentUrls);
        $this->assertEquals($recentUrl->id, $recentUrls[0]->id); // Most recent first
        $this->assertEquals($oldUrl->id, $recentUrls[1]->id);
    }

    public function test_user_recent_urls_limits_to_10()
    {
        $user = User::factory()->create();
        Url::factory()->count(15)->create(['user_id' => $user->id]);

        $recentUrls = $user->recent_urls;

        $this->assertCount(10, $recentUrls);
    }

    public function test_user_active_urls_accessor()
    {
        $user = User::factory()->create();
        
        // Create active and inactive URLs
        $activeUrls = Url::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_active' => true
        ]);
        
        $inactiveUrls = Url::factory()->count(2)->create([
            'user_id' => $user->id,
            'is_active' => false
        ]);

        $this->assertEquals(3, $user->active_urls);
    }

    public function test_user_expired_urls_accessor()
    {
        $user = User::factory()->create();
        
        // Create expired and non-expired URLs
        $expiredUrls = Url::factory()->count(2)->create([
            'user_id' => $user->id,
            'expires_at' => Carbon::now()->subDay()
        ]);
        
        $validUrls = Url::factory()->count(3)->create([
            'user_id' => $user->id,
            'expires_at' => Carbon::now()->addDay()
        ]);

        $this->assertEquals(2, $user->expired_urls);
    }

    public function test_user_password_protected_urls_accessor()
    {
        $user = User::factory()->create();
        
        // Create password protected and regular URLs
        $protectedUrls = Url::factory()->count(2)->create([
            'user_id' => $user->id,
            'password_hash' => bcrypt('secret')
        ]);
        
        $regularUrls = Url::factory()->count(3)->create([
            'user_id' => $user->id,
            'password_hash' => null
        ]);

        $this->assertEquals(2, $user->password_protected_urls);
    }

    public function test_user_statistics_with_no_urls()
    {
        $user = User::factory()->create();

        $this->assertEquals(0, $user->total_urls);
        $this->assertEquals(0, $user->total_clicks);
        $this->assertEquals(0, $user->clicks_today);
        $this->assertEquals(0, $user->clicks_this_week);
        $this->assertEquals(0, $user->clicks_this_month);
        $this->assertEquals(0, $user->active_urls);
        $this->assertEquals(0, $user->expired_urls);
        $this->assertEquals(0, $user->password_protected_urls);
    }

    public function test_user_can_have_multiple_api_tokens()
    {
        $user = User::factory()->create();

        $token1 = $user->generateApiToken();
        $token2 = $user->generateApiToken();

        $this->assertNotEquals($token1, $token2);
        $this->assertEquals($token2, $user->fresh()->api_token);
    }
} 