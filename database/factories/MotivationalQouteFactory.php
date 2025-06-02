<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MotivationalQoute>
 */
class MotivationalQouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
        'detail'=>fake()->paragraph(15),
        'image'=>'/storage/user/motivational_qoute/Bjobb0dBDeIaAGHeAkq0h7obaYpZM8gwxPGS5DRR.jpg'
        ];
    }
}
