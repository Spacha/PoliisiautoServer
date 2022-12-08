<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Organization;
use App\Models\Student;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_login()
    {
        $user = Student::factory()->create();

        $response = $this->postJson($this->api('login'), [
            'email'         => $user->email,
            'password'      => 'password',
            'device_name'   => 'Ellin Oneplus 8',
        ]);
        $response->assertOk();
    }

    public function test_can_logout()
    {
        $this->actingAsStudent();

        $response = $this->postJson($this->api('logout'));
        $response->assertOk();
    }

    public function test_can_get_profile()
    {
        $student = $this->actingAsStudent();

        // make sure all relevant fields are found
        // and that password is not visible
        $response = $this->getJson($this->api('profile'));
        $response->assertOk()->assertJson([
            'id'            => $student->id,
            'first_name'    => $student->first_name,
            'last_name'     => $student->last_name,
            'email'         => $student->email,
            'role'          => 'student',
        ])->assertJsonMissingPath('password');
    }

    public function test_can_get_organization()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);

        $response = $this->getJson($this->api('profile/organization'));
        $response->assertOk()->assertJson([
            'id'                => $organization->id,
            'name'              => $organization->name,
            'street_address'    => $organization->street_address,
            'city'              => $organization->city,
            'zip'               => $organization->zip,
        ]);
    }

    public function test_cannot_access_protected_routes_without_logging_in()
    {
        $response = $this->getJson($this->api('profile'));
        $response->assertUnauthorized();
    }
}
