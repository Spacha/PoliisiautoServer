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
     * List all reports in the user's organization.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return currentOrganization()->reports;
    }

    /**
     * Store a new report to the specified case.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $caseId
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $caseId)
    {
        $request->validate([
            'description'   => 'string|between:0,4095',
            'bully_id'      => 'numeric|exists:users,id',
            'bullied_id'    => 'numeric|exists:users,id',
            'is_anonymous'  => 'required|boolean',
            //'type'          => '',
        ]);

        $report = new Report( $request->all() );
        $report->reporter_id = Auth::user()->id;

        ReportCase::findOrFail($caseId)->reports()->save($report);

        return response(null, 201);
    }

    /**
     * Get the specified report.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Report::findOrFail($id);
    }

    /**
     * Update the specified report.
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
            'is_anonymous'  => 'boolean',
            //'type'          => '',
        ]);

        Report::findOrFail($id)->update( $request->all() );
    }

    /**
     * Remove the specified report.
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
     * List all the messages in the report.
     *
     * @return \Illuminate\Http\Response
     */
    public function messages($id)
    {
        return Report::findOrFail($id)->messages;
    }

    /**
     * Update the case of the specified report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCase(Request $request, $id)
    {
        $request->validate([
            'report_case_id' => 'number|exists:report_cases,id',
        ]);

        // associate the report with the new case
        $case = ReportCase::findOrFail($request->case_id);
        $case->reports()->save( Report::findOrFail($id) );

        // if the old case was unnamed and now empty, remove it, as it is completely unnecessary
        if (empty($case->name) && $case->reports->count() == 0)
            $case->delete();
    }
}
