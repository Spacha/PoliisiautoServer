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

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_teacher_can_list()
    {
        // create organization, 3 reports to it and test if
        // a member teacher can see them
        $organization = Organization::factory()->create();
        Report::factory()->for(ReportCase::factory()->for($organization), 'case')
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
        Report::factory()->for(ReportCase::factory()->for($organization), 'case')
            ->state(['reporter_id' => Student::factory()->for($organization)->create()->id])
            ->count(3)->create();
        $this->actingAsStudent($organization->id);

        $response = $this->getJson($this->api("reports"));
        $response->assertForbidden();
    }

    public function test_student_can_create()
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

    public function test_self_can_show()
    {
        $organization = Organization::factory()->create();
        $student = $this->actingAsStudent($organization->id);
        $report = Report::factory()
            ->forReporter($student)
            ->for(ReportCase::factory()->for($organization)->create(), 'case')
            ->create();

        $response = $this->getJson($this->api("reports/$report->id"));
        $response->assertOk()->assertJson([
            'description' => $report->description,
            'reporter_id' => $student->id,
            'is_anonymous' => $report->is_anonymous,
        ]);
    }

    // public function test_nonmembers_cannot_show()
    // {
    //     $organization = Organization::factory()->create();

    //     // act as a teacher of other organization
    //     $this->actingAsTeacher(Organization::factory()->create()->id);

    //     $response = $this->getJson($this->api("organizations/$organization->id"));
    //     $response->assertForbidden();
    // }

    // public function test_member_administrator_can_update()
    // {
    //     $organization = Organization::factory()->create();
    //     $this->actingAsAdministrator($organization->id);

    //     $response = $this->patchJson($this->api("organizations/$organization->id"), [
    //         'name' => 'Koulun uusi nimi'
    //     ]);

    //     $response->assertOk();
    //     $this->assertDatabaseHas('organizations', [
    //         'id' => $organization->id,
    //         'name' => 'Koulun uusi nimi'
    //     ]);
    // }

    // public function test_nonmembers_cannot_update()
    // {
    //     $organization = Organization::factory()->create();
    //     $administrator = $this->actingAsAdministrator();

    //     $response = $this->patchJson($this->api("organizations/$organization->id"), [
    //         'name' => 'Koulun uusi nimi'
    //     ]);

    //     $response->assertForbidden();
    // }

    // public function test_nobody_can_delete()
    // {
    //     $organization = Organization::factory()->create();

    //     // act as a member administrator
    //     $this->actingAsAdministrator($organization->id);

    //     $response = $this->deleteJson($this->api("organizations/$organization->id"));
    //     $response->assertForbidden();

    //     // act as a non-member administrator
    //     $this->actingAsAdministrator();

    //     $response = $this->deleteJson($this->api("organizations/$organization->id"));
    //     $response->assertForbidden();
    // }
}
