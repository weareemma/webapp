<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Plan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class CustomersController extends Controller
{
    /**
     * Dashboard
     *
     * @return \Inertia\Response
     */
    public function dashboard()
    {
        $user = Auth::user();
        $sub = $user->getFirstStripeSubscription();
        Session::put('photo_profile_remember', false);
        $last_photos = Auth::user()->getLastPhotos();
        return Inertia::render('Customer/Dashboard', [
            'lastPhotos' => $last_photos,
            'stripeSubscription' => $sub
        ]);
    }

    /**
     * Update payment method
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePaymentMethod(Request $request)
    {
        $user = $request->user();
        $pmid = $request->post('pmethod');
        $user->createOrGetStripeCustomer();
        $user->updateDefaultPaymentMethod($pmid);

        return Redirect::back();
    }

    /**
     * Profile page
     *
     * @return \Inertia\Response
     */
    public function profile()
    {
        $user = Auth::user();
        return Inertia::render('Customer/Profile', [
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
            'photo' => 'nullable|mimes:jpg,jpeg,png|max:1024',
            'phone' => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/|min:9'
        ]);

        $model = User::edit($request->user(), $request->all());

        return Redirect::route('customer.profile')
            ->with(
                ($model) ? 'success' : 'error',
                ($model) ? __("Utente modificato") : __("Errore generico")
            );
    }

    /**
     * Upload photo profile
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePhotoProfile(Request $request)
    {
        $user = Auth::user();
        if ($request->hasFile('photo')) {
            $user->updateProfilePhoto($request->file('photo'));
            $user->save();
        }

        return Redirect::back()
            ->with('success', __("Immagine profilo aggiornata"));
    }

    /**
     * Buy subscriptions page
     *
     * @return \Inertia\Response
     */
    public function buySubscription()
    {
        $plans = Plan::query()->orderBy('created_at')->with('pricings')->get();
        return Inertia::render('Customer/Buy/Subscription', [
            'plans' => $plans
        ]);
    }

    /**
     * Buy packages page
     *
     * @return \Inertia\Response
     */
    public function buyPackage()
    {
        $packages = Package::query()->orderBy('created_at')->get();
        return Inertia::render('Customer/Buy/Package', [
            'packages' => $packages
        ]);
    }

    /**
     * Hide photo profile modal
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function hidePhotoProfileModal()
    {
        Auth::user()->hidePhotoProfileRememberModal();
        return back();
    }

    /**
     * Upload booking photos
     *
     * @param Booking $booking
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadBookingPhotos(Booking $booking, Request $request)
    {
        try {
            $photos = $request->get('photos');
            // Delete photos
            foreach ($booking->getMedia('photos') as $media)
            {
                if ( ! in_array($media->id, Arr::pluck($photos ?? [], 'id')))
                {
                    $media->delete();
                }
            }

            // Add new photos
            foreach ($request->files->all()['photos'] as $file)
            {
                $booking
                    ->addMedia($file['file']->getPathName())
                    ->withCustomProperties(['customer' => true])
                    ->toMediaCollection('photos');
            }
        }
        catch (\Exception $ex)
        {
            Log::error('Booking upload photos error: ' . $ex->getMessage() . '; Line: ' . $ex->getLine());
        }

        return Redirect::route('customer.dashboard')
            ->with(
                'success',
                __("Foto aggiornate")
            );
    }

    /**
     * Show bookings index
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function bookingsPast(Request $request)
    {
        $models = Auth::user()
            ->bookings()
            ->with('customer')
            ->with('store')
            ->orderByDesc('date')
            ->orderByDesc('start')
            ->where(function ($q) {
                $q->whereDate('date', '<', now());
                $q->orWhere(function ($q) {
                    $q->whereDate('date', '=', now())
                        ->where('start', '<', now()->format('H:i:s'));
                });
            })
            ->get();
        return Inertia::render('Customer/Bookings/Past', [
            'bookings' => $models
        ]);
    }

    public function bookingsFuture(Request $request)
    {
        $models = Auth::user()
            ->bookings()
            ->with('customer')
            ->with('store')
            ->orderBy('date')
            ->orderBy('start')
            ->where(function ($q) {
                $q->whereDate('date', '>', now());
                $q->orWhere(function ($q) {
                    $q->whereDate('date', '=', now())
                        ->where('start', '>', now()->format('H:i:s'));
                });
            })
            ->get();
        return Inertia::render('Customer/Bookings/Future', [
            'bookings' => $models
        ]);
    }

    /**
     * notifications page
     *
     * @return \Inertia\Response
     */
    public function notifications()
    {
        $models = Auth::user()
            ->notifications()
            ->orderbyDesc('created_at')
            ->paginate(10);
        return Inertia::render('Customer/Notifications/Index', [
            'models' => $models
        ]);
    }

    /**
     * Mark notification as read
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markAsRead(Request $request)
    {
        $notification = Auth::user()
            ->notifications()
            ->where('id', $request->get('notification_id'))
            ->first();

        if ($notification)
        {
            $notification->markAsRead();
        }

        return Redirect::route('customer.notifications')
            ->with(
                'success',
                __("Notifica aggiornata")
            );
    }

    public function gallery()
    {
        $bookings = Auth::user()
            ->bookings()
            ->orderByDesc('date')
            ->orderByDesc('start')
            ->get();

        return Inertia::render('Customer/Gallery/Gallery', [
            'bookings' => $bookings
        ]);
    }
}
