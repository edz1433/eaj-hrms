@php
    $items = [
        ['route' => 'dtr-read', 'pattern' => 'dtr', 'icon' => 'calendar-clock', 'label' => 'DTR', 'hint' => 'Printable time record'],
        ['route' => 'dtrLogs', 'pattern' => 'dtr/dtr-logs', 'icon' => 'file-clock', 'label' => 'Logs', 'hint' => 'Raw time entries'],
    ];
@endphp

<div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
    <div class="flex items-center gap-2 border-b border-border/60 px-4 py-3">
        <i data-lucide="clock-3" class="h-3.5 w-3.5 text-primary/70"></i>
        <span class="text-[11px] font-bold uppercase tracking-wider text-foreground">Timekeeping</span>
    </div>

    <nav class="space-y-1 p-1.5">
        @foreach($items as $item)
            @php $active = request()->is($item['pattern']); @endphp
            <a href="{{ route($item['route']) }}"
                class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs transition-colors
                    {{ $active ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                    {{ $active ? 'bg-primary text-primary-foreground' : 'bg-muted/70 text-muted-foreground group-hover:text-foreground' }}">
                    <i data-lucide="{{ $item['icon'] }}" class="h-4 w-4"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block font-semibold">{{ $item['label'] }}</span>
                    <span class="block truncate text-[10px] opacity-70">{{ $item['hint'] }}</span>
                </span>
            </a>
        @endforeach
    </nav>
</div>
