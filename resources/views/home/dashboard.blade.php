@extends('layouts.master')
@section('pageTitle', 'Dashboard')

@section('body')
<div x-data="dashboardApp()" x-init="initDashboard()" class="space-y-6">
    @if($canTogglePersonalDashboard ?? false)
        <div class="flex justify-end">
            <div class="inline-flex rounded-lg border border-border/60 bg-card p-1 text-xs font-semibold shadow-sm">
                <a href="{{ route('dashboard') }}" class="rounded-md bg-primary px-3 py-1.5 text-primary-foreground no-underline shadow-sm">HR Dashboard</a>
                <a href="{{ route('dashboard', ['dashboard' => 'personal']) }}" class="rounded-md px-3 py-1.5 text-muted-foreground no-underline transition hover:bg-muted hover:text-foreground">Personal Dashboard</a>
            </div>
        </div>
    @endif
    
    {{-- ── Enhanced Hero Section with Glassmorphism ─────────────────────────────── --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-card to-background border shadow-xl">
        
        <!-- Animated background blobs -->
        <div class="absolute -top-40 -right-40 h-80 w-80 rounded-full bg-primary/5 blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 h-80 w-80 rounded-full bg-primary/5 blur-3xl animate-pulse delay-1000"></div>
        
        <div class="relative px-6 py-8 sm:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="group relative">
                            <div class="absolute -inset-1 rounded-full bg-gradient-to-r from-primary/50 to-primary/20 blur opacity-0 transition duration-500 group-hover:opacity-100"></div>
                            <div class="relative inline-flex items-center gap-2 rounded-full bg-primary/10 px-4 py-1.5 text-xs font-semibold text-primary ring-1 ring-primary/20 backdrop-blur-sm transition-all group-hover:scale-105">
                                <span class="relative flex h-2 w-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                                </span>
                                <span>System Live · Real-time Sync</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-1">
                        <h1 class="text-3xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/70 bg-clip-text text-transparent sm:text-4xl">
                            Good <span x-text="currentDay"></span>,
                            <span class="bg-gradient-to-r from-primary to-primary/70 bg-clip-text text-transparent" x-text="authName"></span>
                        </h1>
                        <p class="flex items-center gap-2 text-sm text-muted-foreground">
                            <i data-lucide="calendar" class="h-4 w-4"></i>
                            <span x-text="fullDate"></span>
                            <span class="mx-2">•</span>
                            <i data-lucide="clock" class="h-4 w-4"></i>
                            <span x-text="currentTime" x-init="setInterval(() => currentTime = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }), 1000)"></span>
                        </p>
                    </div>
                </div>
                
                <!-- Branch Filter with Modern Dropdown Style -->
                <div class="flex flex-col items-end gap-3">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-muted-foreground">Filter by:</span>
                        <div class="relative">
                            <select x-model="branchId" @change="setBranch(branchId)" 
                                    class="appearance-none rounded-xl border border-border bg-background/50 px-4 py-2 pr-8 text-sm font-medium text-foreground backdrop-blur-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary cursor-pointer">
                                <option value="0">All Branches</option>
                                <template x-for="office in offices" :key="office.id">
                                    <option :value="office.id" x-text="office.abbr"></option>
                                </template>
                            </select>
                            <i data-lucide="chevron-down" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground pointer-events-none"></i>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <i data-lucide="calendar" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground"></i>
                            <input type="date" x-model="selectedDate" 
                                   class="rounded-xl border border-border bg-background/50 px-3 py-2 pl-9 text-sm text-foreground backdrop-blur-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        </div>
                        <a href="#" @click.prevent="openEmployeeModal()"
                           class="group relative inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-primary/80 px-4 py-2 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-primary/25 active:scale-95">
                            <i data-lucide="plus" class="h-4 w-4 transition-transform group-hover:rotate-90"></i>
                            <span>Add Employee</span>
                            <div class="absolute inset-0 rounded-xl bg-white opacity-0 transition-opacity group-hover:opacity-10"></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- ── Advanced Stat Cards with Micro-interactions ───────────────────────────── --}}
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        
        <!-- Card 1: Total Employees -->
        <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl hover:scale-[1.02] hover:border-primary/20">
            <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
            <div class="relative">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-primary/20 to-primary/10 shadow-lg">
                        <i data-lucide="users" class="h-6 w-6 text-primary"></i>
                    </div>
                    <i data-lucide="trending-up" class="h-4 w-4 text-emerald-500"></i>
                </div>
                <p class="text-3xl font-bold text-foreground" x-text="stats.totalEmployees"></p>
                <p class="mt-1 text-sm font-medium text-muted-foreground">Total Employees</p>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="rounded-full bg-primary/10 px-2 py-0.5 text-primary" x-text="offices.length + ' departments'"></span>
                    <span class="text-muted-foreground">active workforce</span>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-primary/0 via-primary to-primary/0 opacity-0 transition-opacity group-hover:opacity-100"></div>
        </div>
        
        <!-- Card 2: Present Today with Progress Ring -->
        <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
            <div class="relative flex items-center justify-between">
                <div>
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/10 shadow-lg">
                        <i data-lucide="user-check" class="h-6 w-6 text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <p class="text-3xl font-bold text-foreground" x-text="stats.presentToday"></p>
                    <p class="mt-1 text-sm font-medium text-muted-foreground">Present Today</p>
                </div>
                <div class="relative">
                    <svg class="h-20 w-20 -rotate-90 transform">
                        <circle cx="40" cy="40" r="32" fill="none" stroke="currentColor" stroke-width="6" class="text-secondary" />
                        <circle cx="40" cy="40" r="32" fill="none" stroke="currentColor" stroke-width="6" 
                                :stroke-dasharray="`${2 * Math.PI * 32 * (stats.presentPercent / 100)} ${2 * Math.PI * 32}`"
                                class="text-emerald-500 transition-all duration-1000 ease-out" />
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-sm font-bold text-foreground" x-text="stats.presentPercent + '%'"></span>
                </div>
            </div>
        </div>
        
        <!-- Card 3: On Leave -->
        <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
            <div class="relative">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500/20 to-blue-500/10 shadow-lg">
                    <i data-lucide="umbrella" class="h-6 w-6 text-blue-600 dark:text-blue-400"></i>
                </div>
                <p class="text-3xl font-bold text-foreground" x-text="stats.onLeaveToday"></p>
                <p class="mt-1 text-sm font-medium text-muted-foreground">On Leave Today</p>
                <div class="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
                    <i data-lucide="clock" class="h-3 w-3"></i>
                    <span>Approved absences</span>
                </div>
            </div>
        </div>
        
        <!-- Card 4: Pending Leaves with Pulse -->
        <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
            <div class="relative">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500/20 to-amber-500/10 shadow-lg">
                        <i data-lucide="calendar-clock" class="h-6 w-6 text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <span x-show="stats.pendingLeavesCount > 0" 
                          class="relative flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-amber-500"></span>
                    </span>
                </div>
                <p class="text-3xl font-bold text-foreground" x-text="stats.pendingLeavesCount"></p>
                <p class="mt-1 text-sm font-medium text-muted-foreground">Pending Leaves</p>
                <a href="#" @click.prevent="openLeaveReview()" 
                   class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-primary transition-all hover:gap-2">
                    Review now <i data-lucide="arrow-right" class="h-3 w-3"></i>
                </a>
            </div>
        </div>
        
        <!-- Card 5: PDS Updates -->
        <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl hover:scale-[1.02]">
            <div class="relative">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500/20 to-violet-500/10 shadow-lg">
                        <i data-lucide="file-check" class="h-6 w-6 text-violet-600 dark:text-violet-400"></i>
                    </div>
                    <span class="rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-semibold text-violet-700 dark:bg-violet-900/30 dark:text-violet-400" x-text="stats.pdsTotal + ' pending'"></span>
                </div>
                <p class="text-3xl font-bold text-foreground" x-text="stats.pdsTotal"></p>
                <p class="mt-1 text-sm font-medium text-muted-foreground">PDS Updates Required</p>
                <div class="mt-3 flex gap-1 text-[10px] text-muted-foreground">
                    <span>Eligi</span> • <span>Work</span> • <span>L&D</span> • <span>Vol</span>
                </div>
            </div>
        </div>
    </div>
    
    {{-- ── Advanced Analytics Grid ─────────────────────────────────────────────────── --}}
    <div class="grid gap-6 lg:grid-cols-3">
        
        <!-- Main Chart Card -->
        <div class="lg:col-span-2">
            <div class="rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-xl">
                <div class="border-b border-border p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary/20 to-primary/10">
                                <i data-lucide="activity" class="h-5 w-5 text-primary"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-foreground">Attendance & Leave Analytics</h3>
                                <p class="text-xs text-muted-foreground">Monthly trend overview</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="text-2xl font-bold text-foreground" x-text="stats.leavesThisMonth"></p>
                                <p class="text-[10px] text-muted-foreground">this month</p>
                            </div>
                            <div class="h-8 w-px bg-border"></div>
                            <div class="flex gap-3">
                                <div class="flex items-center gap-1.5">
                                    <div class="h-2 w-2 rounded-full bg-amber-400"></div>
                                    <span class="text-xs text-muted-foreground">Pending <span class="font-semibold text-foreground" x-text="leaveBreakdown.pending"></span></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                                    <span class="text-xs text-muted-foreground">Approved <span class="font-semibold text-foreground" x-text="leaveBreakdown.approved"></span></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="h-2 w-2 rounded-full bg-red-500"></div>
                                    <span class="text-xs text-muted-foreground">Rejected <span class="font-semibold text-foreground" x-text="leaveBreakdown.disapproved"></span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="p-5">
                    <div id="trendChart" class="h-72 w-full"></div>
                </div>
                
                <div class="border-t border-border p-5">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Department Distribution</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <template x-for="dept in departmentList" :key="dept.abbr">
                            <div class="group relative">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-medium text-foreground" x-text="dept.abbr"></span>
                                    <span class="text-xs text-muted-foreground" x-text="dept.count + ' employees'"></span>
                                </div>
                                <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-secondary">
                                    <div class="h-full rounded-full bg-gradient-to-r from-primary to-primary/70 transition-all duration-700 group-hover:opacity-80" 
                                         :style="'width:' + dept.percent + '%'"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side Analytics Stack -->
        <div class="flex flex-col gap-6">
            <!-- Workforce Composition -->
            <div class="rounded-2xl border border-border bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl">
                <div class="mb-5 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary/20 to-primary/10">
                        <i data-lucide="pie-chart" class="h-5 w-5 text-primary"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-foreground">Workforce Mix</h3>
                        <p class="text-xs text-muted-foreground">Employment type distribution</p>
                    </div>
                </div>
                
                <div class="flex flex-col items-center gap-6 sm:flex-row">
                    <div class="relative shrink-0">
                        <svg viewBox="0 0 140 140" class="h-32 w-32 -rotate-90">
                            <circle cx="70" cy="70" r="56" fill="none" stroke="var(--border)" stroke-width="18" />
                            <template x-for="seg in statusArcs" :key="seg.color">
                                <circle cx="70" cy="70" r="56" fill="none" :stroke="seg.color" stroke-width="18" 
                                        :stroke-dasharray="seg.dash + ' ' + seg.circum" :stroke-dashoffset="'-' + seg.offset" />
                            </template>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <p class="text-xl font-bold text-foreground" x-text="stats.totalEmployees"></p>
                            <p class="text-[10px] text-muted-foreground">total headcount</p>
                        </div>
                    </div>
                    
                    <div class="flex-1 space-y-2.5">
                        <template x-for="st in statusLabels" :key="st.id">
                            <div class="group flex cursor-pointer items-center gap-2 rounded-lg p-1.5 transition-all hover:bg-accent">
                                <span class="h-2.5 w-2.5 shrink-0 rounded-full" :style="'background:' + st.color"></span>
                                <span class="flex-1 text-xs font-medium text-foreground" x-text="st.label"></span>
                                <span class="text-xs font-bold text-foreground" x-text="statusCounts[st.id] || 0"></span>
                                <span class="text-[10px] text-muted-foreground">
                                    (<span x-text="Math.round((statusCounts[st.id] || 0) / stats.totalEmployees * 100)"></span>%)
                                </span>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-blue-500/10 to-blue-500/5 p-3 text-center transition-all hover:scale-105">
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400" x-text="gender.male"></p>
                        <p class="text-xs font-medium text-blue-600/70 dark:text-blue-400/70">Male</p>
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-blue-500/0 via-blue-500 to-blue-500/0 opacity-0 transition-opacity group-hover:opacity-100"></div>
                    </div>
                    <div class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-primary/20 to-primary/10 p-3 text-center transition-all hover:scale-105">
                        <p class="text-2xl font-bold text-primary" x-text="gender.female"></p>
                        <p class="text-xs font-medium text-primary/70">Female</p>
                        <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-gradient-to-r from-primary/0 via-primary to-primary/0 opacity-0 transition-opacity group-hover:opacity-100"></div>
                    </div>
                </div>
            </div>
            
            <!-- PDS Queue Card -->
            <div class="rounded-2xl border border-border bg-card p-5 shadow-sm transition-all duration-300 hover:shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500/20 to-violet-500/10">
                            <i data-lucide="clipboard-list" class="h-5 w-5 text-violet-600 dark:text-violet-400"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-foreground">PDS Completion Queue</h3>
                            <p class="text-xs text-muted-foreground">Required document updates</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-700 dark:bg-violet-900/30 dark:text-violet-400" x-text="stats.pdsTotal + ' total'"></span>
                </div>
                
                <div class="space-y-3">
                    <template x-for="item in pdsItems" :key="item.label">
                        <div class="group cursor-pointer rounded-lg p-2 transition-all hover:bg-accent">
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-foreground" x-text="item.label"></span>
                                <span class="text-muted-foreground" x-text="item.count + ' pending'"></span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-secondary">
                                <div class="h-full rounded-full transition-all duration-500 group-hover:opacity-80" 
                                     :style="'width:' + item.percent + '%;background:' + item.color"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    
    {{-- ── Activity Feed & Quick Actions ─────────────────────────────────────────── --}}
    <div class="grid gap-6 lg:grid-cols-3">
        
        <!-- Pending Leaves Feed -->
        <div class="rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-xl">
            <div class="flex items-center justify-between border-b border-border p-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500/20 to-amber-500/10">
                        <i data-lucide="calendar-clock" class="h-5 w-5 text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-foreground">Pending Leave Requests</h3>
                        <p class="text-xs text-muted-foreground">Awaiting your approval</p>
                    </div>
                </div>
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400" x-text="filteredLeaves.length"></span>
            </div>
            
            <div class="max-h-96 divide-y overflow-y-auto" style="border-color: var(--border)">
                <template x-for="item in filteredLeaves" :key="item.name">
                    <div class="group relative cursor-pointer p-4 transition-all hover:bg-accent/50">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary/20 to-primary/10 text-sm font-bold text-primary shadow-sm" 
                                 x-text="item.initials"></div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate font-medium text-foreground group-hover:text-primary transition-colors" x-text="item.name"></p>
                                <p class="truncate text-xs text-muted-foreground" x-text="item.position"></p>
                                <div class="mt-1 flex items-center gap-2 text-[10px] text-muted-foreground">
                                    <span class="flex items-center gap-0.5">
                                        <i data-lucide="calendar" class="h-3 w-3"></i>
                                        <span x-text="item.days + ' days'"></span>
                                    </span>
                                    <span>•</span>
                                    <span x-text="item.timeAgo"></span>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <button @click.stop="approveLeave(item)" 
                                        class="rounded-lg bg-emerald-500/10 px-2 py-1 text-[10px] font-semibold text-emerald-600 transition-all hover:bg-emerald-500 hover:text-white">
                                    Approve
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div x-show="filteredLeaves.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="rounded-full bg-secondary/50 p-4">
                        <i data-lucide="calendar-check" class="h-8 w-8 text-muted-foreground/50"></i>
                    </div>
                    <p class="mt-3 font-medium text-foreground">No pending leaves</p>
                    <p class="text-xs text-muted-foreground">All caught up! 🎉</p>
                </div>
            </div>
            
            <div class="border-t border-border p-4 text-center">
                <a href="#" @click.prevent="viewAllLeaves()" class="inline-flex items-center gap-1 text-xs font-semibold text-primary transition-all hover:gap-2">
                    View all requests <i data-lucide="arrow-right" class="h-3 w-3"></i>
                </a>
            </div>
        </div>
        
        <!-- Upcoming Birthdays -->
        <div class="rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-xl">
            <div class="flex items-center justify-between border-b border-border p-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500/20 to-pink-500/10">
                        <i data-lucide="cake" class="h-5 w-5 text-pink-600 dark:text-pink-400"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-foreground">Upcoming Celebrations</h3>
                        <p class="text-xs text-muted-foreground">Birthdays in next 30 days</p>
                    </div>
                </div>
                <i data-lucide="gift" class="h-4 w-4 text-pink-500"></i>
            </div>
            
            <div class="max-h-96 divide-y overflow-y-auto">
                <template x-for="b in filteredBirthdays" :key="b.name">
                    <div class="group p-4 transition-all hover:bg-accent/50">
                        <div class="flex items-start gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-pink-500/20 to-pink-500/10 text-sm font-bold text-pink-600">
                                <span x-text="b.initials"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-medium text-foreground" x-text="b.name"></p>
                                    <span x-show="b.isToday" class="animate-bounce text-lg">🎂</span>
                                </div>
                                <p class="truncate text-xs text-muted-foreground" x-text="b.office"></p>
                            </div>
                            <div class="shrink-0 text-right">
                                <template x-if="b.isToday">
                                    <span class="inline-flex rounded-full bg-gradient-to-r from-primary to-primary/80 px-3 py-1 text-xs font-bold text-white shadow-sm">
                                        TODAY!
                                    </span>
                                </template>
                                <template x-if="!b.isToday">
                                    <div>
                                        <p class="text-lg font-bold text-foreground" x-text="'in ' + b.days + 'd'"></p>
                                        <p class="text-[10px] text-muted-foreground" x-text="b.date"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div x-show="filteredBirthdays.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="rounded-full bg-secondary/50 p-4">
                        <i data-lucide="cake" class="h-8 w-8 text-muted-foreground/50"></i>
                    </div>
                    <p class="mt-3 font-medium text-foreground">No birthdays soon</p>
                    <p class="text-xs text-muted-foreground">Check back later! 🎉</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions & Modules -->
        <div class="flex flex-col gap-6">
            <!-- Quick Actions Grid -->
            <div class="rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-xl">
                <div class="border-b border-border p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary/20 to-primary/10">
                            <i data-lucide="zap" class="h-5 w-5 text-primary"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-foreground">Quick Actions</h3>
                            <p class="text-xs text-muted-foreground">Frequently used tools</p>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-2 p-5">
                    <button @click="openEmployeeModal()" 
                            class="group relative flex flex-col items-center gap-2 rounded-xl p-3 text-center transition-all hover:bg-accent hover:scale-105 active:scale-95">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-pink-500/20 to-pink-500/10 text-pink-600 transition-all group-hover:scale-110">
                            <i data-lucide="user-plus" class="h-5 w-5"></i>
                        </span>
                        <span class="text-xs font-medium text-foreground">Add Employee</span>
                    </button>
                    
                    <button @click="openLeaveReview()" 
                            class="group relative flex flex-col items-center gap-2 rounded-xl p-3 text-center transition-all hover:bg-accent hover:scale-105 active:scale-95">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500/20 to-amber-500/10 text-amber-600 transition-all group-hover:scale-110">
                            <i data-lucide="calendar-check" class="h-5 w-5"></i>
                        </span>
                        <span class="text-xs font-medium text-foreground">Review Leaves</span>
                    </button>
                    
                    <button @click="viewEmployeeList()" 
                            class="group relative flex flex-col items-center gap-2 rounded-xl p-3 text-center transition-all hover:bg-accent hover:scale-105 active:scale-95">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500/20 to-blue-500/10 text-blue-600 transition-all group-hover:scale-110">
                            <i data-lucide="users" class="h-5 w-5"></i>
                        </span>
                        <span class="text-xs font-medium text-foreground">Employee List</span>
                    </button>
                    
                    <button @click="viewDTR()" 
                            class="group relative flex flex-col items-center gap-2 rounded-xl p-3 text-center transition-all hover:bg-accent hover:scale-105 active:scale-95">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500/20 to-indigo-500/10 text-indigo-600 transition-all group-hover:scale-110">
                            <i data-lucide="clock" class="h-5 w-5"></i>
                        </span>
                        <span class="text-xs font-medium text-foreground">DTR Records</span>
                    </button>
                </div>
            </div>
            
            <!-- System Modules Status -->
            <div class="rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:shadow-xl">
                <div class="border-b border-border p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/10">
                            <i data-lucide="cpu" class="h-5 w-5 text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-foreground">System Modules</h3>
                            <p class="text-xs text-muted-foreground">Available features</p>
                        </div>
                    </div>
                </div>
                
                <div class="divide-y">
                    <template x-for="mod in modules" :key="mod.name">
                        <div class="flex items-center justify-between p-4 transition-all hover:bg-accent/50">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary">
                                    <i :data-lucide="mod.live ? 'check-circle' : 'clock'" class="h-4 w-4" 
                                       :class="mod.live ? 'text-emerald-500' : 'text-muted-foreground'"></i>
                                </div>
                                <span class="text-sm font-medium text-foreground" x-text="mod.name"></span>
                            </div>
                            <span :class="mod.live ? 'bg-emerald-500/10 text-emerald-600' : 'bg-secondary text-muted-foreground'" 
                                  class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-semibold">
                                <span x-show="mod.live" class="relative flex h-1.5 w-1.5">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                </span>
                                <span x-text="mod.live ? 'Active' : 'Coming Soon'"></span>
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function dashboardApp() {
    return {
        // State
        branchId: 0,
        selectedDate: new Date().toISOString().slice(0,10),
        currentTime: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
        authName: 'Admin',
        offices: [
            { id: 1, abbr: 'Main Campus' },
            { id: 2, abbr: 'CPSU-MP' },
            { id: 3, abbr: 'CPSU-KAB' },
            { id: 4, abbr: 'CPSU-IB' }
        ],
        
        // Data from AJAX / native
        stats: {
            totalEmployees: 0,
            presentToday: 0,
            onLeaveToday: 0,
            pendingLeavesCount: 0,
            pdsTotal: 0,
            presentPercent: 0,
            leavesThisMonth: 0
        },
        departmentList: [],
        statusLabels: [
            { id: 1, label: 'Permanent', color: '#C9407A' },
            { id: 2, label: 'Casual', color: '#6366f1' },
            { id: 3, label: 'Job Order', color: '#f59e0b' },
            { id: 4, label: 'Contractual', color: '#06b6d4' }
        ],
        statusCounts: {},
        gender: { male: 0, female: 0 },
        leaveBreakdown: { pending: 0, approved: 0, disapproved: 0 },
        leavesList: [],
        birthdaysList: [],
        pdsItems: [
            { label: 'Eligibilities', count: 0, color: '#C9407A', percent: 0 },
            { label: 'Work Experience', count: 0, color: '#6366f1', percent: 0 },
            { label: 'Learning & Dev', count: 0, color: '#f59e0b', percent: 0 },
            { label: 'Voluntary Work', count: 0, color: '#06b6d4', percent: 0 }
        ],
        monthlyTrend: { months: ['Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'], totals: [4, 7, 5, 9, 6, 8] },
        modules: [
            { name: 'Attendance / DTR', live: true },
            { name: 'Leave System', live: true },
            { name: 'PDS Records', live: true },
            { name: 'Payroll Module', live: false },
            { name: 'Recruitment Portal', live: false },
            { name: 'Performance Review', live: false }
        ],
        
        // Filtered copies
        filteredLeaves: [],
        filteredBirthdays: [],
        statusArcs: [],
        
        // Computed helpers
        get currentDay() {
            const now = new Date();
            const hour = now.getHours();
            if (hour < 12) return 'Morning';
            if (hour < 18) return 'Afternoon';
            return 'Evening';
        },
        get fullDate() {
            return new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        },
        
        // Lifecycle
        async initDashboard() {
            await this.loadData();
            this.applyBranchFilter();
            this.updateStatusArc();
            this.renderChart();
            // Initialize icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
                setInterval(() => lucide.createIcons(), 1000);
            }
        },
        
        // Data loading - simulates AJAX with enhanced demo data
        async loadData() {
            // Simulate API delay
            await new Promise(resolve => setTimeout(resolve, 300));
            const demoData = this.generateEnhancedDemoData();
            this.applyData(demoData);
        },
        
        generateEnhancedDemoData() {
            return {
                totalEmployees: 147,
                male: 82,
                female: 65,
                presentToday: 128,
                onLeaveToday: 5,
                pendingLeavesCount: 8,
                pdsBreakdown: { elig: 12, workexp: 8, learnDev: 3, volWork: 2 },
                leavesThisMonth: 12,
                leaveBreakdown: { pending: 8, approved: 24, disapproved: 3 },
                statusDistribution: { 1: 78, 2: 42, 3: 18, 4: 9 },
                departments: [
                    { abbr: 'College of Education', count: 34 },
                    { abbr: 'College of Arts & Sciences', count: 28 },
                    { abbr: 'College of Engineering', count: 22 },
                    { abbr: 'Administration', count: 18 },
                    { abbr: 'College of Business', count: 15 },
                    { abbr: 'College of Agriculture', count: 12 },
                    { abbr: 'Graduate Studies', count: 10 },
                    { abbr: 'Research & Extension', count: 8 }
                ],
                pendingLeaves: [
                    { name: 'Dr. Maria Santos', initials: 'MS', position: 'Dean, College of Education', days: 3, timeAgo: '2 hours ago', officeId: 1 },
                    { name: 'Prof. Juan Dela Cruz', initials: 'JD', position: 'Department Chair', days: 5, timeAgo: '4 hours ago', officeId: 1 },
                    { name: 'Mr. Roberto Reyes', initials: 'RR', position: 'Senior Faculty', days: 2, timeAgo: '1 day ago', officeId: 2 },
                    { name: 'Ms. Ana Fernandez', initials: 'AF', position: 'Admin Staff III', days: 7, timeAgo: '1 day ago', officeId: 4 },
                    { name: 'Dr. Mark Ong', initials: 'MO', position: 'Associate Professor', days: 4, timeAgo: '2 days ago', officeId: 2 },
                    { name: 'Ms. Grace Villanueva', initials: 'GV', position: 'University Nurse', days: 2, timeAgo: '2 days ago', officeId: 1 },
                    { name: 'Prof. James Lopez', initials: 'JL', position: 'Instructor III', days: 3, timeAgo: '3 days ago', officeId: 3 },
                    { name: 'Ms. Patricia Cruz', initials: 'PC', position: 'Registrar Staff', days: 1, timeAgo: '3 days ago', officeId: 4 }
                ],
                birthdays: [
                    { name: 'Dr. Maria Santos', initials: 'MS', office: 'College of Education', officeId: 1, days: 0, isToday: true, date: 'Today' },
                    { name: 'Prof. Juan Dela Cruz', initials: 'JD', office: 'College of Arts & Sciences', officeId: 2, days: 8, isToday: false, date: this.getFutureDateString(8) },
                    { name: 'Ms. Ana Fernandez', initials: 'AF', office: 'Administration', officeId: 4, days: 14, isToday: false, date: this.getFutureDateString(14) },
                    { name: 'Prof. James Lopez', initials: 'JL', office: 'College of Engineering', officeId: 3, days: 21, isToday: false, date: this.getFutureDateString(21) },
                    { name: 'Ms. Patricia Cruz', initials: 'PC', office: 'College of Business', officeId: 1, days: 28, isToday: false, date: this.getFutureDateString(28) }
                ]
            };
        },
        
        getFutureDateString(days) {
            let d = new Date();
            d.setDate(d.getDate() + days);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },
        
        applyData(data) {
            this.stats = {
                totalEmployees: data.totalEmployees,
                presentToday: data.presentToday,
                onLeaveToday: data.onLeaveToday,
                pendingLeavesCount: data.pendingLeavesCount,
                pdsTotal: Object.values(data.pdsBreakdown).reduce((a,b) => a+b, 0),
                presentPercent: Math.round((data.presentToday / data.totalEmployees) * 100),
                leavesThisMonth: data.leavesThisMonth
            };
            this.statusCounts = data.statusDistribution;
            this.gender = { male: data.male, female: data.female };
            this.leaveBreakdown = data.leaveBreakdown;
            this.departmentList = data.departments.map(d => ({
                ...d,
                percent: (d.count / data.totalEmployees) * 100
            }));
            this.leavesList = data.pendingLeaves;
            this.birthdaysList = data.birthdays;
            
            // Update PDS queue percentages
            const pdsVals = data.pdsBreakdown;
            this.pdsItems[0].count = pdsVals.elig;
            this.pdsItems[1].count = pdsVals.workexp;
            this.pdsItems[2].count = pdsVals.learnDev;
            this.pdsItems[3].count = pdsVals.volWork;
            const maxPds = Math.max(...this.pdsItems.map(i => i.count), 1);
            this.pdsItems.forEach(i => { i.percent = (i.count / maxPds) * 100; });
        },
        
        setBranch(id) {
            this.branchId = id;
            this.applyBranchFilter();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },
        
        applyBranchFilter() {
            if (this.branchId === 0) {
                this.filteredLeaves = [...this.leavesList];
                this.filteredBirthdays = [...this.birthdaysList];
            } else {
                this.filteredLeaves = this.leavesList.filter(l => l.officeId === this.branchId);
                this.filteredBirthdays = this.birthdaysList.filter(b => b.officeId === this.branchId);
            }
        },
        
        updateStatusArc() {
            const total = this.stats.totalEmployees || 1;
            const circum = 2 * Math.PI * 56;
            let offset = 0;
            const arcs = [];
            const colorMap = { 1: '#C9407A', 2: '#6366f1', 3: '#f59e0b', 4: '#06b6d4' };
            for (let i = 1; i <= 4; i++) {
                const count = this.statusCounts[i] || 0;
                const fraction = count / total;
                const dash = fraction * circum;
                if (dash > 0) {
                    arcs.push({
                        color: colorMap[i],
                        dash: dash.toFixed(1),
                        circum: circum.toFixed(1),
                        offset: offset.toFixed(1)
                    });
                }
                offset += dash;
            }
            this.statusArcs = arcs;
        },
        
        renderChart() {
            const options = {
                chart: {
                    type: 'area',
                    height: 280,
                    toolbar: { show: false },
                    background: 'transparent',
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                series: [{
                    name: 'Leave Applications',
                    data: this.monthlyTrend.totals,
                    color: 'var(--color-primary, #C9407A)'
                }],
                xaxis: {
                    categories: this.monthlyTrend.months,
                    labels: { style: { fontSize: '12px', fontWeight: 500, colors: '#6b7280' } },
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    title: { text: 'Number of Leaves', style: { fontSize: '11px', fontWeight: 500, color: '#6b7280' } },
                    labels: { style: { fontSize: '11px', colors: '#6b7280' } },
                    min: 0
                },
                stroke: { curve: 'smooth', width: 3, lineCap: 'round' },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.4,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                dataLabels: { enabled: false },
                grid: { borderColor: '#e5e7eb', strokeDashArray: 4, padding: { top: 0, right: 0, bottom: 0, left: 10 } },
                tooltip: { theme: 'dark', y: { formatter: (val) => `${val} leaves` } }
            };
            const chartElement = document.querySelector('#trendChart');
            if (chartElement && typeof ApexCharts !== 'undefined') {
                const chart = new ApexCharts(chartElement, options);
                chart.render();
            }
        },
        
        // Action methods
        openEmployeeModal() {
            alert('🚀 Open Add Employee Modal - Integrate with your form system');
        },
        
        openLeaveReview() {
            alert('📋 Leave Review Panel - Manage pending approvals');
        },
        
        viewEmployeeList() {
            alert('👥 Employee Directory - View all employees');
        },
        
        viewDTR() {
            alert('⏰ DTR Records - Daily Time Record management');
        },
        
        viewAllLeaves() {
            alert('📅 All Leave Requests - Complete history');
        },
        
        approveLeave(item) {
            alert(`✅ Leave request for ${item.name} approved!`);
        }
    };
}

// Auto-initialize after DOM ready
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
</script>
@endpush
@endsection
