@if ($paginator->hasPages())
<nav class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="Pagination">
    <div class="flex flex-wrap items-center gap-3">
        <p class="text-xs text-muted-foreground">
            Showing
            <span class="font-semibold text-foreground">{{ $paginator->firstItem() }}</span>
            -
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
                class="cursor-pointer rounded-lg border border-border/60 bg-background px-2 py-1 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                @foreach([10, 15, 25, 50, 100] as $n)
                    <option value="{{ $n }}" {{ request('per_page', 10) == $n ? 'selected' : '' }}>
                        {{ $n }} / page
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="flex flex-wrap items-center gap-1">
        @if ($paginator->onFirstPage())
            <span class="inline-flex h-8 cursor-not-allowed items-center gap-1.5 rounded-lg border border-border/60 bg-muted/30 px-3 text-xs font-medium text-muted-foreground/60">
                <i data-lucide="chevron-left" class="h-3.5 w-3.5"></i>
                Back
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-border/60 bg-card px-3 text-xs font-medium text-foreground transition hover:bg-muted">
                <i data-lucide="chevron-left" class="h-3.5 w-3.5"></i>
                Back
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex h-8 select-none items-center px-1 text-xs text-muted-foreground">...</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-xs font-semibold text-white shadow-sm">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border/60 bg-card text-xs font-medium text-foreground transition hover:bg-muted">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-border/60 bg-card px-3 text-xs font-medium text-foreground transition hover:bg-muted">
                Next
                <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
            </a>
        @else
            <span class="inline-flex h-8 cursor-not-allowed items-center gap-1.5 rounded-lg border border-border/60 bg-muted/30 px-3 text-xs font-medium text-muted-foreground/60">
                Next
                <i data-lucide="chevron-right" class="h-3.5 w-3.5"></i>
            </span>
        @endif
    </div>
</nav>
@endif
