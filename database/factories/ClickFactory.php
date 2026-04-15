<?php

namespace Database\Factories;

use App\Click;
use App\Offer;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Click>
 */
class ClickFactory extends Factory
{
	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<\Illuminate\Database\Eloquent\Model>
	 */
	protected $model = Click::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
		return [
            'first_timestamp'   => fake()->dateTimeBetween('-12 days', '+5 days'),
            'ip_address'        => fake()->ipv4,
            'browser_agent'     => fake()->userAgent,
            'click_type'        => fake()->numberBetween(0, 1),
        ];
    }
}
