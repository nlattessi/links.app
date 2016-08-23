<?php

// [0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}
$uuidRegex = '[0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f]'
    . '-[0-9a-f][0-9a-f][0-9a-f][0-9a-f]'
    . '-4[0-9a-f][0-9a-f][0-9a-f]'
    . '-[89ab][0-9a-f][0-9a-f][0-9a-f]'
    . '-[0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f][0-9a-f]';

$app->get('/', function () use ($app) {
    return $app->version();
});

// /links --- START
$app->group([
    'prefix' => '/links',
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'auth:api',
], function () use ($app, $uuidRegex) {
    $app->get('/', 'LinksController@index');
    $app->get("/{uuid: ${uuidRegex}}", [
        'as' => 'links.show',
        'uses' => 'LinksController@show',
    ]);
    $app->post('/', 'LinksController@store');
    $app->put("/{uuid: ${uuidRegex}}", 'LinksController@update');
    $app->delete("/{uuid: ${uuidRegex}}", 'LinksController@destroy');
});
// /links --- END

// /categories --- START
$app->group([
    'prefix' => '/categories',
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'auth:api',
], function (\Laravel\Lumen\Application $app) use ($uuidRegex) {
    $app->get('/', 'CategoriesController@index');
    $app->get("/{uuid: ${uuidRegex}}", [
        'as' => 'categories.show',
        'uses' => 'CategoriesController@show',
    ]);
    $app->post('/', 'CategoriesController@store');
    $app->put("/{uuid: ${uuidRegex}}", 'CategoriesController@update');
    $app->delete("/{uuid: ${uuidRegex}}", 'CategoriesController@destroy');
});
// /categories --- END

// /auth --- START
$app->group([
    'prefix' => '/auth',
    'namespace' => 'App\Http\Controllers',
], function () use ($app) {
    $app->post('/login', 'AuthController@login');
    $app->post('/register', 'AuthController@register');
});

// /auth --- END

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
// $app->post('/auth/login', 'AuthController@postLogin');
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
