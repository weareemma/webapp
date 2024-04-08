<?php

namespace App\Http\Controllers;

use App\Http\Requests\Discounts\StoreDiscountRequest;
use App\Http\Requests\Discounts\UpdateDiscountRequest;
use App\Models\Discount;
use App\Models\HairService;
use App\Models\Store;
use App\Models\User;
use App\Services\HelpersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class DiscountController extends Controller
{
    private $pagination_number;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->authorizeResource(Discount::class, 'discount');
        $this->pagination_number = config('app.table_pagination_number');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $models = Discount::allModels($request)->paginate($this->pagination_number);
        return Inertia::render('Discounts/Index', [
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
        $model = new Discount();
        return Inertia::render('Discounts/Form', [
            'model' => $model,
            'stores' => $this->loadStores(),
            'users' => $this->loadUsers(),
            'typologies' => $this->loadTypologies(),
            'service_typologies' => $this->loadServiceTypologies(),
            'services' => $this->loadServices(),
            'valid_for' => $this->loadValidForOptions(),
            'service_levels' => $this->loadLevels(),
            'service_types' => $this->loadServiceTypes(),
            'types' => $this->loadTypes()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreDiscountRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreDiscountRequest $request)
    {
        $model = Discount::storeModel($request->validated());
        return Redirect::route('discount.index')
            ->with(
                ($model) ? 'success' : 'error',
                ($model) ? __("Sconto creato") : __("Errore generico")
            );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function show(Discount $discount)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Inertia\Response
     */
    public function edit(Discount $discount)
    {
        return Inertia::render('Discounts/Form', [
            'model' => $discount,
            'stores' => $this->loadStores(),
            'users' => $this->loadUsers(),
            'typologies' => $this->loadTypologies(),
            'service_typologies' => $this->loadServiceTypologies(),
            'services' => $this->loadServices(),
            'valid_for' => $this->loadValidForOptions(),
            'service_levels' => $this->loadLevels(),
            'service_types' => $this->loadServiceTypes(),
            'types' => $this->loadTypes()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateDiscountRequest $request
     * @param \App\Models\Discount $discount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateDiscountRequest $request, Discount $discount)
    {
        $model = Discount::updateModel($discount, $request->validated());
        return Redirect::route('discount.index')
            ->with(
                ($model) ? 'success' : 'error',
                ($model) ? __("Sconto modificato") : __("Errore generico")
            );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Discount  $discount
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Discount $discount)
    {
        Discount::deleteModel($discount);
        return Redirect::route('discount.index')
            ->with('success', __("Sconto eliminato"));
    }

    /**
     * Load discount typologies
     *
     * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadTypologies()
    {
//        dd(HelpersService::forOptions(Discount::TYPOLOGIES));
        return HelpersService::forOptions(Discount::TYPOLOGIES);
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

    /**
     * Load stores list
     *
     * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadUsers()
    {
        return HelpersService::forMultiselects(User::customers()->sortBy('name')->sortBy('surname'), 'id', 'full_name_reverse');
    }

    /**
     * Load services typologies
     *
     * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadServiceTypologies()
    {
        return HelpersService::forSelects(Discount::SERVICES_TYPOLOGIES);
    }

    /**
     * Load services
     *
     * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadServices()
    {
        return HelpersService::forSelects(HairService::allModels()->get(), 'id', 'title');
    }

    /**
     * Load valid for options
     *
     * @return array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadValidForOptions()
    {
        return HelpersService::forOptions(Discount::TARGET_OPTIONS);
    }

    /**
     * Load levels
     *
     * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadLevels()
    {
        return HelpersService::forSelects(HairService::SERVICE_LEVELS);
    }

    /**
     * Load service types
     *
     * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadServiceTypes()
    {
        return HelpersService::forSelects(HairService::SERVICE_TYPES);
    }

    /**
     * Load discount types
     * 
     */
    private function loadTypes()
    {
        return HelpersService::forOptions(Discount::TYPES);
    }

    public function exportCsv()
    {
        return Discount::exportAndDownload();
    }
}
