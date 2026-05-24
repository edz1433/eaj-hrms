<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class GlobalViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('*', function ($view) {
            $guard = guard();

            // Check if a user is authenticated
            if ($guard && auth()->guard($guard)->check()) {
                $user = auth()->guard($guard)->user();
                $role = $user->role;
                $userid = $user->id;

                // Share variables to all views
                $view->with([
                    'role' => $role,
                    'guard' => $guard,
                    'userid' => $userid,
                ]);
            }
        });
    }

    public function register()
    {
        //
    }
}
