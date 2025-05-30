<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Auth\PlainTextUserProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        DB::listen(function ($query) {
            Log::info('SQL Query: ' . $query->sql, $query->bindings);
        });
        Auth::provider('plaintext', function ($app, array $config) {
            return new PlainTextUserProvider($config['model']);
        });
        
    }
}
