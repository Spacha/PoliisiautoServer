<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use App\Role;

class RoleTest extends TestCase
{
    /**
     * Preparations:
     *   None
     * Test:
     *   Make sure all the role constants match with the role indices.
     */
    public function test_roles_match_with_indices()
    {
        $roleIndices = [
            1 => Role::STUDENT,
            2 => Role::TEACHER,
            3 => Role::ADMINISTRATOR,
        ];

        foreach ($roleIndices as $roleIndex => $role)
            $this->assertTrue($roleIndex == $role);
    }

    /**
     * Preparations:
     *   None
     * Test:
     *   Make sure all the human readable role strings match with the role indices.
     */
    public function test_human_readable_roles_match_with_indices()
    {
        $roleStrings = [
            1 => 'student',
            2 => 'teacher',
            3 => 'administrator',
        ];

        foreach ($roleStrings as $roleIndex => $roleString)
            $this->assertTrue(Role::forHumans($roleIndex) == $roleString);
    }

    /**
     * Preparations:
     *   None
     * Test:
     *   Make sure all the role constants match with their models.
     */
    public function test_role_models_match_with_roles()
    {
        $roleClasses = [
            Role::STUDENT       => \App\Models\Student::class,
            Role::TEACHER       => \App\Models\Teacher::class,
            Role::ADMINISTRATOR => \App\Models\Administrator::class,
        ];

        foreach ($roleClasses as $role => $roleClass)
            $this->assertTrue(Role::getRoleModel($role)::class == $roleClass);
    }

    /**
     * Preparations:
     *   None
     * Test:
     *   Try getting an undefined role using an index larger than the limit.
     *   Make sure that an exception is thrown.
     */
    public function test_role_model_for_invalid_role_throws()
    {
        $thrown = false;
        try {
            Role::getRoleModel(Role::ROLE_LIMITS[1] + 1);
        } catch (UnexpectedValueException $e) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
    }
}
