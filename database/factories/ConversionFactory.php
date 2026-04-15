<?php

namespace Database\Factories;

use App\Conversion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Conversion>
 */
class ConversionFactory extends Factory
{

	/**
	 * The name of the factory's corresponding model.
	 *
	 * @var class-string<\Illuminate\Database\Eloquent\Model>
	 */
	protected $model = Conversion::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'timestamp' => fake()->dateTimeBetween('-12 days', '+5 days'),
	        'paid'      => fake()->randomFloat(2, '0.10', '5.00')
        ];
    }
}
