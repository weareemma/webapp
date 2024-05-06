<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Order;
use App\Models\Store;
use App\Models\Refund;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\HairService;
use Illuminate\Http\Request;
use App\Services\StoreService;
use App\Services\BookingService;
use App\Services\HelpersService;
use App\Services\IpraticoService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\AvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use App\Notifications\PaymentLinkCustomer;
use App\Notifications\BookingUpdatedCustomer;
use App\Notifications\BookingCanceledCustomer;
use App\Http\Requests\Bookings\UpdateBookingRequest;
use App\Notifications\NewAppointmentAlertForStylist;
use App\Notifications\AppointmentConfirmationCustomer;


class BookingController extends Controller
{
  /**
   * Show the booking dashboard
   *
   * @return \Illuminate\Http\Response
   */
  public function wizard(Booking $booking = null)
  {
    $data = [];
    //TODO verficiare che l'if continui a servire oppure può essere eliminato
    if ($booking)
    {
        // primary services
        $primaryHairServices = HairService::primary()->orderBy('order')->get();

        // addon
        $addonsQuery = HairService::addon()->orderBy('order');
        if (!empty($booking->slots[0]['service']['dry_style'])) $addonsQuery->where('type', 'updo');
        $addonHairServices = $addonsQuery->get()->groupBy('type');

        $data = [
            'originalBooking' => $booking,
            'primaryHairServices' => $primaryHairServices,
            'addonHairServices' => $addonHairServices,
        ];
    }
    return Inertia::render('Booking/Wizard', $data);
  }

  /**
   * Show the booking dashboard for the admin
   *
   * @return \Inertia\Response
   */
  public function adminDashboard(Request $request)
  {
    $customers = User::customers()->pluck('full_name', 'id');
    $current_store = StoreService::getCurrentStore();
    $store_id = ($current_store) ? $current_store->id : null;
    $washing_stations = ($current_store) ? $current_store->washing_stations : 0;
    $customer_id = $request->get('customer_id');
    return Inertia::render('Booking/AdminDashboard', compact('customers', 'store_id', 'customer_id', 'washing_stations'));
  }

  public function customersForSelect(Request $request)
  {
      $users = User::allUsers($request)
          ->role(User::ROLE_CUSTOMER)
          ->limit(10)
          ->get()
          ->pluck('full_name', 'id');
      return \response()->json($users);
  }

  public function customerIsSubscribed(Request $request)
  {
      $subscribed = false;
      if ($request->has('customer'))
      {
          $customer = User::find($request->input('customer'));

          if ($customer)
          {
              $subscribed = $customer->hasAnySubscription();
          }
      }

      return \response()->json(['data' => $subscribed]);
  }

  /**
   * Fetch the primary hair services
   *
   * @return \Illuminate\Http\Response
   */
  public function primaryHairServices(Request $request)
  {
    $services = HairService::primary()->orderBy('order')->get();
    return \response()->json(['data' => $services]);
  }

  /**
   * Fetch the addon hair services
   *
   * @return \Illuminate\Http\Response
   */
  public function addonHairServices(HairService $primaryService)
  {
      $query = HairService::addon()->orderBy('order');

      if ($primaryService->dry_style)
      {
          $services = $query->where('type', 'updo')->get()->groupBy('type');
      }
      else
      {
          $services = $query->get()->groupBy('type');
      }

      return \response()->json(['data' => $services]);
  }

    public function stylistAvailable(Store $store)
    {
        $stylists = User::stylistsForStore($store->id);
        return \response()->json(['data' => $stylists]);

    }

  /**
   * Check availability for the service
   *
   * @return \Illuminate\Http\JsonResponse|RedirectResponse|\Illuminate\Http\Response
   */
  public function checkAvailability(Request $request)
  {
        $data = BookingService::checkAvailability($request);
        $datalog = [
          "request" => $request->all(),
          "response" => $data,
        ];

        if (($request->has('axios')) || ($request->hasHeader('X-Header-WeareemmaTest')))
        {
            Log::channel('bookingavailability')->info("FROM AXIOS: ". json_encode($datalog));
            return \response()->json($data);
        }
        Log::channel('bookingavailability')->info("FROM FRONT: ". json_encode($datalog));
        return Redirect::back()->with('flash_data', $data);
  }

  public function checkAvailabilitySingle(Request $request)
  {
    $data = BookingService::checkAvailabilitySingle($request);
    return Redirect::back()->with('flash_data', $data); 
  }

  /**
   * Get the booking infos, like prices sum, applied sub and packages
   *
   * @return \Illuminate\Http\Response
   */
  public function getInfos(Request $request)
  {
    $data = BookingService::getBookingInfos(requestData: $request);
//    dd($data);
    return Redirect::back()->with('flash_data', $data);
  }

  /**
   * Get payment infos
   *
   * @return \Illuminate\Http\Response
   */
  public function getPaymentInfos()
  {
    $user = Auth::user();
    if ($user) {
      return Response::json([
          'user' => $user,
          'intent' => $user->createSetupIntent(),
          'stripeKey' => config('cashier.key')
      ]);
    }
    return Response::json([]);
  }

  /**
   * Check discount
   *
   * @return \Illuminate\Http\Response
   */
  public function checkDiscount(Request $request)
  {
    $discount = BookingService::checkDiscount($request);
    return Redirect::back()->with('flash_data', $discount);
  }

  /**
   * Show the booking
   *
   * @return \Illuminate\Http\Response
   */
  public function show(Booking $booking)
  {
    $booking->load(['customer', 'stylist', 'order', 'store']);
    $payments = $booking->payments()->where('method',  'stripe')->get();
    $storePayments = $booking->payments()->where('method', '<>', 'stripe')->get();
    $refunds = $booking->refunds;

    $history = BookingService::buildHistory($booking);
    return Inertia::render('Booking/Show', compact('booking', 'payments', 'storePayments', 'refunds', 'history'));
  }

  public function showStylist(Booking $booking)
  {
      $booking->load(['customer', 'stylist']);
      $payments = $booking->payments()->where('subject', '!=', 'booking-edit')->get();
      $storePayments = $booking->payments()->where('subject', '=', 'booking-edit')->get();
      $refunds = $booking->refunds;
      return Inertia::render('Stylist/AppointmentDetails', compact('booking', 'payments', 'storePayments', 'refunds'));
  }

  /**
   * Saves the booking
   *
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $booking = BookingService::storeBooking($request, true);
    if ($request->notify) {
      $booking->customer->notify(new PaymentLinkCustomer($booking));
    }

    if (Auth::user()->isAdmin() || Auth::user()->isManager())
    {
        return Redirect::route('schedule.appointment.index')->with(['flash_data' => [
            'booking' => $booking->load('store')
        ]]);
    }

    return Redirect::back()->with(['flash_data' => [
        'booking' => $booking->load('store')
    ]]);
  }

  public function successNoStripe(Request $request)
  {
    return Redirect::route('buy.success')->with(['flash_data' => [
        'booking' => $request->all()
    ]]);
  }

  /**
   * Edit the booking
   *
   * @return \Illuminate\Http\Response
   */
  public function edit(Request $request, Booking $booking)
  {
    $user = Auth::user();
    $isAdmin = $user->hasRole(['admin', 'manager']);

    if (!$isAdmin && $booking->customer_id != $user->id) return abort(403);

    // load original booking relations
    $booking->load('payments');
    $booking->load('customer');
    $booking->load('order');
    $originalBooking = $booking;

    // customers (admin)
    $customers = User::customers()->pluck('name', 'id');

    // primary services
    $primaryHairServices = HairService::primary()->get();

    // addon
    $addonsQuery = HairService::addon();
    if (!empty($booking->slots[0]['service']['dry_style'])) $addonsQuery->where('type', 'updo');
    $addonHairServices = $addonsQuery->get()->groupBy('type');

    // go directly to wizard checkout
    $jumpToStep = $request->get('step');

    if ($isAdmin) return Inertia::render('Booking/AdminDashboard', compact('customers', 'originalBooking', 'primaryHairServices', 'addonHairServices'));
    return Inertia::render('Booking/Wizard', compact('originalBooking', 'primaryHairServices', 'addonHairServices', 'jumpToStep'));
  }

  /**
   * Updates the booking
   *
   * @return \Illuminate\Http\Response
   */
  public function update(UpdateBookingRequest $request, Booking $booking)
  {
    $booking = BookingService::updateBooking($request->all(), $booking, true);
    $booking->refresh();

      // Auto-assignment
      BookingService::autoAssignment($booking);

    return Redirect::route('home')->with('flash_data', $booking);
  }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param Booking $booking
     * @return RedirectResponse
     */
  public function destroy(Request $request, Booking $booking)
  {
    $method = (Auth::user()->isAdmin() && isset($request->method)) ? $request->method : 'refund';

    if ( ! $booking->is_father)
    {
        $booking = $booking->parent;
    }

    if ($booking)
    {
      switch($method)
      {
        case 'refund':
          if ($booking->order)
          {
            $booking->order->refund();
          }
          else
          {
            $booking->refund();
          }
          break;

        case 'discount':
          $booking->generateDiscount();
          break;

        case 'none':
        default:
          break;
      }


        $booking->customer->notify(new BookingCanceledCustomer($booking));

        $booking->update(['status' => 'cancelled']);
        $booking->children()->update(['status' => 'cancelled']);
        $booking->children()->delete();

        if ($booking->order)
        {
          $booking->order->update([
            'status' => Order::STATUS_CANCELED
          ]);
        }

        $booking->delete();
    }

    if ($request->redirect_to) return Redirect::route($request->redirect_to);
    return Redirect::back()->with('success', "Appuntamento cancellato");
  }

    /**
     * Set the booking as paid creating a manual payment
     *
     * @param Request $request
     * @param Booking $booking
     * @return RedirectResponse
     */
  public function paid(Request $request, Booking $booking)
  {
    $request->validate([
      'date' => 'required|date',
      'amount' => 'required|numeric',
    ]);

    if ($booking->order)
    {
      Payment::create([
        'user_id' => $booking->customer_id,
        'type' => 'cash',
        'subject' => 'booking-edit',
        'date' => $request->date,
        'total' => $request->amount,
        'method' => $request->method,
        'payable_type' => Order::class,
        'payable_id' => $booking->order->id,
      ]);
    }
    else
    {
      Payment::create([
        'user_id' => $booking->customer_id,
        'type' => 'cash',
        'subject' => 'booking-edit',
        'date' => $request->date,
        'total' => $request->amount,
        'method' => $request->method,
        'payable_type' => Booking::class,
        'payable_id' => $booking->id,
      ]);
    }

    

    return Redirect::back();
  }

  /**
   * Available stylists
   *
   * @param Booking $booking
   * @return \Illuminate\Http\JsonResponse
   */
  public function availableStylists(Booking $booking)
  {
    if ($booking) {
      return Response::json($booking->availableStylists());
    }
    return Response::json([]);
  }

  /**
   * Update booking stylist
   *
   * @param Booking $booking
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function updateStylist(Booking $booking, Request $request)
  {
      if ($request->has('stylist_id'))
      {
          $stylist = User::find($request->get('stylist_id'));
          $stylist_id = ($stylist) ? $stylist->id : null;

          $datalog = [
              "stylist_from" => $booking->stylist_id,
              "stylist_to" => $stylist_id,
              "booking" => $booking->toArray(),
          ];
          Log::channel('bookingediting')->info("EDIT STYLIST: ". json_encode($datalog));
          $booking->stylist_id = $stylist_id;
          $booking->updatedBy();
          $booking->save();

//          if ($stylist) $stylist->notify(new NewAppointmentAlertForStylist($booking));


          return Redirect::back()
              ->with('success', __("Stylist aggiornato"));
      }

      return Redirect::back()
          ->with('error', __("Impossibile aggiornare lo stylist"));
  }

  /**
   * Export csv
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   */
  public function exportCsv(Request $request)
  {
    return Booking::exportAndDownload($request->all());
  }

  /**
   * Update booking on drag & drop
   *
   * @param Booking $booking
   * @param Request $request
   * @return RedirectResponse
   */
  public function updateFromCalendar(Booking $booking, Request $request)
  {

      $stylist_id = (is_null($request->get('stylist_id')))
          ? $booking->stylist_id
          : User::find($request->get('stylist_id'))?->id;

      $datalog = [
          "stylist_from" => $booking->stylist_id,
          "stylist_to" => $stylist_id,
          "slot_from" => $booking->start,
          "slot_to" => $request->get('start', $booking->start),
          "booking" => $booking->toArray(),
      ];
      Log::channel('bookingediting')->info("EDIT FROM CALENDAR: ". json_encode($datalog));


      // Set stylist
      $booking->stylist_id = $stylist_id;

      // Set start
      $booking->start = $request->get('start', $booking->start);

      // Save
      $booking->updatedBy();
      $booking->save();


      return Redirect::back()
          ->with('success', __("Appuntamento aggiornato"));
  }

  public function updateDate(Booking $booking, Request $request)
  {
      if ($request->get('date'))
      {

          $datalog = [
              "date" => $request->get('date'),
              "booking" => $booking->toArray(),
          ];
          Log::channel('bookingediting')->info("EDIT DATE: ". json_encode($datalog));

          $booking->date = $request->get('date');
          $booking->save();
      }
      return Redirect::back()
          ->with('success', __("Appuntamento aggiornato"));
  }

  /**
   * Set not shown status
   * 
   */
  public function setNotShownStatus(Booking $booking)
  {
    $booking->status = Booking::STATUS_NOT_SHOWN;
    $booking->save();
    return Redirect::back()
          ->with('success', __("Stato aggiornato"));
  }

  /**
   * Save booking notes
   *
   */
  public function saveBookingNotes(Booking $booking, Request $request)
  {
    if ($request->has('notes'))
    {
      $booking->stylist_notes = $request->notes;
      $booking->saveQuietly();
    }

    return;
  }
}
