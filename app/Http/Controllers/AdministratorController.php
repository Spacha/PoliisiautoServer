<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Administrator;

class AdministratorController extends Controller
{
    /**
     * List all administrators in the user's organization.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return currentOrganization()->administrators;
    }

    /**
     * Store a new adminstrator to the user's organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // TODO
        return response()->json("Storing administrators is not implemented.", 501);
    }

    /**
     * Get the specified administrator.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Administrator::findOrFail($id);
    }

    /**
     * Update the specified administrator.
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

        Administrator::findOrFail($id)->update( $request->except(['password']) );
    }

    /**
     * Remove the specified administrator.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // NOTE: All reports and other data belonging to the user will remain.
        Administrator::findOrFail($id)->delete();
    }
}
