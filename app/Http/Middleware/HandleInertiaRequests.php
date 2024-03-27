<?php

namespace App\Http\Middleware;

use App\Jobs\UpdateTandaShifts;
use App\Models\CheckoutError;
use App\Models\FiscalFile;
use App\Models\Store;
use App\Models\User;
use App\Services\HelpersService;
use App\Services\JobStatusService;
use App\Services\StoreService;
use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function share(Request $request): array
    {
        $user = Auth::user();
        $fiscal = new FiscalFile();
        $customer_data = [];
        if ($user)
        {
            if ($user->fiscalFile) $fiscal = $user->fiscalFile;
            if ($user->isCustomer()) $customer_data = User::loadCustomerData();
        }

        $launch_date = Carbon::createFromFormat('d-m-Y', config('app.launch_date'));
        $promo_period = config('app.promo_period');

        return array_merge(parent::share($request), [
            'impersonate' => fn () => $request->session()->get('impersonate'),
            'flash' => [
                'data' => fn () => $request->session()->get('flash_data'),
                'type' => function () use ($request) {
                    if ($request->session()->has('success')) return 'success';
                    if ($request->session()->has('warning')) return 'warning';
                    if ($request->session()->has('error')) return 'error';
                    return '';
                },
                'message' => function () use ($request) {
                    if ($request->session()->has('success'))
                        return $request->session()->get('success');
                    if ($request->session()->has('warning'))
                        return $request->session()->get('warning');
                    if ($request->session()->has('error'))
                        return $request->session()->get('error');
                    return '';
                },
                'restore_state' => fn () => $request->session()->get('restore_state'),
            ],
            'user' => $user,
            'subscribed' => $user ? $user->hasAnySubscription() : false,
            'is_admin' => $user ? $user->isAdmin() : false,
            'is_stylist' => $user ? $user->isStylist() : false,
            'role' => (Auth::user()) ? $request->user()->role : '',
            'current_store' => StoreService::getCurrentStore(),
            'stores_list' => HelpersService::forSelects(StoreService::getAllStores(), 'id', 'name'),
            'stores_list_wizard' => Store::query()->visible()->get(['id', 'name', 'address', 'washing_stations']),
            'customer' => $customer_data,
            'fiscal' => $fiscal,
            'promo_name' => config('app.promo_name'),
            'promo_expires' => Carbon::create(2023,6,10),
            'promo_already' => $user ? $user->hasAlreadyPromo() : false,
            'booking_locked' => config('app.booking_locked', true),
            'last_update' => [
                'shift' => JobStatusService::getLastEnded(UpdateTandaShifts::class)
            ],
            'primaries_not_included' => config('app.primaries_not_included', []),
            'checkoutError' => CheckoutError::checkForErrors()
        ]);
    }
}
