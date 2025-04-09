<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Auth;

class PlainTextUserProvider implements UserProvider
{
    /**
     * Register services.
     */
    protected $model;

    public function __construct($model)
    {
        $this->model = $model;
    }
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        logger('Custom provider being used');
        return $user->getAuthPassword() === $credentials['u_passwd'];
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        // Assuming you have a User model that interacts with your database
        return \App\Models\User::where('emp_code', $credentials['employee_code'])->first();
    }

    public function retrieveById($identifier)
    {
        return $this->createModel()->where('emp_code', $identifier)->first();
    }

    public function retrieveByToken($identifier, $token) { return null; }
    public function updateRememberToken(Authenticatable $user, $token) {}

    public function createModel()
    {
        return new $this->model;
    }

    function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false){
        
    }
}
