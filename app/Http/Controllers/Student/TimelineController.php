<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StudentTimelineService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function index(Request $request): View
    {
        $service = app(StudentTimelineService::class);
        $allEvents = $service->forUser(auth()->user());

        $filters = $request->only(['school_year', 'term', 'category', 'from', 'to']);
        $events = $service->applyFilters($allEvents, $filters, $request->string('q'));

        return view('student.timeline', [
            'events' => $events,
            'options' => $service->filterOptions($allEvents),
            'activeFilters' => $filters,
            'search' => (string) $request->string('q'),
        ]);
    }
}
