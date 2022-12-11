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
use App\Models\Teacher;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Preparations:
     *  - Create a user (student).
     * Test:
     *  - Try logging in using the student's credentials.
     *  - Make sure the response is 'OK'.
     *  - Make sure a token is returned (string with some length).
     *  - Make sure the database contains matching token.
     */
    public function test_can_login()
    {
        $user = Student::factory()->create();

        $response = $this->postJson($this->api("login"), [
            'email'         => $user->email,
            'password'      => 'password',
            'device_name'   => 'Ellin Oneplus 8',
        ]);

        $response->assertOk();
        $token = $response->getContent();
        $this->assertTrue(strlen($token) > 0);
        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Ellin Oneplus 8',
            'tokenable_type' => $user::class,
            'tokenable_id' => $user->id,
        ]);
    }

    /**
     * Preparations:
     *  - Create and login as a student.
     * Test:
     *  - Try logging out.
     *  - Make sure the response is 'OK'.
     */
    public function test_can_logout()
    {
        $this->actingAsStudent();

        $response = $this->postJson($this->api("logout"));
        $response->assertOk();
    }

    /**
     * Preparations:
     *  - Create and login as a student.
     * Test:
     *  - Get the user's profile.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     *  - Make sure password is not visible.
     */
    public function test_self_can_get_profile()
    {
        $student = $this->actingAsStudent();

        $response = $this->getJson($this->api("profile"));
        $response->assertOk()->assertJson([
            'id'            => $student->id,
            'first_name'    => $student->first_name,
            'last_name'     => $student->last_name,
            'email'         => $student->email,
            'role'          => 'student',
        ])->assertJsonMissingPath('password');

        $teacher = $this->actingAsTeacher();

        // make sure all relevant fields are found
        // and that password is not visible
        $response = $this->getJson($this->api("profile"));
        $response->assertOk()->assertJson([
            'id'            => $teacher->id,
            'first_name'    => $teacher->first_name,
            'last_name'     => $teacher->last_name,
            'email'         => $teacher->email,
            'role'          => 'teacher',
        ])->assertJsonMissingPath('password');
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     * Test:
     *  - Get user's organization profile.
     *  - Make sure the response is 'OK'.
     *  - Make sure the response contains all relevant fields.
     */
    public function test_can_get_organization()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);

        $response = $this->getJson($this->api("profile/organization"));
        $response->assertOk()->assertJson([
            'id'                => $organization->id,
            'name'              => $organization->name,
            'street_address'    => $organization->street_address,
            'city'              => $organization->city,
            'zip'               => $organization->zip,
        ]);
    }

    /**
     * Preparations:
     *   None
     * Test:
     *  - Try getting a profile, reports and organizations without logging in.
     *  - Make sure all the responses are unauthorized.
     */
    public function test_cannot_access_protected_routes_without_logging_in()
    {
        $response = $this->getJson($this->api("profile"));
        $response->assertUnauthorized();

        $response = $this->getJson($this->api("reports"));
        $response->assertUnauthorized();

        $response = $this->getJson($this->api("organizations"));
        $response->assertUnauthorized();
    }
}
