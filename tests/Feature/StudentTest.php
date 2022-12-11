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
use App\Models\Report;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a teacher belonging to the organization.
     *  - Create 5 students under the organization.
     * Test:
     *  - Get the students.
     *  - Make sure the response is 'OK'.
     *  - Make sure the response contains 5 students.
     */
    public function test_teacher_can_list()
    {
        $organization = Organization::factory()->create();
        $teacher = $this->actingAsTeacher($organization->id);
        Student::factory()->forOrganization($organization->id)->count(5)->create();

        $response = $this->getJson($this->api("students"));
        $response->assertOk()->assertJsonCount(5);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a teacher belonging to the organization.
     *  - Create a student belonging to the organization.
     * Test:
     *  - Get the student.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     */
    public function test_teacher_can_show()
    {
        $organization = Organization::factory()->create();
        $teacher = $this->actingAsTeacher($organization->id);
        $student = Student::factory()->forOrganization($organization->id)->create();

        $response = $this->getJson($this->api("students/$student->id"));
        $response->assertOk()->assertJson([
            'id' => $student->id,
            'email' => $student->email,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create another student belonging to the organization.
     * Test:
     *  - Get the student.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_student_cannot_show()
    {
        $organization = Organization::factory()->create();
        $this->actingAsStudent($organization->id);
        $student = Student::factory()->forOrganization($organization->id)->create();

        $response = $this->getJson($this->api("students/$student->id"));
        $response->assertForbidden();
    }

    /**
     * Preparations:
     *  - Create and login as a student.
     * Test:
     *  - Update the student.
     *  - Make sure the response is 'OK'.
     *  - Make sure the update is saved to the database.
     */
    public function test_can_update()
    {
        $student = $this->actingAsStudent();

        $response = $this->patchJson($this->api("students/$student->id"), [
            'first_name' => 'Miika'
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'first_name' => 'Miika'
        ]);
    }

    /**
     * Preparations:
     *  - Create and login as a student.
     * Test:
     *  - Delete the student.
     *  - Make sure the response is 'OK'.
     *  - Make sure the user is deleted from the database.
     */
    public function test_self_can_delete()
    {
        $student = $this->actingAsStudent();

        $response = $this->deleteJson($this->api("students/$student->id"));
        $response->assertOk();
        $this->assertDatabaseMissing('users', [
            'id' => $student->id
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization.
     *  - Create and login as another student belonging to the organization.
     * Test:
     *  - Try deleting the other student.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_other_student_cannot_delete()
    {
        $organization = Organization::factory()->create();
        $student = Student::factory()->forOrganization($organization->id)->create();
        $this->actingAsStudent($organization->id);  // act as other student

        $response = $this->deleteJson($this->api("students/$student->id"));
        $response->assertForbidden();
        $this->assertDatabaseHas('users', [
            'id' => $student->id
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create 3 reports under the organization, made by the student.
     * Test:
     *  - Get the user's reports.
     *  - Make sure the response is 'OK'.
     *  - Make sure the response contains 3 reports.
     */
    public function test_self_can_list_reports()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent();
        $reports = Report::factory()
            ->forReporter($student)
            ->forNewCaseIn($organization)
            ->count(3)->create();

        $response = $this->getJson($this->api("students/$student->id/reports"));
        $response->assertOk()->assertJsonCount(3);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization (bully).
     *  - Create another student belonging to the organization (reporter).
     *  - Create 3 reports under the organization, made by the reporter student,
     *    the bully student as bully.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the user's involved reports.
     *  - Make sure the response is 'OK'.
     *  - Make sure the response contains 3 'bully' reports
     *     and no 'bullied' reports.
     */
    public function test_teacher_can_list_involved_reports()
    {
        $organization = Organization::factory()->create();
        $reporterStudent = Student::factory()->for($organization)->create();
        $bullyStudent = Student::factory()->for($organization)->create();

        $reports = Report::factory()
            ->forReporter($reporterStudent)
            ->forNewCaseIn($organization)
            ->for($bullyStudent, 'bully')
            ->count(3)->create();
        $this->actingAsTeacher($organization->id);

        // make sure that the response has no 'bullied' type reports
        // but has 3 'bully' type reports
        $response = $this->getJson($this->api("students/$bullyStudent->id/involved-reports"));
        $response->assertOk()
            ->assertJsonPath('bullied', [])
            ->assertJsonCount(3, 'bully');
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a and login as a student belonging to the organization (bully).
     *  - Create another student belonging to the organization (reporter).
     *  - Create 3 reports under the organization, made by the reporter student,
     *    the bully student as bully.
     * Test:
     *  - Try getting the user's involved reports.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_self_cannot_list_involved_reports()
    {
        $organization = Organization::factory()->create();
        $reporterStudent = Student::factory()->for($organization)->create();
        $bullyStudent = $this->actingAsStudent($organization->id);
        $reports = Report::factory()
            ->forReporter($reporterStudent)
            ->forNewCaseIn($organization)
            ->for($bullyStudent, 'bully')
            ->count(3)->create();

        $response = $this->getJson($this->api("students/$bullyStudent->id/involved-reports"));
        $response->assertForbidden();
    }
}
