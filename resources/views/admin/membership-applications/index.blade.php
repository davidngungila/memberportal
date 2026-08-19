@extends('layouts.admin')

@section('page_title', 'Membership Applications')

@section('content')
<div class="animate-fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-primary-900">Membership Applications</h1>
            <p class="text-primary-600 text-sm">Manage membership applications</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <a href="{{ route('admin.membership-applications.index') }}" class="stat-card card {{ !request('status') ? 'border-primary-400' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-primary-600 font-semibold">Total</p>
                    <p class="text-xl font-bold text-primary-800">{{ $stats['total'] }}</p>
                </div>
                <div class="icon-wrap bg-primary-100 text-primary-600">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.membership-applications.index', ['status' => 'submitted']) }}" class="stat-card card {{ request('status') === 'submitted' ? 'border-primary-400' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-primary-600 font-semibold">Submitted</p>
                    <p class="text-xl font-bold text-primary-800">{{ $stats['submitted'] }}</p>
                </div>
                <div class="icon-wrap bg-blue-100 text-blue-600">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.membership-applications.index', ['status' => 'under_review']) }}" class="stat-card card {{ request('status') === 'under_review' ? 'border-primary-400' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-primary-600 font-semibold">Under Review</p>
                    <p class="text-xl font-bold text-primary-800">{{ $stats['under_review'] }}</p>
                </div>
                <div class="icon-wrap bg-yellow-100 text-yellow-600">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.membership-applications.index', ['status' => 'approved']) }}" class="stat-card card {{ request('status') === 'approved' ? 'border-primary-400' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-primary-600 font-semibold">Approved</p>
                    <p class="text-xl font-bold text-primary-800">{{ $stats['approved'] }}</p>
                </div>
                <div class="icon-wrap bg-green-100 text-green-600">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.membership-applications.index', ['status' => 'rejected']) }}" class="stat-card card {{ request('status') === 'rejected' ? 'border-primary-400' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-primary-600 font-semibold">Rejected</p>
                    <p class="text-xl font-bold text-primary-800">{{ $stats['rejected'] }}</p>
                </div>
                <div class="icon-wrap bg-red-100 text-red-600">
                    <i class="fa-solid fa-times-circle"></i>
                </div>
            </div>
        </a>
    </div>

    <div class="card">
        <div class="p-4 border-b border-primary-100">
            <form method="GET" class="flex items-center gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Search by name, phone, or application number...">
                </div>
                <button type="submit" class="px-4 py-2 rounded-lg bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 transition">
                    <i class="fa-solid fa-search"></i>
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Application #</th>
                        <th>Applicant</th>
                        <th>Membership</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td class="font-bold text-primary-800">{{ $app->application_number }}</td>
                            <td>
                                <div>
                                    <p class="font-semibold text-primary-800">{{ $app->personalDetail?->full_name ?? $app->user?->name ?? '-' }}</p>
                                    <p class="text-xs text-primary-500">{{ $app->user?->phone ?? '-' }}</p>
                                </div>
                            </td>
                            <td>{{ $app->membershipType?->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ ($app->payment_status ?? '') === 'successful' ? 'badge-green' : (($app->payment_status ?? '') === 'pending' ? 'badge-yellow' : 'badge-gray') }}">
                                    {{ ucfirst($app->payment_status ?? 'N/A') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ match($app->application_status ?? '') {
                                    'submitted' => 'badge-blue',
                                    'under_review' => 'badge-blue',
                                    'approved' => 'badge-green',
                                    'rejected' => 'badge-red',
                                    'correction_required' => 'badge-yellow',
                                    default => 'badge-gray',
                                } }}">
                                    {{ ucfirst(str_replace('_', ' ', $app->application_status ?? '')) }}
                                </span>
                            </td>
                            <td>{{ $app->submitted_at?->format('d M Y') ?? '-' }}</td>
                            <td>
                                <a href="{{ route('admin.membership-applications.show', $app) }}" class="px-3 py-1.5 rounded-lg bg-primary-50 text-primary-700 text-xs font-semibold hover:bg-primary-100 transition">
                                    <i class="fa-solid fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-primary-500 text-sm">
                                No applications found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $applications->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
