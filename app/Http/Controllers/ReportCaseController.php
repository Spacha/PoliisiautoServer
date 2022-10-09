<?php

namespace App\Http\Controllers;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use App\Models\ReportCase;
use Auth;

class ReportCaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Auth::user()->organization->cases;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|between:1,255'
        ]);

        // store the new case under the user's organization
        Auth::user()->organization->cases()->save(
            new ReportCase( $request->all() )
        );

        return response(null, 201);
    }

    /**
     * Display the specified resource.
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|between:1,255'
        ]);

        ReportCase::findOrFail($id)->update( $request->all() );
    }

    /**
     * Remove the specified resource from storage.
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
     * Get a list of the reports in the case.
     *
     * @return \Illuminate\Http\Response
     */
    public function reports($id)
    {
        return ReportCase::findOrFail($id)->reports;
    }
}
