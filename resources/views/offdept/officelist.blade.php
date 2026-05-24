@extends('layouts.master')

@section('body')
@php
    $currentRoute = request()->route()->getName();
    $isEdit = $currentRoute === 'officeEdit';
    $formAction = $isEdit ? route('officeUpdate') : route('officeCreate');
    $hasFilters = request()->hasAny(['search', 'staffing']);
    $total = $stats['total'] ?? 0;
    $headPct = $total > 0 ? round((($stats['with_head'] ?? 0) / $total) * 100) : 0;
    $oicPct = $total > 0 ? round((($stats['with_oic'] ?? 0) / $total) * 100) : 0;
@endphp

<div class="flex flex-col gap-5 p-4 sm:p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold tracking-tight text-foreground">Office Management</h1>
                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">
                    {{ number_format($total) }}
                </span>
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
                <i data-lucide="building-2" class="mr-1 inline h-3.5 w-3.5 opacity-60"></i>
                Manage offices, abbreviations, heads, OICs, and employee assignments
            </p>
        </div>

        <button type="button"
            onclick="openOfficeForm()"
            class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-all hover:bg-primary/90 hover:shadow-md sm:shrink-0">
            <i data-lucide="plus" class="h-3.5 w-3.5"></i> Add Office
        </button>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Total Offices</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($total) }}</p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="building-2" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width:100%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-muted-foreground">All office records</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">With Head</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stats['with_head'] ?? 0) }}</p>
                </div>
                <span class="mt-0.5 rounded-lg bg-primary/10 px-2 py-0.5 text-[11px] font-bold text-primary">{{ $headPct }}%</span>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width:{{ $headPct }}%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-muted-foreground">Assigned office heads</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">With OIC</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stats['with_oic'] ?? 0) }}</p>
                </div>
                <span class="mt-0.5 rounded-lg bg-primary/10 px-2 py-0.5 text-[11px] font-bold text-primary">{{ $oicPct }}%</span>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width:{{ $oicPct }}%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-muted-foreground">Officer-in-charge coverage</p>
        </div>

        <div class="relative overflow-hidden rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Employees</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stats['assigned_employees'] ?? 0) }}</p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="users" class="h-4 w-4"></i>
                </div>
            </div>
            <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width:100%"></div>
            </div>
            <p class="mt-1.5 text-[10px] text-muted-foreground">Active employees assigned</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
        <form method="GET" action="{{ route('officeList') }}" id="filter-form">
            <div class="flex flex-col gap-3 border-b border-border/60 px-4 py-3 sm:flex-row sm:items-center sm:flex-wrap">
                <div class="relative flex-1 min-w-[180px] max-w-xs">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-muted-foreground"></i>
                    <input type="text" name="search"
                        value="{{ request('search') }}"
                        placeholder="Office, abbreviation, head..."
                        class="w-full rounded-xl border border-border/60 bg-background py-2 pl-8 pr-3 text-xs text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20">
                </div>

                <div class="flex flex-wrap items-center gap-1">
                    <a href="{{ route('officeList', request()->except(['staffing', 'page'])) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ !request('staffing') ? 'bg-primary text-white shadow-sm' : 'border border-border/60 bg-background text-muted-foreground hover:bg-muted' }}">
                        All <span class="ml-1 opacity-70">{{ number_format($total) }}</span>
                    </a>
                    <a href="{{ route('officeList', array_merge(request()->except(['staffing', 'page']), ['staffing' => 'with_head'])) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ request('staffing') === 'with_head' ? 'bg-primary text-white shadow-sm' : 'border border-border/60 bg-background text-muted-foreground hover:bg-muted' }}">
                        With Head <span class="ml-1 opacity-70">{{ number_format($stats['with_head'] ?? 0) }}</span>
                    </a>
                    <a href="{{ route('officeList', array_merge(request()->except(['staffing', 'page']), ['staffing' => 'without_head'])) }}"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition {{ request('staffing') === 'without_head' ? 'bg-primary text-white shadow-sm' : 'border border-border/60 bg-background text-muted-foreground hover:bg-muted' }}">
                        No Head <span class="ml-1 opacity-70">{{ number_format($stats['without_head'] ?? 0) }}</span>
                    </a>
                </div>

                <div class="ml-auto flex items-center gap-2">
                    @if($hasFilters)
                        <a href="{{ route('officeList') }}"
                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-medium text-muted-foreground transition hover:bg-muted hover:text-foreground">
                            <i data-lucide="x" class="h-3.5 w-3.5"></i> Clear
                        </a>
                    @endif

                    <select name="per_page" onchange="this.form.submit()"
                        class="rounded-xl border border-border/60 bg-background px-2.5 py-2 text-xs text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20">
                        @foreach([10,15,25,50,100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>{{ $size }}/page</option>
                        @endforeach
                    </select>

                    <button type="submit"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-border/60 bg-background text-muted-foreground transition hover:bg-muted hover:text-foreground">
                        <i data-lucide="filter" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-border/60 bg-muted/30 text-left">
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Office</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Head</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">OIC</th>
                        <th class="px-4 py-3 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Employees</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60">
                    @forelse($office as $off)
                        <tr id="tr-{{ $off->id }}" class="transition hover:bg-muted/30">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-sm font-bold text-primary">
                                        {{ \Illuminate\Support\Str::of($off->office_abbr ?: $off->office_name)->substr(0, 3)->upper() }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-foreground">{{ $off->office_name }}</p>
                                        <p class="mt-0.5 text-xs text-muted-foreground">{{ $off->office_abbr ?: 'No abbreviation' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if($off->head)
                                    <p class="text-sm font-medium text-foreground">{{ $off->head->fname }} {{ $off->head->lname }}</p>
                                    <p class="mt-0.5 text-xs text-muted-foreground">{{ $off->head->emp_ID }}</p>
                                @else
                                    <span class="inline-flex rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">Unassigned</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($off->oic)
                                    <p class="text-sm font-medium text-foreground">{{ $off->oic->fname }} {{ $off->oic->lname }}</p>
                                    <p class="mt-0.5 text-xs text-muted-foreground">{{ $off->oic->emp_ID }}</p>
                                @else
                                    <span class="inline-flex rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-muted-foreground">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                                    <i data-lucide="users" class="h-3.5 w-3.5"></i>{{ number_format($off->employee_count) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('officeEdit', $off->id) }}"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-border/60 bg-background text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                        title="Edit office">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                    </a>
                                    <button type="button" value="{{ $off->id }}"
                                        data-name="{{ $off->office_name }}"
                                        class="office-delete inline-flex h-8 w-8 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-400"
                                        title="Delete office">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-muted text-muted-foreground">
                                    <i data-lucide="building-2" class="h-5 w-5"></i>
                                </div>
                                <p class="mt-3 text-sm font-medium text-foreground">No offices found</p>
                                <p class="mt-1 text-xs text-muted-foreground">Try a different search or create a new office.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($office->hasPages() || $office->total() > 0)
        <div class="border-t border-border/60 bg-muted/10 px-4 py-3">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs text-muted-foreground">
                    Showing <span class="font-semibold text-foreground">{{ number_format($office->firstItem() ?? 0) }}</span>
                    to <span class="font-semibold text-foreground">{{ number_format($office->lastItem() ?? 0) }}</span>
                    of <span class="font-semibold text-foreground">{{ number_format($office->total()) }}</span> offices
                </p>
                <div>
                    {{ $office->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div id="office-form-backdrop" class="fixed inset-0 z-50 {{ $isEdit ? 'flex' : 'hidden' }} items-stretch justify-end overflow-hidden p-0" aria-modal="true" role="dialog">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" onclick="closeOfficeForm()"></div>
    <div class="relative h-screen max-h-screen w-full max-w-md overflow-y-auto border-l border-border bg-card shadow-2xl">
        <div class="sticky top-0 z-10 flex items-center justify-between border-b border-border/60 bg-card px-5 py-4">
            <div>
                <h2 class="text-base font-semibold text-foreground">{{ $isEdit ? 'Edit Office' : 'Add Office' }}</h2>
                <p class="mt-0.5 text-xs text-muted-foreground">Set office details and assignments.</p>
            </div>
            <button type="button" onclick="closeOfficeForm()"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-muted hover:text-foreground">
                <i data-lucide="x" class="h-4 w-4"></i>
            </button>
        </div>

        <form action="{{ $formAction }}" method="POST" class="space-y-5 p-5">
            @csrf
            <input type="hidden" name="oid" value="{{ $isEdit ? $offEdit->id : '' }}">

            <div>
                <label class="text-xs font-semibold text-muted-foreground">Office Name</label>
                <input type="text" name="OfficeName" required value="{{ $isEdit ? $offEdit->office_name : old('OfficeName') }}"
                    autocomplete="off"
                    class="mt-1.5 w-full rounded-xl border border-border/60 bg-background px-3 py-2.5 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <div>
                <label class="text-xs font-semibold text-muted-foreground">Office Abbreviation</label>
                <input type="text" name="OfficeAbbreviation" required value="{{ $isEdit ? $offEdit->office_abbr : old('OfficeAbbreviation') }}"
                    autocomplete="off"
                    class="mt-1.5 w-full rounded-xl border border-border/60 bg-background px-3 py-2.5 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20">
            </div>

            <div>
                <label class="text-xs font-semibold text-muted-foreground">Office Head</label>
                <select name="office_head_id" data-placeholder="Search office head"
                    class="mt-1.5 w-full rounded-xl border border-border/60 bg-background px-3 py-2.5 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">Select employee</option>
                    @foreach($employee as $emp)
                        <option value="{{ $emp->id }}" @selected(($isEdit ? $offEdit->office_head_id : old('office_head_id')) == $emp->id)>
                            {{ $emp->emp_ID }} - {{ $emp->lname }}, {{ $emp->fname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="text-xs font-semibold text-muted-foreground">OIC</label>
                <select name="oic_id" data-placeholder="Search OIC"
                    class="mt-1.5 w-full rounded-xl border border-border/60 bg-background px-3 py-2.5 text-sm text-foreground focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20">
                    <option value="">Select employee</option>
                    @foreach($employee as $emp)
                        <option value="{{ $emp->id }}" @selected(($isEdit ? $offEdit->oic_id : old('oic_id')) == $emp->id)>
                            {{ $emp->emp_ID }} - {{ $emp->lname }}, {{ $emp->fname }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end gap-2 border-t border-border/60 pt-5">
                <a href="{{ route('officeList') }}"
                    class="inline-flex items-center rounded-xl border border-border/60 px-4 py-2 text-sm font-medium text-foreground transition hover:bg-muted">
                    Cancel
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary/90">
                    <i data-lucide="save" class="h-4 w-4"></i> Save
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openOfficeForm() {
    document.getElementById('office-form-backdrop').classList.replace('hidden', 'flex');
}

function closeOfficeForm() {
    window.location.href = '{{ route('officeList') }}';
}

$(document).on('click', '.office-delete', function() {
    const button = $(this);
    const id = button.val();
    const name = button.data('name') || 'this office';
    let url = "{{ route('officeDelete', ['id' => ':id']) }}".replace(':id', id);

    Swal.fire({
        title: 'Delete office?',
        text: `This will permanently delete ${name}.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Delete'
    }).then((result) => {
        if (!result.isConfirmed) return;

        $.ajax({
            type: 'GET',
            url: url,
            success: function () {
                $('#tr-' + id).fadeOut(200, function() {
                    $(this).remove();
                });
                Swal.fire({
                    title: 'Deleted',
                    text: 'Office deleted successfully.',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 1200
                });
            }
        });
    });
});
</script>
@endpush
