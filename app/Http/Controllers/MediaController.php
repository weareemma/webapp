<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class MediaController extends Controller
{
    /**
     * Upload media into user temp collection
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadTempFile(Request $request)
    {
        MediaService::uploadTempFile($request);
        return Redirect::back();
    }
}
