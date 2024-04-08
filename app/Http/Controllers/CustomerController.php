<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Models\Booking;
use App\Models\Plan;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\HelpersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CustomerController extends Controller
{
  /**
   * Constructor
   */
  public function __construct()
  {
//      $this->authorizeResource(User::class, 'customer');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Inertia\Response
   */
  public function index(Request $request)
  {
    $customers = User::allUsers($request)->role(User::ROLE_CUSTOMER)
      ->paginate(10)
      ->withQueryString();
    return Inertia::render('Customers/Index', [
      'customers' => $customers,
      'plans' => HelpersService::forSelects(Plan::query()->get(), 'stripe_product_id', 'name')
    ]);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Inertia\Response
   */
  public function create()
  {
    $model = new User();
    return Inertia::render('Customers/Form', [
      'customer' => $model
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  StoreCustomerRequest  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StoreCustomerRequest $request)
  {
    $model = User::store($request->validated());
    return Redirect::route('customer.index')
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Utente creato") : __("Errore generico")
      );
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\User  $customer
   * @return \Inertia\Response
   */
  public function show(User $customer)
  {
      $customer->load(['lastNotesBy','bookings', 'bookings.order']);
    return Inertia::render('Customers/Show', [
        'customer' => $customer,
        'subscription' => ($customer->hasAnySubscription()) ? $customer->getFirstStripeSubscription() : null,
        'plan' => $customer->getFirstPlan(),
        'bookings' => Booking::query()->where('customer_id', $customer->id)->withCanceled()->orderByDesc('date')->orderByDesc('start')->with(['stylist', 'store'])->paginate(10),
        'payments' => $customer->payments()->latest()->paginate(10),
        'packages' => $customer->activePackages()
    ]);
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\User  $customer
   * @return \Inertia\Response
   */
  public function edit(User $customer)
  {
    return Inertia::render('Customers/Form', [
      'customer' => $customer
    ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param UpdateCustomerRequest $request
   * @param \App\Models\User $customer
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateCustomerRequest $request, User $customer)
  {
    $model = User::edit($customer, $request->validated());
    return Redirect::route('customer.edit', $customer->id)
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Utente modificato") : __("Errore generico")
      );
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\User  $customer
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(User $customer)
  {
    User::deleteModel($customer);
    return Redirect::route('customer.index')
      ->with('success', __("Utente eliminato"));
  }
}
