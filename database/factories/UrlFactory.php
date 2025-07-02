<?php

namespace Database\Factories;

use App\Models\Url;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Url>
 */
class UrlFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Url::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'original_url' => $this->faker->url(),
            'short_code' => $this->faker->unique()->regexify('[a-zA-Z0-9]{6}'),
            'user_id' => null,
            'title' => $this->faker->optional()->sentence(3),
            'description' => $this->faker->optional()->sentence(10),
            'expires_at' => null,
            'password_hash' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the URL belongs to a user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the URL is password protected.
     */
    public function passwordProtected(string $password = 'secret'): static
    {
        return $this->state(fn (array $attributes) => [
            'password_hash' => bcrypt($password),
        ]);
    }

    /**
     * Indicate that the URL is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => Carbon::now()->subDays($this->faker->numberBetween(1, 30)),
        ]);
    }

    /**
     * Indicate that the URL is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the URL expires in the future.
     */
    public function expiresInFuture(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => Carbon::now()->addDays($this->faker->numberBetween(1, 30)),
        ]);
    }
} 