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



    /**
     * @OA\Get(
     *   tags={"Authentication"},
     *   path="/is_auth",
     *   summary="This API is for checking if the user is authenticated.",
     *   @OA\Response(
     *     response=200, 
     *     description="OK",
     *      @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Authenticated"),
     *             @OA\Property(property="code", type="integer", example=200),
     *     )
     *   )
     * )
     */
    public function authorized()
    {
        return response()->json([
            'status' => true,
            'message' => 'Authenticated',
            'code' => 200
        ]);
    }
}
