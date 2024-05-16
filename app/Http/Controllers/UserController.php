<?php

namespace App\Http\Controllers;

use App\Http\Requests\FiscalFile\StoreFiscalFileRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\HairService;
use App\Models\Store;
use App\Models\User;
use App\Services\HelpersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $users = User::allUsers($request)
            ->role([User::ROLE_ADMIN, User::ROLE_STYLIST, User::ROLE_MANAGER, User::ROLE_OPERATOR])
            ->paginate(10);
        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $model = new User();
        return Inertia::render('Users/Form', [
            'user' => $model,
            'stores' => $this->loadStores()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreUserRequest $request)
    {
        $model = User::store($request->validated());
        $back_url = ($model && $model->isCustomer()) ? 'customer.index' : 'user.index';
        return Redirect::route($back_url)
            ->with(
                ($model) ? 'success' : 'error',
                ($model) ? __("Utente creato") : __("Errore generico")
            );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Inertia\Response
     */
    public function edit(User $user)
    {

        $massage = [];
        $treatment = [];
        $updo = [];
        $hair_service = [];

        foreach(HairService::where("type", "massage")->where("level", "addon")->where("active", 1)->get() as $service){
            $massage[] = [
                "id" => $service->id,
                "title" => $service->title,
                "selected" => ($user->hairServices()->where('hair_services.id', $service->id)->count())?true:false,
            ];
            if($user->hairServices()->where('hair_services.id', $service->id)->count()){
                $hair_service[] = $service->id;
            }
        }
        foreach(HairService::where("type", "treatment")->where("level", "addon")->where("active", 1)->get() as $service){
            $treatment[] = [
                "id" => $service->id,
                "title" => $service->title,
                "selected" => ($user->hairServices()->where('hair_services.id', $service->id)->count())?true:false,
            ];
            if($user->hairServices()->where('hair_services.id', $service->id)->count()){
                $hair_service[] = $service->id;
            }
        }
        foreach(HairService::where("type", "updo")->where("level", "addon")->where("active", 1)->get() as $service){
            $updo[] = [
                "id" => $service->id,
                "title" => $service->title,
                "selected" => ($user->hairServices()->where('hair_services.id', $service->id)->count())?true:false,
            ];
            if($user->hairServices()->where('hair_services.id', $service->id)->count()){
                $hair_service[] = $service->id;
            }
        }

        $dataReturn = [
            'user' => $user,
            'stores' => $this->loadStores(),
            'addOns' => [
                "massage" => $massage,
                "treatment" => $treatment,
                "updo" => $updo,
            ],
            'hairService' => $hair_service
        ];
        return Inertia::render('Users/Form', $dataReturn);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUserRequest $request
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $model = User::edit($user, $request->validated());
        return Redirect::route('user.edit', $user->id)
            ->with(
                ($model) ? 'success' : 'error',
                ($model) ? __("Utente modificato") : __("Errore generico")
            );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        User::deleteModel($user);
        return Redirect::route('user.index')
            ->with('success', __("Utente eliminato"));
    }

    /**
     * Load stores
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function loadStores()
    {
        return HelpersService::forSelects(Store::allStores()->get(), 'id', 'name');
    }

    /**
     * Impersonate.
     *
     */
    public function impersonate(User $user)
    {
        Auth::user()->impersonate($user);
        Session::put('impersonate', true);
        return Redirect::route('home');
    }

    /**
     * Leave impersonation.
     *
     */
    public function leave()
    {
        if (Auth::user())
        {
            Auth::user()->leaveImpersonation();
        }
        Session::remove('impersonate');
        return Redirect::route('home');
    }

    /**
     * Update fiscal file
     *
     * @param StoreFiscalFileRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateFiscalFile(StoreFiscalFileRequest $request)
    {
        User::updateFiscalFile(Auth::user(), $request->validated());
        return Redirect::back();
    }

    /**
     * Export csv customers list
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportCsv(Request $request)
    {
        return User::exportAndDownload($request->toArray());
    }

    /**
     * Save customer notes
     *
     */
    public function saveCustomerNotes(User $user, Request $request)
    {
        if ($request->has('notes'))
        {
            $user->last_notes = $request->notes;
            $user->last_notes_updated_at = now();
            $user->last_notes_by_id = Auth::id();
            $user->saveQuietly();
        }

        return;
    }
}
