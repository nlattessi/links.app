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

$app->group([
    'prefix' => '/links',
    'namespace' => 'App\Http\Controllers'
], function () use ($app) {
    $app->get('/', 'LinksController@index');
    $app->get('/{id: [\d]+}', [
        'as' => 'links.show', 'uses' => 'LinksController@show',
    ]);
    $app->post('/', 'LinksController@store');
    $app->put('/{id: [\d]+}', 'LinksController@update');
    $app->delete('/{id: [\d]+}', 'LinksController@destroy');
});
