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
        return currentOrganization()->cases;
    }

    /**
     * Store a new case to the user's organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
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
        return ReportCase::findOrFail($id);
        // students can only show their own cases
        //if (Auth::user()->isStudent() && $case->reporter != Auth::user()->id)
        //    throw new AuthenticationException();
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
        $request->validate([
            'name' => 'string|between:1,255'
        ]);

        ReportCase::findOrFail($id)->update( $request->all() );
    }

    /**
     * Remove the specified case.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // NOTE: All reports and other data in the case will remain.
        ReportCase::findOrFail($id)->delete();
    }

    /**
     * List all the reports in the case.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function reports($id)
    {
        return ReportCase::findOrFail($id)->reports;
    }
}
