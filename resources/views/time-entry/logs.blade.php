@extends('layouts.master')

@section('pageTitle', 'Time Entry Logs')

@section('body')
<div class="mx-auto flex w-full max-w-7xl flex-col gap-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-normal text-foreground">Time Entry Logs</h1>
            <p class="mt-1 text-sm text-muted-foreground">Successful and failed QR, face, geo, and DTR attempts.</p>
        </div>
        <a href="{{ route('time-entry.index') }}" class="inline-flex h-10 items-center gap-2 rounded-lg border border-border bg-card px-3 text-sm font-semibold text-foreground no-underline transition hover:bg-accent">
            <i data-lucide="scan-face" class="h-4 w-4"></i>
            Time Entry
        </a>
    </div>

    <div class="overflow-hidden rounded-lg border border-border bg-card">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border/40 text-left text-sm">
                <thead class="bg-muted/40 text-xs uppercase text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3">Logged At</th>
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Action</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Geo</th>
                        <th class="px-4 py-3">Score</th>
                        <th class="px-4 py-3">Reason</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/40">
                    @forelse($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ optional($log->logged_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 font-medium text-foreground">{{ $log->employee_id ?? '-' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ ucwords(str_replace('_', ' ', $log->selected_action ?? '-')) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $log->status === 'success' ? 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'bg-red-500/10 text-red-700 dark:text-red-300' }}">{{ $log->status }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">
                                @if($log->latitude && $log->longitude)
                                    {{ $log->latitude }}, {{ $log->longitude }} ({{ $log->accuracy }}m)
                                @else
                                    -
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $log->matched_score ?? '-' }}</td>
                            <td class="min-w-[220px] px-4 py-3 text-muted-foreground">{{ $log->reason }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-muted-foreground">No time entry logs yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-border px-4 py-3">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
