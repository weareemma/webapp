<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\HairService;
use App\Models\Store;
use App\Models\User;
use App\Services\HelpersService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class StylistsController extends Controller
{
    /**
     * Stylist dashboard
     *
     * @return \Inertia\Response
     */
    public function dashboard(Request $request)
    {
        // Current stylist
        $stylist = Auth::user();

        // Stores
        $stores = Store::query()->get();

        // Bookings
        $bookings_query = $stylist->bookings();
        if ($request->has('store'))
        {
            $bookings_query->wherehas('store', function ($query) use ($request) {
                $query->where('name', $request->get('store'));
            });
        }
        $date = Carbon::parse($request->get('day'));
        $bookings_query->whereDate('date', '=', $date->format('Y-m-d'));
        $bookings_query->with(['customer', 'store']);

        if ($date->isCurrentDay())
        {
            $bookings = (clone $bookings_query)->orderBy('start')->get();
            $bookings_next = $bookings->filter(function ($booking) {
                return ! Carbon::parse($booking->start)->addMinutes($booking->total_execution_time)->isPast();
            });

            $bookings = (clone $bookings_query)->orderBy('start')->get();
            $bookings_past = $bookings->filter(function ($booking) {
                return Carbon::parse($booking->start)->addMinutes($booking->total_execution_time)->isPast();
            });
        }
        elseif ($date->isPast())
        {
            $bookings = (clone $bookings_query)->orderBy('start')->get();
            $bookings_past = $bookings;
            $bookings_next = [];
        }
        else
        {
            $bookings = (clone $bookings_query)->orderBy('start')->get();
            $bookings_past = [];
            $bookings_next = $bookings;
        }


        return Inertia::render('Stylist/Dashboard', [
            'stores' => $stores,
            'bookings_next' => $bookings_next,
            'bookings_past' => $bookings_past
        ]);
    }

    /**
     * Past bookings
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function bookingPast(Request $request)
    {
        // Current stylist
        $stylist = Auth::user();

        $query = $stylist
            ->bookings()
            ->with(['store', 'customer'])
            ->where(function ($q) {
                $q->whereDate('date', '<', now());
                $q->orWhere(function ($q) {
                    $q->whereDate('date', '=', now())
                        ->where('start', '<', now()->format('H:i:s'))
                        ->orWhere(function ($q) {
                            $q->where('status', '=', Booking::STATUS_ENDED);
                        });
                });
            });


        if ($request->get('q'))
        {
            $query->whereHas('customer', function ($q) use ($request) {
                $q
                    ->where('name', 'like', "%{$request->get('q')}%")
                    ->orWhere('surname', 'like', "%{$request->get('q')}%");

            });
        }

        $bookings = $query
            ->orderByDesc('date')
            ->orderByDesc('start')
            ->get();

        return Inertia::render('Stylist/Bookings/PastBookings', [
            'bookings' => $bookings
        ]);
    }

    /**
     * Future bookings
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function bookingFuture(Request $request)
    {
        // Current stylist
        $stylist = Auth::user();

        $query = $stylist
            ->bookings()
            ->with(['store', 'customer'])
            ->where(function ($q) {
                $q->whereDate('date', '>', now());
                $q->orWhere(function ($q) {
                    $q->whereDate('date', '=', now())
                        ->where('start', '>', now()->format('H:i:s'));
                });
            });

        if ($request->get('q'))
        {
            $query->whereHas('customer', function ($q) use ($request) {
                $q
                    ->where('name', 'like', "%{$request->get('q')}%")
                    ->orWhere('surname', 'like', "%{$request->get('q')}%");

            });
        }

        $bookings = $query
            ->orderBy('date')
            ->orderBy('start')
            ->get();

        return Inertia::render('Stylist/Bookings/FutureBookings', [
            'bookings' => $bookings
        ]);
    }


    /**
     * Booking details page
     *
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function bookingDetails(Booking $booking)
    {
        if ($booking->stylist_id != Auth::user()->id)
            return Redirect::route('stylist.dashboard');

        $booking->load(['customer', 'store', 'customer.lastNotesBy']);

        $services = HelpersService::forSelects(HairService::addon()->get(), 'id', 'title');

        return Inertia::render('Stylist/Bookings/Details', [
            'booking' => $booking,
            'services' => $services
        ]);
    }

    /**
     * Stylist takes charge
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bookingTakeCharge(Request $request)
    {
        $booking = Booking::find($request->get('booking_id'));
        if ($booking)
        {
            $booking->takeChargeFrom(Auth::user());
        }

        return Redirect::route('stylist.booking.details', $booking->id)
            ->with(
                'success',
                __("Servizio iniziato")
            );
    }

    /**
     * Stylist ends booking
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bookingEndService(Request $request)
    {
        $booking = Booking::find($request->get('booking_id'));
        if ($booking)
        {
            $booking->endBooking(
                Auth::user(),
                $request->get('stylist_notes'),
                $request->get('customer_notes'),
            );

            try {
                $photos = $request->get('photos');
                // Delete photos
                foreach ($booking->getMedia('photos', ['customer' => false]) as $media)
                {
                    if ( ! in_array($media->id, Arr::pluck($photos ?? [], 'id')))
                    {
                        $media->delete();
                    }
                }

                // Add new photos
                if (array_key_exists('photos', $request->files->all()))
                {
                    foreach ($request->files->all()['photos'] as $file)
                    {
                        $booking
                            ->addMedia($file['file']->getPathName())
                            ->withCustomProperties(['customer' => false])
                            ->toMediaCollection('photos');
                    }
                }
            }
            catch (\Exception $ex)
            {
                Log::error('Booking upload photos error: ' . $ex->getMessage() . '; Line: ' . $ex->getLine());
            }
        }

        return Redirect::route('stylist.booking.details', $booking->id)
            ->with(
                'success',
                __("Servizio terminato")
            );
    }

    /**
     * Customer show page
     *
     * @param User $customer
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function customerShow(User $customer, Request $request)
    {
        
        $customer->load('lastNotesBy', 'bookings');

        $booking_count = $customer->bookings->where('stylist_id', Auth::user()->id)->count();
        if ($booking_count == 0)
        {
            return Redirect::route('stylist.dashboard');
        }

        $past_bookings = $customer->bookings()
            ->with(['store', 'customer'])
            ->where(function ($q) {
                $q->whereDate('date', '<', now());
                $q->orWhere(function ($q) {
                    $q->whereDate('date', '=', now())
                        ->where('start', '<', now()->format('H:i:s'));
                });
                $q->orWhere(function ($q) {
                    $q->where('status', '=', Booking::STATUS_ENDED);
                });
            })
            ->orderByDesc('date')
            ->orderByDesc('start')
            ->get();

        $next_bookings = $customer->bookings()
            ->with(['store', 'customer'])
            ->where(function ($q) {
                $q->whereDate('date', '>', now());
                $q->orWhere(function ($q) {
                    $q->whereDate('date', '=', now())
                        ->where('start', '>', now()->format('H:i:s'));
                });
            })
            ->orderBy('date')
            ->orderBy('start')
            ->get();

        $customer->last = $past_bookings->first();

        return Inertia::render('Stylist/Customers/Show', [
            'customer' => $customer,
            'packages' => $customer->activePackages(),
            'bookings' => $past_bookings,
            'nextBookings' => $next_bookings,
            'subscription' => $customer->getFirstPlan(),
            'bookingCount' => $booking_count
        ]);
    }

    /**
     * Profile page
     *
     * @return \Inertia\Response
     */
    public function profile()
    {
        $user = Auth::user();
        return Inertia::render('Stylist/Profile', [
            'user' => $user
        ]);
    }

    /**
     * Update profile
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function profileUpdate(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255|unique:users,email,' . $request->user()->id,
            'password' => 'nullable|confirmed|min:8',
            'photo' => 'nullable|mimes:jpg,jpeg,png|max:1024'
        ]);

        $model = User::edit($request->user(), $request->all());

        return Redirect::route('stylist.profile')
            ->with(
                ($model) ? 'success' : 'error',
                ($model) ? __("Utente modificato") : __("Errore generico")
            );
    }

    /**
     * Edit booking page
     *
     * @param Booking $booking
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Inertia\Response
     */
    public function bookingEdit(Booking $booking, Request $request)
    {
        if ($booking->stylist_id != Auth::user()->id)
            return Redirect::route('stylist.dashboard');

        $booking->load(['customer', 'store', 'payments']);

        $originalBooking = $booking;

        // customers (admin)
        $customers = User::customers()->pluck('name', 'id');

        // primary services
        $primaryHairServices = HairService::primary()->get();

        // addon
        $addonsQuery = HairService::addon();
        if (!empty($booking->slots[0]['service']['dry_style'])) $addonsQuery->where('type', 'updo');
        $addonHairServices = $addonsQuery->get()->groupBy('type');

        // Stores
        $stores_list = Store::all()->pluck(['id', 'name']);

        return Inertia::render('Stylist/Bookings/Edit', [
            'stores_list' => $stores_list,
            'customers' => $customers,
            'originalBooking' => $originalBooking,
            'primaryHairServices' => $primaryHairServices,
            'addonHairServices' => $addonHairServices
        ]);
    }

    public function addExtra(Booking $booking, Request $request)
    {
        $slots = $booking->slots;

        // remove all extra
        foreach($slots as $slot)
        {
            if (isset($slot['service']['extra']))
            {
                if ($booking->is_father)
                {
                    $booking->total_net_price -= $slot['service']['net_price'];
                    if($booking->order)
                    {
                        $booking->order->total = $booking->total_net_price;
                    }
                }
                elseif ($booking->parent)
                {
                    $booking->parent->total_net_price -= $slot['service']['net_price'];
                    if($booking->parent->order)
                    {
                        $booking->parent->order->total = $booking->parent->total_net_price;
                    }
                }
            }
        }

        $slots = collect($slots)->filter(function ($slot) {
            return ! isset($slot['service']['extra']);
        })->toArray();

        foreach($request->services ?? [] as $serviceId)
        {
            $service = HairService::find($serviceId);

            if ($service)
            {
                $service->extra = true;
                $slots[] = [
                    'service' => $service,
                    'duration' => 0,
                    'start_time' => $booking->start,
                    'station_type' => 'style'
                ];

                if ($booking->is_father)
                {
                    $booking->total_net_price += $service->net_price;

                    if($booking->order)
                    {
                        $booking->order->total = $booking->total_net_price;
                    }
                }
                elseif ($booking->parent)
                {
                    $booking->parent->total_net_price += $service->net_price;

                    if($booking->parent->order)
                    {
                        $booking->parent->order->total = $booking->parent->total_net_price;
                    }
                }
            }
        }

        $booking->slots = $slots;

        if ( ! $booking->is_father) {
            $booking->parent->updatedBy();
            $booking->parent->save();
            $booking->parent->order->save();
        }
        else
        {
            $booking->order->save();
        }


        $booking->updatedBy();
        $booking->save();

        return Redirect::back()
            ->with(
                'success',
                __("Servizi extra aggiunti")
            );
    }
}
