<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Http\Controllers;

use App\Http\Resources\TeacherCollection;
use App\Http\Resources\TeacherResource;
use App\Http\Resources\ReportCollection;
use Illuminate\Http\Request;
use App\Models\Teacher;

class TeacherController extends Controller
{
    /**
     * List all teachers in the user's organization.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // TODO: Add an endpoint for getting user names and ids only that
        //       is accessible by everyone in the organization!
        return new TeacherCollection(currentOrganization()->teachers);
    }

    /**
     * Store a new teacher to the user's organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO
        return response()->json("Storing teachers is not implemented.", 501);
    }

    /**
     * Get the specified teacher.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return new TeacherResource(Teacher::findOrFail($id));
    }

    /**
     * Update the specified teacher.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name'    => 'string|min:1|max:127',
            'last_name'     => 'string|min:1|max:127',
            'email'         => 'string|unique:users,email|max:127',
            //'password'      => 'string|confirmed|min:8|max:127',
            'phone'         => 'string|min:1|max:127',
        ]);

        Teacher::findOrFail($id)->update( $request->except(['password']) );
    }

    /**
     * Remove the specified teacher.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // NOTE: All reports and other data belonging to the user will remain.
        Teacher::findOrFail($id)->delete();
    }

    /**
     * List all the reports belonging to the specified teacher.
     *
     * @return \Illuminate\Http\Response
     */
    public function reports($id)
    {
        return new ReportCollection(Teacher::findOrFail($id)->reports);
    }

    /**
     * List all the reports in which the specified teacher is the handler.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assignedReports($id)
    {
        return new ReportCollection(Teacher::findOrFail($id)->assignedReports);
    }
}
