<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

// /links
$app->group([
    'prefix' => '/links',
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'oauth',
], function () use ($app) {
    $app->get('/', 'LinksController@index');
    $app->get('/{id: [\d]+}', [
        'as' => 'links.show', 'uses' => 'LinksController@show',
    ]);
    $app->post('/', 'LinksController@store');
    $app->put('/{id: [\d]+}', 'LinksController@update');
    $app->delete('/{id: [\d]+}', 'LinksController@destroy');
});

// /users
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
    // $app->put('/{id: [\d]+}', 'UsersController@update');
    $app->delete('/{id: [\d]+}', 'UsersController@destroy');
});

// OAuth2
$app->post('login', function() use ($app) {
    $credentials = app()->make('request')->input("credentials");
    return $app->make('App\Auth\Proxy')->attemptLogin($credentials);
});

$app->post('refresh-token', function() use ($app) {
    return $app->make('App\Auth\Proxy')->attemptRefresh();
});

$app->post('oauth/access-token', function() use ($app) {
    return response()->json(Authorizer::issueAccessToken());
});

// OAuth2 test

$app->get('/client', function() use ($app) {
    return view()->make('client');
});

$app->group([
    'prefix' => 'api',
    'middleware' => 'oauth'
], function () use ($app) {
    $app->get('resource', function() {
        return response()->json([
            "id" => 1,
            "name" => "A resource"
        ]);
    });
});
