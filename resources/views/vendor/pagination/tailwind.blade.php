@if ($paginator->hasPages())
<nav class="flex items-center justify-between" aria-label="Pagination">

    {{-- Left: showing info + per-page --}}
    <div class="flex items-center gap-3">
        <p class="text-xs text-muted-foreground">
            Showing
            <span class="font-semibold text-foreground">{{ $paginator->firstItem() }}</span>
            –
            <span class="font-semibold text-foreground">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-semibold text-foreground">{{ number_format($paginator->total()) }}</span>
        </p>

        <form method="GET" id="per-page-form" class="flex items-center">
            @foreach(request()->except('page', 'per_page') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach
            <select name="per_page"
                    onchange="document.getElementById('per-page-form').submit()"
                    class="rounded-lg border border-border/60 bg-background px-2 py-1 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 cursor-pointer">
                @foreach([10, 15, 25, 50] as $n)
                    <option value="{{ $n }}" {{ request('per_page', 15) == $n ? 'selected' : '' }}>
                        {{ $n }} / page
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Right: page buttons --}}
    <div class="flex items-center gap-1">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground/30 cursor-not-allowed">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border/60 bg-card text-muted-foreground transition hover:bg-muted hover:text-foreground">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex h-8 items-center px-1 text-xs text-muted-foreground select-none">…</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-xs font-semibold text-white shadow-sm">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border/60 bg-card text-xs text-muted-foreground transition hover:bg-muted hover:text-foreground">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
               class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border/60 bg-card text-muted-foreground transition hover:bg-muted hover:text-foreground">
                <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
        @else
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground/30 cursor-not-allowed">
                <i class="fas fa-chevron-right text-[10px]"></i>
            </span>
        @endif

    </div>
</nav>
@endif
