<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use App\Http\Resources\OrganizationResource;
use App\Http\Resources\OrganizationCollection;

class OrganizationController extends Controller
{
    /**
     * List all existing organizations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('list-organizations');
        return new OrganizationCollection(Organization::all());
    }

    /**
     * Store a new organization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create-organizations');

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
        $organization = Organization::findOrFail($id);
        $this->authorize('view-organization', $organization);

        return new OrganizationResource($organization);
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
        $organization = Organization::findOrFail($id);
        $this->authorize('update-organization', $organization);

        $request->validate([
            'name'              => 'string|between:3,255|unique:organizations,name,' . $id,
            'street_address'    => 'string|between:3,255',
            'city'              => 'string|between:3,255',
            'zip'               => 'numeric',
        ]);

        $organization->update( $request->all() );
    }

    /**
     * Remove the specified organization.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $organization = Organization::findOrFail($id);
        $this->authorize('delete-organization', $organization);

        // NOTE: All users, reports and other data
        // in the organization will remain.
        $organization->delete();
    }
}
