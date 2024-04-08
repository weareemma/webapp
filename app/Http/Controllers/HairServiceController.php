<?php

namespace App\Http\Controllers;

use App\Http\Requests\HairServices\StoreHairServiceRequest;
use App\Http\Requests\HairServices\UpdateHairServiceRequest;
use App\Models\HairService;
use App\Services\HelpersService;
use App\Services\IpraticoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class HairServiceController extends Controller
{
    private $pagination_number;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->authorizeResource(HairService::class, 'hairService');
        $this->pagination_number = config('app.table_pagination_number');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $models = HairService::allModels($request)->paginate($this->pagination_number);
        return Inertia::render('HairServices/Index', [
            'models' => $models,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $model = new HairService();
        return Inertia::render('HairServices/Form', [
            'model' => $model,
            'levels' => $this->loadLevels(),
            'types' => $this->loadTypes()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreHairServiceRequest $request)
    {
        $model = HairService::storeModel($request->validated());
        return Redirect::route('hairService.index')
            ->with(
                ($model) ? 'success' : 'error',
                ($model) ? __("Servizio creato") : __("Errore generico")
            );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HairService  $hairService
     * @return \Illuminate\Http\Response
     */
    public function show(HairService $hairService)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HairService  $hairService
     * @return \Inertia\Response
     */
    public function edit(HairService $hairService)
    {
        return Inertia::render('HairServices/Form', [
            'model' => $hairService,
            'levels' => $this->loadLevels(),
            'types' => $this->loadTypes()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HairService  $hairService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateHairServiceRequest $request, HairService $hairService)
    {
        $model = HairService::updateModel($hairService, $request->validated());
        return Redirect::route('hairService.index')
            ->with(
                ($model) ? 'success' : 'error',
                ($model) ? __("Servizio modificato") : __("Errore generico")
            );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HairService  $hairService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(HairService $hairService)
    {
        HairService::deleteModel($hairService);
        return Redirect::route('hairService.index')
            ->with('success', __("Servizio eliminato"));
    }

    /**
     * Load levels
     *
     * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadLevels()
    {
        return HelpersService::forOptions(HairService::SERVICE_LEVELS);
    }

    /**
     * Load service types
     *
     * @return array|array[]|bool|\Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadTypes()
    {
        return HelpersService::forOptions(HairService::SERVICE_TYPES);
    }

    /**
     * Sync services
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncServices()
    {
        (new IpraticoService())->syncProducts();

        return Redirect::route('hairService.index')
            ->with('success', __("Servizi sincronizzati"));
    }
}
