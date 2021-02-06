<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName,
            'phone_number' => $this->faker->phoneNumber,
            'country_code' => $this->faker->countryCode,
            'timezone_name' => $this->faker->timezone,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
