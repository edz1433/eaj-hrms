@extends('layouts.master')

@section('body')
@include('leaves.style')
<style>
    .modal-content {
        background: rgba(255, 255, 255, 0.515);
        border: none;
        box-shadow: none;
    } 
    .modal-backdrop {
        background-color: transparent;
    }
    .vcenter{
        text-align: center;
        vertical-align: middle;
    }
</style>
@php
    $isLeaveManagement = ($leaveMode ?? ($guard == 'web' ? 'management' : 'personal')) === 'management';
@endphp
<div class="p-4 sm:p-6">
    @if($isLeaveManagement)
    <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-foreground">Leave Management</h1>
            <p class="mt-1 text-xs text-muted-foreground">
                <i data-lucide="calendar-days" class="mr-1 inline h-3.5 w-3.5 opacity-60"></i>
                Review selected employee leave history.
            </p>
        </div>
    </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-[24rem_minmax(0,1fr)]">
        @include("leaves.side-menu")
        <div class="min-w-0">
            <div class="overflow-hidden rounded-lg border border-border/60 border-t-4 border-t-primary bg-card shadow-sm">
                <div class="border-b border-border/60 px-5 py-4">
                    @include("leaves.top-menu")
                </div>           
                <div class="p-5">
                    @if($guard == "web")
                    <div class="mb-4 flex justify-end">
                        <form action="{{ route('leaveReport') }}" method="POST" target="_blank" class="flex w-full max-w-xl flex-col gap-2 sm:flex-row sm:items-center">
                            @csrf
                            <div class="relative flex-1">
                                <i class="far fa-calendar-alt pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"></i>
                                <input
                                    type="text"
                                    class="w-full rounded-lg border border-border/60 bg-background px-9 py-2 text-sm text-foreground placeholder:text-muted-foreground/60 focus:border-primary/40 focus:outline-none focus:ring-2 focus:ring-primary/20"
                                    name="date"
                                    id="dateRange"
                                    placeholder="Select date or date range"
                                    required
                                >
                            </div>
                            <button type="submit" class="inline-flex h-10 items-center justify-center gap-1.5 rounded-lg bg-destructive px-3 text-xs font-semibold text-destructive-foreground shadow-sm transition hover:bg-destructive/90">
                                <i class="fas fa-file-pdf"></i>
                                <span>Report</span>
                            </button>
                        </form>
                    </div>
                    @endif
                    <div class="tab-content">
                        <div class="overflow-x-auto rounded-lg border border-border/60">
                        <table class="table table-collapsed table-hover mb-0 min-w-[56rem]" id="leaveHistory">
                            <thead>
                                <tr>
                                    <th>LEAVE TYPE</th>
                                    <th class="vcenter">INCLUSIVE DATES</th>
                                    <th class="vcenter" width="50">DAYS APPLIED</th>
                                    <th class="vcenter" width="60">DAYS W/OUT PAY</th>
                                    <th class="vcenter">DATE OF FILING</th>
                                    <th class="vcenter">STATUS</th>
                                    <th class="vcenter">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $leavetype = [
                                        '1' => 'Vacation Leave',
                                        '2' => 'Mandatory/Forced Leave',
                                        '3' => 'Sick Leave',
                                        '4' => 'Maternity Leave',
                                        '5' => 'Paternity Leave',
                                        '6' => 'Special Privilege Leave',
                                        '7' => 'Solo Parent Leave',
                                        '8' => 'Study Leave',
                                        '9' => '10-Day VAWC Leave',
                                        '10' => 'Rehabilitation Privilege',
                                        '11' => 'Special Leave Benefits for Women',
                                        '12' => 'Special Emergency (Calamity) Leave',
                                        '13' => 'Adoption Leave',
                                        '14' => 'Vacation Service Credit',
                                        '15' => 'Wellness Leave'
                                    ];
                                @endphp
                                @foreach($leaveApplication as $leaves)
                                    @php
                                        if (strpos($leaves->date_range, 'to') !== false) {
                                            [$startDate, $endDate] = explode(' to ', $leaves->date_range);
                                            
                                            $formattedStartDate = \Carbon\Carbon::parse($startDate)->format('M d, Y');
                                            $formattedEndDate = \Carbon\Carbon::parse($endDate)->format('M d, Y');
                                        } else {
                                            $startDate = $leaves->date_range;
                                            $formattedStartDate = \Carbon\Carbon::parse($leaves->date_range)->format('M d, Y');
                                            $formattedEndDate = null;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ strtoupper($leavetype[$leaves->leave_type]) }}</td>
                                        <td data-order="{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }}">
                                            @if($formattedEndDate)
                                                {{ strtoupper($formattedStartDate) }} - {{ strtoupper($formattedEndDate) }}
                                            @else
                                                {{ strtoupper($formattedStartDate) }}
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $leaves->days }}</td>
                                        <td class="text-center">{{ ($leaves->day_wpay) ? $leaves->day_wpay : '' }}</td>
                                        <td data-order="{{ isset($leaves->date_filing) ? \Carbon\Carbon::parse($leaves->date_filing)->format('Y-m-d') : '' }}">
                                            {{ isset($leaves->date_filing) ? strtoupper(\Carbon\Carbon::parse($leaves->date_filing)->format('M d, Y')) : '' }}
                                        </td>
                                        <td width="100">
                                            @if($leaves->remarks_stat == 0)
                                                <span class="badge badge-success">approved</span>
                                            @elseif($leaves->remarks_stat == 4)
                                                <span class="badge badge-danger">canceled</span>
                                                <div class="callout callout-danger remarks-details" style="padding: 4px !important; display:none;">
                                                    <p>{{ $leaves->remarks_details2 }}</p>
                                                </div>
                                            @else
                                                <span class="badge badge-danger">disapproved</span>
                                                <div class="callout callout-danger remarks-details" style="padding: 4px !important; display:none;">
                                                    <p>{{ $leaves->remarks_details }}</p>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($guard == "web")
                                                <button type="button" class="btn btn-warning btn-sm @if($leaves->remarks_stat !== 0) cancel @endif disapprove-leave" data-id="{{ $leaves->id }}" data-by="4" @if($leaves->remarks_stat !== 0) disabled @endif><i class="fas fa-times"></i></button>
                                            @endif
                                            <button type="button" class="btn btn-danger btn-sm" title="view" data-id="{{ $leaves->id }}" data-toggle="modal" data-target="#pdfModalHistory"><i class="fas fa-file-pdf"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach($leaveApplication1 as $leaves)
                                    @php
                                        if (strpos($leaves->date_range, 'to') !== false) {
                                            [$startDate, $endDate] = explode(' to ', $leaves->date_range);
                                            
                                            $formattedStartDate = \Carbon\Carbon::parse($startDate)->format('M d, Y');
                                            $formattedEndDate = \Carbon\Carbon::parse($endDate)->format('M d, Y');
                                        } else {
                                            $startDate = $leaves->date_range;
                                            $formattedStartDate = \Carbon\Carbon::parse($leaves->date_range)->format('M d, Y');
                                            $formattedEndDate = null;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ strtoupper($leavetype[$leaves->leave_type]) }}</td>
                                        <td data-order="{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }}">
                                            @if($formattedEndDate)
                                                {{ strtoupper($formattedStartDate) }} - {{ strtoupper($formattedEndDate) }}
                                            @else
                                                {{ strtoupper($formattedStartDate) }}
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $leaves->days }}</td>
                                        <td class="text-center">{{ ($leaves->day_wpay) ? $leaves->day_wpay : '' }}</td>
                                        <td data-order="{{ isset($leaves->date_filing) ? \Carbon\Carbon::parse($leaves->date_filing)->format('Y-m-d') : '' }}">
                                            {{ isset($leaves->date_filing) ? strtoupper(\Carbon\Carbon::parse($leaves->date_filing)->format('M d, Y')) : '' }}
                                        </td>
                                        <td width="100">
                                            @if($leaves->remarks_stat == 0)
                                                <span class="badge badge-success">approved</span>
                                            @elseif($leaves->remarks_stat == 4)
                                                <span class="badge badge-danger">canceled</span>
                                                <div class="callout callout-danger remarks-details" style="padding: 4px !important; display:none;">
                                                    <p>{{ $leaves->remarks_details1 }}</p>
                                                </div>
                                            @else
                                                <span class="badge badge-danger">disapproved</span>
                                                <div class="callout callout-danger remarks-details" style="padding: 4px !important; display:none;">
                                                    <p>{{ $leaves->remarks_details }}</p>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($guard == "web")
                                                <button type="button" class="btn btn-warning btn-sm @if($leaves->remarks_stat !== 0) cancel @endif disapprove-leave" data-id="{{ $leaves->id }}" data-by="4" @if($leaves->remarks_stat !== 0) disabled @endif><i class="fas fa-times"></i></button>
                                            @endif
                                            <button type="button" class="btn btn-danger btn-sm" title="view" data-id="{{ $leaves->id }}" data-toggle="modal" data-target="#pdfModalHistory"><i class="fas fa-file-pdf"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>
                    </div>                    
                </div>
            </div>                        
        </div>
    </div>
</div>
@if($isLeaveManagement)
    @include("leaves.modal")
    @include("leaves.credit-modal-scripts")
@endif
<div class="modal fade" id="pdfModal" tabindex="-1" role="dialog" aria-labelledby="pdfModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <iframe id="pdfIframe" src="" width="100%" height="600px" style="border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="pdfModalHistory" tabindex="-1" role="dialog" aria-labelledby="pdfModalHistoryLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <iframe id="pdfIframeHistory" src="" width="100%" height="600px" style="border:none;"></iframe>
            </div>
        </div>
    </div>
</div>
</section>
<style>
    td:hover .remarks-details {
        display: block !important;
    }
</style>
@endsection
