<?php

namespace App\Http\Controllers;

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
        $request->validate([
            'content'       => 'string|between:0,4095',
            'is_anonymous'  => 'required|boolean',
        ]);

        $message = new ReportMessage( $request->all() );
        $message->author_id = Auth::user()->id;

        Report::findOrFail($reportId)->messages()->save($message);

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
        return ReportMessage::findOrFail($id);
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
        $request->validate([
            'content'       => 'string|between:0,4095',
            'is_anonymous'  => 'required|boolean',
        ]);

        ReportMessage::findOrFail($id)->update( $request->all() );
    }

    /**
     * Remove the specified report message.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        ReportMessage::findOrFail($id)->delete();
    }
}
