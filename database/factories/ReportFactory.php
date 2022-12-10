<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

use App\Models\Organization;
use App\Models\ReportCase;
use App\Models\User;

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
            'is_anonymous' => 0,
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

    /**
     * Indicate that the report should belong to given reporter.
     * This is a workaround since 'reporter' relationship doesn't wor 100 %.
     *
     * @return static
     */
    public function forReporter(User $reporter)
    {
        return $this->state(fn (array $attributes) => [
            'reporter_id' => $reporter->id,
        ]);
    }

    /**
     * Indicate that the report should be created under
     * a new case that belongs to given organization.
     *
     * @return static
     */
    public function forNewCaseIn(Organization $organization)
    {
        return $this->for(
            ReportCase::factory()->for($organization)->create(), 'case'
        );
    }
}
