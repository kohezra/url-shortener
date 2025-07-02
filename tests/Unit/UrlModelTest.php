<?php

namespace Tests\Unit;

use App\Models\Url;
use App\Models\User;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UrlModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_url_can_be_created()
    {
        $url = Url::create([
            'original_url' => 'https://example.com',
            'short_code' => 'abc123',
            'title' => 'Example Site',
            'description' => 'A test site',
        ]);

        $this->assertInstanceOf(Url::class, $url);
        $this->assertEquals('https://example.com', $url->original_url);
        $this->assertEquals('abc123', $url->short_code);
        $this->assertEquals('Example Site', $url->title);
        $this->assertEquals('A test site', $url->description);
        $this->assertTrue($url->is_active);
        $this->assertNull($url->expires_at);
        $this->assertNull($url->password_hash);
    }

    public function test_url_belongs_to_user()
    {
        $user = User::factory()->create();
        $url = Url::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $url->user);
        $this->assertEquals($user->id, $url->user->id);
    }

    public function test_url_has_many_clicks()
    {
        $url = Url::factory()->create();
        $clicks = Click::factory()->count(3)->create(['url_id' => $url->id]);

        $this->assertCount(3, $url->clicks);
        $this->assertInstanceOf(Click::class, $url->clicks->first());
    }

    public function test_is_expired_returns_false_when_no_expiry()
    {
        $url = Url::factory()->create(['expires_at' => null]);

        $this->assertFalse($url->isExpired());
    }

    public function test_is_expired_returns_false_when_not_expired()
    {
        $url = Url::factory()->create(['expires_at' => Carbon::now()->addDay()]);

        $this->assertFalse($url->isExpired());
    }

    public function test_is_expired_returns_true_when_expired()
    {
        $url = Url::factory()->create(['expires_at' => Carbon::now()->subDay()]);

        $this->assertTrue($url->isExpired());
    }

    public function test_is_password_protected_returns_false_when_no_password()
    {
        $url = Url::factory()->create(['password_hash' => null]);

        $this->assertFalse($url->isPasswordProtected());
    }

    public function test_is_password_protected_returns_true_when_password_set()
    {
        $url = Url::factory()->create(['password_hash' => bcrypt('secret')]);

        $this->assertTrue($url->isPasswordProtected());
    }

    public function test_is_accessible_returns_true_for_active_non_expired_url()
    {
        $url = Url::factory()->create([
            'is_active' => true,
            'expires_at' => null
        ]);

        $this->assertTrue($url->isAccessible());
    }

    public function test_is_accessible_returns_false_for_inactive_url()
    {
        $url = Url::factory()->create(['is_active' => false]);

        $this->assertFalse($url->isAccessible());
    }

    public function test_is_accessible_returns_false_for_expired_url()
    {
        $url = Url::factory()->create([
            'is_active' => true,
            'expires_at' => Carbon::now()->subDay()
        ]);

        $this->assertFalse($url->isAccessible());
    }

    public function test_total_clicks_accessor()
    {
        $url = Url::factory()->create();
        Click::factory()->count(5)->create(['url_id' => $url->id]);

        $this->assertEquals(5, $url->total_clicks);
    }

    public function test_unique_clicks_accessor()
    {
        $url = Url::factory()->create();
        
        // Create clicks with same IP (should count as 1 unique)
        Click::factory()->count(3)->create([
            'url_id' => $url->id,
            'ip_address' => '192.168.1.1'
        ]);
        
        // Create clicks with different IP
        Click::factory()->count(2)->create([
            'url_id' => $url->id,
            'ip_address' => '192.168.1.2'
        ]);

        $this->assertEquals(2, $url->unique_clicks);
    }

    public function test_clicks_today_accessor()
    {
        $url = Url::factory()->create();
        
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

        $this->assertEquals(3, $url->clicks_today);
    }

    public function test_clicks_this_week_accessor()
    {
        $url = Url::factory()->create();
        
        // Create clicks this week
        Click::factory()->count(4)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->startOfWeek()->addDays(2)
        ]);
        
        // Create clicks last week
        Click::factory()->count(2)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->subWeek()
        ]);

        $this->assertEquals(4, $url->clicks_this_week);
    }

    public function test_clicks_this_month_accessor()
    {
        $url = Url::factory()->create();
        
        // Create clicks this month
        Click::factory()->count(6)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->startOfMonth()->addDays(10)
        ]);
        
        // Create clicks last month
        Click::factory()->count(3)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->subMonth()
        ]);

        $this->assertEquals(6, $url->clicks_this_month);
    }

    public function test_short_url_accessor()
    {
        $url = Url::factory()->create(['short_code' => 'abc123']);

        $expectedUrl = url('abc123');
        $this->assertEquals($expectedUrl, $url->short_url);
    }
} 