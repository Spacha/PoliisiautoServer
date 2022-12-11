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
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Report;

class ReportMessageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create a report belonging to the organization made by the student.
     *  - Create a report message made by the student but do not store it.
     * Test:
     *  - Store a new message under the report using the post data.
     *  - Make sure the response is 'created'.
     *  - Make sure the message is saved to the database.
     */
    public function test_reporter_can_create()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);
        $report = Report::factory()->forReporter($student)
            ->forNewCaseIn($organization)->create();
        $message = ReportMessage::factory()->forAuthor($student)->make();

        $response = $this->postJson($this->api("reports/$report->id/messages"), [
            'content' => $message->content,
            'is_anonymous' => $message->is_anonymous,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('report_messages', [
            'content' => $message->content,
            'author_id' => $student->id,
            'report_id' => $report->id,
            'is_anonymous' => $message->is_anonymous,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create a case belonging to the organization.
     * Test:
     *  - Get the case.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     */
    public function test_author_can_show()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);
        $message = ReportMessage::factory()
            ->forAuthor($student)
            ->for(Report::factory()->forReporter($student)
                ->forNewCaseIn($organization)->create()
            )->create();

        $response = $this->getJson($this->api("report-messages/$message->id"));
        $response->assertOk()->assertJson([
            'content' => $message->content,
            'author_id' => $student->id,
            'author_name' => $student->name,
            'is_anonymous' => $message->is_anonymous,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization.
     *  - Create a report message belonging to a report in the organization,
     *    made by the student.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the message.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found.
     */
    public function test_member_teacher_can_show()
    {
        $organization = Organization::factory()->create();
        $student = Student::factory()->for($organization)->create();
        $message = ReportMessage::factory()
            ->forAuthor($student)
            ->for(Report::factory()->forReporter($student)
                ->forNewCaseIn($organization)->create()
            )->create();

        $this->actingAsTeacher($organization->id);  // act as other teacher
        $response = $this->getJson($this->api("report-messages/$message->id"));
        $response->assertOk()->assertJson([
            'content' => $message->content,
            'author_id' => $student->id,
            'author_name' => $student->name,
            'is_anonymous' => $message->is_anonymous,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization.
     *  - Create an anonymous report message belonging to a report in the organization,
     *    made by the student.
     *  - Create and login as a teacher belonging to the organization.
     * Test:
     *  - Get the message.
     *  - Make sure the response is 'OK'.
     *  - Make sure the relevant fields are found but author's info is hidden.
     */
    public function test_anonymous_does_not_show_author()
    {
        $organization = Organization::factory()->create();
        $student = Student::factory()->for($organization)->create();
        $message = ReportMessage::factory()
            ->forAuthor($student)
            ->for(Report::factory()->forReporter($student)
                ->forNewCaseIn($organization)->create()
            )->anonymous()->create();

        $this->actingAsTeacher($organization->id);  // act as other teacher
        $response = $this->getJson($this->api("report-messages/$message->id"));
        $response->assertOk()->assertJson([
            'content'       => $message->content,
            'author_id'     => null,
            'author_name'   => null,
            'is_anonymous'  => $message->is_anonymous,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create a student belonging to the organization.
     *  - Create a report message belonging to a report in the organization,
     *    made by the student.
     *  - Create and login as another student belonging to the organization.
     * Test:
     *  - Get the message.
     *  - Make sure the response is 'forbidden'.
     */
    public function test_other_student_cannot_show()
    {
        $organization = Organization::factory()->create();
        $authorStudent = Student::factory()->for($organization)->create();
        $message = ReportMessage::factory()
            ->forAuthor($authorStudent)
            ->for(Report::factory()->forReporter($authorStudent)
                ->forNewCaseIn($organization)->create()
            )->create();

        $this->actingAsStudent($organization->id);  // act as other student
        $response = $this->getJson($this->api("report-messages/$message->id"));
        $response->assertForbidden();
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create a report message belonging to a report in the organization,
     *    made by the student.
     * Test:
     *  - Update the message.
     *  - Make sure the response is 'OK'.
     *  - Make sure the update is saved to the database.
     */
    public function test_self_can_update()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);
        $message = ReportMessage::factory()
            ->forAuthor($student)
            ->for(Report::factory()->forReporter($student)
                ->forNewCaseIn($organization)->create()
            )->create();

        $response = $this->patchJson($this->api("report-messages/$message->id"), [
            'content' => 'New content!',
            'is_anonymous' => true,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('report_messages', [
            'id'            => $message->id,
            'content'       => 'New content!',
            'author_id'     => $student->id,
            'is_anonymous'  => true,
        ]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create a report message belonging to a report in the organization,
     *    made by the student.
     * Test:
     *  - Delete the message.
     *  - Make sure the response is 'OK'.
     *  - Make sure the report message is deleted from the database.
     */
    public function test_self_can_delete()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);
        $message = ReportMessage::factory()
            ->forAuthor($student)
            ->for(Report::factory()->forReporter($student)
                ->forNewCaseIn($organization)->create()
            )->create();

        $response = $this->deleteJson($this->api("report-messages/$message->id"));
        $response->assertOk();
        $this->assertDatabaseMissing('report_messages', ['id' => $message->id]);
    }

    /**
     * Preparations:
     *  - Create an organization.
     *  - Create and login as a student belonging to the organization.
     *  - Create a report message belonging to a report in the organization,
     *    made by the student.
     * Test:
     *  - Update the message.
     *  - Make sure the response is 'forbidden'.
     *  - Make sure the report message remains in the database.
     */
    public function test_other_student_cannot_delete()
    {
        $organization = Organization::factory()->create();
        $authorStudent = Student::factory()->for($organization)->create();
        $message = ReportMessage::factory()
            ->forAuthor($authorStudent)
            ->for(Report::factory()->forReporter($authorStudent)
                ->forNewCaseIn($organization)->create()
            )->create();

        $this->actingAsStudent($organization->id);  // act as other student
        $response = $this->deleteJson($this->api("report-messages/$message->id"));
        $response->assertForbidden();
        $this->assertDatabaseHas('report_messages', ['id' => $message->id]);
    }
}
