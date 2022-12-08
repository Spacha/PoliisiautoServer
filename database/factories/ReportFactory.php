<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'description' => fake()->realTextBetween(10, 200),
            'is_anonymous' => rand(0, 1),
        ];
    }

    /**
     * Indicate that the report should be anonymous.
     *
     * @return static
     */
    public function anonymous()
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => 1,
        ]);
    }
}