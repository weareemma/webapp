<?php

namespace App\Http\Controllers;

use App\Http\Requests\Stores\StoreOpeningTimeRequest;
use App\Http\Requests\Stores\UpdateOpeningTimeRequest;
use App\Models\OpeningTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class OpeningTimeController extends Controller
{
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->authorizeResource(OpeningTime::class, 'openingTime');
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
  public function store(StoreOpeningTimeRequest $request)
  {
    $model = OpeningTime::storeModel($request->validated());
    return Redirect::route(($model) ? 'store.edit' : 'store.index', ($model) ? $model->store->id : null)
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Orario creato") : __("Errore generico")
      );
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\OpeningTime  $openingTime
   * @return \Illuminate\Http\Response
   */
  public function show(OpeningTime $openingTime)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\OpeningTime  $openingTime
   * @return \Illuminate\Http\Response
   */
  public function edit(OpeningTime $openingTime)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\OpeningTime  $openingTime
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateOpeningTimeRequest $request, OpeningTime $openingTime)
  {
    $model = OpeningTime::updateModel($openingTime, $request->validated());
    return Redirect::route('store.edit', $model->store->id)
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Orario modificato") : __("Errore generico")
      );
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\OpeningTime  $openingTime
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(OpeningTime $openingTime)
  {
    OpeningTime::deleteModel($openingTime);
    return Redirect::back()->with('success', __("Orario eliminato"));
  }
}
