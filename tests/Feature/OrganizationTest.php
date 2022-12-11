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

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * NOTE: When someone can list, update this to test that functionality.
     * 
     * Preparations:
     *  - Create 3 organizations.
     *  - Create and login as a student.
     *  - Create and login as a teacher.
     * Test:
     *  - Try getting an organization list as the student.
     *  - Make sure the response is 'forbidden'.
     *  - Try getting an organization list as the teacher.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_nobody_can_list()
    {
        Organization::factory()->count(3)->create();

        // try as a student
        $this->actingAsStudent();
        $response = $this->getJson($this->api("organizations"));
        $response->assertForbidden();

        // try as a teacher
        $this->actingAsTeacher();
        $response = $this->getJson($this->api("organizations"));
        $response->assertForbidden();
    }

    /**
     * NOTE: When someone can update, update this to test that functionality.
     * 
     * Preparations:
     *  - Create an organization but do not store it.
     *  - Create and login as a student.
     *  - Create and login as a teacher.
     * Test:
     *  - Try storing a new organization using the post data as the student.
     *  - Make sure the response is 'forbidden'.
     *  - Try storing a new organization using the post data as the teacher.
     *  - Make sure that the response is forbidden.
     */
    public function test_nobody_can_create()
    {
        // make: create a fake organization but do not store to database
        $organization = Organization::factory()->make();
        $postData = [
            'name'              => $organization->name,
            'street_address'    => $organization->street_address,
            'city'              => $organization->city,
            'zip'               => $organization->zip,
        ];

        // try as a student
        $this->actingAsStudent();
        $response = $this->postJson($this->api("organizations"), $postData);
        $response->assertForbidden();

        // try as a teacher
        $this->actingAsTeacher();
        $response = $this->postJson($this->api("organizations"), $postData);
        $response->assertForbidden();
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the organization.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     */
    public function test_member_teacher_can_show()
    {
        $organization = Organization::factory()->create();
        $this->actingAsTeacher($organization->id);

        $response = $this->getJson($this->api("organizations/$organization->id"));
        $response->assertOk()->assertJson([
            'id' => $organization->id,
            'name' => $organization->name,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a teacher belonging to other organization.
     * Test:
     *  - Get the organization.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_nonmember_cannot_show()
    {
        $organization = Organization::factory()->create();

        // act as a teacher of other organization
        $this->actingAsTeacher(Organization::factory()->create()->id);
        $response = $this->getJson($this->api("organizations/$organization->id"));
        $response->assertForbidden();
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as an administrator belonging to the organization.
     * Test:
     *  - Update the organization.
     *  - Make sure the response is 'OK'.
     *  - Make sure the update is saved to the database.
     */
    public function test_member_administrator_can_update()
    {
        $organization = Organization::factory()->create();
        $this->actingAsAdministrator($organization->id);

        $response = $this->patchJson($this->api("organizations/$organization->id"), [
            'name' => 'Koulun uusi nimi'
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Koulun uusi nimi'
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as an administrator belonging to no organization.
     * Test:
     *  - Update the organization.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_nonmember_cannot_update()
    {
        $organization = Organization::factory()->create();
        $administrator = $this->actingAsAdministrator();

        $response = $this->patchJson($this->api("organizations/$organization->id"), [
            'name' => 'Koulun uusi nimi'
        ]);

        $response->assertForbidden();
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as an administrator belonging to the organization.
     *  - Create and login as an administrator belonging to no organization.
     * Test:
     *  - Try deleting the organization as the member administrator.
     *  - Make sure the response is 'forbidden'.
     *  - Try deleting the organization as the non-member administrator.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_nobody_can_delete()
    {
        $organization = Organization::factory()->create();

        // act as a member administrator
        $this->actingAsAdministrator($organization->id);
        $response = $this->deleteJson($this->api("organizations/$organization->id"));
        $response->assertForbidden();

        // act as a non-member administrator
        $this->actingAsAdministrator();
        $response = $this->deleteJson($this->api("organizations/$organization->id"));
        $response->assertForbidden();
    }
}
