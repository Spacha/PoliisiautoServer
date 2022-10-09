<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name'              => faker()->company(),
            'street_address'    => faker()->streetAddress(),
            'city'              => faker()->city(),
            'zip'               => faker()->postcode(),
        ];
    }
}
