<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace Tests;

/**
 * A trait that makes the inheriting test case to be skipped
 * when running tests.
 */
trait TestCaseIsIgnored {
    protected function setUp() : void {
        $this->markTestIncomplete();
    }
}