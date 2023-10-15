<?php

namespace Database\Factories;

use App\Enums\CardType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Card>
 */
class CardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cardType = $this->faker->randomElement(CardType::values());

        return [
            'id'   => Uuid::uuid4()->toString(),
            'name' => $this->faker->sentence(),
            'type' => $cardType,
            'cmc'  => $cardType === CardType::LAND->value ? 0 : $this->faker->numberBetween(1, 10),
        ];
    }
}
