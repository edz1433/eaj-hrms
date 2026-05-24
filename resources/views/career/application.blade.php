@extends('layouts.master')

@section('body')
@php
    $statusLabels = [
        0 => 'Submitted',
        1 => 'Reviewing',
        2 => 'Ready for Interview',
        3 => 'Disqualified',
        4 => 'Qualified Not Selected',
        5 => 'Top 5 / Testing',
        6 => 'Not Hired',
        7 => 'Hired',
    ];
    $statusClasses = [
        0 => 'bg-muted text-muted-foreground',
        1 => 'bg-sky-50 text-sky-700 dark:bg-sky-950/30 dark:text-sky-300',
        2 => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300',
        3 => 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300',
        4 => 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-300',
        5 => 'bg-primary/10 text-primary',
        6 => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-900/50 dark:text-zinc-300',
        7 => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200',
    ];
    $total = $applications->count();
    $reviewing = $applications->where('status', 1)->count();
    $interview = $applications->where('status', 2)->count();
    $hired = $applications->where('status', 7)->count();
@endphp

<div class="flex flex-col gap-5 p-4 sm:p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold tracking-tight text-foreground">Job Applications</h1>
                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ number_format($total) }}</span>
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
                <i data-lucide="clipboard-list" class="mr-1 inline h-3.5 w-3.5 opacity-60"></i>
                Review submitted applications and move candidates through the hiring stages.
            </p>
        </div>
        <a href="{{ route('jlist') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-border/60 bg-card px-3.5 py-2 text-xs font-medium text-foreground shadow-sm transition hover:border-border hover:shadow-md">
            <i data-lucide="briefcase-business" class="h-3.5 w-3.5 opacity-70"></i> Job Postings
        </a>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Total Applications', 'value' => $total, 'icon' => 'inbox'],
            ['label' => 'Reviewing', 'value' => $reviewing, 'icon' => 'scan-search'],
            ['label' => 'Interview Ready', 'value' => $interview, 'icon' => 'calendar-check'],
            ['label' => 'Hired', 'value' => $hired, 'icon' => 'user-check'],
        ] as $stat)
        <div class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">{{ $stat['label'] }}</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stat['value']) }}</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="{{ $stat['icon'] }}" class="h-4 w-4"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
        <div class="border-b border-border/60 px-5 py-4">
            <h2 class="text-sm font-semibold text-foreground">Applicant Pipeline</h2>
            <p class="mt-0.5 text-xs text-muted-foreground">Control numbers unlock file access and move applicants into review.</p>
        </div>
        <div class="overflow-x-auto">
            <table id="example1" class="min-w-full divide-y divide-border/60 text-sm">
                <thead class="bg-muted/40">
                    <tr class="text-left text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                        <th class="px-4 py-3">Applicant</th>
                        <th class="px-4 py-3">Position</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Files</th>
                        <th class="px-4 py-3">Applied</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60">
                    @forelse($applications as $app)
                    <tr id="tr-{{ $app->id }}" class="transition hover:bg-muted/30">
                        <td class="px-4 py-3">
                            <p class="font-semibold text-foreground">{{ $app->first_name }} {{ $app->middle_name }} {{ $app->last_name }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-1.5 text-[11px] text-muted-foreground">
                                <span class="rounded-md bg-muted px-1.5 py-0.5 font-mono">{{ $app->app_number }}</span>
                                <span>{{ ucfirst($app->sex) }}</span>
                                @if($app->ctrl_no)
                                <span class="rounded-md bg-primary/10 px-1.5 py-0.5 font-mono text-primary">{{ $app->ctrl_no }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="max-w-[220px] px-4 py-3 text-xs text-muted-foreground">{{ $app->position }}</td>
                        <td class="px-4 py-3 text-xs text-muted-foreground">
                            <p>{{ $app->mobile }}</p>
                            <p class="mt-0.5">{{ $app->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if(empty($app->ctrl_no))
                                <button type="button" class="set-ctrl inline-flex items-center gap-1.5 rounded-xl border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-100" value="{{ $app->id }}" data-toggle="modal" data-target="#setCtrlModal">
                                    <i data-lucide="key-round" class="h-3.5 w-3.5"></i> Set Control No.
                                </button>
                            @else
                                <div class="flex flex-wrap gap-1">
                                    @foreach([
                                        'pds' => ['PDS', 'file-text'],
                                        'wes' => ['WES', 'briefcase-business'],
                                        'intent' => ['Intent', 'mail-open'],
                                        'resume' => ['Resume', 'user'],
                                        'tor' => ['TOR', 'graduation-cap'],
                                    ] as $field => [$label, $icon])
                                        @if(!empty($app->{$field}))
                                        <a href="{{ asset('storage/' . $app->{$field}) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg border border-border/60 px-2 py-1 text-[11px] font-medium text-foreground transition hover:bg-muted">
                                            <i data-lucide="{{ $icon }}" class="h-3 w-3"></i>{{ $label }}
                                        </a>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs font-medium text-muted-foreground">{{ strtoupper($app->created_at->format('M. d, Y h:i A')) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full {{ $statusClasses[$app->status] ?? 'bg-muted text-muted-foreground' }} px-2.5 py-0.5 text-[11px] font-semibold">
                                {{ $statusLabels[$app->status] ?? 'Unknown' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                @if($app->status == 1)
                                    <button type="button" class="q-btn rounded-lg p-2 text-emerald-600 transition hover:bg-emerald-50 dark:hover:bg-emerald-950/40" data-app-id="{{ $app->id }}" data-toggle="modal" data-target="#qualifyModal" title="Set interview">
                                        <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                    </button>
                                    <button type="button" class="dq-btn rounded-lg p-2 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-950/40" data-app-id="{{ $app->id }}" data-toggle="modal" data-target="#dqModal" title="Disqualify">
                                        <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                    </button>
                                @elseif($app->status == 2)
                                    <form method="POST" action="{{ route('updateStatus') }}" class="inline">@csrf<input type="hidden" name="id" value="{{ $app->id }}"><input type="hidden" name="status" value="4"><button type="submit" class="rounded-lg p-2 text-amber-600 transition hover:bg-amber-50" title="Not selected"><i data-lucide="user-clock" class="h-3.5 w-3.5"></i></button></form>
                                    <form method="POST" action="{{ route('updateStatus') }}" class="inline">@csrf<input type="hidden" name="id" value="{{ $app->id }}"><input type="hidden" name="status" value="5"><button type="submit" class="rounded-lg p-2 text-primary transition hover:bg-primary/10" title="Move to next stage"><i data-lucide="arrow-right" class="h-3.5 w-3.5"></i></button></form>
                                @elseif($app->status == 5)
                                    <form method="POST" action="{{ route('updateStatus') }}" class="inline">@csrf<input type="hidden" name="id" value="{{ $app->id }}"><input type="hidden" name="status" value="6"><button type="submit" class="rounded-lg p-2 text-zinc-600 transition hover:bg-muted" title="Not hired"><i data-lucide="user-x" class="h-3.5 w-3.5"></i></button></form>
                                    <form method="POST" action="{{ route('updateStatus') }}" class="inline">@csrf<input type="hidden" name="id" value="{{ $app->id }}"><input type="hidden" name="status" value="7"><button type="submit" class="rounded-lg p-2 text-emerald-600 transition hover:bg-emerald-50" title="Hired"><i data-lucide="user-check" class="h-3.5 w-3.5"></i></button></form>
                                @else
                                    <span class="rounded-lg p-2 text-muted-foreground/30"><i data-lucide="ban" class="h-3.5 w-3.5"></i></span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-sm text-muted-foreground">No applications yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="setCtrlModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <form method="POST" action="{{ route('setCtrlNo') }}">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Set Control Number</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="ctrlAppId">
          <label class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Control Number</label>
          <input type="text" name="ctrl_no" class="form-control" placeholder="Enter control number" autocomplete="off" required>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-warning">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="qualifyModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <form method="POST" action="{{ route('updateStatus') }}">
        @csrf
        <input type="hidden" name="id" id="qualifyAppId">
        <input type="hidden" name="status" value="2">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Set Interview Schedule</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Interview Schedule</label>
            <input type="datetime-local" name="interview_datetime" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Venue</label>
            <textarea name="venue" class="form-control" rows="2" required>Conference Room, Admin Building/Bidding Room/Accreditation/ Mini Hotel</textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Confirm & Qualify</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="dqModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow-lg">
      <form method="POST" action="{{ route('updateStatus') }}">
        @csrf
        <input type="hidden" name="id" id="dqAppId">
        <input type="hidden" name="status" value="3">
        <div class="modal-header">
          <h5 class="modal-title font-weight-bold">Disqualify Applicant</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <label>Reason for Disqualification</label>
          <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason..." required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Confirm Disqualification</button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.set-ctrl').forEach(btn => {
        btn.addEventListener('click', () => document.getElementById('ctrlAppId').value = btn.value);
    });
    document.querySelectorAll('.q-btn').forEach(btn => {
        btn.addEventListener('click', () => document.getElementById('qualifyAppId').value = btn.dataset.appId);
    });
    document.querySelectorAll('.dq-btn').forEach(btn => {
        btn.addEventListener('click', () => document.getElementById('dqAppId').value = btn.dataset.appId);
    });
});
</script>
@endpush
@endsection
