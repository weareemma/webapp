<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
  public function redirectToProvider($provider) {
    if (request()->get('no_redirect')) {
      session([
          'no_redirect' => true,
          'route' => request()->get('route')
      ]);
    }
    $url = Socialite::driver($provider)->redirect()->getTargetUrl();
    return Inertia::location($url);
  }

  public function handleProviderCallback($provider) {
    
    try {
      $providerUser = Socialite::driver($provider)->user();
      $no_redirect = session('no_redirect');
      $route = session('route');
      DB::beginTransaction();
      $user = User::where("{$provider}_id", '=', $providerUser->id)->first();
      if (! $user) {
        $user = User::where("email", '=', $providerUser->email)->first();
        if (! $user) {
          $user = User::create([
              'name' => $provider == 'google' ? $providerUser->user['given_name'] : $providerUser->name,
              'surname' => $provider == 'google' ? $providerUser->user['family_name'] : '',
              'email' => $providerUser->email,
              "{$provider}_id" => $providerUser->id
          ]);
          $user->makeCustomer();
        } else {
          $user->update([
            'name' => $provider == 'google' ? $providerUser->user['given_name'] : $providerUser->name,
            'surname' => $provider == 'google' ? $providerUser->user['family_name'] : '',
              "{$provider}_id" => $providerUser->id
          ]);
        }
      }
      DB::commit();
      Auth::login($user);
//      if ($no_redirect) {
//        return Redirect::route($route)->with(['restore_state' => true]);
//      }
//      return Redirect::route('home');

        return \redirect()->intended();

    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('Socialite exception: ' . $e->getMessage());
    }
    abort(500);
  }
}
//public function handleProviderCallback()
//{
//  try {
//    $user = Socialite::driver(‘facebook’)->user();
//    $isUser = User::where(‘fb_id’, $user->id)->first();
//    If ($isUser) {
//      Auth::login($isUser);
//      return redirect(‘/dashboard’);
//    } else {
//      $createUser = User::create ([
//          ‘name’ => $user->name,
//         ‘email’ => $user->email,
//          ‘fb_id’ => $user->id,
//          ‘password’ => encrypt(‘admin@123’)
//]);
//Auth::login($createUser);
//return redirect(‘/dashboard’);
//}
//  } catch (Exception $exception) {
//    dd($exception->getMessage());
//  }
//}
