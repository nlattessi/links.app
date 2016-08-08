<?php

$app->get('/', function () use ($app) {
    return $app->version();
});

// /links --- START
$app->group([
    'prefix' => '/links',
    'namespace' => 'App\Http\Controllers',
    // 'middleware' => 'oauth',
], function () use ($app) {
    $app->get('/', 'LinksController@index');
    $app->get('/{id: [\d]+}', [
        'as' => 'links.show', 'uses' => 'LinksController@show',
    ]);
    $app->post('/', 'LinksController@store');
    $app->put('/{id: [\d]+}', 'LinksController@update');
    $app->delete('/{id: [\d]+}', 'LinksController@destroy');
});
// /links --- END

// /users --- START
$app->group([
    'prefix' => '/users',
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'oauth',
], function () use ($app) {
    $app->get('/', 'UsersController@index');
    $app->get('/{id: [\d]+}', [
        'as' => 'users.show', 'uses' => 'UsersController@show',
    ]);
    $app->post('/', 'UsersController@store');
    $app->put('/{id: [\d]+}', 'UsersController@update');
    $app->delete('/{id: [\d]+}', 'UsersController@destroy');
});
// /users --- END

// JWT --- START
$app->post('/auth/login', 'AuthController@postLogin');
$app->group(['middleware' => 'auth:api'], function($app)
{
    $app->get('/test', function() {
        $user = Auth::user();
        $forceForever = false;
Auth::invalidate($forceForever);
        return response()->json([
            'message' => 'Hello World!',
            'user' => $user->email,
            'check' => Auth::check(),
            'token' => Auth::getToken()
        ]);
    });
});
// JWT --- END



// OAuth2 --- START
// $app->post('login', function() use ($app) {
//     $credentials = app()->make('request')->input("credentials");
//     return $app->make('App\Auth\Proxy')->attemptLogin($credentials);
// });

// $app->post('refresh-token', function() use ($app) {
//     return $app->make('App\Auth\Proxy')->attemptRefresh();
// });

// $app->post('oauth/access-token', function() use ($app) {
//     return response()->json(Authorizer::issueAccessToken());
// });
// OAuth2 --- END

// OAuth2 test --- START
// $app->get('/client', function() use ($app) {
//     return view()->make('client');
// });

// $app->group([
//     'prefix' => 'api',
//     'middleware' => 'auth:api'
// ], function () use ($app) {
//     $app->get('resource', function() {
//         return response()->json([
//             "id" => 1,
//             "name" => "A resource"
//         ]);
//     });
// });
// OAuth2 test --- END

// JWT --- START
// $app->post('/auth/login', 'AuthController@postLogin');
// $app->get('/auth/user', 'AuthController@getAuthenticatedUser');
// $app->group([
//     'prefix' => 'apiuser',
//     'middleware' => 'auth'
// ], function () use ($app) {
//     $app->get('resource', function() {
//         $user = Auth::user();
//         return response()->json([
//             "id" => 1,
//             "name" => "A resource",
//             "user_email" => $user->getJWTIdentifier(),
//         ]);
//     });
// });
