<?php

namespace App\Http\Controllers;

use App\User;
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
        $this->validate($request, [
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
            $response = [
                'message' => 'User not found.',
                'status' => Response::HTTP_NOT_FOUND,
            ];

            return response()->json(
                ['error' => $response],
                $response['status']
            );
        }

        return response()->json(
            compact('token'),
            Response::HTTP_OK
        );
    }

    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6',
        ]);

        User::create([
            'email' => $request->input('email'),
            'password' => app('hash')->make($request->input('password')),
        ]);

        if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
            $response = [
                'message' => 'User not found.',
                'status' => Response::HTTP_NOT_FOUND,
            ];

            return response()->json(['error' => $response], $response['status']);
        }

        return response()->json(
            compact('token'),
            Response::HTTP_CREATED
        );
    }

    // for testing purposes
    public function getAuthenticatedUser()
    {
        if (! $user = $this->jwt->parseToken()->authenticate()) {
            return response()->json(
                ['user_not_found'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json(
            compact('user'),
            Response::HTTP_OK
        );
    }
}
