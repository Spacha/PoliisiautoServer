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

    public function test_member_teacher_can_list()
    {
        // create organization, 3 cases to it and test if
        // a member teacher can see them
        $organization = Organization::factory()->create();
        ReportCase::factory()->for($organization)->count(3)->create();
        $this->actingAsTeacher($organization->id);

        $response = $this->getJson($this->api("cases"));
        $response->assertOk()->assertJsonCount(3);
    }

    public function test_member_student_cannot_list()
    {
        $organization = Organization::factory()->create();
        ReportCase::factory()->for($organization)->count(3)->create();
        $this->actingAsStudent($organization->id);

        $response = $this->getJson($this->api("cases"));
        $response->assertForbidden();
    }

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
