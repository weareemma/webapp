<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PaymentController extends Controller
{
  private $pagination_number;

  /**
   * Constructor
   */
  public function __construct()
  {
//    $this->authorizeResource(Payment::class, 'payment');
    $this->pagination_number = config('app.table_pagination_number');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Inertia\Response
   */
  public function index(Request $request)
  {
    $models = Payment::allModels($request)
        ->paginate($this->pagination_number)
        ->withQueryString();
    return Inertia::render('Payments/Index', [
      'models' => $models,
      'refundables' => $models
          ->where('refundable', true)
          ->count()
    ]);
  }

    /**
     * Display customer index
     *
     * @param Request $request
     * @return \Inertia\Response
     */
  public function indexCustomer(Request $request)
  {
      $models = Auth::user()->payments()->orderByDesc('date')->paginate($this->pagination_number);
      return Inertia::render('Customer/Payments/Index', [
          'models' => $models
      ]);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\Payment  $payment
   * @return \Illuminate\Http\Response
   */
  public function show(Payment $payment)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\Payment  $payment
   * @return \Illuminate\Http\Response
   */
  public function edit(Payment $payment)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\Payment  $payment
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, Payment $payment)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\Payment  $payment
   * @return \Illuminate\Http\Response
   */
  public function destroy(Payment $payment)
  {
    $payment->delete();
    return Redirect::back();
  }

  /**
   * Invoice download
   *
   * @param User $user
   * @param Payment $payment
   * @return \Symfony\Component\HttpFoundation\Response|null
   */
  public function invoice(User $user, Payment $payment)
  {
    if (Auth::user()->isAdmin()) {
      return StripeService::getInvoiceDownloadLink($user, $payment);
    }

    return Redirect::route('payment.index');
  }

    /**
     * Refund payment
     *
     * @param Payment $payment
     * @return \Illuminate\Http\RedirectResponse
     */
  public function refund(Payment $payment)
  {
      // Stripe refund
      try {
          $payment->user->refund($payment->stripe_payment_id);
          $payment->update([
              'refunded' => true
          ]);
      }
      catch (\Exception $ex)
      {
          Log::error('Payment refund: ' . $ex->getMessage());
          return Redirect::route('payment.index')
              ->with(
                  'error',
                  __("Si è verificato un errore")
              );
      }

      return Redirect::route('payment.index')
          ->with(
              'success',
              __("Pagamento rimborsato")
          );
  }

  public function exportCsv(Request $request)
  {
      return Payment::exportAndDownload($request->toArray());
  }
}
