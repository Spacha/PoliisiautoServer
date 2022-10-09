<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;

class OrganizationController extends Controller
{
    /**
     * List all existing organizations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Organization::all();
    }

    /**
     * Store a new organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'              => 'required|string|between:3,255|unique:organizations,name',
            'street_address'    => 'required|string|between:3,255',
            'city'              => 'required|string|between:3,255',
            'zip'               => 'required|numeric',
        ]);

        $organization = Organization::create( $request->all() );

        return response(null, 201);
    }

    /**
     * Get the specified organization.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Organization::findOrFail($id);
    }

    /**
     * Update the specified organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'              => 'string|between:3,255|unique:organizations,name,' . $id,
            'street_address'    => 'string|between:3,255',
            'city'              => 'string|between:3,255',
            'zip'               => 'numeric',
        ]);

        Organization::findOrFail($id)->update( $request->all() );
    }

    /**
     * Remove the specified organization.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // NOTE: All users, reports and other data
        // in the organization will remain.
        Organization::findOrFail($id)->delete();
    }
}
