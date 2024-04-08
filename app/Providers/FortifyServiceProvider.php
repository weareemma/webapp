<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use App\Actions\Fortify\UpdateUserProfileInformation;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->instance(LoginResponse::class, new class implements LoginResponse {
        public function toResponse($request)
        {
          if ($request->no_redirect && $request->route) {
            return redirect(route($request->route))->with(['restore_state' => true]);
          }
          return redirect()->intended((RouteServiceProvider::HOME));
        }
      });

      $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
        public function toResponse($request)
        {
          if ($request->no_redirect && $request->route) {
            return redirect(route($request->route))->with(['restore_state' => true]);
          }
          return redirect()->intended((RouteServiceProvider::HOME));
        }
      });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::authenticateUsing(function(Request $request) {
          $user = User::query()
            ->where('email', $request->email)
            ->where('active', 1)
            ->first();
 
          if ($user &&
              Hash::check($request->password, $user->password)) {
              return $user;
          }
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
