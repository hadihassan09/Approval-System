<?php

namespace App\Providers;

use App\Models\Data;
use App\Models\Token;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        Gate::define('data-owner', function (User $user, Data $data) {
            return $user->id === $data->user_id;
        });

        Gate::define('system-admin', function (User $user) {
            return $user->isAdmin == true;
        });
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            if ($request->header('Authorization')) {
                $token = Token::where('token', $request->header('Authorization'))->first();
                if($token)
                    return $token->user;
                return null;
            }
        });
    }
}
