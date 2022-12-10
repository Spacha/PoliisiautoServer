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

    public function test_teacher_can_list()
    {
        $organization = Organization::factory()->create();
        $teacher = $this->actingAsTeacher($organization->id);
        Teacher::factory()->forOrganization($organization->id)->count(4)->create();

        $response = $this->getJson($this->api("teachers"));
        $response->assertOk()->assertJsonCount(5);
    }

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

    public function test_self_can_delete()
    {
        $teacher = $this->actingAsTeacher();
        $response = $this->deleteJson($this->api("teachers/$teacher->id"));
        $response->assertOk();
        $this->assertDatabaseMissing('users', [
            'id' => $teacher->id
        ]);
    }

    public function test_other_teachers_cannot_delete()
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

    public function test_self_can_list_reports()
    {
        $organization = Organization::factory()->create();
        $teacher = $this->actingAsTeacher();
        $reports = Report::factory()
            ->forReporter($teacher)
            ->forNewCaseIn($organization)
            ->count(3)->create();

        $response = $this->getJson($this->api("teachers/$teacher->id/reports"));
        $response->assertOk()->assertJsonCount(3);
    }

    public function test_self_can_list_assigned_reports()
    {
        $organization = Organization::factory()->create();
        $reporterTeacher = Teacher::factory()->create();
        $teacher = $this->actingAsTeacher();
        $reports = Report::factory()
            ->forReporter($reporterTeacher)
            ->forNewCaseIn($organization)
            ->for($teacher, 'handler')
            ->count(3)->create();

        $response = $this->getJson($this->api("teachers/$teacher->id/assigned-reports"));
        $response->assertOk()->assertJsonCount(3);
    }
}
