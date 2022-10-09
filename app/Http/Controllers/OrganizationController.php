<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;

class OrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Organization::all();
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
            'name'              => 'required|string|between:3,255|unique:organizations,name',
            'street_address'    => 'required|string|between:3,255',
            'city'              => 'required|string|between:3,255',
            'zip'               => 'required|numeric',
        ]);

        $organization = Organization::create( $request->all() );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Organization::findOrFail($id);
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
            'name'              => 'required|string|between:3,255|unique:organizations,name,' . $id,
            'street_address'    => 'required|string|between:3,255',
            'city'              => 'required|string|between:3,255',
            'zip'               => 'required|numeric',
        ]);

        Organization::findOrFail($id)->update( $request->all() );
    }

    /**
     * Remove the specified resource from storage.
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
