<?php

namespace App\Http\Controllers;

use App\Models\TimeEntryLog;
use App\Services\TimeEntryService;
use Illuminate\Http\Request;

class TimeEntryPageController extends Controller
{
    public function index(TimeEntryService $service)
    {
        return view('time-entry.index', [
            'canRegisterFace' => $service->canRegisterFace(auth()->guard('web')->user()),
        ]);
    }

    public function register()
    {
        return view('time-entry.register');
    }

    public function logs(Request $request)
    {
        $logs = TimeEntryLog::query()
            ->latest('logged_at')
            ->paginate(25);

        return view('time-entry.logs', compact('logs'));
    }
}
