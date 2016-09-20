<?php

namespace App\Http\Controllers;

use Auth;
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
            // 'email' => 'required|email|exists:users',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $response = $this->createResponse($request, Response::HTTP_OK);

        return response()->json(
            $response['body'],
            $response['code']
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

        $response = $this->createResponse($request, Response::HTTP_CREATED);

        return response()->json(
            $response['body'],
            $response['code']
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

    public function facebook(Request $request)
    {
        $fb = new \Facebook\Facebook([
            'app_id' => '1120178214727062',
            'app_secret' => 'c894cd4b8d5979cef4821ccecaa07a9c',
            'default_graph_version' => 'v2.7',
        ]);

        $accessToken = $request->input('accessToken');

        try {
            // Returns a `Facebook\FacebookResponse` object
            $data = $fb->get('/me?fields=id,name,email', $accessToken);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            $data = $e->getMessage();
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            $data = $e->getMessage();
        }

        $fbUser = $data->getGraphUser();

        $user = \App\User::firstOrCreate([
            'email' => $fbUser->getEmail()
        ]);

        if ($user) {
            if (! $token = Auth::tokenById($user->id)) {
                return response()->json([
                    [
                        'error' => [
                            'message' => 'User not found.',
                            'status' => Response::HTTP_NOT_FOUND,
                        ],
                    ],
                    Response::HTTP_NOT_FOUND
                ]);
            }

            return response()->json(
                compact('token'),
                Response::HTTP_OK
            );
        }

        return response()->json([
            [
                'error' => [
                    'message' => 'Error Log in with Facebook.',
                    'status' => Response::HTTP_UNAUTHORIZED,
                ],
            ],
            Response::HTTP_UNAUTHORIZED
        ]);
    }

    public function google(Request $request)
    {
        $client_id = '367309930083-53pu80b66ua3jro4fdu0tv8cvsqceqhs.apps.googleusercontent.com';
        $client_secret = 'nAkaHs7860Nbzsr2zBTmLUek';

        $client = new \Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);

        $accessToken = $request->input('accessToken');

        $ticket = $client->verifyIdToken($accessToken);

        if ($ticket) {

            $user = \App\User::firstOrCreate([
                'email' => $ticket['email']
            ]);

            if ($user) {
                if (! $token = Auth::tokenById($user->id)) {
                    return response()->json([
                        [
                            'error' => [
                                'message' => 'User not found.',
                                'status' => Response::HTTP_NOT_FOUND,
                            ],
                        ],
                        Response::HTTP_NOT_FOUND
                    ]);
                }

                return response()->json(
                    compact('token'),
                    Response::HTTP_OK
                );
            }
        }

        return response()->json([
            [
                'error' => [
                    'message' => 'Error Log in with Google.',
                    'status' => Response::HTTP_UNAUTHORIZED,
                ],
            ],
            Response::HTTP_UNAUTHORIZED
        ]);
    }

    private function createResponse(Request $request, $statusCode)
    {
        if (! $token = $this->jwt->attempt($request->only('email', 'password'))) {
            return [
                'body' => [
                    'error' => [
                        'message' => 'User not found.',
                        'status' => Response::HTTP_NOT_FOUND,
                    ],
                ],
                'code' => Response::HTTP_NOT_FOUND
            ];
        }

        return [
            'body' => compact('token'),
            'code' => $statusCode,
        ];
    }
}
