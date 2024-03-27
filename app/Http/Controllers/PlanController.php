<?php

namespace App\Http\Controllers;

use App\Http\Requests\Plans\StorePlanRequest;
use App\Http\Requests\Plans\UpdatePlanRequest;
use App\Models\Package;
use App\Models\Plan;
use App\Models\PlanPricing;
use App\Models\User;
use App\Notifications\Admin\SubscriptionCanceled;
use App\Notifications\Admin\SubscriptionConfirmation;
use App\Notifications\SubscriptionCanceledCustomer;
use App\Services\HelpersService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PlanController extends Controller
{
  private $pagination_number;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->authorizeResource(Plan::class, 'plan');
    $this->pagination_number = config('app.table_pagination_number');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Inertia\Response
   */
  public function index(Request $request)
  {
    $models = Plan::allModels($request)->paginate($this->pagination_number);
    return Inertia::render('Plans/Index', [
      'models' => $models
    ]);
  }

  /**
   * Display the detail page.
   *
   * @return \Inertia\Response
   */
  public function detail()
  {
    $user = Auth::user();
    $plan = $user->getFirstPlan();
    $subscriptions = Plan::query()
//        ->when($user->hasAlreadyPromo(), function ($query) {
//            $query->where('name', '<>', config('app.promo_name', 'PROMO'));
//        })
        ->get();
    $user_packs = $user->activePackages()->pluck('id');
    $packs = Package::query()->notExpired()->get();
    $data = [];
    if ($plan)
    {
        $subscription = $user->getFirstSubscription($plan);
        $price = $plan->pricings()->where('stripe_price_id', $subscription->stripe_price)->first();
        $stripeSubscription = $subscription->asStripeSubscription();
        $lastPaymentDate = Carbon::parse($stripeSubscription->current_period_start);
        $nextPaymentDate = Carbon::parse($stripeSubscription->current_period_end);

        $data = [
            'plan' => $plan,
            'subscription' => $subscription,
            'price' => $price,
            'lastPaymentDate' => $lastPaymentDate,
            'nextPaymentDate' => $nextPaymentDate
        ];
    }
//    dd($data);

    return Inertia::render('Plans/Detail', array_merge($data, [
        'subscriptions' => $subscriptions,
        'user_packs' => $user_packs,
        'packs' => $packs
    ]));
  }

  /**
   * Edit user plan
   *
   * @return \Inertia\Response
   */
  public function editUserPlan()
  {
      $user = Auth::user();
      $plan = $user->getFirstPlan();
      $data = [];
      if ($plan)
      {
          $subscription = $user->getFirstSubscription($plan);
          $price = $plan->pricings()->where('stripe_price_id', $subscription->stripe_price)->first();
          $stripeSubscription = $subscription->asStripeSubscription();
          $lastPaymentDate = Carbon::parse($stripeSubscription->current_period_start);
          $nextPaymentDate = Carbon::parse($stripeSubscription->current_period_end);

          $data = [
              'plan' => $plan,
              'subscription' => $subscription,
              'price' => $price,
              'lastPaymentDate' => $lastPaymentDate,
              'nextPaymentDate' => $nextPaymentDate
          ];
      }

      return Inertia::render('Plans/Edit', $data);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Inertia\Response
   */
  public function create()
  {
    $model = new Plan();
    return Inertia::render('Plans/Form', [
      'model' => $model,
      'availableDurations' => PlanPricing::AVAILABLE_DURATIONS
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param StorePlanRequest $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StorePlanRequest $request)
  {
    $model = Plan::storeModel($request->validated());
    return Redirect::route('plan.index')
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Abbonamento creato") : __("Errore generico")
      );
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\Plan  $plan
   * @return \Illuminate\Http\Response
   */
  public function show(Plan $plan)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\Plan  $plan
   * @return \Inertia\Response
   */
  public function edit(Plan $plan)
  {
    $plan->load('pricings');
    return Inertia::render('Plans/Form', [
      'model' => $plan,
      'availableDurations' => PlanPricing::AVAILABLE_DURATIONS
    ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param UpdatePlanRequest $request
   * @param \App\Models\Plan $plan
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdatePlanRequest $request, Plan $plan)
  {
    $model = Plan::updateModel($plan, $request->all());
    return Redirect::route('plan.index')
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Abbonamento modificato") : __("Errore generico")
      );
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\Plan  $plan
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Plan $plan)
  {
    $error = Plan::deleteModel($plan);
    return Redirect::route('plan.index')
      ->with(
        ($error) ? 'error' : 'success',
        $error ?? __("Abbonamento cancellato")
      );
  }

  /**
   * Cancel plan
   *
   * @return \Illuminate\Http\RedirectResponse|void
   */
  public function cancelSubscription()
  {
      try
      {
          $user = Auth::user();
          $current_subscription = $user->subscriptions()->active()->first();

          $current_plan = $user->getFirstPlan();

          $user->subscription($current_subscription->name)->cancel();


          // Notify info
          $info = new User(['email' => 'info@weareemma.com']);
          $info->notify(new SubscriptionCanceled([
              'customer' => $user->full_name ?? '',
              'plan' => $current_plan?->name
          ]));

          return Redirect::route('customer.dashboard')
          ->with(
              'success',
              __("Abbonamento cancellato")
          );
      }
      catch (\Exception $ex)
      {
          Log::error('Subscription cancel error: user ('.$user->id.') ' . $ex->getMessage());
      }

  }
}
