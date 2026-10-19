<?php

namespace Database\Factories;

use App\Models\Meeting;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Meeting>
 */
class MeetingFactory extends Factory
{
    protected $model = Meeting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_code' => 'AYK-'.Str::upper(Str::random(6)),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('##########'),
            'company_name' => $this->faker->company(),
            'meeting_topic' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'start_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'end_date' => $this->faker->dateTimeBetween('+30 days', '+60 days'),
            'from_location' => $this->faker->optional()->city(),
            'to_location' => $this->faker->optional()->city(),
            'physical_location' => $this->faker->optional()->address(),
            'status' => 'scheduled',
            'is_online' => true,
            'session_provider' => 'internal',
            'meeting_type' => 'online',
            'is_multi_company' => false,
            'settings' => [],
        ];
    }

    public function online(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_online' => true,
            'meeting_type' => 'online',
            'session_provider' => 'internal',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
            'started_at' => now(),
            'start_date' => now()->subHour(),
            'end_date' => now()->addHour(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
            'start_date' => now()->subHours(2),
            'end_date' => now()->subHour(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
