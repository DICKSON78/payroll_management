@extends('layout.global')

@section('title', 'Payroll')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">Payroll Management</span>
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-bolt mr-1.5"></i> Premium Plan
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Process and review payroll records for {{ $settings->company_name ?? 'Summit' }}.</span>
@endsection

@section('content')
    @if(!in_array(strtolower(Auth::user()->role), ['admin', 'hr']))
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
            <span class="block sm:inline">Unauthorized access. This page is restricted to Admin and HR roles only.</span>
        </div>
    @else
        <!-- Success/Error/Warning Messages -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-3"></i>
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                    <div>
                        <span class="block font-medium">Please fix the following errors:</span>
                        <ul class="mt-1 list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                <i class="fas fa-bolt text-green-500 mr-2"></i> Quick Actions
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:bg-green-50 hover:shadow-md transition-all duration-200 p-4 cursor-pointer" onclick="openModal('runPayrollModal')">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-calculator text-green-600 text-lg"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Run Payroll</div>
                            <div class="text-sm text-gray-500">Calculate and process employee salaries</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:bg-green-50 hover:shadow-md transition-all duration-200 p-4 cursor-pointer" onclick="openModal('retroactivePayModal')">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-history text-green-600 text-lg"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Retroactive Pay</div>
                            <div class="text-sm text-gray-500">Adjust payments for a past period</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:bg-green-50 hover:shadow-md transition-all duration-200 p-4 cursor-pointer" onclick="openModal('revertPayrollModal')">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-undo text-green-600 text-lg"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Revert Payroll</div>
                            <div class="text-sm text-gray-500">Undo a specific payroll record</div>
                        </div>
                    </div>
                </div>
                <!-- MPYA: Revert All Data Button -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:bg-red-50 hover:shadow-md transition-all duration-200 p-4 cursor-pointer" onclick="openModal('revertAllModal')">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 bg-red-100 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-trash-alt text-red-600 text-lg"></i>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Revert All Data</div>
                            <div class="text-sm text-gray-500">Delete payroll, transactions & alerts</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Month Filter -->
        <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-calendar-alt text-green-500 mr-2"></i> Filter by Month
                </h3>
                <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar text-gray-400"></i>
                        </div>
                        <input type="text" id="monthFilter" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Select month to filter..." readonly>
                    </div>
                    <button onclick="clearMonthFilter()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition-all duration-200 flex items-center justify-center">
                        <i class="fas fa-times mr-2"></i> Clear Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="mb-6">
            <div class="flex space-x-4 border-b border-gray-200" role="tablist">
                <button id="payrollTab" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-t-md focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="true" aria-controls="payrollContainer">
                    <i class="fas fa-file-invoice-dollar mr-2"></i>Payroll Records
                </button>
                <button id="transactionsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="transactionsContainer">
                    <i class="fas fa-exchange-alt mr-2"></i>Transactions
                </button>
                <button id="alertsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="alertsContainer">
                    <i class="fas fa-bell mr-2"></i>Alerts
                    @if($unread_alerts_count > 0)
                        <span class="ml-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $unread_alerts_count }}</span>
                    @endif
                </button>
            </div>
        </div>

        <!-- Payroll Tab -->
        <div id="payrollContainer" class="block">
            <!-- Search and Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-file-invoice-dollar text-green-500 mr-2"></i> Payroll Records
                    <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full"><span id="payrollCount">{{ $payrolls->total() }}</span> records</span>
                </h3>

                <div class="flex items-center space-x-3">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchPayroll" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Search by ID or employee...">
                    </div>

                    <!-- Refresh Button -->
                    <button onclick="refreshPayroll()" class="text-gray-600 hover:text-green-600 p-2 rounded-lg hover:bg-green-50 transition-all duration-200" title="Refresh Payroll">
                        <i class="fas fa-sync-alt text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-green-50 to-green-100 text-gray-700 text-sm">
                                <th class="py-3.5 px-6 text-left font-semibold">Payroll ID</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Employee Details</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Period</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Base Salary (TZS)</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Allowances (TZS)</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Deductions (TZS)</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Net Salary (TZS)</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="payrollTable" class="divide-y divide-gray-100">
                            @foreach($payrolls as $payroll)
                                @php
                                    $statusColors = [
                                        'processed' => 'bg-green-100 text-green-800 border border-green-200',
                                        'paid' => 'bg-green-100 text-green-800 border border-green-200',
                                        'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                        'failed' => 'bg-red-100 text-red-800 border border-red-200'
                                    ];
                                    $statusColor = $statusColors[strtolower($payroll->status)] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                                @endphp
                                <tr class="bg-white hover:bg-gray-50 transition-all duration-200 payroll-row group" 
                                    data-id="{{ strtolower($payroll->payroll_id ?? '') }}" 
                                    data-employee="{{ strtolower($payroll->employee_name ?? '') }}" 
                                    data-period="{{ strtolower($payroll->period ?? '') }}">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-file-invoice text-green-400 text-sm"></i>
                                            <span class="text-sm text-gray-600 font-mono font-medium">{{ $payroll->payroll_id ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3 shadow-sm">
                                                <span class="font-medium text-white text-xs">{{ substr($payroll->employee_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-600 text-sm">{{ $payroll->employee_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $payroll->employee_id ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">{{ $payroll->position ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-calendar text-orange-500 text-sm"></i>
                                            <span class="text-sm text-gray-600 font-medium">{{ $payroll->period ? \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') : 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-900 font-semibold">TZS {{ number_format($payroll->base_salary, 0) }}</td>
                                    <td class="py-4 px-6 text-sm text-green-600 font-semibold">+{{ number_format($payroll->allowances, 0) }}</td>
                                    <td class="py-4 px-6 text-sm text-red-600 font-semibold">-{{ number_format($payroll->deductions, 0) }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-900 font-bold">TZS {{ number_format($payroll->net_salary, 0) }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                            @if($payroll->status === 'processed' || $payroll->status === 'paid')
                                                <i class="fas fa-check-circle mr-1.5 text-xs"></i>
                                            @elseif($payroll->status === 'pending')
                                                <i class="fas fa-clock mr-1.5 text-xs"></i>
                                            @elseif($payroll->status === 'failed')
                                                <i class="fas fa-exclamation-circle mr-1.5 text-xs"></i>
                                            @endif
                                            {{ ucfirst($payroll->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-1">
                                            <button onclick="viewPayrollDetails('{{ $payroll->payroll_id }}')" 
                                                    class="text-green-600 hover:text-green-800 p-2 rounded-lg hover:bg-green-50 transition-all duration-200 group relative" 
                                                    title="View Details">
                                                <i class="fas fa-eye text-sm"></i>
                                                <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                    View Details
                                                </span>
                                            </button>
                                            <button onclick="revertPayroll('{{ $payroll->payroll_id }}')" 
                                                    class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-all duration-200 group relative" 
                                                    title="Revert Payroll">
                                                <i class="fas fa-undo text-sm"></i>
                                                <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                    Revert Payroll
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                @if($payrolls->count() == 0)
                    <div class="text-center py-16">
                        <div class="mx-auto w-24 h-24 mb-6 rounded-full bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center shadow-sm">
                            <i class="fas fa-file-invoice-dollar text-green-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No payroll records found</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">Get started by running your first payroll.</p>
                        <button class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-6 py-3 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md transform hover:-translate-y-0.5" 
                                onclick="openModal('runPayrollModal')">
                            <i class="fas fa-calculator mr-2"></i> Run Payroll
                        </button>
                    </div>
                @endif
            </div>

            <!-- Custom Pagination -->
            @if($payrolls->lastPage() > 1)
                <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-500">
                        Showing {{ $payrolls->firstItem() }} to {{ $payrolls->lastItem() }} of {{ $payrolls->total() }} results
                    </div>
                    
                    <nav class="flex items-center space-x-1" aria-label="Pagination">
                        <!-- Previous Button -->
                        <a href="{{ $payrolls->previousPageUrl() ? $payrolls->previousPageUrl() . '&' . http_build_query(request()->except('page')) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $payrolls->onFirstPage() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' }}"
                           aria-label="Previous page"
                           {{ $payrolls->onFirstPage() ? 'disabled' : '' }}>
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>

                        <!-- Page Numbers -->
                        @php
                            $currentPage = $payrolls->currentPage();
                            $lastPage = $payrolls->lastPage();
                            $range = 2;
                            $start = max(1, $currentPage - $range);
                            $end = min($lastPage, $currentPage + $range);

                            if ($end - $start < 2 * $range) {
                                if ($start == 1) {
                                    $end = min($lastPage, $start + 2 * $range);
                                } elseif ($end == $lastPage) {
                                    $start = max(1, $end - 2 * $range);
                                }
                            }
                        @endphp

                        @if($start > 1)
                            <a href="{{ $payrolls->url(1) . '&' . http_build_query(request()->except('page')) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300 rounded-lg transition-all duration-200"
                               aria-label="Page 1">1</a>
                            @if($start > 2)
                                <span class="px-2 py-2 text-sm text-gray-400">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            <a href="{{ $payrolls->url($page) . '&' . http_build_query(request()->except('page')) }}"
                               class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $page == $currentPage ? 'text-white bg-green-600 border border-green-600 shadow-sm' : 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' }}"
                               aria-label="Page {{ $page }}"
                               aria-current="{{ $page == $currentPage ? 'page' : 'false' }}">{{ $page }}</a>
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="px-2 py-2 text-sm text-gray-400">...</span>
                            @endif
                            <a href="{{ $payrolls->url($lastPage) . '&' . http_build_query(request()->except('page')) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300 rounded-lg transition-all duration-200"
                               aria-label="Page {{ $lastPage }}">{{ $lastPage }}</a>
                        @endif

                        <!-- Next Button -->
                        <a href="{{ $payrolls->nextPageUrl() ? $payrolls->nextPageUrl() . '&' . http_build_query(request()->except('page')) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $payrolls->hasMorePages() ? 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' : 'text-gray-400 bg-gray-100 cursor-not-allowed' }}"
                           aria-label="Next page"
                           {{ !$payrolls->hasMorePages() ? 'disabled' : '' }}>
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    </nav>
                </div>
            @endif
        </div>

        <!-- Transactions Tab -->
        <div id="transactionsContainer" class="hidden">
            <!-- Search and Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-exchange-alt text-green-500 mr-2"></i> Transactions
                    <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full"><span id="transactionCount">{{ $transactions->total() }}</span> transactions</span>
                </h3>

                <div class="flex items-center space-x-3">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchTransaction" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Search by ID or employee...">
                    </div>

                    <!-- Refresh Button -->
                    <button onclick="refreshTransactions()" class="text-gray-600 hover:text-green-600 p-2 rounded-lg hover:bg-green-50 transition-all duration-200" title="Refresh Transactions">
                        <i class="fas fa-sync-alt text-sm"></i>
                    </button>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-green-50 to-green-100 text-gray-700 text-sm">
                                <th class="py-3.5 px-6 text-left font-semibold">Transaction ID</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Employee Details</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Amount (TZS)</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Date</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Type</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transactionTable" class="divide-y divide-gray-100">
                            @foreach($transactions as $transaction)
                                @php
                                    $statusColors = [
                                        'completed' => 'bg-green-100 text-green-800 border border-green-200',
                                        'processed' => 'bg-green-100 text-green-800 border border-green-200',
                                        'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                        'failed' => 'bg-red-100 text-red-800 border border-red-200'
                                    ];
                                    $statusColor = $statusColors[strtolower($transaction->status)] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                                    
                                    $typeColors = [
                                        'salary_payment' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                        'bonus' => 'bg-green-100 text-green-800 border border-green-200',
                                        'deduction' => 'bg-red-100 text-red-800 border border-red-200',
                                        'adjustment' => 'bg-yellow-100 text-yellow-800 border border-yellow-200'
                                    ];
                                    $typeColor = $typeColors[strtolower($transaction->type)] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                                @endphp
                                <tr class="bg-white hover:bg-gray-50 transition-all duration-200 transaction-row group" 
                                    data-id="{{ strtolower($transaction->transaction_id ?? '') }}" 
                                    data-employee="{{ strtolower($transaction->employee_name ?? '') }}" 
                                    data-period="{{ strtolower($transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m') : '') }}">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-receipt text-blue-400 text-sm"></i>
                                            <span class="text-sm text-gray-600 font-mono font-medium">{{ $transaction->transaction_id ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3 shadow-sm">
                                                <span class="font-medium text-white text-xs">{{ substr($transaction->employee_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-600 text-sm">{{ $transaction->employee_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $transaction->employee_id ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">{{ $transaction->position ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-900 font-bold">TZS {{ number_format($transaction->amount, 0) }}</td>
                                    <td class="py-4 px-6">
                                        <div class="text-xs text-gray-500">
                                            <div>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M j, Y') }}</div>
                                            <div>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('g:i A') }}</div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $typeColor }}">
                                            <i class="fas fa-tag mr-1.5 text-xs"></i>
                                            {{ str_replace('_', ' ', ucfirst($transaction->type)) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                            @if($transaction->status === 'completed' || $transaction->status === 'processed')
                                                <i class="fas fa-check-circle mr-1.5 text-xs"></i>
                                            @elseif($transaction->status === 'pending')
                                                <i class="fas fa-clock mr-1.5 text-xs"></i>
                                            @elseif($transaction->status === 'failed')
                                                <i class="fas fa-exclamation-circle mr-1.5 text-xs"></i>
                                            @endif
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-1">
                                            <button onclick="viewTransactionDetails('{{ $transaction->transaction_id }}')" 
                                                    class="text-green-600 hover:text-green-800 p-2 rounded-lg hover:bg-green-50 transition-all duration-200 group relative" 
                                                    title="View Details">
                                                <i class="fas fa-eye text-sm"></i>
                                                <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                    View Details
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                @if($transactions->count() == 0)
                    <div class="text-center py-16">
                        <div class="mx-auto w-24 h-24 mb-6 rounded-full bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center shadow-sm">
                            <i class="fas fa-exchange-alt text-green-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No transactions found</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">Transactions will appear here after payroll processing.</p>
                    </div>
                @endif
            </div>

            <!-- Custom Pagination -->
            @if($transactions->lastPage() > 1)
                <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-500">
                        Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} results
                    </div>
                    
                    <nav class="flex items-center space-x-1" aria-label="Pagination">
                        <!-- Previous Button -->
                        <a href="{{ $transactions->previousPageUrl() ? $transactions->previousPageUrl() . '&' . http_build_query(request()->except('page')) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $transactions->onFirstPage() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' }}"
                           aria-label="Previous page"
                           {{ $transactions->onFirstPage() ? 'disabled' : '' }}>
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>

                        <!-- Page Numbers -->
                        @php
                            $currentPage = $transactions->currentPage();
                            $lastPage = $transactions->lastPage();
                            $range = 2;
                            $start = max(1, $currentPage - $range);
                            $end = min($lastPage, $currentPage + $range);

                            if ($end - $start < 2 * $range) {
                                if ($start == 1) {
                                    $end = min($lastPage, $start + 2 * $range);
                                } elseif ($end == $lastPage) {
                                    $start = max(1, $end - 2 * $range);
                                }
                            }
                        @endphp

                        @if($start > 1)
                            <a href="{{ $transactions->url(1) . '&' . http_build_query(request()->except('page')) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300 rounded-lg transition-all duration-200"
                               aria-label="Page 1">1</a>
                            @if($start > 2)
                                <span class="px-2 py-2 text-sm text-gray-400">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            <a href="{{ $transactions->url($page) . '&' . http_build_query(request()->except('page')) }}"
                               class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $page == $currentPage ? 'text-white bg-green-600 border border-green-600 shadow-sm' : 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' }}"
                               aria-label="Page {{ $page }}"
                               aria-current="{{ $page == $currentPage ? 'page' : 'false' }}">{{ $page }}</a>
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="px-2 py-2 text-sm text-gray-400">...</span>
                            @endif
                            <a href="{{ $transactions->url($lastPage) . '&' . http_build_query(request()->except('page')) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300 rounded-lg transition-all duration-200"
                               aria-label="Page {{ $lastPage }}">{{ $lastPage }}</a>
                        @endif

                        <!-- Next Button -->
                        <a href="{{ $transactions->nextPageUrl() ? $transactions->nextPageUrl() . '&' . http_build_query(request()->except('page')) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $transactions->hasMorePages() ? 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' : 'text-gray-400 bg-gray-100 cursor-not-allowed' }}"
                           aria-label="Next page"
                           {{ !$transactions->hasMorePages() ? 'disabled' : '' }}>
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    </nav>
                </div>
            @endif
        </div>

        <!-- Alerts Tab -->
        <div id="alertsContainer" class="hidden">
            <!-- Search and Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-bell text-green-500 mr-2"></i> Alerts
                    <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full"><span id="alertCount">{{ $payroll_alerts->total() }}</span> alerts</span>
                </h3>

                <div class="flex items-center space-x-3">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="searchAlerts" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Search by ID or employee...">
                    </div>

                    <!-- Refresh Button -->
                    <button onclick="refreshAlerts()" class="text-gray-600 hover:text-green-600 p-2 rounded-lg hover:bg-green-50 transition-all duration-200" title="Refresh Alerts">
                        <i class="fas fa-sync-alt text-sm"></i>
                    </button>

                    <!-- Mark All as Read Button -->
                    @if($unread_alerts_count > 0)
                        <button onclick="markAllAlertsAsRead()" class="text-blue-600 hover:text-blue-800 px-3 py-2 text-sm font-medium bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-all duration-200 flex items-center">
                            <i class="fas fa-check-double mr-2"></i> Mark All Read
                        </button>
                    @endif
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-green-50 to-green-100 text-gray-700 text-sm">
                                <th class="py-3.5 px-6 text-left font-semibold">Alert ID</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Employee Details</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Type</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Message</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Date</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="alertTable" class="divide-y divide-gray-100">
                            @foreach($payroll_alerts as $alert)
                                @php
                                    $statusColors = [
                                        'unread' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                        'read' => 'bg-green-100 text-green-800 border border-green-200'
                                    ];
                                    $statusColor = $statusColors[strtolower($alert->status)] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                                    
                                    $typeColors = [
                                        'high_deductions' => 'bg-red-100 text-red-800 border border-red-200',
                                        'retroactive_adjustment' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                        'payroll_reverted' => 'bg-orange-100 text-orange-800 border border-orange-200',
                                        'system_alert' => 'bg-yellow-100 text-yellow-800 border border-yellow-200'
                                    ];
                                    $typeColor = $typeColors[strtolower(str_replace(' ', '_', $alert->type))] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                                @endphp
                                <tr class="bg-white hover:bg-gray-50 transition-all duration-200 alert-row group {{ $alert->status === 'unread' ? 'bg-yellow-50' : '' }}" 
                                    data-id="{{ strtolower($alert->alert_id ?? '') }}" 
                                    data-employee="{{ strtolower($alert->employee_name ?? '') }}" 
                                    data-period="{{ strtolower($alert->created_at ? \Carbon\Carbon::parse($alert->created_at)->format('Y-m') : '') }}">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <i class="fas fa-bell text-yellow-400 text-sm"></i>
                                            <span class="text-sm text-gray-600 font-mono font-medium">{{ $alert->alert_id ?? 'N/A' }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3 shadow-sm">
                                                <span class="font-medium text-white text-xs">{{ substr($alert->employee_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-600 text-sm">{{ $alert->employee_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $alert->employee_id ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">{{ $alert->position ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $typeColor }}">
                                            <i class="fas fa-info-circle mr-1.5 text-xs"></i>
                                            {{ $alert->type }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-500 max-w-xs truncate">{{ $alert->message }}</td>
                                    <td class="py-4 px-6">
                                        <div class="text-xs text-gray-500">
                                            <div>{{ \Carbon\Carbon::parse($alert->created_at)->format('M j, Y') }}</div>
                                            <div>{{ \Carbon\Carbon::parse($alert->created_at)->format('g:i A') }}</div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                            @if($alert->status === 'read')
                                                <i class="fas fa-check-circle mr-1.5 text-xs"></i>
                                            @else
                                                <i class="fas fa-exclamation-circle mr-1.5 text-xs"></i>
                                            @endif
                                            {{ ucfirst($alert->status) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-1">
                                            <button onclick="viewAlertDetails('{{ $alert->alert_id }}')" 
                                                    class="text-green-600 hover:text-green-800 p-2 rounded-lg hover:bg-green-50 transition-all duration-200 group relative" 
                                                    title="View Details">
                                                <i class="fas fa-eye text-sm"></i>
                                                <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                    View Details
                                                </span>
                                            </button>
                                            @if(strtolower($alert->status) === 'unread')
                                                <button onclick="markAlertAsRead('{{ $alert->alert_id }}')" 
                                                        class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-all duration-200 group relative" 
                                                        title="Mark as Read">
                                                    <i class="fas fa-check text-sm"></i>
                                                    <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                        Mark as Read
                                                    </span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                @if($payroll_alerts->count() == 0)
                    <div class="text-center py-16">
                        <div class="mx-auto w-24 h-24 mb-6 rounded-full bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center shadow-sm">
                            <i class="fas fa-bell text-green-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">No alerts found</h3>
                        <p class="text-gray-500 mb-8 max-w-md mx-auto">Alerts will appear here when payroll issues are detected.</p>
                    </div>
                @endif
            </div>

            <!-- Custom Pagination -->
            @if($payroll_alerts->lastPage() > 1)
                <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-500">
                        Showing {{ $payroll_alerts->firstItem() }} to {{ $payroll_alerts->lastItem() }} of {{ $payroll_alerts->total() }} results
                    </div>
                    
                    <nav class="flex items-center space-x-1" aria-label="Pagination">
                        <!-- Previous Button -->
                        <a href="{{ $payroll_alerts->previousPageUrl() ? $payroll_alerts->previousPageUrl() . '&' . http_build_query(request()->except('page')) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $payroll_alerts->onFirstPage() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' }}"
                           aria-label="Previous page"
                           {{ $payroll_alerts->onFirstPage() ? 'disabled' : '' }}>
                            <i class="fas fa-chevron-left text-xs"></i>
                        </a>

                        <!-- Page Numbers -->
                        @php
                            $currentPage = $payroll_alerts->currentPage();
                            $lastPage = $payroll_alerts->lastPage();
                            $range = 2;
                            $start = max(1, $currentPage - $range);
                            $end = min($lastPage, $currentPage + $range);

                            if ($end - $start < 2 * $range) {
                                if ($start == 1) {
                                    $end = min($lastPage, $start + 2 * $range);
                                } elseif ($end == $lastPage) {
                                    $start = max(1, $end - 2 * $range);
                                }
                            }
                        @endphp

                        @if($start > 1)
                            <a href="{{ $payroll_alerts->url(1) . '&' . http_build_query(request()->except('page')) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300 rounded-lg transition-all duration-200"
                               aria-label="Page 1">1</a>
                            @if($start > 2)
                                <span class="px-2 py-2 text-sm text-gray-400">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            <a href="{{ $payroll_alerts->url($page) . '&' . http_build_query(request()->except('page')) }}"
                               class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $page == $currentPage ? 'text-white bg-green-600 border border-green-600 shadow-sm' : 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' }}"
                               aria-label="Page {{ $page }}"
                               aria-current="{{ $page == $currentPage ? 'page' : 'false' }}">{{ $page }}</a>
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="px-2 py-2 text-sm text-gray-400">...</span>
                            @endif
                            <a href="{{ $payroll_alerts->url($lastPage) . '&' . http_build_query(request()->except('page')) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300 rounded-lg transition-all duration-200"
                               aria-label="Page {{ $lastPage }}">{{ $lastPage }}</a>
                        @endif

                        <!-- Next Button -->
                        <a href="{{ $payroll_alerts->nextPageUrl() ? $payroll_alerts->nextPageUrl() . '&' . http_build_query(request()->except('page')) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $payroll_alerts->hasMorePages() ? 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' : 'text-gray-400 bg-gray-100 cursor-not-allowed' }}"
                           aria-label="Next page"
                           {{ !$payroll_alerts->hasMorePages() ? 'disabled' : '' }}>
                            <i class="fas fa-chevron-right text-xs"></i>
                        </a>
                    </nav>
                </div>
            @endif
        </div>
    @endif

    <!-- MODALS SECTION -->
    @include('payroll.modals.run-payroll')
    @include('payroll.modals.retroactive-pay')
    @include('payroll.modals.revert-payroll')
    @include('payroll.modals.revert-all')
    @include('payroll.modals.payroll-details')
    @include('payroll.modals.transaction-details')
    @include('payroll.modals.alert-details')

@endsection

@section('scripts')
    <!-- Include Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded - initializing payroll management system');

            // Initialize tabs
            initializeTabs();
            
            // Initialize search functionality
            initializeSearch();
            
            // Initialize date pickers
            initializeDatePickers();
            
            // Initialize month filter
            initializeMonthFilter();

            // Add smooth animations
            addSmoothAnimations();
        });

        // Tab Management
        function initializeTabs() {
            const tabs = {
                payroll: { 
                    tab: document.getElementById('payrollTab'), 
                    container: document.getElementById('payrollContainer') 
                },
                transactions: { 
                    tab: document.getElementById('transactionsTab'), 
                    container: document.getElementById('transactionsContainer') 
                },
                alerts: { 
                    tab: document.getElementById('alertsTab'), 
                    container: document.getElementById('alertsContainer') 
                }
            };

            // Initialize from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'payroll';
            switchTab(activeTab);

            // Add click events
            Object.keys(tabs).forEach(tabKey => {
                if (tabs[tabKey].tab) {
                    tabs[tabKey].tab.addEventListener('click', () => {
                        switchTab(tabKey);
                    });
                }
            });
        }

        function switchTab(tabName) {
            // Hide all containers
            document.querySelectorAll('[id$="Container"]').forEach(container => {
                container.classList.add('hidden');
            });

            // Reset all tabs
            document.querySelectorAll('[role="tab"]').forEach(tab => {
                tab.classList.remove('text-white', 'bg-green-600', 'shadow-inner');
                tab.classList.add('text-gray-700', 'bg-gray-100', 'hover:bg-gray-200');
                tab.setAttribute('aria-selected', 'false');
            });

            // Activate selected tab and container
            const activeContainer = document.getElementById(tabName + 'Container');
            const activeTab = document.getElementById(tabName + 'Tab');

            if (activeContainer && activeTab) {
                activeContainer.classList.remove('hidden');
                activeTab.classList.remove('text-gray-700', 'bg-gray-100', 'hover:bg-gray-200');
                activeTab.classList.add('text-white', 'bg-green-600', 'shadow-inner');
                activeTab.setAttribute('aria-selected', 'true');

                // Add fade-in animation
                activeContainer.style.opacity = '0';
                activeContainer.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    activeContainer.style.transition = 'all 0.3s ease';
                    activeContainer.style.opacity = '1';
                    activeContainer.style.transform = 'translateY(0)';
                }, 50);
            }

            // Update URL
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.replaceState({}, '', url);
        }

        // Search Functionality
        function initializeSearch() {
            const searchConfig = [
                { inputId: 'searchPayroll', tableId: 'payrollTable', rowClass: 'payroll-row' },
                { inputId: 'searchTransaction', tableId: 'transactionTable', rowClass: 'transaction-row' },
                { inputId: 'searchAlerts', tableId: 'alertTable', rowClass: 'alert-row' }
            ];

            searchConfig.forEach(config => {
                const input = document.getElementById(config.inputId);
                if (input) {
                    let searchTimeout;
                    input.addEventListener('input', function() {
                        clearTimeout(searchTimeout);
                        const searchTerm = this.value.toLowerCase();
                        
                        // Show loading state
                        const rows = document.querySelectorAll(`.${config.rowClass}`);
                        rows.forEach(row => row.style.opacity = '0.6');
                        
                        searchTimeout = setTimeout(() => {
                            filterTable(config.tableId, config.rowClass, searchTerm);
                        }, 300);
                    });
                }
            });
        }

        function filterTable(tableId, rowClass, searchTerm) {
            const rows = document.querySelectorAll(`.${rowClass}`);
            let visibleCount = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    row.style.opacity = '1';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update count
            const countElement = document.getElementById(tableId.replace('Table', 'Count'));
            if (countElement) {
                countElement.textContent = visibleCount;
            }
        }

        // Date Pickers
        function initializeDatePickers() {
            // Month filter
            const monthFilter = document.getElementById('monthFilter');
            if (monthFilter) {
                flatpickr(monthFilter, {
                    dateFormat: "Y-m",
                    plugins: [new monthSelectPlugin({
                        shorthand: true,
                        dateFormat: "Y-m",
                        altFormat: "F Y"
                    })],
                    onChange: function(selectedDates) {
                        if (selectedDates.length > 0) {
                            filterByMonth(selectedDates[0]);
                        }
                    }
                });
            }

            // Modal date pickers
            const modalDateInputs = ['payroll_period', 'retro_period'];
            modalDateInputs.forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    flatpickr(element, {
                        dateFormat: "Y-m",
                        plugins: [new monthSelectPlugin({
                            shorthand: true,
                            dateFormat: "Y-m",
                            altFormat: "F Y"
                        })]
                    });
                }
            });
        }

        // Month Filter
        function initializeMonthFilter() {
            // Already handled in date pickers initialization
        }

        function filterByMonth(date) {
            const selectedMonth = date.getMonth() + 1;
            const selectedYear = date.getFullYear();
            const monthYear = `${selectedYear}-${selectedMonth.toString().padStart(2, '0')}`;

            const rowTypes = [
                { class: 'payroll-row', countId: 'payrollCount' },
                { class: 'transaction-row', countId: 'transactionCount' },
                { class: 'alert-row', countId: 'alertCount' }
            ];

            rowTypes.forEach(rowType => {
                let visible = 0;
                document.querySelectorAll(`.${rowType.class}`).forEach(row => {
                    const rowPeriod = row.getAttribute('data-period');
                    if (rowPeriod === monthYear) {
                        row.style.display = '';
                        visible++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                const countEl = document.getElementById(rowType.countId);
                if (countEl) countEl.textContent = visible;
            });
        }

        function clearMonthFilter() {
            const monthFilter = document.getElementById('monthFilter');
            if (monthFilter) monthFilter.value = '';

            const allRows = document.querySelectorAll('.payroll-row, .transaction-row, .alert-row');
            allRows.forEach(row => row.style.display = '');

            // Reset counts
            document.getElementById('payrollCount').textContent = document.querySelectorAll('.payroll-row').length;
            document.getElementById('transactionCount').textContent = document.querySelectorAll('.transaction-row').length;
            document.getElementById('alertCount').textContent = document.querySelectorAll('.alert-row').length;
        }

        // Modal Functions
        function openModal(modalId) {
            console.log('Opening modal:', modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                
                // Add animation
                setTimeout(() => {
                    const modalContent = modal.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.classList.remove('scale-95', 'opacity-0');
                        modalContent.classList.add('scale-100', 'opacity-100');
                    }
                }, 10);
            }
        }

        function closeModal(modalId) {
            console.log('Closing modal:', modalId);
            const modal = document.getElementById(modalId);
            if (modal) {
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.remove('scale-100', 'opacity-100');
                    modalContent.classList.add('scale-95', 'opacity-0');
                }
                
                setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = 'auto';
                }, 300);
            }
        }

        // Refresh Functions
        function refreshPayroll() {
            const btn = event.target.closest('button');
            btn.classList.add('animate-spin');
            setTimeout(() => {
                btn.classList.remove('animate-spin');
                window.location.reload();
            }, 1000);
        }

        function refreshTransactions() {
            const btn = event.target.closest('button');
            btn.classList.add('animate-spin');
            setTimeout(() => {
                btn.classList.remove('animate-spin');
                switchTab('transactions');
                window.location.reload();
            }, 1000);
        }

        function refreshAlerts() {
            const btn = event.target.closest('button');
            btn.classList.add('animate-spin');
            setTimeout(() => {
                btn.classList.remove('animate-spin');
                switchTab('alerts');
                window.location.reload();
            }, 1000);
        }

        // Alert Functions
        function markAlertAsRead(alertId) {
            fetch(`/dashboard/payroll/alert/${alertId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close modal if open
                    closeModal('alertDetailsModal');
                    // Reload to reflect changes
                    setTimeout(() => location.reload(), 500);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function markAllAlertsAsRead() {
            if (confirm('Are you sure you want to mark all alerts as read?')) {
                fetch(`/dashboard/payroll/alerts/mark-all-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }

        // View Details Functions
        function viewPayrollDetails(payrollId) {
            // Implementation for viewing payroll details
            console.log('View payroll details:', payrollId);
            openModal('payrollDetailsModal');
        }

        function viewTransactionDetails(transactionId) {
            // Implementation for viewing transaction details
            console.log('View transaction details:', transactionId);
            openModal('transactionDetailsModal');
        }

        function viewAlertDetails(alertId) {
            // Implementation for viewing alert details
            console.log('View alert details:', alertId);
            openModal('alertDetailsModal');
        }

        function revertPayroll(payrollId) {
            if (confirm('Are you sure you want to revert this payroll? This action cannot be undone.')) {
                // Implementation for reverting payroll
                console.log('Revert payroll:', payrollId);
            }
        }

        // Smooth Animations
        function addSmoothAnimations() {
            // Add hover effects to table rows
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(4px)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateX(0)';
                });
            });

            // Add loading states to buttons
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    if (this.type === 'submit' || this.getAttribute('onclick')?.includes('submit')) {
                        const spinner = this.querySelector('.fa-spinner') || this.querySelector('.animate-spin');
                        if (spinner) {
                            spinner.classList.remove('hidden');
                        }
                    }
                });
            });
        }

        // Close modals when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed')) {
                const modalId = event.target.id;
                if (modalId.includes('Modal')) {
                    closeModal(modalId);
                }
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const openModals = document.querySelectorAll('.fixed:not(.hidden)');
                openModals.forEach(modal => {
                    if (modal.id.includes('Modal')) {
                        closeModal(modal.id);
                    }
                });
            }
        });
    </script>

    <style>
        .flatpickr-calendar {
            background: white !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
        .flatpickr-day.selected {
            background: #10b981 !important;
            border-color: #10b981 !important;
        }
        .flatpickr-day.today {
            border-color: #10b981 !important;
        }

        /* Smooth transitions for all elements */
        * {
            transition-property: color, background-color, border-color, transform, opacity;
            transition-duration: 200ms;
            transition-timing-function: ease-in-out;
        }

        /* Custom animations */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .animate-shake {
            animation: shake 0.5s ease-in-out;
        }

        /* Hover effects for cards */
        .hover-lift:hover {
            transform: translateY(-2px);
        }

        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
    </style>
@endsection