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

$app->get('/key', function () use ($app) {
    return str_random(32);
});

// DEPRECATED
/*$app->group([
    'prefix' => '/links',
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'auth:api',
    // 'middleware' => 'jwt.auth',
], function () use ($app, $uuidRegex) {
    $app->get('/', 'LinksController@index');
    $app->get("/{uuid: ${uuidRegex}}", [
        'as' => 'links.show',
        'uses' => 'LinksController@show',
    ]);
    $app->post('/', 'LinksController@store');
    $app->put("/{uuid: ${uuidRegex}}", 'LinksController@update');
    $app->delete("/{uuid: ${uuidRegex}}", 'LinksController@destroy');
});*/

// DEPRECATED
/*$app->group([
    'prefix' => '/categories',
    'namespace' => 'App\Http\Controllers',
    'middleware' => 'auth:api',
    // 'middleware' => 'jwt.auth',
], function () use ($app, $uuidRegex) {
    $app->get('/', 'CategoriesController@index');
    $app->get("/{uuid: ${uuidRegex}}", [
        'as' => 'categories.show',
        'uses' => 'CategoriesController@show',
    ]);
    $app->post('/', 'CategoriesController@store');
    $app->put("/{uuid: ${uuidRegex}}", 'CategoriesController@update');
    $app->delete("/{uuid: ${uuidRegex}}", 'CategoriesController@destroy');
});*/

$app->group([
    'prefix' => '/auth',
    'namespace' => 'App\Http\Controllers',
], function () use ($app) {
    $app->post('/login', 'AuthController@login');
    $app->post('/register', 'AuthController@register');
    $app->get('/refresh', ['middleware' => 'jwt.refresh', function () {}]);

    $app->get('/facebook', 'AuthController@facebook');
});

$app->group([
    'prefix' => '/user/links',
    'namespace' => 'App\Http\Controllers',
    'middleware' => [
        'testjwt',
        // 'auth:api',
        'jwt.auth',
    ],
], function () use ($app, $uuidRegex) {
    $app->get('/', 'UserLinksController@index');
    $app->get("/{uuid: ${uuidRegex}}", [
        'as' => 'UserLinks.show',
        'uses' => 'UserLinksController@show',
    ]);
    $app->post('/', 'UserLinksController@store');
    $app->patch("/{uuid: ${uuidRegex}}", 'UserLinksController@update');
    $app->delete("/{uuid: ${uuidRegex}}", 'UserLinksController@destroy');
});

$app->group([
    'prefix' => '/user/categories',
    'namespace' => 'App\Http\Controllers',
    'middleware' => [
        'testjwt',
        // 'auth:api',
        'jwt.auth',
    ],
], function () use ($app, $uuidRegex) {
    $app->get('/', 'UserCategoriesController@index');
    $app->get("/{uuid: ${uuidRegex}}", [
        'as' => 'userCategories.show',
        'uses' => 'UserCategoriesController@show',
    ]);
    $app->post('/', 'UserCategoriesController@store');
    $app->patch("/{uuid: ${uuidRegex}}", 'UserCategoriesController@update');
    $app->delete("/{uuid: ${uuidRegex}}", 'UserCategoriesController@destroy');
});
