@extends('layouts.member')

@section('breadcrumb', 'Dashboard')
@section('page_title', 'Dashboard')

@php
    $memberStatus = $member['status'] ?? 'Active';
    $statusActive = strtolower($memberStatus) === 'active';
    $memberNum = $member['membercode'] ?? $user->member_number ?? 'N/A';

    function fmtTsh($val): string {
        return 'TSh ' . number_format((float)$val, 2, '.', ',');
    }

    $loanDelta = count($loans) > 0 ? -12.4 : 0;
    $savDelta = 8.6;
    $depDelta = 2.1;
    $invDelta = 15.3;
    $swfDelta = 4.2;
@endphp

@section('content')

<div class="space-y-6">

    <div class="glass p-6 lg:p-8 rounded-2xl" style="background: linear-gradient(135deg, rgba(16,185,129,0.12) 0%, rgba(52,211,153,0.08) 100%); backdrop-filter: blur(16px); border: 1px solid rgba(52,211,153,0.3);">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg shadow-primary-500/20 flex-shrink-0">
                    <i class="fa-solid fa-leaf text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl lg:text-3xl font-extrabold text-primary-900 dark:text-white leading-tight">
                        Welcome back, {{ auth()->user()->name }}!
                    </h2>
                    <p class="mt-1.5 text-sm text-primary-700 dark:text-primary-300">
                        Here's a snapshot of your accounts as of {{ now()->format('F j, Y') }}.
                    </p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white/70 dark:bg-dark-card/70 border border-primary-200 dark:border-dark-border text-xs font-semibold text-primary-700 dark:text-primary-300">
                    <i class="fa-solid fa-clock-rotate-left text-primary-500"></i>
                    Last login: Today, {{ now()->format('g:i A') }}
                </span>
            </div>
        </div>
    </div>

    <div class="glass p-5 rounded-2xl">
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 justify-between">
            <div class="flex flex-wrap items-center gap-3">
                <div>
                    <p class="text-[11px] uppercase tracking-wider font-bold text-primary-500 dark:text-primary-400 mb-1">Member Code</p>
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg font-mono text-sm font-bold bg-primary-50 dark:bg-primary-900/40 text-primary-800 dark:text-primary-200 border border-primary-200 dark:border-primary-800/60">
                        <i class="fa-solid fa-id-card mr-2 text-primary-500 text-xs"></i>
                        {{ $memberNum }}
                    </span>
                </div>
                <div class="hidden sm:block w-px h-10 bg-primary-100 dark:bg-primary-800/50"></div>
                <div>
                    <p class="text-[11px] uppercase tracking-wider font-bold text-primary-500 dark:text-primary-400 mb-1">Membership Status</p>
                    <span class="badge {{ $statusActive ? 'badge-green' : 'badge-gray' }} inline-flex items-center gap-1.5 py-1.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusActive ? 'bg-primary-500 animate-pulse' : 'bg-gray-400' }}"></span>
                        {{ $memberStatus }}
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @if(!empty($member['branch']))
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 border border-primary-100 dark:border-primary-800/50 font-semibold">
                        <i class="fa-solid fa-location-dot text-primary-500"></i>
                        {{ $member['branch'] }} Branch
                    </span>
                @endif
                @if(!empty($loans) && count($loans) > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-800/50 font-semibold">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        {{ count($loans) }} Active Loan{{ count($loans) > 1 ? 's' : '' }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="stat-card glass">
            <div class="bg-blob" style="background: #ef4444;"></div>
            <div class="flex items-start justify-between mb-3">
                <div class="icon-wrap bg-red-50 dark:bg-red-900/30 text-red-500">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-red-500 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full">
                    <i class="fa-solid fa-arrow-down"></i>
                    {{ number_format(abs($loanDelta), 1) }}%
                </span>
            </div>
            <p class="text-[11px] uppercase font-bold tracking-wider text-primary-500 dark:text-primary-400 mb-1">Current Loan Balance</p>
            <p class="text-xl lg:text-2xl font-extrabold text-primary-900 dark:text-white leading-tight">
                {{ fmtTsh($loanBalance) }}
            </p>
            <p class="text-[11px] text-primary-500 dark:text-primary-400 mt-1">
                {{ count($loans) }} active account{{ count($loans) !== 1 ? 's' : '' }}
            </p>
        </div>

        <div class="stat-card glass sm:col-span-2 lg:col-span-1">
            <div class="bg-blob" style="background: #10b981;"></div>
            <div class="flex items-start justify-between mb-3">
                <div class="icon-wrap bg-green-50 dark:bg-green-900/30 text-green-500">
                    <i class="fa-solid fa-piggy-bank"></i>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-green-500 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-full">
                    <i class="fa-solid fa-arrow-up"></i>
                    {{ number_format($savDelta, 1) }}%
                </span>
            </div>
            <p class="text-[11px] uppercase font-bold tracking-wider text-primary-500 dark:text-primary-400 mb-1">Savings & Deposits</p>
            <p class="text-xl lg:text-2xl font-extrabold text-primary-900 dark:text-white leading-tight">
                {{ fmtTsh($savingsBalance + $depositBalance) }}
            </p>
            <div class="flex items-center gap-3 mt-1">
                <p class="text-[11px] text-primary-500 dark:text-primary-400">
                    Interest: {{ fmtTsh($savings['interest_earned'] ?? 0) }}
                </p>
                <span class="text-primary-300 dark:text-primary-600">•</span>
                <p class="text-[11px] text-primary-500 dark:text-primary-400">
                    {{ count($deposits) }} fixed deposit{{ count($deposits) !== 1 ? 's' : '' }}
                </p>
            </div>
        </div>

        <div class="stat-card glass">
            <div class="bg-blob" style="background: #8b5cf6;"></div>
            <div class="flex items-start justify-between mb-3">
                <div class="icon-wrap bg-purple-50 dark:bg-purple-900/30 text-purple-500">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-green-500 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-full">
                    <i class="fa-solid fa-arrow-up"></i>
                    {{ number_format($invDelta, 1) }}%
                </span>
            </div>
            <p class="text-[11px] uppercase font-bold tracking-wider text-primary-500 dark:text-primary-400 mb-1">Investment Balance</p>
            <p class="text-xl lg:text-2xl font-extrabold text-primary-900 dark:text-white leading-tight">
                {{ fmtTsh($investmentBalance) }}
            </p>
            <p class="text-[11px] text-primary-500 dark:text-primary-400 mt-1">
                {{ count($investments) }} investment{{ count($investments) !== 1 ? 's' : '' }}
            </p>
        </div>

        <div class="stat-card glass sm:col-span-2 lg:col-span-1">
            <div class="bg-blob" style="background: #f59e0b;"></div>
            <div class="flex items-start justify-between mb-3">
                <div class="icon-wrap bg-amber-50 dark:bg-amber-900/30 text-amber-500">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-green-500 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-full">
                    <i class="fa-solid fa-arrow-up"></i>
                    {{ number_format($swfDelta, 1) }}%
                </span>
            </div>
            <p class="text-[11px] uppercase font-bold tracking-wider text-primary-500 dark:text-primary-400 mb-1">SWF Balance</p>
            <p class="text-xl lg:text-2xl font-extrabold text-primary-900 dark:text-white leading-tight">
                {{ fmtTsh($swfBalance) }}
            </p>
            <p class="text-[11px] text-primary-500 dark:text-primary-400 mt-1">
                Social Welfare Fund
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

        <div class="glass rounded-2xl overflow-hidden xl:col-span-3">
            <div class="flex items-center justify-between px-5 py-4 border-b border-primary-100 dark:border-dark-border">
                <div>
                    <h3 class="font-bold text-primary-900 dark:text-white text-sm">Active Loans</h3>
                    <p class="text-[11px] text-primary-500 dark:text-primary-400 mt-0.5">{{ count($activeLoans) }} active loan{{ count($activeLoans) !== 1 ? 's' : '' }}</p>
                </div>
                <a href="{{ route('member.loans.index') }}" class="text-[11px] font-bold text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 inline-flex items-center gap-1">
                    View All
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>
            @forelse($activeLoans as $loan)
                <div class="p-4 border-b border-primary-100 dark:border-dark-border last:border-b-0 hover:bg-primary-50/30 dark:hover:bg-primary-900/10 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-primary-50 dark:bg-primary-900/40 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800/60">
                                    {{ $loan['loan_number'] }}
                                </span>
                                <span class="badge badge-green text-[10px]">Active</span>
                            </div>
                            <h4 class="font-bold text-primary-900 dark:text-white text-sm truncate">
                                {{ $loan['loan_product'] }}
                            </h4>
                        </div>
                        <div class="text-right ml-4">
                            <p class="text-xs font-bold text-primary-900 dark:text-white tabular-nums">
                                {{ fmtTsh($loan['outstanding_balance'] ?? 0) }}
                            </p>
                            <p class="text-[10px] text-primary-500 dark:text-primary-400">Outstanding</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-500 flex items-center justify-center mx-auto mb-3">
                        <i class="fa-solid fa-circle-check text-xl"></i>
                    </div>
                    <h4 class="font-bold text-primary-900 dark:text-white text-sm mb-1">No Active Loans</h4>
                    <p class="text-xs text-primary-600 dark:text-primary-400">You have no active loan accounts.</p>
                </div>
            @endforelse
        </div>

        <div class="glass rounded-2xl overflow-hidden xl:col-span-2 flex flex-col">
            <div class="flex items-center justify-between px-5 py-4 border-b border-primary-100 dark:border-dark-border">
                <div>
                    <h3 class="font-bold text-primary-900 dark:text-white text-sm">Savings Growth</h3>
                    <p class="text-[11px] text-primary-500 dark:text-primary-400 mt-0.5">Last 6 months performance</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-[11px] font-bold border border-green-100 dark:border-green-800/50">
                    <i class="fa-solid fa-chart-line"></i>
                    +{{ number_format($savDelta, 1) }}%
                </span>
            </div>
            <div class="p-4 flex-1 min-h-[280px]">
                <canvas id="savingsGrowthChart" x-init x-data x-ref="chart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="glass rounded-2xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-5 py-4 border-b border-primary-100 dark:border-dark-border">
                <div>
                    <h3 class="font-bold text-primary-900 dark:text-white text-sm">Investment Distribution</h3>
                    <p class="text-[11px] text-primary-500 dark:text-primary-400 mt-0.5">By product type</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 text-[11px] font-bold border border-purple-100 dark:border-purple-800/50">
                    <i class="fa-solid fa-chart-pie"></i>
                    {{ count($investments) }} investment{{ count($investments) !== 1 ? 's' : '' }}
                </span>
            </div>
            <div class="p-4 flex-1 min-h-[280px]">
                <canvas id="investmentDistributionChart" x-init x-data x-ref="chart"></canvas>
            </div>
        </div>

        <div class="glass rounded-2xl overflow-hidden flex flex-col">
            <div class="flex items-center justify-between px-5 py-4 border-b border-primary-100 dark:border-dark-border">
                <div>
                    <h3 class="font-bold text-primary-900 dark:text-white text-sm">Investment Performance</h3>
                    <p class="text-[11px] text-primary-500 dark:text-primary-400 mt-0.5">Last 6 months performance</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-[11px] font-bold border border-green-100 dark:border-green-800/50">
                    <i class="fa-solid fa-chart-line"></i>
                    +{{ number_format($invDelta, 1) }}%
                </span>
            </div>
            <div class="p-4 flex-1 min-h-[280px]">
                <canvas id="investmentPerformanceChart" x-init x-data x-ref="chart"></canvas>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    queueMicrotask(() => {
        initSavingsChart();
        initInvestmentDistributionChart();
        initInvestmentPerformanceChart();
    });
});
function initSavingsChart() {
    const canvas = document.getElementById('savingsGrowthChart');
    if (!canvas || typeof Chart === 'undefined') return;
    const ctx = canvas.getContext('2d');
    const labels = @json($savingsGrowth['labels'] ?? []);
    const values = @json($savingsGrowth['values'] ?? []);

    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(26,51,40,0.6)' : 'rgba(209,250,229,0.6)';
    const textColor = isDark ? '#6ee7b7' : '#047857';
    const lineColor = '#10b981';
    const fillStart = 'rgba(16,185,129,0.28)';
    const fillEnd = 'rgba(16,185,129,0.00)';

    const grad = ctx.createLinearGradient(0, 0, 0, 300);
    grad.addColorStop(0, fillStart);
    grad.addColorStop(1, fillEnd);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Savings Balance (TSh)',
                data: values,
                borderColor: lineColor,
                backgroundColor: grad,
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: lineColor,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: lineColor,
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#0d1f16' : '#ffffff',
                    borderColor: isDark ? '#1a3328' : '#d1fae5',
                    borderWidth: 1,
                    titleColor: isDark ? '#6ee7b7' : '#065f46',
                    bodyColor: isDark ? '#d1fae5' : '#064e3b',
                    padding: 10,
                    cornerRadius: 10,
                    titleFont: { weight: 'bold', size: 12 },
                    callbacks: {
                        label: (ctx) => ' TSh ' + Number(ctx.parsed.y).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: textColor,
                        font: { size: 11, weight: 600 }
                    },
                    border: { display: false }
                },
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: {
                        color: textColor,
                        font: { size: 11, weight: 600 },
                        callback: (v) => {
                            if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                            if (v >= 1000) return (v / 1000).toFixed(0) + 'k';
                            return v;
                        },
                        maxTicksLimit: 6
                    },
                    border: { display: false }
                }
            }
        }
    });
}

function initInvestmentDistributionChart() {
    const canvas = document.getElementById('investmentDistributionChart');
    if (!canvas || typeof Chart === 'undefined') return;
    const ctx = canvas.getContext('2d');
    const labels = @json($investmentDistribution['labels'] ?? []);
    const values = @json($investmentDistribution['values'] ?? []);

    const isDark = document.documentElement.classList.contains('dark');
    const colors = [
        '#8b5cf6', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#ec4899', '#06b6d4', '#84cc16'
    ];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderWidth: 2,
                borderColor: isDark ? '#1f2937' : '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: isDark ? '#d1d5db' : '#374151',
                        font: { size: 11, weight: 600 },
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1f2937' : '#ffffff',
                    borderColor: isDark ? '#374151' : '#e5e7eb',
                    borderWidth: 1,
                    titleColor: isDark ? '#f3f4f6' : '#111827',
                    bodyColor: isDark ? '#d1d5db' : '#374151',
                    padding: 12,
                    cornerRadius: 10,
                    titleFont: { weight: 'bold', size: 12 },
                    callbacks: {
                        label: (ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((ctx.parsed / total) * 100).toFixed(1);
                            return ' TSh ' + Number(ctx.parsed).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' (' + percentage + '%)';
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

function initInvestmentPerformanceChart() {
    const canvas = document.getElementById('investmentPerformanceChart');
    if (!canvas || typeof Chart === 'undefined') return;
    const ctx = canvas.getContext('2d');
    const labels = @json($investmentPerformance['labels'] ?? []);
    const values = @json($investmentPerformance['values'] ?? []);

    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(49,46,129,0.6)' : 'rgba(233,213,255,0.6)';
    const textColor = isDark ? '#a78bfa' : '#7c3aed';
    const lineColor = '#8b5cf6';
    const fillStart = 'rgba(139,92,246,0.28)';
    const fillEnd = 'rgba(139,92,246,0.00)';

    const grad = ctx.createLinearGradient(0, 0, 0, 300);
    grad.addColorStop(0, fillStart);
    grad.addColorStop(1, fillEnd);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Investment Value (TSh)',
                data: values,
                borderColor: lineColor,
                backgroundColor: grad,
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: lineColor,
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: lineColor,
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: isDark ? '#1e1b4b' : '#ffffff',
                    borderColor: isDark ? '#312e81' : '#e9d5ff',
                    borderWidth: 1,
                    titleColor: isDark ? '#a78bfa' : '#5b21b6',
                    bodyColor: isDark ? '#e9d5ff' : '#4c1d95',
                    padding: 10,
                    cornerRadius: 10,
                    titleFont: { weight: 'bold', size: 12 },
                    callbacks: {
                        label: (ctx) => ' TSh ' + Number(ctx.parsed.y).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: {
                        color: textColor,
                        font: { size: 11, weight: 600 }
                    },
                    border: { display: false }
                },
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: {
                        color: textColor,
                        font: { size: 11, weight: 600 },
                        callback: (v) => {
                            if (v >= 1000000) return (v / 1000000).toFixed(1) + 'M';
                            if (v >= 1000) return (v / 1000).toFixed(0) + 'k';
                            return v;
                        },
                        maxTicksLimit: 6
                    },
                    border: { display: false }
                }
            }
        }
    });
}
</script>
@endpush
