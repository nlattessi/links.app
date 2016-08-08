<?php

namespace App\Http\Controllers;

use App\User;
// use App\Transformers\UserTransformer;
// use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
// use Illuminate\Http\Response;
use Tymon\JWTAuth\JWTAuth;

class AuthController extends Controller
{
    private $jwt;

    public function __construct(JWTAuth $jwt)
    {
        $this->jwt = $jwt;
    }

    public function postLogin(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required|email|max:255',
            'password' => 'required',
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
