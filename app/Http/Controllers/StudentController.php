<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    /**
     * List all students in the user's organization.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return currentOrganization()->students;
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
        return Student::findOrFail($id);
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
        $request->validate([
            'first_name'    => 'string|min:1|max:127',
            'last_name'     => 'string|min:1|max:127',
            'email'         => 'string|unique:users,email|max:127',
            //'password'      => 'string|confirmed|min:8|max:127',
            'phone'         => 'string|min:1|max:127',
        ]);

        Student::findOrFail($id)->update( $request->except(['password']) );
    }

    /**
     * Remove the specified student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // NOTE: All reports and other data belonging to the user will remain.
        Student::findOrFail($id)->delete();
    }

    /**
     * List all the reports belonging to the student.
     *
     * @return \Illuminate\Http\Response
     */
    public function reports($id)
    {
        return Student::findOrFail($id)->reports;
    }

    /**
     * List all the reports the student is involved in.
     *
     * @return \Illuminate\Http\Response
     */
    public function involvedReports($id)
    {
        $student = Student::findOrFail($id);

        return collect([
            'bullied'   => $student->bulliedReports,
            'bully'     => $student->bullyReports
        ]);
    }
}
