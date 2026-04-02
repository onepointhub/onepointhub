<?php

namespace App\Modules\Projects\database\factories;

use App\Modules\Core\Models\User;
use App\Modules\Projects\Models\TimeEntry;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

#[UseModel(TimeEntry::class)]
class TimeEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $started = fake()->dateTimeBetween('-30 days', '-1 hour');
        $ended = fake()->dateTimeBetween($started, 'now');
        $duration = (int) (($ended->getTimestamp() - $started->getTimestamp()) / 60);

        return [
            'user_id' => User::factory(),
            'description' => fake()->optional()->sentence(),
            'started_at' => $started,
            'ended_at' => $ended,
            'duration_minutes' => $duration,
            'billable' => fake()->boolean(80),
            'hourly_rate' => fake()->optional()->randomFloat(2, 50, 200),
            'invoiced_at' => null,
        ];
    }

    public function running(): static
    {
        return $this->state([
            'started_at' => now()->subMinutes(30),
            'ended_at' => null,
            'duration_minutes' => null,
        ]);
    }
}
