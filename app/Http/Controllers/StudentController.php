<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Http\Controllers;

use App\Http\Resources\StudentCollection;
use App\Http\Resources\StudentResource;
use App\Http\Resources\ReportCollection;
use Illuminate\Http\Request;
use App\Models\Student;
use Auth;

class StudentController extends Controller
{
    /**
     * List all students in the user's organization.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $organization = currentOrganization();
        $this->authorize('list-students', $organization);

        // TODO: Add an endpoint for getting user names and ids only that
        //       is accessible by everyone in the organization!
        return new StudentCollection($organization->students);
    }

    /**
     * Store a new student to the user's organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO
        return response()->json("Storing students is not implemented.", 501);
    }

    /**
     * Get the specified student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $student = Student::findOrFail($id);
        $this->authorize('show-student', $student);

        return new StudentResource($student);
    }

    /**
     * Update the specified student.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);
        $this->authorize('update-student', $student);

        $request->validate([
            'first_name'    => 'string|min:1|max:127',
            'last_name'     => 'string|min:1|max:127',
            'email'         => 'string|unique:users,email|max:127',
            //'password'      => 'string|confirmed|min:8|max:127',
            'phone'         => 'string|min:1|max:127',
        ]);

        $student->update( $request->except(['password']) );
    }

    /**
     * Remove the specified student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $this->authorize('delete-student', $student);

        // NOTE: All reports and other data belonging to the user will remain.
        $student->delete();
    }

    /**
     * List all the reports belonging to the student.
     *
     * @return \Illuminate\Http\Response
     */
    public function reports($id)
    {
        $student = Student::findOrFail($id);
        $this->authorize('list-student-reports', $student);

        // only the student themself or a teacher can view
        // if (Auth::user()->isStudent() && Auth::user()->id != $id)
        //     return response()->json("Unauthorized.", 401);

        return new ReportCollection($student->reports);
    }

    /**
     * List all the reports the student is involved in.
     *
     * @return \Illuminate\Http\Response
     */
    public function involvedReports($id)
    {
        $student = Student::findOrFail($id);
        $this->authorize('list-student-involved-reports', $student);

        return collect([
            'bullied'   => new ReportCollection($student->bulliedReports),
            'bully'     => new ReportCollection($student->bullyReports)
        ]);
    }
}
