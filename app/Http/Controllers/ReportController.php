<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

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
        $organization = currentOrganization();
        $this->authorize('list-reports', $organization);

        return new ReportCollection($organization->reports);
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
        $case = ReportCase::findOrFail($caseId);
        $this->authorize('create-report', $case);

        $request->validate([
            'description'   => 'string|between:0,2048',
            'bully_id'      => 'nullable|numeric|exists:users,id',
            'bullied_id'    => 'nullable|numeric|exists:users,id',
            'handler_id'    => 'nullable|numeric|exists:users,id',
            'is_anonymous'  => 'required|boolean',
            //'type'          => '',
        ]);

        // TODO: Validate that the handler belongs to the user's organization

        $report = new Report( $request->all() );
        $report->reporter_id = Auth::user()->id;

        $case->reports()->save($report);

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
        $report = Report::findOrFail($id);
        $this->authorize('show-report', $report);

        return new ReportResource($report);
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
        $report = Report::findOrFail($id);
        $this->authorize('update-report', $report);

        $request->validate([
            'description'   => 'string|between:0,2048',
            'bully_id'      => 'nullable|numeric|exists:users,id',
            'bullied_id'    => 'nullable|numeric|exists:users,id',
            'handler_id'    => 'nullable|numeric|exists:users,id',
            'is_anonymous'  => 'nullable|boolean',
            //'type'          => '',
        ]);

        $report->update( $request->all() );
    }

    /**
     * Remove the specified report.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $this->authorize('delete-report', $report);

        // NOTE: All report messages and other data in the report will remain.
        $report->delete();
    }

    /**
     * List all the messages in the report.
     *
     * @return \Illuminate\Http\Response
     */
    public function messages($id)
    {
        $report = Report::findOrFail($id);
        $this->authorize('list-report-messages', $report);

        return new ReportMessageCollection($report->messages);
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
        $report = Report::findOrFail($id);
        $this->authorize('update-report-case', $report);

        $request->validate([
            'report_case_id' => 'numeric|exists:report_cases,id',
        ]);

        // associate the report with the new case
        $case = ReportCase::findOrFail($request->case_id);
        $case->reports()->save($report);

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
        $organization = currentOrganization();
        $this->authorize('create-report-and-case', $organization);

        // store a new, empty case under the user's organization
        $case = $organization->cases()->save(
            new ReportCase()
        );

        // and store normally under it
        return $this->store($request, $case->id);
    }
}
