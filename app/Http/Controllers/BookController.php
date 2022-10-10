<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\OpenLibrary;

/**
* @OA\Tag(
*     name="Book",
*     description="API Endpoints of Book"
* )
*/

class BookController extends Controller
{
    public $open_library;

    public function __construct(OpenLibrary $library)
    {
        $this->open_library = $library;
    }

    /**
     * @OA\Get(
     *      path="/api/books",
     *      operationId="getBookList",
     *      tags={"Book"},
     *      summary="Get list of book",
     *      description="Returns list of book",
     *      security={{ "bearerAuth":{} }},
     *      @OA\Parameter(
     *         name="title",
     *         in="query",
     *         description="Title of book",
     *         required=false,
     *         example="Software Engineering",
     *         @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *         name="author",
     *         in="query",
     *         description="Author of book",
     *         required=false,
     *         @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *         name="cover_id",
     *         in="query",
     *         description="Cover id of book",
     *         required=false,
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
        $content = $this->open_library->get_book_json();

        $book_list = collect(json_decode($content->data));

        $result = $book_list->when($request->title, function ($collection, $value) use ($request) {
            return $collection->filter(function ($q) use ($request) {
                return Str::startsWith(strtolower($q->title), strtolower($request->title));
            });
        })
        ->when($request->author, function ($collection, $value) use ($request) {
            return $collection->filter(function ($q) use ($request) {
                return in_array($request->author, array_column($q->authors, 'name'));
            });
        })
        ->when($request->cover_id, function ($collection, $value) use ($request) {
            return $collection->where('cover_id', $request->cover_id);
        });

        $data = $result->map(function ($qry) {
            return [
                'cover_id' => $qry->cover_id,
                'title' => $qry->title,
                'isbn' => isset($qry->availability) ? $qry->availability->isbn : '',
                'edition' => $qry->edition_count, 
                'authors' => isset($qry->authors) ? collect($qry->authors)->map(function ($ath) {
                    return ['name' => $ath->name];
                }) : '',
            ];
        });

        return response()->json($data->values()->all());
    }

    /**
     * @OA\Post(
     *      path="/api/books",
     *      operationId="PickupBook",
     *      tags={"Book"},
     *      summary="Borrow and pick up book",
     *      description="Returns list of book",
     *      security={{ "bearerAuth":{} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="cover_id", type="array",
     *                  @OA\Items(
     *                      type="number",
     *                      example="3956527"
     *                  ),
     *              ),
     *              @OA\Property(property="pick_up_date", type="string", example="2022-10-12 08:30")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *      ),
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
     */

    public function order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cover_id' => 'required|array',
            'cover_id.*' => 'required|numeric',
            'pick_up_date' => 'required|date_format:Y-m-d H:i',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        } else {
            $pick_up = Carbon::parse($request->pick_up_date);

            $order = new Order;
            $order->user_id = auth()->user()->id;
            $order->pickup_date = $pick_up;
            $order->save();
            
            foreach ($request->cover_id as $key => $value) {
                $item = $this->open_library->book_by_id($value)[0];
                $detail = new OrderDetail;
                $detail->order_id = $order->id;
                $detail->cover_id = $value;
                $detail->isbn = $item['isbn'];
                $detail->title = $item['title'];
                $detail->author = implode(", ", $item['authors']);
                $detail->edition = $item['edition'];
                $detail->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Thank you for your order, please pick up your book at '.$pick_up->format('d F Y H:i')
            ]);
        }
    }
}