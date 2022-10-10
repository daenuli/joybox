<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints of Authentication"
 * )
 */

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 422);
        } else {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role = 'customer';
            $user->save();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully registered, now you are able to sign in'
            ]);
        }
    }

    /**
     * @OA\Post(
     *      path="/api/login",
     *      summary="Sign in",
     *      tags={"Authentication"},
     *      @OA\RequestBody(
     *         required=true,
     *         description="User auth",
     *         @OA\JsonContent(
     *         required={"email", "password"},
     *              @OA\Property(property="email", type="string", format="email", example="customer@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="111111"),
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Login Successful",
     *       ),
     *      @OA\Response(
     *          response=422,
     *          description="Wrong credentials response",
     *              @OA\JsonContent(
     *                  @OA\Property(property="status", type="string", example="error"),
     *                  @OA\Property(property="message", type="string", example="Incorrect Details. Please try again")
     *              )
     *       )
     *     )
     * )
     */

    public function login(Request $request)
    {
        $data = $this->validate($request, [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);

        if (!$token = auth()->attempt($data)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Incorrect Details. Please try again'
            ], 422);
        }

        return $this->respondWithToken($token);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'You have been successfully logged out!'
        ]);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

}