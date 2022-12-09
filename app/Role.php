<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App;

use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Administrator;
use App\Role;

use UnexpectedValueException;

class Role
{
    const ROLE_LIMITS = [1, 3];

    const STUDENT       = 1;
    const TEACHER       = 2;
    const ADMINISTRATOR = 3;
    
    const HUMAN_READABLE = [
        self::STUDENT       => "student",
        self::TEACHER       => "teacher",
        self::ADMINISTRATOR => "administrator"
    ];
    
    public static function forHumans(int $role) : string
    {
        if ( !($role >= 0 && $role <= 3) )
            throw new UnexpectedValueException("Role between ".self::ROLE_LIMITS[0]." and ".self::ROLE_LIMITS[1]." expected, got $role!");

        return self::HUMAN_READABLE[$role];
    }

    /**
     * Checks the role and returns the corresponding
     * model without initializing it.
     *
     * @param  int $role    User role ID.
     * @return User         Child of User, depending on role.
     */
    public static function getRoleModel(int $role) : User
    {
        switch($role)
        {
            case Role::STUDENT:
                return new Student;
                break;
            case Role::TEACHER:
                return new Teacher;
                break;
            case Role::ADMINISTRATOR:
                return new Administrator;
                break;
            default:
                throw new UnexpectedValueException("Unknown role {$role}!");
        }
    }
}