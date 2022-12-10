<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReportMessage>
 */
class ReportMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'content' => fake()->realTextBetween(5, 100),
            'is_anonymous' => rand(0, 1),
        ];
    }

    /**
     * Indicate that the report message should be anonymous.
     *
     * @return static
     */
    public function anonymous()
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => 1,
        ]);
    }

    /**
     * Indicate that the message should belong to given author.
     * This is a workaround since 'author' relationship doesn't work 100 %.
     *
     * @return static
     */
    public function forAuthor(User $author)
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $author->id,
        ]);
    }
}
