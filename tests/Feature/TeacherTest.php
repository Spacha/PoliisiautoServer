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
use App\Models\Teacher;
use App\Models\Report;

class TeacherTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a teacher belonging to the organization.
     *  - Create 4 teachers under the organization.
     * Test:
     *  - Get the teachers.
     *  - Make sure the response is 'OK'.
     *  - Make sure the response contains 4 students.
     */
    public function test_teacher_can_list()
    {
        $organization = Organization::factory()->create();
        $teacher = $this->actingAsTeacher($organization->id);
        Teacher::factory()->forOrganization($organization->id)->count(4)->create();

        $response = $this->getJson($this->api("teachers"));
        $response->assertOk()->assertJsonCount(5);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the teacher.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     */
    public function test_teacher_can_show()
    {
        $organization = Organization::factory()->create();
        $teacher = $this->actingAsTeacher($organization->id);

        $response = $this->getJson($this->api("teachers/$teacher->id"));
        $response->assertOk()->assertJson([
            'id' => $teacher->id,
            'email' => $teacher->email,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create a teacher belonging to the organization.
     * Test:
     *  - Get the teacher.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     */
    public function test_student_can_show()
    {
        $organization = Organization::factory()->create();
        $this->actingAsStudent($organization->id);
        $teacher = Teacher::factory()->forOrganization($organization->id)->create();

        $response = $this->getJson($this->api("teachers/$teacher->id"));
        $response->assertOk()->assertJson([
            'id' => $teacher->id,
            'email' => $teacher->email,
        ]);
    }

    /**
     * Preparations:
     *  - Create and login as a teacher.
     * Test:
     *  - Update the teacher.
     *  - Make sure the response is 'OK'.
     *  - Make sure the update is saved to the database.
     */
    public function test_can_update()
    {
        $teacher = $this->actingAsTeacher();

        $response = $this->patchJson($this->api("teachers/$teacher->id"), [
            'first_name' => 'Miika'
        ]);
        
        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'first_name' => 'Miika'
        ]);
    }

    /**
     * Preparations:
     *  - Create and login as a teacher.
     * Test:
     *  - Delete the teacher.
     *  - Make sure the response is 'OK'.
     *  - Make sure the user is deleted from the database.
     */
    public function test_self_can_delete()
    {
        $teacher = $this->actingAsTeacher();
        $response = $this->deleteJson($this->api("teachers/$teacher->id"));
        $response->assertOk();
        $this->assertDatabaseMissing('users', [
            'id' => $teacher->id
        ]);
    }

    /**
     * Preparations:
     *  - Craete an organization
     *  - Create and login as a teacher belonging to the organization.
     *  - Create and login as another teacher belonging to the organization.
     * Test:
     *  - Try deleting the teacher.
     *  - Make sure the response is 'forbidden'.
     *  - Make sure the user remains in the database.
     */
    public function test_other_teacher_cannot_delete()
    {
        $organization = Organization::factory()->create();
        $teacher = Teacher::factory()->forOrganization($organization->id)->create();
        $this->actingAsTeacher($organization->id);  // act as other teacher

        $response = $this->deleteJson($this->api("teachers/$teacher->id"));
        $response->assertForbidden();
        $this->assertDatabaseHas('users', [
            'id' => $teacher->id
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a teacher belonging to the organization.
     *  - Create 3 reports under the organization, made by the teacher.
     * Test:
     *   - Get the user's reports.
     *   - Make sure the response is 'OK'.
     *   - Make sure the response contains 3 reports.
     */
    public function test_self_can_list_reports()
    {
        $organization = Organization::factory()->create();
        $teacher = $this->actingAsTeacher($organization->id);
        $reports = Report::factory()
            ->forReporter($teacher)
            ->forNewCaseIn($organization)
            ->count(3)->create();

        $response = $this->getJson($this->api("teachers/$teacher->id/reports"));
        $response->assertOk()->assertJsonCount(3);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a teacher belonging to the organization (reporter).
     *  - Create and login as a teacher belonging to the organization.
     *  - Create 3 reports under the organization, made by the reporter teacher
     *    and assigned to the teacher.
     * Test:
     *   - Get the user's assigned reports.
     *   - Make sure the response is 'OK'.
     *   - Make sure the response contains 3 reports.
     */
    public function test_self_can_list_assigned_reports()
    {
        $organization = Organization::factory()->create();
        $reporterTeacher = Teacher::factory()->for($organization)->create();
        $teacher = $this->actingAsTeacher($organization->id);
        $reports = Report::factory()
            ->forReporter($reporterTeacher)
            ->forNewCaseIn($organization)
            ->forHandler($teacher)
            ->count(3)->create();

        $response = $this->getJson($this->api("teachers/$teacher->id/assigned-reports"));
        $response->assertOk()->assertJsonCount(3);
    }
}
