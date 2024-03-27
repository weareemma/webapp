<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stores\StoreExceptionalTimeRequest;
use App\Http\Requests\Stores\UpdateExceptionalTimeRequest;
use App\Models\ExceptionalTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ExceptionalTimeController extends Controller
{
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->authorizeResource(ExceptionalTime::class, 'exceptionalTime');
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
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StoreExceptionalTimeRequest $request)
  {
    $model = ExceptionalTime::storeModel($request->validated());
    return Redirect::route(($model) ? 'store.edit' : 'store.index', ($model) ? $model->store->id : null)
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Orario creato") : __("Errore generico")
      );
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\ExceptionalTime  $exceptionalTime
   * @return \Illuminate\Http\Response
   */
  public function show(ExceptionalTime $exceptionalTime)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\ExceptionalTime  $exceptionalTime
   * @return \Inertia\Response
   */
  public function edit(ExceptionalTime $exceptionalTime)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\ExceptionalTime  $exceptionalTime
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateExceptionalTimeRequest $request, ExceptionalTime $exceptionalTime)
  {
    $model = ExceptionalTime::edit($exceptionalTime, $request->validated());
    return Redirect::route('store.edit', $model->store->id)
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Orario modificato") : __("Errore generico")
      );
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\ExceptionalTime  $exceptionalTime
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(ExceptionalTime $exceptionalTime)
  {
    ExceptionalTime::deleteModel($exceptionalTime);
    return Redirect::back()->with('success', __("Orario eliminato"));
  }
}
