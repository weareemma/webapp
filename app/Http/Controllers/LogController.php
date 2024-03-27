<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LogController extends Controller
{
    /**
     * Index
     *
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        $logs = Log::allLogs($request)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Logs/Index', [
            'logs' => $logs
        ]);
    }
}
