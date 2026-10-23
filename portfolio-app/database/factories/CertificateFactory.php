<?php

namespace Database\Factories;

use App\Models\Certificate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => ['lo' => $title, 'en' => $title],
            'issuer' => fake()->company(),
            'issued_on' => fake()->date(),
            'image' => 'certificates/'.fake()->uuid().'.webp',
            'sort_order' => fake()->numberBetween(0, 50),
            'status' => 'published',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => 'draft']);
    }
}
