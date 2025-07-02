<?php

namespace Tests\Unit;

use App\Models\Click;
use App\Models\Url;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ClickModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_click_can_be_created()
    {
        $url = Url::factory()->create();
        
        $click = Click::create([
            'url_id' => $url->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            'referer' => 'https://google.com',
            'country' => 'US',
            'city' => 'New York',
            'browser' => 'Chrome',
            'os' => 'Windows',
            'device_type' => 'desktop',
            'clicked_at' => Carbon::now()
        ]);

        $this->assertInstanceOf(Click::class, $click);
        $this->assertEquals($url->id, $click->url_id);
        $this->assertEquals('192.168.1.1', $click->ip_address);
        $this->assertEquals('US', $click->country);
        $this->assertEquals('Chrome', $click->browser);
    }

    public function test_click_belongs_to_url()
    {
        $url = Url::factory()->create();
        $click = Click::factory()->create(['url_id' => $url->id]);

        $this->assertInstanceOf(Url::class, $click->url);
        $this->assertEquals($url->id, $click->url->id);
    }

    public function test_parse_user_agent_chrome()
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
        
        $result = Click::parseUserAgent($userAgent);

        $this->assertEquals('Chrome', $result['browser']);
        $this->assertEquals('Windows', $result['os']);
        $this->assertEquals('desktop', $result['deviceType']);
    }

    public function test_parse_user_agent_firefox()
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0';
        
        $result = Click::parseUserAgent($userAgent);

        $this->assertEquals('Firefox', $result['browser']);
        $this->assertEquals('Windows', $result['os']);
        $this->assertEquals('desktop', $result['deviceType']);
    }

    public function test_parse_user_agent_safari()
    {
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15';
        
        $result = Click::parseUserAgent($userAgent);

        $this->assertEquals('Safari', $result['browser']);
        $this->assertEquals('macOS', $result['os']);
        $this->assertEquals('desktop', $result['deviceType']);
    }

    public function test_parse_user_agent_mobile_chrome()
    {
        $userAgent = 'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36';
        
        $result = Click::parseUserAgent($userAgent);

        $this->assertEquals('Chrome', $result['browser']);
        $this->assertEquals('Android', $result['os']);
        $this->assertEquals('mobile', $result['deviceType']);
    }

    public function test_parse_user_agent_iphone()
    {
        $userAgent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1';
        
        $result = Click::parseUserAgent($userAgent);

        $this->assertEquals('Safari', $result['browser']);
        $this->assertEquals('iOS', $result['os']);
        $this->assertEquals('mobile', $result['deviceType']);
    }

    public function test_parse_user_agent_tablet()
    {
        $userAgent = 'Mozilla/5.0 (iPad; CPU OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Mobile/15E148 Safari/604.1';
        
        $result = Click::parseUserAgent($userAgent);

        $this->assertEquals('Safari', $result['browser']);
        $this->assertEquals('iOS', $result['os']);
        $this->assertEquals('tablet', $result['deviceType']);
    }

    public function test_parse_user_agent_unknown()
    {
        $userAgent = 'SomeUnknownBot/1.0';
        
        $result = Click::parseUserAgent($userAgent);

        $this->assertEquals('Unknown', $result['browser']);
        $this->assertEquals('Unknown', $result['os']);
        $this->assertEquals('desktop', $result['deviceType']);
    }

    public function test_parse_user_agent_empty()
    {
        $result = Click::parseUserAgent('');

        $this->assertEquals('Unknown', $result['browser']);
        $this->assertEquals('Unknown', $result['os']);
        $this->assertEquals('desktop', $result['deviceType']);
    }

    public function test_parse_user_agent_null()
    {
        $result = Click::parseUserAgent(null);

        $this->assertEquals('Unknown', $result['browser']);
        $this->assertEquals('Unknown', $result['os']);
        $this->assertEquals('desktop', $result['deviceType']);
    }

    public function test_today_scope()
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

        $todayClicks = Click::today()->count();
        $this->assertEquals(3, $todayClicks);
    }

    public function test_this_week_scope()
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

        $thisWeekClicks = Click::thisWeek()->count();
        $this->assertEquals(4, $thisWeekClicks);
    }

    public function test_this_month_scope()
    {
        $url = Url::factory()->create();
        
        // Create clicks this month
        Click::factory()->count(5)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->startOfMonth()->addDays(10)
        ]);
        
        // Create clicks last month
        Click::factory()->count(3)->create([
            'url_id' => $url->id,
            'clicked_at' => Carbon::now()->subMonth()
        ]);

        $thisMonthClicks = Click::thisMonth()->count();
        $this->assertEquals(5, $thisMonthClicks);
    }

    public function test_by_country_scope()
    {
        $url = Url::factory()->create();
        
        Click::factory()->count(3)->create([
            'url_id' => $url->id,
            'country' => 'US'
        ]);
        
        Click::factory()->count(2)->create([
            'url_id' => $url->id,
            'country' => 'CA'
        ]);

        $usClicks = Click::byCountry('US')->count();
        $this->assertEquals(3, $usClicks);
    }

    public function test_by_browser_scope()
    {
        $url = Url::factory()->create();
        
        Click::factory()->count(4)->create([
            'url_id' => $url->id,
            'browser' => 'Chrome'
        ]);
        
        Click::factory()->count(2)->create([
            'url_id' => $url->id,
            'browser' => 'Firefox'
        ]);

        $chromeClicks = Click::byBrowser('Chrome')->count();
        $this->assertEquals(4, $chromeClicks);
    }

    public function test_by_device_type_scope()
    {
        $url = Url::factory()->create();
        
        Click::factory()->count(5)->create([
            'url_id' => $url->id,
            'device_type' => 'mobile'
        ]);
        
        Click::factory()->count(3)->create([
            'url_id' => $url->id,
            'device_type' => 'desktop'
        ]);

        $mobileClicks = Click::byDeviceType('mobile')->count();
        $this->assertEquals(5, $mobileClicks);
    }
} 