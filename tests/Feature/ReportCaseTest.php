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
use App\Models\ReportCase;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Report;

class ReportCaseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create 3 report cases belonging to the organization.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the case list.
     *  - Make sure the response is 'OK'.
     *  - Make sure the response contains 3 cases.
     */
    public function test_member_teacher_can_list()
    {
        $organization = Organization::factory()->create();
        ReportCase::factory()->for($organization)->count(3)->create();
        $this->actingAsTeacher($organization->id);

        $response = $this->getJson($this->api("cases"));
        $response->assertOk()->assertJsonCount(3);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create 3 report cases belonging to the organization.
     *  - Create and login as a student belonging to the organization.
     * Test:
     *  - Get the case list.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_member_student_cannot_list()
    {
        $organization = Organization::factory()->create();
        ReportCase::factory()->for($organization)->count(3)->create();
        $this->actingAsStudent($organization->id);

        $response = $this->getJson($this->api("cases"));
        $response->assertForbidden();
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a report case belonging to the organization but do not store it.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Store a new case using the post data.
     *  - Make sure the response is 'created'.
     *  - Make sure the case is saved to the database.
     */
    public function test_member_teacher_can_create()
    {
        $organization = Organization::factory()->create();
        $case = ReportCase::factory()->for($organization)->make();
        $this->actingAsTeacher($organization->id);
        $postData = ['name' => $case->name];

        $response = $this->postJson($this->api("cases"), $postData);

        $response->assertCreated();
        $this->assertDatabaseHas('report_cases', $postData);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a case belonging to the organization.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the case.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     */
    public function test_member_teacher_can_show()
    {
        $organization = Organization::factory()->create();
        $case = ReportCase::factory()->for($organization)->create();
        $this->actingAsTeacher($organization->id);

        $response = $this->getJson($this->api("cases/$case->id"));
        $response->assertOk()->assertJson([
            'id' => $case->id,
            'name' => $case->name,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a case belonging to the organization.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Update the case.
     *  - Make sure the response is 'OK'.
     *  - Make sure the update is saved to the database.
     */
    public function test_member_teacher_can_update()
    {
        $organization = Organization::factory()->create();
        $case = ReportCase::factory()->for($organization)->create();
        $this->actingAsTeacher($organization->id);

        $response = $this->patchJson($this->api("cases/$case->id"), [
            'name' => 'Case\'s new name!',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('report_cases', [
            'id' => $case->id,
            'name' => 'Case\'s new name!',
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a case belonging to the organization.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Delete the case.
     *  - Make sure the response is 'OK'.
     *  - Make sure the case is deleted from the database.
     */
    public function test_member_teacher_can_delete()
    {
        $organization = Organization::factory()->create();
        $case = ReportCase::factory()->for($organization)->create();
        $this->actingAsTeacher($organization->id);

        $response = $this->deleteJson($this->api("cases/$case->id"));

        $response->assertOk();
        $this->assertDatabaseMissing('report_cases', [
            'id' => $case->id,
        ]);
    }
}
