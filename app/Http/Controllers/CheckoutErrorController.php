<?php

namespace App\Http\Controllers;

use App\Models\CheckoutError;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class CheckoutErrorController extends Controller
{
    /**
     * Index
     * 
     */
    public function index() {

        $errors = CheckoutError::query()
            ->with(['user', 'order', 'store'])
            ->where('resolved', false)
            ->paginate(10);

        return Inertia::render('CheckoutError/Index', [
            'errors' => $errors
        ]);
    }

    /**
     *Mark as resolved
     * 
     */
    public function markAsResolved(CheckoutError $checkoutError)
    {
        $checkoutError->update([
            'resolved' => true
        ]);

        return Redirect::route('checkoutError.index')
            ->with('success', __("Risolto"));
    }
}
