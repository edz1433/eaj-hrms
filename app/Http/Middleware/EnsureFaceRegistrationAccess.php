<?php

namespace App\Http\Middleware;

use App\Services\TimeEntryService;
use Closure;
use Illuminate\Http\Request;

class EnsureFaceRegistrationAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->guard('web')->user();

        $service = app(TimeEntryService::class);

        if ($service->canRegisterFace($user)) {
            return $next($request);
        }

        $employee = auth()->guard('employee')->user();
        if ($employee && $service->canRegisterFaceForEmployee($employee)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Face registration access is not allowed.'], 403);
        }

        return redirect()->route('time-entry.index')->with('error1', 'Face registration access is not allowed.');
    }
}
