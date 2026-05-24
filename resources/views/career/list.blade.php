@extends('layouts.master')

@section('body')
@php
    $currentRoute = request()->route()->getName();
    $isEdit = $currentRoute === 'jEdit' && isset($jEdit);
    $totalJobs = $jobs->count();
    $openJobs = $jobs->where('status', 'Open')->count();
    $teachingJobs = $jobs->where('type', 2)->count();
    $nonTeachingJobs = $jobs->where('type', 1)->count();
    $inputCls = 'w-full rounded-xl border border-border/60 bg-background px-3 py-2 text-xs text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20';
    $labelCls = 'text-[11px] font-semibold uppercase tracking-wider text-muted-foreground';
@endphp

<div class="flex flex-col gap-5 p-4 sm:p-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold tracking-tight text-foreground">Recruitment</h1>
                <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ number_format($totalJobs) }}</span>
            </div>
            <p class="mt-1 text-xs text-muted-foreground">
                <i data-lucide="briefcase-business" class="mr-1 inline h-3.5 w-3.5 opacity-60"></i>
                Manage job postings and hiring availability.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('appList') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-border/60 bg-card px-3.5 py-2 text-xs font-medium text-foreground shadow-sm transition hover:border-border hover:shadow-md">
                <i data-lucide="clipboard-list" class="h-3.5 w-3.5 opacity-70"></i> Applications
            </a>
            @if($isEdit)
            <a href="{{ route('jlist') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-border/60 bg-card px-3.5 py-2 text-xs font-medium text-foreground shadow-sm transition hover:border-border hover:shadow-md">
                <i data-lucide="plus" class="h-3.5 w-3.5 opacity-70"></i> New Posting
            </a>
            @endif
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['label' => 'Total Postings', 'value' => $totalJobs, 'icon' => 'briefcase-business'],
            ['label' => 'Open', 'value' => $openJobs, 'icon' => 'door-open'],
            ['label' => 'Teaching', 'value' => $teachingJobs, 'icon' => 'graduation-cap'],
            ['label' => 'Non-Teaching', 'value' => $nonTeachingJobs, 'icon' => 'building-2'],
        ] as $stat)
        <div class="rounded-2xl border border-border/60 bg-card p-4 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="{{ $labelCls }}">{{ $stat['label'] }}</p>
                    <p class="mt-1 text-3xl font-bold text-foreground tabular-nums">{{ number_format($stat['value']) }}</p>
                </div>
                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                    <i data-lucide="{{ $stat['icon'] }}" class="h-4 w-4"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="grid gap-4 xl:grid-cols-[360px_minmax(0,1fr)]">
        <form class="rounded-2xl border border-border/60 bg-card shadow-sm" action="{{ $isEdit ? route('jUpdate') : route('jCreate') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $isEdit ? $jEdit->id : '' }}">
            <div class="border-b border-border/60 px-5 py-4">
                <h2 class="text-sm font-semibold text-foreground">{{ $isEdit ? 'Edit Job Posting' : 'Add Job Posting' }}</h2>
                <p class="mt-0.5 text-xs text-muted-foreground">Postings appear in the career application flow.</p>
            </div>
            <div class="space-y-3 p-5">
                @if($errors->any())
                    <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label class="{{ $labelCls }}">Position Title</label>
                    <input type="text" name="title" value="{{ old('title', $isEdit ? $jEdit->title : '') }}" placeholder="Enter job title" class="{{ $inputCls }}" required>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label class="{{ $labelCls }}">Job Type</label>
                        <select name="type" class="{{ $inputCls }}" required>
                            <option value="">Select type</option>
                            <option value="1" {{ old('type', $isEdit ? $jEdit->type : '') == 1 ? 'selected' : '' }}>Non-Teaching</option>
                            <option value="2" {{ old('type', $isEdit ? $jEdit->type : '') == 2 ? 'selected' : '' }}>Teaching</option>
                        </select>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Status</label>
                        <select name="status" class="{{ $inputCls }}" required>
                            @foreach(['Open', 'Closed'] as $status)
                            <option value="{{ $status }}" {{ old('status', $isEdit ? $jEdit->status : 'Open') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label class="{{ $labelCls }}">Plantilla Item No.</label>
                        <input type="text" name="plantilla_item_no" value="{{ old('plantilla_item_no', $isEdit ? $jEdit->plantilla_item_no : '') }}" placeholder="Item no." class="{{ $inputCls }}" required>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Salary</label>
                        <input type="number" step="0.01" name="salary" value="{{ old('salary', $isEdit ? $jEdit->salary : '') }}" placeholder="0.00" class="{{ $inputCls }}" required>
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div>
                        <label class="{{ $labelCls }}">Posted At</label>
                        <input type="date" name="posted_at" value="{{ old('posted_at', $isEdit ? $jEdit->posted_at : now()->toDateString()) }}" class="{{ $inputCls }}" required>
                    </div>
                    <div>
                        <label class="{{ $labelCls }}">Expiration At</label>
                        <input type="date" name="expiration_at" value="{{ old('expiration_at', $isEdit ? $jEdit->expiration_at : '') }}" class="{{ $inputCls }}" required>
                    </div>
                </div>
                @foreach([
                    'assignment' => 'Assignment',
                    'education' => 'Education',
                    'eligibility' => 'Eligibility',
                    'training' => 'Training',
                    'experience' => 'Experience',
                    'competency' => 'Competency',
                ] as $field => $label)
                <div>
                    <label class="{{ $labelCls }}">{{ $label }}</label>
                    <textarea name="{{ $field }}" rows="2" placeholder="{{ $label }}" class="{{ $inputCls }}" {{ in_array($field, ['education', 'eligibility']) ? 'required' : '' }}>{{ old($field, $isEdit ? $jEdit->{$field} : '') }}</textarea>
                </div>
                @endforeach
            </div>
            <div class="flex justify-end gap-2 border-t border-border/60 px-5 py-4">
                @if($isEdit)
                <a href="{{ route('jlist') }}" class="rounded-xl border border-border/60 px-4 py-2 text-xs font-medium text-foreground transition hover:bg-muted">Cancel</a>
                @endif
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-primary px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-primary/90">
                    <i data-lucide="save" class="h-3.5 w-3.5"></i> {{ $isEdit ? 'Update Posting' : 'Save Posting' }}
                </button>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm">
            <div class="flex flex-col gap-2 border-b border-border/60 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-foreground">Job Postings</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Open and closed vacancies managed by HR.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table id="example1" class="min-w-full divide-y divide-border/60 text-sm">
                    <thead class="bg-muted/40">
                        <tr class="text-left text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                            <th class="px-4 py-3">Position</th>
                            <th class="px-4 py-3">Plantilla</th>
                            <th class="px-4 py-3">Salary</th>
                            <th class="px-4 py-3">Assignment</th>
                            <th class="px-4 py-3">Requirements</th>
                            <th class="px-4 py-3">Timeline</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="tbody" class="divide-y divide-border/60">
                        @forelse($jobs as $job)
                        <tr id="tr-{{ $job->id }}" class="transition hover:bg-muted/30">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-foreground">{{ $job->title }}</p>
                                <span class="mt-1 inline-flex items-center rounded-full {{ $job->type == 1 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300' : 'bg-sky-50 text-sky-700 dark:bg-sky-950/30 dark:text-sky-300' }} px-2 py-0.5 text-[11px] font-semibold">
                                    {{ $job->type == 1 ? 'Non-Teaching' : 'Teaching' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-muted-foreground">{{ $job->plantilla_item_no }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-foreground">PHP {{ number_format($job->salary, 2) }}</td>
                            <td class="max-w-[180px] px-4 py-3 text-xs text-muted-foreground">{{ $job->assignment ?: '-' }}</td>
                            <td class="max-w-[260px] px-4 py-3 text-xs text-muted-foreground">
                                <p><span class="font-semibold text-foreground">Education:</span> {{ $job->education }}</p>
                                <p><span class="font-semibold text-foreground">Eligibility:</span> {{ $job->eligibility }}</p>
                                <p><span class="font-semibold text-foreground">Experience:</span> {{ $job->experience ?: '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">
                                <p>{{ \Carbon\Carbon::parse($job->posted_at)->format('M d, Y') }}</p>
                                <p class="mt-0.5">until {{ \Carbon\Carbon::parse($job->expiration_at)->format('M d, Y') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full {{ $job->status === 'Open' ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground' }} px-2.5 py-0.5 text-[11px] font-semibold">
                                    {{ $job->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('jEdit', $job->id) }}" title="Edit" class="rounded-lg p-2 text-sky-600 transition hover:bg-sky-50 dark:hover:bg-sky-950/40">
                                        <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                                    </a>
                                    <button type="button" value="{{ $job->id }}" title="Delete" class="job-delete rounded-lg p-2 text-red-500 transition hover:bg-red-50 dark:hover:bg-red-950/40">
                                        <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-muted-foreground">No job postings yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.job-delete').forEach((button) => {
    button.addEventListener('click', async () => {
        if (!confirm('Delete this job posting?')) return;

        const response = await fetch('{{ route('jDelete') }}', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ id: button.value }),
        });
        const data = await response.json();

        if (data.status === 200) {
            document.getElementById(`tr-${data.id}`)?.remove();
        } else {
            alert(data.message || 'Unable to delete posting.');
        }
    });
});
</script>
@endpush
@endsection
