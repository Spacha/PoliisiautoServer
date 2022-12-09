<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Http\Controllers;

use App\Http\Resources\ReportMessageResource;
use Illuminate\Http\Request;
use App\Models\ReportMessage;
use App\Models\Report;
use Auth;

class ReportMessageController extends Controller
{
    /**
     * Store a new message to the specified report.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $reportId)
    {
        $report = Report::findOrFail($reportId);
        $this->authorize('create-report-message', $report);

        $request->validate([
            'content'       => 'string|between:0,4095',
            'is_anonymous'  => 'required|boolean',
        ]);

        $message = new ReportMessage( $request->all() );
        $message->author_id = Auth::user()->id;

        $report->messages()->save($message);

        return response(null, 201);
    }

    /**
     * Get the specified report message.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $reportMessage = ReportMessage::findOrFail($id);
        $this->authorize('show-report-message', $reportMessage);

        return new ReportMessageResource($reportMessage);
    }

    /**
     * Update the specified report message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $reportMessage = ReportMessage::findOrFail($id);
        $this->authorize('update-report-message', $reportMessage);

        $request->validate([
            'content'       => 'string|between:0,4095',
            'is_anonymous'  => 'required|boolean',
        ]);

        $reportMessage->update( $request->all() );
    }

    /**
     * Remove the specified report message.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $reportMessage = ReportMessage::findOrFail($id);
        $this->authorize('delete-report-message', $reportMessage);

        $reportMessage->delete();
    }
}
