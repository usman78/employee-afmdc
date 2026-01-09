<?php

namespace App\Providers;

use Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Auth\PlainTextUserProvider;
use Illuminate\Support\Facades\View;

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

        View::composer(['layouts.app', 'jobs.layouts.app'], function ($view) {
            if(!Auth::check()){
                return;
            }
            $user = Cache::remember('user_'.Auth::id(), 60, function() {
                return Auth::user()->only(['emp_code', 'name']);
            });
            $emp_code = $user['emp_code'];
            $view->with(['emp_code' => $emp_code, 'user_name' => $user['name']]);
        });
        
    }
}
