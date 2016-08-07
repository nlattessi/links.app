<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(__DIR__.'/../'))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    realpath(__DIR__.'/../')
);

// OAuth2 --- START
// if (!class_exists('Authorizer')) {
//     class_alias(\LucaDegasperi\OAuth2Server\Facades\Authorizer::class, 'Authorizer');
// }
// OAuth2 --- END

$app->withFacades();

$app->withEloquent();

/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

// OAuth2 --- START
// $app->singleton('cookie', function () use ($app) {
//     return $app->loadComponent('session', 'Illuminate\Cookie\CookieServiceProvider', 'cookie');
// });

// $app->bind('Illuminate\Contracts\Cookie\QueueingFactory', 'cookie');
// OAuth2 --- END

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

// $app->middleware([
//    App\Http\Middleware\ExampleMiddleware::class
// ]);

// OAuth2 --- START
// $app->middleware([
//     \LucaDegasperi\OAuth2Server\Middleware\OAuthExceptionHandlerMiddleware::class,
//     'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse'
// ]);
// OAuth2 --- END

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

// OAuth2 --- START
// $app->routeMiddleware([
//     'check-authorization-params' => \LucaDegasperi\OAuth2Server\Middleware\CheckAuthCodeRequestMiddleware::class,
//     'oauth' => \LucaDegasperi\OAuth2Server\Middleware\OAuthMiddleware::class,
//     'oauth-client' => \LucaDegasperi\OAuth2Server\Middleware\OAuthClientOwnerMiddleware::class,
//     'oauth-user' => \LucaDegasperi\OAuth2Server\Middleware\OAuthUserOwnerMiddleware::class,
// ]);
// OAuth2 --- END

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

// $app->register(App\Providers\AppServiceProvider::class);
// $app->register(App\Providers\AuthServiceProvider::class);
// $app->register(App\Providers\EventServiceProvider::class);

// Fractal
$app->register(App\Providers\FractalServiceProvider::class);

// OAuth2
// $app->register(\LucaDegasperi\OAuth2Server\Storage\FluentStorageServiceProvider::class);
// $app->register(\LucaDegasperi\OAuth2Server\OAuth2ServerServiceProvider::class);

// JWT
$app->register(\Tymon\JWTAuth\Providers\LumenServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->group(['namespace' => 'App\Http\Controllers'], function ($app) {
    require __DIR__.'/../app/Http/routes.php';
});

$stderr = env('APP_STDERR', false);
if ($stderr === true) {
    $app->configureMonologUsing(function($monolog) {
        $monolog->pushHandler(
            new Monolog\Handler\StreamHandler('php://stderr', Monolog\Logger::WARNING)
        );

        return $monolog;
    });
}

// OAuth2 --- START
// $app->configure('app');
// $app->configure('secrets');
// OAuth2 --- END

return $app;
