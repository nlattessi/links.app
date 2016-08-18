<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        $stderr = env('APP_STDERR', false);

        if ($stderr === true) {
            $app->configureMonologUsing(function($monolog) {
                $monolog->pushHandler(
                    new Monolog\Handler\StreamHandler('php://stderr', Monolog\Logger::WARNING)
                );

                return $monolog;
            });
        }
    }
}
