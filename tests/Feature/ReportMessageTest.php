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

    public function test_anonymous_does_not_show_author()
    {
        $organization = Organization::factory()->create();
        $student = Student::factory()->for($organization)->create();
        $message = ReportMessage::factory()
            ->forAuthor($student)
            ->for(Report::factory()->forReporter($student)
                ->forNewCaseIn($organization)->create()
            )
            ->anonymous()->create();

        $this->actingAsTeacher($organization->id);  // act as other teacher
        $response = $this->getJson($this->api("report-messages/$message->id"));
        $response->assertOk()->assertJson([
            'content'       => $message->content,
            'author_id'     => null,
            'author_name'   => null,
            'is_anonymous'  => $message->is_anonymous,
        ]);
    }

    public function test_other_student_cannot_show() {
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
