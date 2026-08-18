@extends('layouts.admin')

@section('breadcrumb', 'Communication / SMS')
@section('page_title', 'SMS')

@section('content')
<div class="space-y-6">
    <div class="glass p-6 rounded-2xl">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-500 flex items-center justify-center">
                <i class="fa-solid fa-message text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl font-bold text-primary-900 dark:text-white">SMS Messaging</h2>
                <p class="text-sm text-primary-500 dark:text-primary-400">Send SMS messages to members</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                        <i class="fa-solid fa-paper-plane text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-600 dark:text-blue-400">Sent Today</p>
                        <p class="text-lg font-bold text-blue-900 dark:text-blue-200">{{ $stats['sent_today'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-500 flex items-center justify-center">
                        <i class="fa-solid fa-circle-xmark text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-red-600 dark:text-red-400">Failed Today</p>
                        <p class="text-lg font-bold text-red-900 dark:text-red-200">{{ $stats['failed_today'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-green-500 flex items-center justify-center">
                        <i class="fa-solid fa-calendar-check text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-green-600 dark:text-green-400">Sent This Month</p>
                        <p class="text-lg font-bold text-green-900 dark:text-green-200">{{ $stats['sent_month'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center">
                        <i class="fa-solid fa-chart-line text-white text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs text-purple-600 dark:text-purple-400">Total This Month</p>
                        <p class="text-lg font-bold text-purple-900 dark:text-purple-200">{{ $stats['total_month'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="{ activeTab: 'single' }">
            <div class="flex border-b border-primary-200 dark:border-dark-border mb-6">
                <button @click="activeTab = 'single'" :class="activeTab === 'single' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-primary-500 hover:text-primary-700'" class="px-4 py-3 text-sm font-semibold transition-all">
                    <i class="fa-solid fa-paper-plane mr-2"></i>Single SMS
                </button>
                <button @click="activeTab = 'bulk'" :class="activeTab === 'bulk' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-primary-500 hover:text-primary-700'" class="px-4 py-3 text-sm font-semibold transition-all">
                    <i class="fa-solid fa-layer-group mr-2"></i>Bulk SMS
                </button>
                <button @click="activeTab = 'history'" :class="activeTab === 'history' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-primary-500 hover:text-primary-700'" class="px-4 py-3 text-sm font-semibold transition-all">
                    <i class="fa-solid fa-clock-rotate-left mr-2"></i>History
                </button>
            </div>

            <div x-show="activeTab === 'single'">
                <form action="{{ route('admin.communication.sms.send') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 dark:text-white mb-2">Recipient Phone Number</label>
                            <input type="text" name="recipients[]" class="w-full px-4 py-3 rounded-xl border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" placeholder="e.g., 0712345678 or 255712345678" required>
                            <p class="text-xs text-primary-500 dark:text-primary-400 mt-1">Phone number with country code (255 for Tanzania)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary-900 dark:text-white mb-2">Message</label>
                            <textarea name="message" rows="4" class="w-full px-4 py-3 rounded-xl border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" placeholder="Type your SMS message here..." required maxlength="1600"></textarea>
                            <p class="text-xs text-primary-500 dark:text-primary-400 mt-1">Maximum 1600 characters</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="test_mode" value="1" class="w-4 h-4 text-blue-500 border-primary-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-primary-700 dark:text-primary-300">Test Mode</span>
                            </label>
                        </div>

                        <button type="submit" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all">
                            <i class="fa-solid fa-paper-plane mr-2"></i>Send SMS
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="activeTab === 'bulk'">
                <form action="{{ route('admin.communication.sms.bulk') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-primary-900 dark:text-white mb-2">Target Group</label>
                            <select name="member_type" class="w-full px-4 py-3 rounded-xl border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all">
                                <option value="">All Members</option>
                                @php
                                    $memberTypes = \App\Models\MemberType::active()->orderBy('priority', 'desc')->get();
                                @endphp
                                @foreach($memberTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary-900 dark:text-white mb-2">Or Enter Phone Numbers (one per line)</label>
                            <textarea name="phone_numbers" rows="4" class="w-full px-4 py-3 rounded-xl border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" placeholder="0712345678&#10;0723456789&#10;0734567890"></textarea>
                            <p class="text-xs text-primary-500 dark:text-primary-400 mt-1">Leave blank to use member group above</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-primary-900 dark:text-white mb-2">Message</label>
                            <textarea name="message" rows="4" class="w-full px-4 py-3 rounded-xl border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" placeholder="Type your bulk SMS message here..." required maxlength="1600"></textarea>
                            <p class="text-xs text-primary-500 dark:text-primary-400 mt-1">Maximum 1600 characters</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="test_mode" value="1" class="w-4 h-4 text-blue-500 border-primary-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-primary-700 dark:text-primary-300">Test Mode</span>
                            </label>
                        </div>

                        <button type="submit" class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-semibold rounded-xl transition-all">
                            <i class="fa-solid fa-layer-group mr-2"></i>Send Bulk SMS
                        </button>
                    </div>
                </form>
            </div>

            <div x-show="activeTab === 'history'">
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-3 items-center">
                        <input type="text" placeholder="Search phone or message..." class="px-4 py-2 rounded-xl border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" id="smsSearch">
                        <select class="px-4 py-2 rounded-xl border border-primary-200 dark:border-dark-border bg-white dark:bg-dark-card text-primary-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all" id="smsStatus">
                            <option value="">All Status</option>
                            <option value="sent">Sent</option>
                            <option value="failed">Failed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-primary-200 dark:border-dark-border">
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-primary-500 dark:text-primary-400 uppercase">To</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-primary-500 dark:text-primary-400 uppercase">Message</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-primary-500 dark:text-primary-400 uppercase">Status</th>
                                    <th class="text-left py-3 px-4 text-xs font-semibold text-primary-500 dark:text-primary-400 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMessages as $msg)
                                    <tr class="border-b border-primary-100 dark:border-dark-border hover:bg-primary-50 dark:hover:bg-dark-card/50 transition-all">
                                        <td class="py-3 px-4 text-primary-900 dark:text-white font-mono text-xs">{{ $msg->to }}</td>
                                        <td class="py-3 px-4 text-primary-700 dark:text-primary-300 max-w-xs truncate">{{ Str::limit($msg->message, 60) }}</td>
                                        <td class="py-3 px-4">
                                            @if($msg->status === 'sent')
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                                                    <i class="fa-solid fa-circle-check"></i> Sent
                                                </span>
                                            @elseif($msg->status === 'failed')
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                                                    <i class="fa-solid fa-circle-xmark"></i> Failed
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                                    <i class="fa-solid fa-clock"></i> Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-primary-500 dark:text-primary-400 text-xs">{{ $msg->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-8 text-center text-primary-500 dark:text-primary-400">
                                            <i class="fa-solid fa-inbox text-3xl mb-3 block opacity-50"></i>
                                            No SMS messages sent yet
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-center">
                        {{ $recentMessages->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
