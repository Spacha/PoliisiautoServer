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

    public function test_member_teacher_can_list()
    {
        // create organization, 3 reports to it and test if
        // a member teacher can see them
        $organization = Organization::factory()->create();
        Report::factory()->forNewCaseIn($organization)
            ->state(['reporter_id' => Student::factory()->for($organization)->create()->id])
            ->count(3)->create();
        $this->actingAsTeacher($organization->id);

        $response = $this->getJson($this->api("reports"));
        $response->assertOk()->assertJsonCount(3);
    }

    public function test_student_cannot_list()
    {
        // create organization, 3 reports to it and test if
        // a member student can see them (should not!)
        $organization = Organization::factory()->create();
        Report::factory()->forNewCaseIn($organization)
            ->state(['reporter_id' => Student::factory()->for($organization)->create()->id])
            ->count(3)->create();
        $this->actingAsStudent($organization->id);

        $response = $this->getJson($this->api("reports"));
        $response->assertForbidden();
    }

    public function test_student_can_create_with_handler()
    {
        $organization = Organization::factory()->create();
        $bully = Student::factory()->for($organization)->create();
        $handler = Teacher::factory()->for($organization)->create();
        // make: create a fake report but do not store to database
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

    public function test_teacher_can_create_without_handler()
    {
        $organization = Organization::factory()->create();
        $bully = Student::factory()->for($organization)->create();
        // make: create a fake report but do not store to database
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
