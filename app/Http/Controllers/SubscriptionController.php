<?php

namespace App\Http\Controllers;

use App\Models\Refund;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Inertia\Response
   */
  public function index(Request $request)
  {
    $subscriptions = Subscription::allModels($request)->with(['user', 'plan.pricings', 'items'])->paginate();
    return Inertia::render('Subscriptions/Index', [
      'models' => $subscriptions
    ]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\Subscription  $subscription
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Subscription $subscription, Request $request)
  {
    try {
      if ($request->method && $request->method == 'now')
      {
        $subscription->cancelNow();
      }
      else
      {
        $subscription->cancel();
      }
    } catch (\Throwable $th) {
      return Redirect::back()->with('error', __("Impossibile annullare l'abbonamento"));
    }
    return Redirect::back()->with('success', __("Abbonamento annullato"));
  }

  /**
   * Export CSV
   * 
   */
  public function exportCsv()
  {
      return Subscription::exportAndDownload();
  }
}
