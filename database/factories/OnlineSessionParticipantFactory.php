<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\MeetingCompany;
use App\Models\OnlineSessionParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<OnlineSessionParticipant>
 */
class OnlineSessionParticipantFactory extends Factory
{
    protected $model = OnlineSessionParticipant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'attendee_id' => null,
            'user_id' => null,
            'company_id' => null,
            'peer_id' => (string) Str::uuid(),
            'token_hash' => hash('sha256', Str::random(64)),
            'display_name' => $this->faker->name(),
            'role' => OnlineSessionParticipant::ROLE_PARTICIPANT,
            'assignment_source' => OnlineSessionParticipant::SOURCE_GUEST,
            'company_verified_at' => null,
            'is_host' => false,
            'is_co_host' => false,
            'co_host_permissions' => null,
            'status' => OnlineSessionParticipant::STATUS_ADMITTED,
            'is_muted_by_host' => false,
            'joined_at' => now(),
            'requested_at' => now(),
            'admitted_at' => now(),
            'last_seen_at' => now(),
            'left_at' => null,
            'hand_raised' => false,
        ];
    }

    public function host(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => OnlineSessionParticipant::ROLE_HOST,
            'is_host' => true,
            'display_name' => 'Host User',
        ]);
    }

    public function coHost(array $permissions = []): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => OnlineSessionParticipant::ROLE_CO_HOST,
            'is_co_host' => true,
            'co_host_permissions' => $permissions ?: ['mute_participants', 'manage_waiting_room'],
            'display_name' => 'Co-Host User',
        ]);
    }

    public function admitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OnlineSessionParticipant::STATUS_ADMITTED,
        ]);
    }

    public function waiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OnlineSessionParticipant::STATUS_WAITING,
            'joined_at' => null,
            'admitted_at' => null,
            'requested_at' => now(),
        ]);
    }

    public function left(): static
    {
        return $this->state(fn (array $attributes) => [
            'left_at' => now(),
        ]);
    }

    public function withCompany(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => MeetingCompany::factory(),
        ]);
    }
}
