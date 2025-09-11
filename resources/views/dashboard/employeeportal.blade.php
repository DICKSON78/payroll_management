@extends('layout.global')

@section('title', 'Employee Portal')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">Employee Portal</span>
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-bolt mr-1.5"></i> Employee Access
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Manage your personal details, payslips, and leave balances.</span>
@endsection

@section('content')
    <!-- Success/Error Message -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="flex space-x-4 border-b border-gray-200" role="tablist">
            <button id="updateDetailsTab" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-t-md focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="true" aria-controls="updateDetailsContainer">
                Update Details
            </button>
            <button id="payslipsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="payslipsContainer">
                View Payslips
            </button>
            <button id="leaveBalancesTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="leaveBalancesContainer">
                Leave Balances
            </button>
        </div>
    </div>

    <!-- Update Details Container -->
    <div id="updateDetailsContainer" class="block">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-xl font-semibold text-green-600 flex items-center mb-6">
                <i class="fas fa-user-edit mr-2"></i> Update Personal Details
            </h3>
            <form id="updateDetailsForm" action="{{ route('employee.portal.update') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="phone" class="block text-gray-600 text-sm font-medium mb-2">Phone</label>
                        <input type="text" name="phone" id="phone" value="{{ $employee->phone ?? '' }}" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Enter phone number">
                        @error('phone')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="address" class="block text-gray-600 text-sm font-medium mb-2">Address</label>
                        <input type="text" name="address" id="address" value="{{ $employee->address ?? '' }}" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Enter address">
                        @error('address')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="bank_name" class="block text-gray-600 text-sm font-medium mb-2">Bank Name</label>
                        <select name="bank_name" id="bank_name" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                            <option value="">Select a bank</option>
                            @foreach($banks as $bank)
                                <option value="{{ $bank->name }}" {{ $employee->bank_name == $bank->name ? 'selected' : '' }}>{{ $bank->name }}</option>
                            @endforeach
                        </select>
                        @error('bank_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="account_number" class="block text-gray-600 text-sm font-medium mb-2">Account Number</label>
                        <input type="text" name="account_number" id="account_number" value="{{ $employee->account_number ?? '' }}" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Enter account number">
                        @error('account_number')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="toggleTab('updateDetailsTab')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <i class="fas fa-check mr-2"></i> Update Details
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Payslips Container -->
    <div id="payslipsContainer" class="hidden">
        <!-- Search Input -->
        <div class="mb-6 relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input id="searchPayslips" type="text" placeholder="Search by period..." class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" aria-label="Search payslips by period">
        </div>

        <!-- Payslips Table Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-file-invoice text-green-500 mr-2"></i> Your Payslips
                <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $payslips->total() }} payslips</span>
            </h3>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Period</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Gross Salary</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Net Salary</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="payslipsTable" class="divide-y divide-gray-100">
                        @foreach($payslips as $payslip)
                            <tr class="bg-white hover:bg-gray-50 transition-all duration-200 payslip-row group" data-period="{{ strtolower($payslip->period ?? $payslip->pay_period ?? '') }}">
                                <td class="py-4 px-6 text-sm text-gray-600">{{ $payslip->period ?? $payslip->pay_period ?? 'N/A' }}</td>
                                <td class="py-4 px-6 text-sm text-gray-600">{{ number_format($payslip->gross_salary ?? 0, 2) }}</td>
                                <td class="py-4 px-6 text-sm text-gray-600">{{ number_format($payslip->net_salary ?? 0, 2) }}</td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($payslip->status ?? 'Pending') == 'Processed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        <span class="w-2 h-2 {{ ($payslip->status ?? 'Pending') == 'Processed' ? 'bg-green-500' : 'bg-yellow-500' }} rounded-full mr-1.5"></span>
                                        {{ $payslip->status ?? 'Pending' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm">
                                    <a href="{{ route('employee.payslip.download', $payslip->id) }}" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition-all duration-200" title="Download payslip">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            @if($payslips->count() == 0)
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-file-invoice text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No payslips found</h3>
                    <p class="text-gray-500 mb-6">No payslips are available yet.</p>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($payslips->hasPages())
            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5">
                <div class="text-sm text-gray-700">
                    Showing {{ $payslips->firstItem() }} to {{ $payslips->lastItem() }} of {{ $payslips->total() }} results
                </div>
                <div class="flex space-x-2">
                    @if($payslips->onFirstPage())
                        <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-sm">Previous</span>
                    @else
                        <a href="{{ $payslips->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Previous</a>
                    @endif
                    @if($payslips->hasMorePages())
                        <a href="{{ $payslips->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Next</a>
                    @else
                        <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-sm">Next</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Leave Balances Container -->
    <div id="leaveBalancesContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-xl font-semibold text-green-600 flex items-center mb-6">
                <i class="fas fa-calendar-alt mr-2"></i> Leave Balances
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                    <h4 class="font-medium text-gray-900">Sick Leave</h4>
                    <p class="text-sm text-gray-500 mt-1">{{ $leaveBalances['sick_leave_balance'] ?? 0 }} days remaining</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                    <h4 class="font-medium text-gray-900">Vacation Leave</h4>
                    <p class="text-sm text-gray-500 mt-1">{{ $leaveBalances['vacation_leave_balance'] ?? 0 }} days remaining</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                    <h4 class="font-medium text-gray-900">Maternity Leave</h4>
                    <p class="text-sm text-gray-500 mt-1">{{ $leaveBalances['maternity_leave_balance'] ?? 0 }} days remaining</p>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('modals')
    @parent
@endsection

<script>
    // Initialize Tab Navigation
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('updateDetailsTab').addEventListener('click', () => toggleTab('updateDetailsTab'));
        document.getElementById('payslipsTab').addEventListener('click', () => toggleTab('payslipsTab'));
        document.getElementById('leaveBalancesTab').addEventListener('click', () => toggleTab('leaveBalancesTab'));

        // Search functionality for payslips
        const searchPayslips = document.getElementById('searchPayslips');
        if (searchPayslips) {
            searchPayslips.addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                const rows = document.querySelectorAll('.payslip-row');
                rows.forEach(row => {
                    const period = row.dataset.period || '';
                    const matches = period.includes(searchValue);
                    row.style.display = matches ? '' : 'none';
                });
            });
        }

        // Reset form on tab switch
        document.getElementById('updateDetailsTab').addEventListener('click', () => {
            document.getElementById('updateDetailsForm').reset();
        });
    });

    function toggleTab(tabId) {
        const tabs = ['updateDetailsTab', 'payslipsTab', 'leaveBalancesTab'];
        const containers = ['updateDetailsContainer', 'payslipsContainer', 'leaveBalancesContainer'];

        tabs.forEach(id => {
            const tab = document.getElementById(id);
            if (tab) {
                tab.classList.remove('bg-green-600', 'text-white');
                tab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                tab.setAttribute('aria-selected', 'false');
            }
        });

        containers.forEach(id => {
            const container = document.getElementById(id);
            if (container) container.classList.add('hidden');
        });

        const activeTab = document.getElementById(tabId);
        if (activeTab) {
            activeTab.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            activeTab.classList.add('bg-green-600', 'text-white');
            activeTab.setAttribute('aria-selected', 'true');
        }

        const containerId = tabId === 'updateDetailsTab' ? 'updateDetailsContainer' :
                           tabId === 'payslipsTab' ? 'payslipsContainer' : 'leaveBalancesContainer';
        const container = document.getElementById(containerId);
        if (container) container.classList.remove('hidden');
    }
</script>