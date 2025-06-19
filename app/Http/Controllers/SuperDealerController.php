<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;

class SuperDealerController extends Controller
{



    /**
     * Get all bikers under a specific super dealer.
     *
     * @OA\Get(
     *     path="/bikers",
     *     summary="List bikers of the authenticated super dealer",
     *     tags={"Super Dealer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of bikers of the logged in super dealer",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="List of bikers of the logged in super dealer"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=201),
     *                         @OA\Property(property="name", type="string", example="Biker Alex"),
     *                         @OA\Property(property="email", type="string", example="biker@delivery.com"),
     *                         @OA\Property(property="role", type="string", example="biker"),
     *                         @OA\Property(property="super_dealer_id", type="integer", example=101),
     *                         @OA\Property(
     *                             property="vehicle",
     *                             type="object",
     *                                      @OA\Property(property="id", type="integer", example=1),
     *                                      @OA\Property(property="biker_id", type="integer", example=1),
     *                                      @OA\Property(property="plate_number", type="string", example="T123ABC"),
     *                                      @OA\Property(property="type", type="string", example="Boxer"),
     *                                      @OA\Property(property="brand", type="integer", example="Toyota")           
     *                        )
     *                  )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Super dealer does not exist",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Super dealer does not exist.")
     *         )
     *     )
     * )
     */
    public function bikers()
    {
        $authId = auth()->user()->id;

        $bikers = User::with('vehicle')->
            where('super_dealer_id', $authId)->
            where('role', 'biker')->get();

        return ResponseHelper::success('List of all bikers', $bikers);
    }
}
