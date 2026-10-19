<?php

namespace Database\Factories;

use App\Models\Meeting;
use App\Models\MeetingCompany;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MeetingCompany>
 */
class MeetingCompanyFactory extends Factory
{
    protected $model = MeetingCompany::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'meeting_id' => Meeting::factory(),
            'name' => $this->faker->company(),
            'slug' => Str::slug($this->faker->unique()->company()),
            'contact_name' => $this->faker->optional()->name(),
            'contact_email' => $this->faker->optional()->safeEmail(),
            'contact_phone' => $this->faker->optional()->numerify('##########'),
            'status' => 'pending',
            'is_host_company' => false,
            'is_active' => true,
        ];
    }

    public function hostCompany(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_host_company' => true,
            'name' => 'Host Company',
            'slug' => 'host-company',
        ]);
    }
}
