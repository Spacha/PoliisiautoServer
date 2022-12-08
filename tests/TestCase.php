<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\Student;
use App\Models\Teacher;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Returns the API url for given endpoint without hostname.
     * Basically prepends it with 'api/{version)/'
     */
    protected function api(string $endpoint = '') : string {
        return '/api/v1/' . ltrim($endpoint, '/');
    }

    /**
     * Create a student and login.
     * @param int $organizationId  The organization which the student belongs to.
     * @return Student
     */
    protected function actingAsStudent(int $organizationId = null) : Student {
        $student = Student::factory()->forOrganization($organizationId)->create();
        Sanctum::actingAs($student);
        return $student;
    }

    /**
     * Create a teacher and login.
     * @param int $organizationId  The organization which the teacher belongs to.
     * @return Teacher
     */
    protected function actingAsTeacher(int $organizationId = null) : Teacher {
        $teacher = Teacher::factory()->forOrganization($organizationId)->create();
        Sanctum::actingAs($teacher);
        return $teacher;
    }
}
