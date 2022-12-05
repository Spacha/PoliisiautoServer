<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ReportCollection;
use App\Http\Resources\ReportMessageCollection;
use App\Http\Resources\ReportResource;
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
        // TODO: Use guards/policies!

        // only teachers can view
        if (!Auth::user()->is_teacher)
            return response()->json("Unauthorized.", 401);

        return new ReportCollection(currentOrganization()->reports);
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
            'description'   => 'string|between:0,2048',
            'bully_id'      => 'nullable|numeric|exists:users,id',
            'bullied_id'    => 'nullable|numeric|exists:users,id',
            'handler_id'    => 'nullable|numeric|exists:users,id',
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
        return new ReportResource(Report::findOrFail($id));
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
            'description'   => 'string|between:0,2048',
            'bully_id'      => 'nullable|numeric|exists:users,id',
            'bullied_id'    => 'nullable|numeric|exists:users,id',
            'handler_id'    => 'nullable|numeric|exists:users,id',
            'is_anonymous'  => 'nullable|boolean',
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
        return new ReportMessageCollection(Report::findOrFail($id)->messages);
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
            'report_case_id' => 'numeric|exists:report_cases,id',
        ]);

        // associate the report with the new case
        $case = ReportCase::findOrFail($request->case_id);
        $case->reports()->save( Report::findOrFail($id) );

        // if the old case was unnamed and now empty, remove it, as it is completely unnecessary
        if (empty($case->name) && $case->reports->count() == 0)
            $case->delete();
    }

    /**
     * Store a new report to a new case.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeToNewCase(Request $request)
    {
        \Log::debug($request->all());
        // store a new, empty case under the user's organization
        $case = currentOrganization()->cases()->save(
            new ReportCase()
        );

        // and store normally under it
        return $this->store($request, $case->id);
    }
}
