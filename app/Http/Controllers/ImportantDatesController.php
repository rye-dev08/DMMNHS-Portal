<?php

namespace App\Http\Controllers;

use App\Services\ImportantDatesService;
use Illuminate\View\View;

class ImportantDatesController extends Controller
{
    /**
     * "View All" page for the Important Dates widget. Role-aware: the
     * aggregation service returns only dates relevant to the signed-in user.
     */
    public function index(): View
    {
        $items = app(ImportantDatesService::class)->forUser(auth()->user());

        return view('important-dates.index', [
            'items' => $items,
        ]);
    }
}
