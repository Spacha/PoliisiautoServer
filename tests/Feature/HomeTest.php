<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace Tests\Feature;

use Tests\TestCase;

class HomeTest extends TestCase
{
    /**
     * Preparations:
     *   None
     * Test:
     *   Get the home page.
     *   Make sure the response is OK.
     */
    public function test_home_page_works()
    {
        $response = $this->get('/');
        $response->assertOk();
    }
}
