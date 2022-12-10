<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App\Models\ReportCase;
use Auth;

class ReportCaseController extends Controller
{
    /**
     * List all cases in the user's organization.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $organization = currentOrganization();
        $this->authorize('list-report-cases', $organization);

        return $organization->cases;
    }

    /**
     * Store a new case to the user's organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $organization = currentOrganization();
        $this->authorize('create-report-case', $organization);

        $request->validate([
            'name' => 'string|between:1,255'
        ]);

        // store the new case under the user's organization
        currentOrganization()->cases()->save(
            new ReportCase( $request->all() )
        );

        return response(null, 201);
    }

    /**
     * Get the specified case.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $case = ReportCase::findOrFail($id)
        $this->authorize('show-report-case', $case);

        return $case;
    }

    /**
     * Update the specified case.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $case = ReportCase::findOrFail($id)
        $this->authorize('update-report-case', $case);

        $request->validate([
            'name' => 'string|between:1,255'
        ]);

        $case->update( $request->all() );
    }

    /**
     * Remove the specified case.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $case = ReportCase::findOrFail($id)
        $this->authorize('delete-report-case', $case);

        // NOTE: All reports and other data in the case will remain.
        $case->delete();
    }

    /**
     * List all the reports in the case.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reports($id)
    {
        $case = ReportCase::findOrFail($id)
        $this->authorize('list-reports-in-case', $case);

        return ReportCase::findOrFail($id)->reports;
    }
}
