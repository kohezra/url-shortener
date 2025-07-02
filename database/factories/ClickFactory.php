<?php

namespace Database\Factories;

use App\Models\Click;
use App\Models\Url;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Click>
 */
class ClickFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Click::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera'];
        $operatingSystems = ['Windows', 'macOS', 'Linux', 'iOS', 'Android'];
        $deviceTypes = ['desktop', 'mobile', 'tablet'];
        $countries = ['US', 'CA', 'GB', 'DE', 'FR', 'JP', 'AU', 'BR'];
        $cities = ['New York', 'London', 'Paris', 'Tokyo', 'Sydney', 'Toronto', 'Berlin', 'São Paulo'];

        return [
            'url_id' => Url::factory(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'referer' => $this->faker->optional()->url(),
            'country' => $this->faker->randomElement($countries),
            'city' => $this->faker->randomElement($cities),
            'browser' => $this->faker->randomElement($browsers),
            'os' => $this->faker->randomElement($operatingSystems),
            'device_type' => $this->faker->randomElement($deviceTypes),
            'clicked_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the click happened today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => Carbon::today()->addHours($this->faker->numberBetween(0, 23)),
        ]);
    }

    /**
     * Indicate that the click happened this week.
     */
    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => Carbon::now()->startOfWeek()->addDays($this->faker->numberBetween(0, 6)),
        ]);
    }

    /**
     * Indicate that the click happened this month.
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'clicked_at' => Carbon::now()->startOfMonth()->addDays($this->faker->numberBetween(0, 29)),
        ]);
    }

    /**
     * Indicate that the click is from a mobile device.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'mobile',
            'os' => $this->faker->randomElement(['iOS', 'Android']),
            'browser' => $this->faker->randomElement(['Safari', 'Chrome']),
        ]);
    }

    /**
     * Indicate that the click is from a desktop device.
     */
    public function desktop(): static
    {
        return $this->state(fn (array $attributes) => [
            'device_type' => 'desktop',
            'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux']),
            'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
        ]);
    }

    /**
     * Indicate that the click is from a specific country.
     */
    public function fromCountry(string $country): static
    {
        return $this->state(fn (array $attributes) => [
            'country' => $country,
        ]);
    }

    /**
     * Indicate that the click is from a specific browser.
     */
    public function fromBrowser(string $browser): static
    {
        return $this->state(fn (array $attributes) => [
            'browser' => $browser,
        ]);
    }
} 