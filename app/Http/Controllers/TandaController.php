<?php

namespace App\Http\Controllers;

use App\Jobs\UpdateTandaShifts;
use App\Services\JobStatusService;
use App\Services\TandaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class TandaController extends Controller
{
    public function updateShifts()
    {
        UpdateTandaShifts::dispatch();
        JobStatusService::jobDispatch(UpdateTandaShifts::class);
        return response()->json([]);
    }

    public function updateUsers()
    {
        TandaService::updateTandaCodeForAllUsers();
        return Redirect::route('user.index')
            ->with(
                'success',
                __("Utenti aggiornati")
            );
    }

    public function updateStores()
    {
        TandaService::updateTandaCodeForAllStores();
        return Redirect::route('store.index')
            ->with(
                'success',
                __("Store aggiornati")
            );
    }
}
