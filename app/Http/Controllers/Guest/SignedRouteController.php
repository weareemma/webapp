<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SignedRouteController extends Controller
{
    public function verifyPaymentLink(Request $request)
    {
      if (! $request->hasValidSignature()) {
//        abort(401);
      }
      return redirect()->route('booking.edit', [
          'booking' => $request->get('booking'),
          'step' => 'step_checkout'
      ]);
    }
}
