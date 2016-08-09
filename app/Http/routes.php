<?php

$app->get('/', function () use ($app) {
    return $app->version();
});

// /links --- START
$app->group([
    'prefix' => '/links',
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'auth:api',
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
    'middleware' => 'auth:api',
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
// JWT-TEST
$app->group([
    'prefix' => '/jwt',
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'auth:api',
], function () use ($app) {
    $app->get('/test', function() {
        return response()->json([
            'message' => 'Hello World!',
        ]);
    });
    $app->get('/user', 'AuthController@getAuthenticatedUser');
});
// JWT --- END
