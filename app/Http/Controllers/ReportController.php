<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Models\ReportCase;
use App\Models\Report;
use Auth;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Auth::user()->organization->reports;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $caseId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $caseId)
    {
        $request->validate([
            'description'   => 'string|between:0,4095',
            'bully_id'      => 'number|exists:users',
            'bullied_id'    => 'number|exists:users',
            'is_anonymous'  => 'required|boolean',
            //'type'          => '',
        ]);

        $report = new Report( $request->all() );
        $report->reporter_id = Auth::user()->id;

        ReportCase::findOrFail($caseId)->reports()->save($report);

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
        return Report::findOrFail($id);
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
            'description'   => 'string|between:0,4095',
            'bully_id'      => 'number|exists:users',
            'bullied_id'    => 'number|exists:users',
            'is_anonymous'  => 'required|boolean',
            //'type'          => '',
        ]);

        Report::findOrFail($id)->update( $request->all() );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // NOTE: All report messages and other data in the report will remain.
        Report::findOrFail($id)->delete();
    }

    /**
     * Get a listing of report messages in the report.
     *
     * @return \Illuminate\Http\Response
     */
    public function messages($id)
    {
        return Report::findOrFail($id)->reportMessages;
    }
}
