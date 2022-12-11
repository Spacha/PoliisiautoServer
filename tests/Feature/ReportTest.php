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

use App\Models\ReportMessage;
use App\Models\Organization;
use App\Models\ReportCase;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Report;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create 3 reports belonging to the organization, made by a new student.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the report list.
     *  - Make sure the response is 'OK'.
     *  - Make sure the response contains 3 reports.
     */
    public function test_member_teacher_can_list()
    {
        $organization = Organization::factory()->create();
        Report::factory()->forNewCaseIn($organization)
            ->forReporter(Student::factory()->for($organization)->create())
            ->count(3)->create();
        $this->actingAsTeacher($organization->id);

        $response = $this->getJson($this->api("reports"));
        $response->assertOk()->assertJsonCount(3);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create 3 reports belonging to the organization, made by a new student.
     *  - Create and login as another student belonging to the organization.
     * Test:
     *  - Get the report list.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_student_cannot_list()
    {
        $organization = Organization::factory()->create();
        Report::factory()->forNewCaseIn($organization)
            ->forReporter(Student::factory()->for($organization)->create())
            ->count(3)->create();
        $this->actingAsStudent($organization->id);

        $response = $this->getJson($this->api("reports"));
        $response->assertForbidden();
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization (bully)
     *  - Create a teacher belonging to the organization (handler)
     *  - Create a report but do not store it.
     *  - Create and login as another student belonging to the organization.
     * Test:
     *  - Store the report using the post data.
     *  - Make sure the response is 'OK'.
     *  - Make sure the report is saved to the database.
     */
    public function test_student_can_create_with_handler()
    {
        $organization = Organization::factory()->create();
        $bully = Student::factory()->for($organization)->create();
        $handler = Teacher::factory()->for($organization)->create();
        $report = Report::factory()->make();
        $postData = [
            'description'   => $report->description,
            'bully_id'      => $bully->id,
            'handler_id'    => $handler->id,
            'is_anonymous'  => $report->is_anonymous,
        ];

        $student = $this->actingAsStudent($organization->id);
        $response = $this->postJson($this->api("reports"), $postData);
        $response->assertCreated();

        $this->assertDatabaseHas('reports', $postData + ['reporter_id' => $student->id]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization (bully)
     *  - Create a teacher belonging to the organization (handler)
     *  - Create a report but do not store it.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Store the report using the post data.
     *  - Make sure the response is 'OK'.
     *  - Make sure the report is saved to the database.
     */
    public function test_teacher_can_create_without_handler()
    {
        $organization = Organization::factory()->create();
        $bully = Student::factory()->for($organization)->create();
        $report = Report::factory()->make();
        $postData = [
            'description'   => $report->description,
            'bully_id'      => $bully->id,
            'is_anonymous'  => $report->is_anonymous,
        ];

        $teacher = $this->actingAsTeacher($organization->id);
        $response = $this->postJson($this->api("reports"), $postData);
        $response->assertCreated();

        $this->assertDatabaseHas('reports', $postData + ['reporter_id' => $teacher->id]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create a report belonging to a the organization, made by the student.
     * Test:
     *  - Get the report.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     */
    public function test_self_can_show()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);
        $report = Report::factory()
            ->forReporter($student)
            ->forNewCaseIn($organization)
            ->create();

        $response = $this->getJson($this->api("reports/$report->id"));
        $response->assertOk()->assertJson([
            'description' => $report->description,
            'reporter_id' => $student->id,
            'is_anonymous' => $report->is_anonymous,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization.
     *  - Create a report belonging to a the organization, made by the student.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the report.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     */
    public function test_member_teacher_can_show()
    {
        $organization = Organization::factory()->create();
        $student = Student::factory()->for($organization)->create();
        $report = Report::factory()
            ->forReporter($student)
            ->forNewCaseIn($organization)
            ->create();

        $this->actingAsTeacher($organization->id);  // act as other teacher
        $response = $this->getJson($this->api("reports/$report->id"));
        $response->assertOk()->assertJson([
            'description' => $report->description,
            'reporter_id' => $student->id,
            'is_anonymous' => $report->is_anonymous,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a teacher belonging to the organization.
     *  - Create an anonymous report belonging to a the organization,
     *    made by a new student belonging to the organziation.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the report.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found but reporter's info is hidden.
     */
    public function test_anonymous_does_not_show_reporter()
    {
        $organization = Organization::factory()->create();
        $teacher = $this->actingAsTeacher($organization->id);
        $report = Report::factory()
            ->forReporter(Student::factory()->for($organization)->create())
            ->forNewCaseIn($organization)
            ->anonymous()
            ->create();

        // get the report and make sure the reporter's name and ID are not visible
        $response = $this->getJson($this->api("reports/$report->id"));
        $response->assertOk()->assertJson([
            'description' => $report->description,
            'reporter_id' => null,
            'is_anonymous' => 1,
            'reporter_name' => null,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a report belonging to a the organization,
     *    made by a new student belonging to the organziation.
     *  - Create and login as another student belonging to the organization.
     * Test:
     *  - Get the report.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_other_student_cannot_show()
    {
        $organization = Organization::factory()->create();
        $report = Report::factory()
            ->forReporter(Student::factory()->for($organization)->create())
            ->forNewCaseIn($organization)
            ->create();

        // act as other student who created the report (but in same organization)
        $this->actingAsStudent($organization->id);
        $response = $this->getJson($this->api("reports/$report->id"));
        $response->assertForbidden();
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login a student belonging to the organization.
     *  - Create a report belonging to a the organization, made by the student.
     * Test:
     *  - Update the report.
     *  - Make sure the response is 'OK'.
     *  - Make sure the udpate is saved to the database.
     */
    public function test_reporter_can_update()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);
        $report = Report::factory()
            ->forReporter($student)
            ->forNewCaseIn($organization)
            ->create();

        $response = $this->patchJson($this->api("reports/$report->id"), [
            'description' => 'Updated description!'
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'description' => 'Updated description!'
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login a student belonging to the organization.
     *  - Create a report belonging to a the organization, made by the student.
     * Test:
     *  - Delete the report.
     *  - Make sure the response is 'OK'.
     *  - Make sure the report is deleted from the database.
     */
    public function test_reporter_can_delete()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);
        $report = Report::factory()
            ->forReporter($student)
            ->forNewCaseIn($organization)
            ->create();

        $response = $this->deleteJson($this->api("reports/$report->id"));
        $response->assertOk();
        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization.
     *  - Create a report belonging to a the organization, made by the student.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Delete the report as the teacher.
     *  - Make sure the response is 'forbidden'.
     *  - Make sure the report remains in the database.
     */
    public function test_teacher_cannot_delete()
    {
        $organization = Organization::factory()->create();
        $student = Student::factory()->for($organization)->create();
        $report = Report::factory()
            ->forReporter($student)
            ->forNewCaseIn($organization)
            ->create();

        $this->actingAsTeacher($organization->id);  // act as other teacher
        $response = $this->deleteJson($this->api("reports/$report->id"));
        $response->assertForbidden();
        $this->assertDatabaseHas('reports', ['id' => $report->id]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create a report belonging to a the organization, made by the student.
     *  - Create 3 report messaged under the report, made by the student.
     * Test:
     *  - Get the report messages belonging to the report.
     *  - Make sure the response is 'OK'.
     *  - Make sure the response contains 3 report messages.
     */
    public function test_reporter_can_list_messages()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);
        $report = Report::factory()
            ->forReporter($student)
            ->forNewCaseIn($organization)
            ->create();

        ReportMessage::factory()
            ->forAuthor($student)->for($report)
            ->count(3)->create();

        $response = $this->getJson($this->api("reports/$report->id/messages"));
        $response->assertOk()->assertJsonCount(3);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization.
     *  - Create a report belonging to a the organization, made by the student.
     *  - Create 3 report messaged under the report, made by the student.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the report messages belonging to the report.
     *  - Make sure the response is 'OK'.
     *  - Make sure the response contains 3 report messages.
     */
    public function test_member_teacher_can_list_messages()
    {
        $organization = Organization::factory()->create();
        $student = Student::factory()->for($organization)->create();
        $report = Report::factory()
            ->forReporter($student)
            ->forNewCaseIn($organization)
            ->create();

        ReportMessage::factory()
            ->forAuthor($student)->for($report)
            ->count(3)->create();

        $this->actingAsTeacher($organization->id);  // act as other teacher
        $response = $this->getJson($this->api("reports/$report->id/messages"));
        $response->assertOk()->assertJsonCount(3);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization.
     *  - Create a report belonging to a the organization, made by the student.
     *  - Create and login as another student belonging to the organization.
     * Test:
     *  - Get the report messages belonging to the report.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_other_student_cannot_list_messages()
    {
        $organization = Organization::factory()->create();
        $reporterStudent = Student::factory()->for($organization)->create();
        $report = Report::factory()
            ->forReporter($reporterStudent)
            ->forNewCaseIn($organization)
            ->create();

        ReportMessage::factory()
            ->forAuthor($reporterStudent)->for($report)
            ->count(3)->create();

        $this->actingAsStudent($organization->id);  // act as other student
        $response = $this->getJson($this->api("reports/$report->id/messages"));
        $response->assertForbidden();
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization.
     *  - Create a report belonging to a the organization, made by the student.
     *  - Create a new report case belonging to the organization.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Update the report's case to the new one.
     *  - Make sure the response is 'OK'.
     *  - Make sure the update is saved to the database.
     */
    public function test_member_teacher_can_update_case()
    {
        $organization = Organization::factory()->create();
        $student = Student::factory()->for($organization)->create();
        $report = Report::factory()
            ->forReporter($student)
            ->forNewCaseIn($organization)
            ->create();

        // create a new case to which to 'move' the report
        $newCase = ReportCase::factory()->for($organization)->create();

        $this->actingAsTeacher($organization->id);  // act as other teacher
        $response = $this->putJson($this->api("reports/$report->id/update-case"), [
            'case_id' => $newCase->id
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'report_case_id' => $newCase->id,
        ]);
    }
}
