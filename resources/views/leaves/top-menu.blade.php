@php
    $isLeaveManagement = ($leaveMode ?? ($guard == 'web' ? 'management' : 'personal')) === 'management';
    $leaveCreditsRoute = $isLeaveManagement ? route('leavesRead', $employee->id) : route('leavesReadEmp');
    $statusRoute = $isLeaveManagement ? route('leaveStatus', $employee->id) : route('leaveStatus');
    $historyRoute = $isLeaveManagement ? route('historyRead', $employee->id) : route('historyRead');

    $isLeaveCreditsActive = request()->is('leave') || request()->is('leaves*');
    $isStatusActive = request()->is('leave/status') || request()->is('leave/status/*') || request()->is('leaves/status*');
    $isHistoryActive = request()->is('leave/history*') || request()->is('leaves/history');

    $applicationCount = 0;
    $leaveCreditCount = isset($leaves) && method_exists($leaves, 'total') ? $leaves->total() : count($leaves ?? []);
    $statusCount = isset($leavesapp) && is_countable($leavesapp) ? count($leavesapp) : 0;
    $historyCount = isset($leavehistory) && is_countable($leavehistory) ? count($leavehistory) : 0;
    $firstTabLabel = $isLeaveManagement ? 'Leave Credits' : 'Leave Application';
    $firstTabIcon = $isLeaveManagement ? 'wallet-cards' : 'file-pen-line';
    $tabBase = 'group flex min-h-11 items-center justify-between gap-3 rounded-lg px-3.5 py-2 text-sm font-semibold no-underline transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/30';
    $tabOn = 'bg-primary text-primary-foreground shadow-sm hover:bg-primary/90';
    $tabOff = 'border border-border/60 bg-background text-muted-foreground hover:border-primary/30 hover:bg-muted/60 hover:text-foreground';
    $badgeBase = 'inline-flex min-w-6 shrink-0 items-center justify-center rounded-full px-2 py-0.5 text-[11px] font-bold tabular-nums transition';
    $badgeOn = 'bg-primary-foreground/20 text-primary-foreground';
    $badgeOff = 'bg-muted text-muted-foreground group-hover:bg-primary/10 group-hover:text-primary';
@endphp

<div class="rounded-xl border border-border/60 bg-muted/30 p-1">
<div class="grid gap-1 sm:grid-cols-3">
    <a href="{{ $leaveCreditsRoute }}" class="{{ $tabBase }} {{ $isLeaveCreditsActive ? $tabOn : $tabOff }}" aria-current="{{ $isLeaveCreditsActive ? 'page' : 'false' }}">
        <span class="inline-flex min-w-0 items-center gap-2">
            <i data-lucide="{{ $firstTabIcon }}" class="h-4 w-4 shrink-0"></i>
            <span class="truncate">{{ $firstTabLabel }}</span>
        </span>
        <span class="{{ $badgeBase }} {{ $isLeaveCreditsActive ? $badgeOn : $badgeOff }}">{{ $isLeaveManagement ? $leaveCreditCount : $applicationCount }}</span>
    </a>
    <a href="{{ $statusRoute }}" class="{{ $tabBase }} {{ $isStatusActive ? $tabOn : $tabOff }}" aria-current="{{ $isStatusActive ? 'page' : 'false' }}">
        <span class="inline-flex min-w-0 items-center gap-2">
            <i data-lucide="stamp" class="h-4 w-4 shrink-0"></i>
            <span class="truncate">Status</span>
        </span>
        <span class="{{ $badgeBase }} {{ $isStatusActive ? $badgeOn : $badgeOff }}">{{ $statusCount }}</span>
    </a>
    <a href="{{ $historyRoute }}" class="{{ $tabBase }} {{ $isHistoryActive ? $tabOn : $tabOff }}" aria-current="{{ $isHistoryActive ? 'page' : 'false' }}">
        <span class="inline-flex min-w-0 items-center gap-2">
            <i data-lucide="history" class="h-4 w-4 shrink-0"></i>
            <span class="truncate">History</span>
        </span>
        <span class="{{ $badgeBase }} {{ $isHistoryActive ? $badgeOn : $badgeOff }}">{{ $historyCount }}</span>
    </a>
</div>
</div>
