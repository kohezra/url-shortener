<?php

namespace Database\Seeders;

use App\Models\Click;
use App\Models\Url;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UrlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample users
        $users = User::factory(3)->create();

        // Create sample URLs
        $sampleUrls = [
            [
                'original_url' => 'https://laravel.com',
                'title' => 'Laravel - The PHP Framework for Web Artisans',
                'description' => 'Official Laravel website'
            ],
            [
                'original_url' => 'https://github.com',
                'title' => 'GitHub - Where the world builds software',
                'description' => 'GitHub repository hosting'
            ],
            [
                'original_url' => 'https://stackoverflow.com',
                'title' => 'Stack Overflow - Developer Community',
                'description' => 'Programming Q&A platform'
            ],
            [
                'original_url' => 'https://tailwindcss.com',
                'title' => 'Tailwind CSS - Utility-first CSS framework',
                'description' => 'CSS framework documentation'
            ],
            [
                'original_url' => 'https://vuejs.org',
                'title' => 'Vue.js - The Progressive JavaScript Framework',
                'description' => 'Vue.js official website'
            ]
        ];

        foreach ($sampleUrls as $index => $urlData) {
            $url = Url::create([
                'original_url' => $urlData['original_url'],
                'short_code' => $this->generateUniqueShortCode(),
                'user_id' => $users->random()->id,
                'title' => $urlData['title'],
                'description' => $urlData['description'],
                'is_active' => true
            ]);

            // Create sample clicks for each URL
            $this->createSampleClicks($url, rand(5, 50));
        }

        // Create some anonymous URLs (without user)
        $anonymousUrls = [
            'https://google.com',
            'https://youtube.com',
            'https://twitter.com'
        ];

        foreach ($anonymousUrls as $originalUrl) {
            $url = Url::create([
                'original_url' => $originalUrl,
                'short_code' => $this->generateUniqueShortCode(),
                'user_id' => null,
                'is_active' => true
            ]);

            $this->createSampleClicks($url, rand(10, 100));
        }
    }

    /**
     * Generate a unique short code
     */
    private function generateUniqueShortCode(): string
    {
        do {
            $shortCode = Str::random(6);
        } while (Url::where('short_code', $shortCode)->exists());

        return $shortCode;
    }

    /**
     * Create sample clicks for a URL
     */
    private function createSampleClicks(Url $url, int $count): void
    {
        $countries = ['US', 'GB', 'CA', 'AU', 'DE', 'FR', 'JP', 'IN'];
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge'];
        $operatingSystems = ['Windows', 'macOS', 'Linux', 'Android', 'iOS'];
        $deviceTypes = ['desktop', 'mobile', 'tablet'];
        $cities = ['New York', 'London', 'Toronto', 'Sydney', 'Berlin', 'Paris', 'Tokyo', 'Mumbai'];

        for ($i = 0; $i < $count; $i++) {
            Click::create([
                'url_id' => $url->id,
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'referer' => fake()->randomElement([
                    'https://google.com',
                    'https://facebook.com',
                    'https://twitter.com',
                    'https://reddit.com',
                    null
                ]),
                'country' => fake()->randomElement($countries),
                'city' => fake()->randomElement($cities),
                'browser' => fake()->randomElement($browsers),
                'os' => fake()->randomElement($operatingSystems),
                'device_type' => fake()->randomElement($deviceTypes),
                'clicked_at' => fake()->dateTimeBetween('-30 days', 'now')
            ]);
        }
    }
}
