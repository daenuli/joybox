<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
* @OA\Tag(
*     name="Order",
*     description="API Endpoints of PickUp Book"
* )
*/

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/order",
     *      operationId="getPickUpList",
     *      tags={"Order"},
     *      summary="Get list of pickup book",
     *      description="Returns list of pickup book",
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status of pickup",
     *         required=false,
     *         example="pending",
     *         @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index(Request $request)
    {
        $status = $request->status;
        $data = Order::orderBy('pickup_date', 'asc')
                ->with(['item:id,order_id,title,cover_id,isbn,author,edition','user:id,name'])
                ->select('id', 'user_id', 'pickup_date', 'status')
                ->when($status, function ($query, $status) {
                    $query->where('status', $status);
                })
                ->get();
        return response()->json($data);
    }


    /**
     * @OA\Post(
     *      path="/api/order_status",
     *      operationId="UpdatePickUpStatus",
     *      tags={"Order"},
     *      summary="Update pickup status",
     *      security={{ "bearerAuth":{} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="order_id", type="string", description="order_id get from list pickup/order", example="1"),
     *              @OA\Property(property="status", type="borrow", description="status of pickup", example="borrow")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="The fields are required"
     *      )
     *     )
     *     )
     */

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|numeric',
            'status' => ['required', Rule::in(['borrow', 'return'])]
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        } else {
            $order = Order::find($request->order_id);
            $order->status = $request->status;
            $order->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully update status'
            ]);
        }
    }
}