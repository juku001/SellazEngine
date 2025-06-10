<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function unauthorized()
    {
        return response()->json([
            'status' => false,
            'message' => 'Unauthrorized',
            'code' => 401,
        ], 401);
    }


    public function authorized()
    {
        return response()->json([
            'status' => true,
            'message' => 'Authenticated'
        ]);
    }
}
