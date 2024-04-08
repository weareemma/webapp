<?php

namespace App\Http\Controllers;

use App\Http\Requests\Packages\StorePackageRequest;
use App\Http\Requests\Packages\UpdatePackageRequest;
use App\Models\HairService;
use App\Models\Package;
use App\Models\Store;
use App\Services\HelpersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PackageController extends Controller
{
  private $pagination_number;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->authorizeResource(Package::class, 'package');
    $this->pagination_number = config('app.table_pagination_number');
  }

  /**
   * Display a listing of the resource.
   *
   * @return \Inertia\Response
   */
  public function index(Request $request)
  {
    $models = Package::allModels($request)->paginate($this->pagination_number);
    return Inertia::render('Packages/Index', [
      'models' => $models
    ]);
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Inertia\Response
   */
  public function create()
  {
    $model = new Package();
    return Inertia::render('Packages/Form', [
      'model' => $model,
      'services' => $this->loadServices(),
      'stores' => $this->loadStores()
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param StorePackageRequest $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StorePackageRequest $request)
  {
    $model = Package::storeModel($request->validated());
    return Redirect::route('package.index')
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Pacchetto creato") : __("Errore generico")
      );
  }

  /**
   * Display the specified resource.
   *
   * @param  \App\Models\Package  $package
   * @return \Illuminate\Http\Response
   */
  public function show(Package $package)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\Package  $package
   * @return \Inertia\Response
   */
  public function edit(Package $package)
  {
    return Inertia::render('Packages/Form', [
      'model' => $package,
      'services' => $this->loadServices(),
      'stores' => $this->loadStores()
    ]);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param UpdatePackageRequest $request
   * @param \App\Models\Package $package
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdatePackageRequest $request, Package $package)
  {
    $model = Package::updateModel($package, $request->validated());
    return Redirect::route('package.index')
      ->with(
        ($model) ? 'success' : 'error',
        ($model) ? __("Pacchetto modificato") : __("Errore generico")
      );
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\Package  $package
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Package $package)
  {
    Package::deleteModel($package);
    return Redirect::route('package.index')
      ->with('success', __("Pacchetto eliminato"));
  }

  /**
   * Load hair services list
   *
   * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
   */
  private function loadServices()
  {
    return HelpersService::forSelects(HairService::allModels()->get(), 'id', 'title');
  }

  /**
   * Load stores list
   *
   * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
   */
  private function loadStores()
  {
    return HelpersService::forSelects(Store::allStores()->get(), 'id', 'name');
  }
}
