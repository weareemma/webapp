<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class StoreService
{
    private const CURRENT_STORE_KEY = 'current_store';

    /**
     * Get stores list
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAllStores()
    {
        return Store::query()
            ->latest('created_at')
            ->get();
    }

    /**
     * Change current store in session
     *
     * @param Request $request
     * @return void
     */
    public static function changeStore(Request $request)
    {
        if ($request->post('store')) {
            $store = Store::find($request->get('store'));
            if ($store) self::updateCurrentStore($store);
        } else {
            Session::forget(self::CURRENT_STORE_KEY);
        }
    }

    /**
     * Get current store
     *
     * @return mixed|null
     */
    public static function getCurrentStore()
    {
        if (Auth::check())
        {
            return (Session::exists(self::CURRENT_STORE_KEY))
                ? Session::get(self::CURRENT_STORE_KEY)
                : self::setupStore();
        }
        return null;
    }

    /**
     * Forget current store session key
     *
     * @return void
     */
    public static function forgetCurrentStoreSession()
    {
        Session::forget(self::CURRENT_STORE_KEY);
    }

    /**
     * initial setup store
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    private static function setupStore()
    {
        $store = self::loadCurrentStoreFromUser() ??
            Store::query()->latest('created_at')->first();
        self::updateCurrentStore($store);
        return $store;
    }

    /**
     * Update current store in session
     *
     * @param Store|null $store
     * @return void
     */
    private static function updateCurrentStore(Store $store = null)
    {
        if ($store) {
            Session::put(self::CURRENT_STORE_KEY, $store);
            self::updateCurrentStoreForUser($store);
        }
    }

    /**
     * Load current store from logged user
     *
     * @return null
     */
    private static function loadCurrentStoreFromUser()
    {
        $store = null;
        $user = Auth::user();
        if ($user)
        {
            $store = Store::find($user->current_store);
        }
        return $store;
    }

    /**
     * Update current store for user
     *
     * @param Store $store
     * @return void
     */
    private static function updateCurrentStoreForUser(Store $store)
    {
        $user = Auth::user();
        if ($store && $user)
        {
            $user->current_store = $store->id;
            $user->save();
        }
    }
}
