<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stores\StoreClosingDayRequest;
use App\Http\Requests\Stores\UpdateClosingDayRequest;
use App\Models\ClosingDay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ClosingDayController extends Controller
{
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->authorizeResource(ClosingDay::class, 'closingDay');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    //
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
   * @param StoreClosingDayRequest $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StoreClosingDayRequest $request)
  {
    $model = ClosingDay::storeModel($request->validated());
    return Redirect::route(($model) ? 'store.edit' : 'store.index', ($model) ? $model->store->id : null)
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Slot di chiusura creato") : __("Errore generico")
      );
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\ClosingDay  $closingDay
   * @return \Illuminate\Http\Response
   */
  public function show(ClosingDay $closingDay)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\ClosingDay  $closingDay
   * @return \Illuminate\Http\Response
   */
  public function edit(ClosingDay $closingDay)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\ClosingDay  $closingDay
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateClosingDayRequest $request, ClosingDay $closingDay)
  {
    $model = ClosingDay::updateModel($closingDay, $request->validated());
    return Redirect::route('store.edit', $model->store->id)
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Slot di chiusura modificato") : __("Errore generico")
      );
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\ClosingDay  $closingDay
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(ClosingDay $closingDay)
  {
    ClosingDay::deleteModel($closingDay);
    return Redirect::back()->with('success', __("Slot di chiusura eliminato"));
  }
}
