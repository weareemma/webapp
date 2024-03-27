<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stores\StoreStoreRequest;
use App\Http\Requests\Stores\UpdateStoreRequest;
use App\Models\ClosingDay;
use App\Models\ExceptionalTime;
use App\Models\OpeningTime;
use App\Models\Store;
use App\Models\User;
use App\Services\HelpersService;
use App\Services\StoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use phpDocumentor\Reflection\Types\Integer;

class StoreController extends Controller
{
  private $pagination_number;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->authorizeResource(Store::class, 'store');
    $this->pagination_number = config('app.table_pagination_number');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Inertia\Response
   */
  public function index(Request $request)
  {
    $stores = Store::allStores($request)->paginate($this->pagination_number);
    return Inertia::render('Stores/Index', [
      'stores' => $stores
    ]);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Inertia\Response
   */
  public function create()
  {
    $model = new Store();
    return Inertia::render('Stores/Form', [
      'store' => $model,
      'managers' => $this->loadManagers()
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param StoreStoreRequest $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StoreStoreRequest $request)
  {
    $model = Store::store($request->validated());
    return Redirect::route('store.index')
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Store creato") : __("Errore generico")
      );
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\Store  $store
   * @return \Illuminate\Http\Response
   */
  public function show(Store $store)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\Store  $store
   * @return \Inertia\Response
   */
  public function edit(Store $store)
  {
    return Inertia::render('Stores/Form', [
      'store' => $store,
      'managers' => $this->loadManagers(),
      'openingTimes' => $store->openingTimes()->orderBy('order')->paginate($this->pagination_number),
      'exceptionalTimes' => $store->exceptionalTimes()->paginate($this->pagination_number),
      'closingDays' => $store->closingDays()->paginate($this->pagination_number),
      'closingDay' => new ClosingDay(),
      'exceptionalTime' => new ExceptionalTime(),
      'openingTime' => new OpeningTime(),
      'days' => OpeningTime::availableDaysForStore($store)
    ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param UpdateStoreRequest $request
   * @param \App\Models\Store $store
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateStoreRequest $request, Store $store)
  {
    $model = Store::edit($store, $request->validated());
    return Redirect::route('store.index')
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Store modificato") : __("Errore generico")
      );
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\Store  $store
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Store $store)
  {
    Store::deleteModel($store);
    return Redirect::route('store.index')
      ->with('success', __("Store eliminato"));
  }

  /**
   * Change current store
   *
   * @param Request $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function changeCurrentStore(Request $request)
  {
    StoreService::changeStore($request);
    return Redirect::back()
      ->with('success', __("Store corrente modificato"));
  }

  /**
   * Load managers
   *
   * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
   */
  private function loadManagers()
  {
    return HelpersService::forSelects(User::managers(), 'id', 'full_name');
  }
}
