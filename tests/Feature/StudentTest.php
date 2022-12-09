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
use App\Models\Report;

class StudentTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_list()
    {
        $organization = Organization::factory()->create();
        $teacher = $this->actingAsTeacher($organization->id);
        Student::factory()->forOrganization($organization->id)->count(5)->create();

        $response = $this->getJson($this->api("students"));
        $response->assertOk()->assertJsonCount(5);
    }

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

    public function test_student_cannot_show()
    {
        $organization = Organization::factory()->create();
        $this->actingAsStudent($organization->id);
        $student = Student::factory()->forOrganization($organization->id)->create();

        $response = $this->getJson($this->api("students/$student->id"));
        $response->assertForbidden();
    }

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

    public function test_self_can_delete()
    {
        $student = $this->actingAsStudent();
        $response = $this->deleteJson($this->api("students/$student->id"));
        $response->assertOk();
        $this->assertDatabaseMissing('users', [
            'id' => $student->id
        ]);
    }

    public function test_other_students_cannot_delete()
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

    public function test_self_can_list_reports()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent();
        $reports = Report::factory()
            ->forReporter($student)
            ->for(ReportCase::factory()->for($organization), 'case')
            ->count(3)->create();

        $response = $this->getJson($this->api("students/$student->id/reports"));
        $response->assertOk()->assertJsonCount(3);
    }

    public function test_self_can_list_involved_reports()
    {
        $organization = Organization::factory()->create();
        $reporterStudent = Student::factory()->create();
        $student = $this->actingAsStudent();
        $reports = Report::factory()
            ->forReporter($reporterStudent)
            ->for(ReportCase::factory()->for($organization), 'case')
            ->for($student, 'bully')
            ->count(3)->create();

        // make sure that the response has no 'bullied' type reports
        // but has 3 'bully' type reports
        $response = $this->getJson($this->api("students/$student->id/involved-reports"));
        $response->assertOk()
            ->assertJsonPath('bullied', [])
            ->assertJsonCount(3, 'bully');
    }
}
