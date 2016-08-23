<?php

namespace App\Http\Controllers;

use App\User;
// use App\Transformers\UserTransformer;
// use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    private $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function login(Request $request)
    {
        // $this->validate($request, [
        //     'email' => 'required|email|max:255',
        //     'password' => 'required|max:255'
        // ]);

        try {
            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                $response = [
                    'message' => 'User not found.',
                    'status' => Response::HTTP_NOT_FOUND,
                ];

                return response()->json(['error' => $response], $response['status']);
            }
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(
                ['token error'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return response()->json(
            compact('token'),
            Response::HTTP_OK
        );
        // return response()->json(compact('token'));
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required|max:255',
        ], [
            'email.required' => 'The :attribute field is required.',
            'email.email' => 'The :attribute must be valid.',
            'email.max' => 'The :attribute may not be greater than :max characters.',

            'password.required' => 'The :attribute field is required.',
            'password.max' => 'The :attribute may not be greater than :max characters.',
        ]);

        try {

            if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], 500);

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent' => $e->getMessage()], 500);

        }

        return response()->json(compact('token'));
    }

    // somewhere in your controller
    public function getAuthenticatedUser()
    {
        // try {

            if (! $user = $this->jwt->parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        // } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

        //     return response()->json(['token_expired'], $e->getStatusCode());

        // } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

        //     return response()->json(['token_invalid'], $e->getStatusCode());

        // } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

        //     return response()->json(['token_absent'], $e->getStatusCode());

        // }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }
}
