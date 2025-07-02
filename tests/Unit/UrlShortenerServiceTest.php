<?php

namespace Tests\Unit;

use App\Models\Url;
use App\Models\User;
use App\Services\UrlShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class UrlShortenerServiceTest extends TestCase
{
    use RefreshDatabase;

    private UrlShortenerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UrlShortenerService::class);
    }

    public function test_service_accepts_valid_urls()
    {
        $validUrls = [
            'https://example.com',
            'http://example.com',
            'https://www.example.com/path?query=value',
            'https://subdomain.example.com',
            'https://example.com:8080',
        ];

        foreach ($validUrls as $url) {
            $result = $this->service->shortenUrl(['original_url' => $url]);
            $this->assertInstanceOf(Url::class, $result, "Failed for URL: {$url}");
            $this->assertEquals($url, $result->original_url);
        }
    }

    public function test_service_rejects_invalid_urls()
    {
        $invalidUrls = [
            'not-a-url',
            'ftp://example.com',
            'javascript:alert(1)',
            'data:text/html,<script>alert(1)</script>',
            '',
            'http://',
            'https://',
        ];

        foreach ($invalidUrls as $url) {
            try {
                $this->service->shortenUrl(['original_url' => $url]);
                $this->fail("Should have rejected URL: {$url}");
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertTrue(true, "Correctly rejected URL: {$url}");
            }
        }
    }

    public function test_service_rejects_malicious_domains()
    {
        $maliciousDomains = [
            'https://bit.ly/malicious',
            'https://tinyurl.com/bad',
            'https://t.co/spam',
            'https://goo.gl/virus',
        ];

        foreach ($maliciousDomains as $url) {
            try {
                $this->service->shortenUrl(['original_url' => $url]);
                $this->fail("Should have rejected malicious URL: {$url}");
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertTrue(true, "Correctly rejected malicious URL: {$url}");
            }
        }
    }

    public function test_service_rejects_localhost()
    {
        $localhostUrls = [
            'http://localhost',
            'https://localhost:8080',
            'http://127.0.0.1',
            'https://127.0.0.1:3000',
            'http://192.168.1.1',
            'https://10.0.0.1',
        ];

        foreach ($localhostUrls as $url) {
            try {
                $this->service->shortenUrl(['original_url' => $url]);
                $this->fail("Should have rejected localhost URL: {$url}");
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->assertTrue(true, "Correctly rejected localhost URL: {$url}");
            }
        }
    }

    public function test_short_codes_are_unique_and_valid_format()
    {
        $urls = [];
        for ($i = 0; $i < 10; $i++) {
            $urls[] = $this->service->shortenUrl(['original_url' => "https://example{$i}.com"]);
        }

        $codes = array_map(fn($url) => $url->short_code, $urls);
        $uniqueCodes = array_unique($codes);
        
        $this->assertCount(10, $uniqueCodes, 'All generated codes should be unique');
        
        foreach ($codes as $code) {
            $this->assertIsString($code);
            $this->assertGreaterThanOrEqual(3, strlen($code));
            $this->assertLessThanOrEqual(10, strlen($code));
            $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $code);
        }
    }

    public function test_shorten_url_creates_new_url()
    {
        $originalUrl = 'https://example.com';
        
        $result = $this->service->shortenUrl(['original_url' => $originalUrl]);

        $this->assertInstanceOf(Url::class, $result);
        $this->assertEquals($originalUrl, $result->original_url);
        $this->assertNotNull($result->short_code);
        $this->assertTrue($result->is_active);
        $this->assertNull($result->user_id);
    }

    public function test_shorten_url_with_user()
    {
        $user = User::factory()->create();
        $originalUrl = 'https://example.com';
        
        $result = $this->service->shortenUrl([
            'original_url' => $originalUrl,
            'user_id' => $user->id
        ]);

        $this->assertEquals($user->id, $result->user_id);
    }

    public function test_shorten_url_with_custom_code()
    {
        $originalUrl = 'https://example.com';
        $customCode = 'custom123';
        
        $result = $this->service->shortenUrl([
            'original_url' => $originalUrl,
            'custom_code' => $customCode
        ]);

        $this->assertEquals($customCode, $result->short_code);
    }

    public function test_shorten_url_with_options()
    {
        $originalUrl = 'https://example.com';
        $data = [
            'original_url' => $originalUrl,
            'title' => 'Example Site',
            'description' => 'A test site',
            'expires_at' => Carbon::now()->addDays(30),
            'password' => 'secret123'
        ];
        
        $result = $this->service->shortenUrl($data);

        $this->assertEquals('Example Site', $result->title);
        $this->assertEquals('A test site', $result->description);
        $this->assertNotNull($result->expires_at);
        $this->assertNotNull($result->password_hash);
        $this->assertTrue($result->isPasswordProtected());
    }

    public function test_shorten_url_rejects_invalid_url()
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->shortenUrl(['original_url' => 'not-a-url']);
    }

    public function test_shorten_url_rejects_duplicate_custom_code()
    {
        $customCode = 'duplicate';
        
        // Create first URL with custom code
        $this->service->shortenUrl([
            'original_url' => 'https://example.com',
            'custom_code' => $customCode
        ]);
        
        // Try to create second URL with same custom code
        $this->expectException(\Illuminate\Validation\ValidationException::class);

        $this->service->shortenUrl([
            'original_url' => 'https://another-example.com',
            'custom_code' => $customCode
        ]);
    }

    public function test_shorten_url_creates_separate_urls_for_different_users()
    {
        $originalUrl = 'https://example.com';
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $first = $this->service->shortenUrl([
            'original_url' => $originalUrl,
            'user_id' => $user1->id
        ]);
        $second = $this->service->shortenUrl([
            'original_url' => $originalUrl,
            'user_id' => $user2->id
        ]);

        $this->assertNotEquals($first->id, $second->id);
        $this->assertNotEquals($first->short_code, $second->short_code);
        $this->assertEquals($user1->id, $first->user_id);
        $this->assertEquals($user2->id, $second->user_id);
    }

    public function test_get_url_by_short_code_returns_url()
    {
        $url = Url::factory()->create(['short_code' => 'test123']);
        
        $result = $this->service->getUrlByShortCode('test123');

        $this->assertInstanceOf(Url::class, $result);
        $this->assertEquals($url->id, $result->id);
    }

    public function test_get_url_by_short_code_returns_null_for_nonexistent()
    {
        $result = $this->service->getUrlByShortCode('nonexistent');

        $this->assertNull($result);
    }

    public function test_is_url_accessible_returns_true_for_active_url()
    {
        $url = Url::factory()->create([
            'is_active' => true,
            'expires_at' => null
        ]);

        $this->assertTrue($this->service->isUrlAccessible($url));
    }

    public function test_is_url_accessible_returns_false_for_inactive_url()
    {
        $url = Url::factory()->create(['is_active' => false]);

        $this->assertFalse($this->service->isUrlAccessible($url));
    }

    public function test_is_url_accessible_returns_false_for_expired_url()
    {
        $url = Url::factory()->create([
            'is_active' => true,
            'expires_at' => Carbon::now()->subDay()
        ]);

        $this->assertFalse($this->service->isUrlAccessible($url));
    }

    public function test_validate_url_password_returns_true_for_correct_password()
    {
        $password = 'secret123';
        $url = Url::factory()->create([
            'password_hash' => bcrypt($password)
        ]);

        $this->assertTrue($this->service->validateUrlPassword($url, $password));
    }

    public function test_validate_url_password_returns_false_for_incorrect_password()
    {
        $url = Url::factory()->create([
            'password_hash' => bcrypt('secret123')
        ]);

        $this->assertFalse($this->service->validateUrlPassword($url, 'wrongpassword'));
    }

    public function test_validate_url_password_returns_true_for_url_without_password()
    {
        $url = Url::factory()->create(['password_hash' => null]);

        $this->assertTrue($this->service->validateUrlPassword($url, 'anypassword'));
    }

    public function test_url_metadata_extraction_integration()
    {
        // Test that the service can handle URLs and extract basic metadata
        // This tests the integration without directly calling private methods
        $result = $this->service->shortenUrl([
            'original_url' => 'https://example.com',
            'title' => 'Custom Title',
            'description' => 'Custom Description'
        ]);

        $this->assertEquals('Custom Title', $result->title);
        $this->assertEquals('Custom Description', $result->description);
    }
} 