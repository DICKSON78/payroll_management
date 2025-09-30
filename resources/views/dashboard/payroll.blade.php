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
    <span class="text-gray-600">Process and review payroll records for Summit.</span>
@endsection

@section('content')
    @if(!in_array(strtolower(Auth::user()->role), ['admin', 'hr']))
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
            <span class="block sm:inline">Unauthorized access. This page is restricted to Admin and HR roles only.</span>
        </div>
    @else
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
                    Payroll Records
                </button>
                <button id="transactionsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="transactionsContainer">
                    Transactions
                </button>
                <button id="alertsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="alertsContainer">
                    Alerts
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

                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchPayroll" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Search by ID or employee...">
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700 text-sm">
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
                                        'processed' => 'bg-green-100 text-green-800',
                                        'paid' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'failed' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusColor = $statusColors[strtolower($payroll->status)] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <tr class="bg-white hover:bg-gray-50 transition-all duration-200 payroll-row group" data-id="{{ strtolower($payroll->payroll_id ?? '') }}" data-employee="{{ strtolower($payroll->employee_name ?? '') }}" data-period="{{ strtolower($payroll->period ?? '') }}">
                                    <td class="py-4 px-6 text-sm text-gray-900 font-mono">{{ $payroll->payroll_id ?? 'N/A' }}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="font-medium text-green-800">{{ substr($payroll->employee_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $payroll->employee_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $payroll->employee->department ?? 'N/A' }} • {{ $payroll->employee->employee_id ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">{{ $payroll->employee->position ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-900">{{ $payroll->period ? \Carbon\Carbon::createFromFormat('Y-m', $payroll->period)->format('F Y') : 'N/A' }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-900 font-semibold">TZS {{ number_format($payroll->base_salary, 0) }}</td>
                                    <td class="py-4 px-6 text-sm text-green-600 font-semibold">+{{ number_format($payroll->allowances, 0) }}</td>
                                    <td class="py-4 px-6 text-sm text-red-600 font-semibold">-{{ number_format($payroll->deductions, 0) }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-900 font-bold">TZS {{ number_format($payroll->net_salary, 0) }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                                            {{ $payroll->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="viewPayrollDetails('{{ $payroll->payroll_id }}')" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition-all duration-200" title="View Details">
                                                <i class="fas fa-eye text-sm"></i>
                                            </button>
                                            <button onclick="revertPayroll('{{ $payroll->payroll_id }}')" class="text-red-600 hover:text-red-800 p-1.5 rounded-md hover:bg-red-50 transition-all duration-200" title="Revert Payroll">
                                                <i class="fas fa-undo text-sm"></i>
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
                    <div class="text-center py-12">
                        <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-file-invoice-dollar text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No payroll records found</h3>
                        <p class="text-gray-500 mb-6">Get started by running your first payroll.</p>
                        <button class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="openModal('runPayrollModal')">
                            <i class="fas fa-calculator mr-2"></i> Run Payroll
                        </button>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($payrolls->lastPage() > 1)
                <div class="mt-6 flex justify-center">
                    <nav class="flex items-center space-x-2" aria-label="Pagination">
                        <!-- Previous Button -->
                        <a href="{{ $payrolls->previousPageUrl() ? $payrolls->previousPageUrl() . ($payrolls->previousPageUrl() ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'payroll'])) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $payrolls->onFirstPage() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' }}"
                           aria-label="Previous page"
                           {{ $payrolls->onFirstPage() ? 'disabled' : '' }}>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
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
                            <a href="{{ $payrolls->url(1) . ($payrolls->url(1) ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'payroll'])) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800 rounded-md transition-all duration-200"
                               aria-label="Page 1">1</a>
                            @if($start > 2)
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            <a href="{{ $payrolls->url($page) . ($payrolls->url($page) ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'payroll'])) }}"
                               class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $page == $currentPage ? 'text-white bg-green-600' : 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' }}"
                               aria-label="Page {{ $page }}"
                               aria-current="{{ $page == $currentPage ? 'page' : 'false' }}">{{ $page }}</a>
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            @endif
                            <a href="{{ $payrolls->url($lastPage) . ($payrolls->url($lastPage) ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'payroll'])) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800 rounded-md transition-all duration-200"
                               aria-label="Page {{ $lastPage }}">{{ $lastPage }}</a>
                        @endif

                        <!-- Next Button -->
                        <a href="{{ $payrolls->nextPageUrl() ? $payrolls->nextPageUrl() . ($payrolls->nextPageUrl() ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'payroll'])) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $payrolls->hasMorePages() ? 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' : 'text-gray-400 bg-gray-100 cursor-not-allowed' }}"
                           aria-label="Next page"
                           {{ !$payrolls->hasMorePages() ? 'disabled' : '' }}>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
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

                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchTransaction" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Search by ID or employee...">
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700 text-sm">
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
                                        'completed' => 'bg-green-100 text-green-800',
                                        'processed' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'failed' => 'bg-red-100 text-red-800'
                                    ];
                                    $statusColor = $statusColors[strtolower($transaction->status)] ?? 'bg-gray-100 text-gray-800';
                                    $typeColors = [
                                        'salary_payment' => 'text-blue-600 bg-blue-50',
                                        'bonus' => 'text-green-600 bg-green-50',
                                        'deduction' => 'text-red-600 bg-red-50',
                                        'adjustment' => 'text-purple-600 bg-purple-50'
                                    ];
                                    $typeColor = $typeColors[strtolower($transaction->type)] ?? 'text-gray-600 bg-gray-50';
                                @endphp
                                <tr class="bg-white hover:bg-gray-50 transition-all duration-200 transaction-row group" data-id="{{ strtolower($transaction->transaction_id ?? '') }}" data-employee="{{ strtolower($transaction->employee_name ?? '') }}" data-period="{{ strtolower($transaction->transaction_date ? \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m') : '') }}">
                                    <td class="py-4 px-6 text-sm text-gray-900 font-mono">{{ $transaction->transaction_id ?? 'N/A' }}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="font-medium text-green-800">{{ substr($transaction->employee_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $transaction->employee_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $transaction->employee->department ?? 'N/A' }} • {{ $transaction->employee->employee_id ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">{{ $transaction->employee->position ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-900 font-bold">TZS {{ number_format($transaction->amount, 0) }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-500">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('M d, Y') }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">
                                            {{ str_replace('_', ' ', ucfirst($transaction->type)) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                                            {{ $transaction->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="viewTransactionDetails('{{ $transaction->transaction_id }}')" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition-all duration-200" title="View Details">
                                                <i class="fas fa-eye text-sm"></i>
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
                    <div class="text-center py-12">
                        <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-exchange-alt text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No transactions found</h3>
                        <p class="text-gray-500">Transactions will appear here after payroll processing.</p>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($transactions->lastPage() > 1)
                <div class="mt-6 flex justify-center">
                    <nav class="flex items-center space-x-2" aria-label="Pagination">
                        <!-- Previous Button -->
                        <a href="{{ $transactions->previousPageUrl() ? $transactions->previousPageUrl() . ($transactions->previousPageUrl() ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'transactions'])) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $transactions->onFirstPage() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' }}"
                           aria-label="Previous page"
                           {{ $transactions->onFirstPage() ? 'disabled' : '' }}>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
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
                            <a href="{{ $transactions->url(1) . ($transactions->url(1) ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'transactions'])) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800 rounded-md transition-all duration-200"
                               aria-label="Page 1">1</a>
                            @if($start > 2)
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            <a href="{{ $transactions->url($page) . ($transactions->url($page) ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'transactions'])) }}"
                               class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $page == $currentPage ? 'text-white bg-green-600' : 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' }}"
                               aria-label="Page {{ $page }}"
                               aria-current="{{ $page == $currentPage ? 'page' : 'false' }}">{{ $page }}</a>
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            @endif
                            <a href="{{ $transactions->url($lastPage) . ($transactions->url($lastPage) ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'transactions'])) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800 rounded-md transition-all duration-200"
                               aria-label="Page {{ $lastPage }}">{{ $lastPage }}</a>
                        @endif

                        <!-- Next Button -->
                        <a href="{{ $transactions->nextPageUrl() ? $transactions->nextPageUrl() . ($transactions->nextPageUrl() ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'transactions'])) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $transactions->hasMorePages() ? 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' : 'text-gray-400 bg-gray-100 cursor-not-allowed' }}"
                           aria-label="Next page"
                           {{ !$transactions->hasMorePages() ? 'disabled' : '' }}>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
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

                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchAlerts" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Search by ID or employee...">
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700 text-sm">
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
                                        'unread' => 'bg-yellow-100 text-yellow-800',
                                        'read' => 'bg-green-100 text-green-800'
                                    ];
                                    $statusColor = $statusColors[strtolower($alert->status)] ?? 'bg-gray-100 text-gray-800';
                                    $typeColors = [
                                        'high_deductions' => 'text-red-600 bg-red-50',
                                        'retroactive_adjustment' => 'text-blue-600 bg-blue-50',
                                        'payroll_reverted' => 'text-orange-600 bg-orange-50',
                                        'system_alert' => 'text-purple-600 bg-purple-50'
                                    ];
                                    $typeColor = $typeColors[strtolower(str_replace(' ', '_', $alert->type))] ?? 'text-gray-600 bg-gray-50';
                                @endphp
                                <tr class="bg-white hover:bg-gray-50 transition-all duration-200 alert-row group" data-id="{{ strtolower($alert->alert_id ?? '') }}" data-employee="{{ strtolower($alert->employee_name ?? '') }}" data-period="{{ strtolower($alert->created_at ? \Carbon\Carbon::parse($alert->created_at)->format('Y-m') : '') }}">
                                    <td class="py-4 px-6 text-sm text-gray-900 font-mono">{{ $alert->alert_id ?? 'N/A' }}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="font-medium text-green-800">{{ substr($alert->employee_name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $alert->employee_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $alert->employee->department ?? 'N/A' }} • {{ $alert->employee->employee_id ?? 'N/A' }}</div>
                                                <div class="text-xs text-gray-400">{{ $alert->employee->position ?? 'N/A' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">
                                            {{ $alert->type }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-500 max-w-xs truncate">{{ $alert->message }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-500">{{ \Carbon\Carbon::parse($alert->created_at)->format('M d, Y') }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                                            {{ $alert->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="viewAlertDetails('{{ $alert->alert_id }}')" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition-all duration-200" title="View Details">
                                                <i class="fas fa-eye text-sm"></i>
                                            </button>
                                            @if(strtolower($alert->status) === 'unread')
                                                <button onclick="markAlertAsRead('{{ $alert->alert_id }}')" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-md hover:bg-blue-50 transition-all duration-200" title="Mark as Read">
                                                    <i class="fas fa-check text-sm"></i>
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
                    <div class="text-center py-12">
                        <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-bell text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No alerts found</h3>
                        <p class="text-gray-500">Alerts will appear here when payroll issues are detected.</p>
                    </div>
                @endif
            </div>

            <!-- Pagination -->
            @if($payroll_alerts->lastPage() > 1)
                <div class="mt-6 flex justify-center">
                    <nav class="flex items-center space-x-2" aria-label="Pagination">
                        <!-- Previous Button -->
                        <a href="{{ $payroll_alerts->previousPageUrl() ? $payroll_alerts->previousPageUrl() . ($payroll_alerts->previousPageUrl() ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'alerts'])) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $payroll_alerts->onFirstPage() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' }}"
                           aria-label="Previous page"
                           {{ $payroll_alerts->onFirstPage() ? 'disabled' : '' }}>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
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
                            <a href="{{ $payroll_alerts->url(1) . ($payroll_alerts->url(1) ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'alerts'])) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800 rounded-md transition-all duration-200"
                               aria-label="Page 1">1</a>
                            @if($start > 2)
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            @endif
                        @endif

                        @for($page = $start; $page <= $end; $page++)
                            <a href="{{ $payroll_alerts->url($page) . ($payroll_alerts->url($page) ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'alerts'])) }}"
                               class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $page == $currentPage ? 'text-white bg-green-600' : 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' }}"
                               aria-label="Page {{ $page }}"
                               aria-current="{{ $page == $currentPage ? 'page' : 'false' }}">{{ $page }}</a>
                        @endfor

                        @if($end < $lastPage)
                            @if($end < $lastPage - 1)
                                <span class="px-3 py-2 text-sm text-gray-500">...</span>
                            @endif
                            <a href="{{ $payroll_alerts->url($lastPage) . ($payroll_alerts->url($lastPage) ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'alerts'])) }}"
                               class="px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800 rounded-md transition-all duration-200"
                               aria-label="Page {{ $lastPage }}">{{ $lastPage }}</a>
                        @endif

                        <!-- Next Button -->
                        <a href="{{ $payroll_alerts->nextPageUrl() ? $payroll_alerts->nextPageUrl() . ($payroll_alerts->nextPageUrl() ? '&' : '?') . http_build_query(array_merge(request()->query(), ['tab' => 'alerts'])) : '#' }}"
                           class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $payroll_alerts->hasMorePages() ? 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' : 'text-gray-400 bg-gray-100 cursor-not-allowed' }}"
                           aria-label="Next page"
                           {{ !$payroll_alerts->hasMorePages() ? 'disabled' : '' }}>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </nav>
                </div>
            @endif
        </div>
    @endif
@endsection

@section('modals')
    <!-- Run Payroll Modal -->
    <div id="runPayrollModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="runPayrollModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="runPayrollModalTitle">
                        <i class="fas fa-calculator mr-2"></i> Run Payroll
                    </h3>
                    <button type="button" onclick="closeModal('runPayrollModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close run payroll modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="runPayrollForm" action="{{ route('payroll.run') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="payroll_period" class="block text-gray-600 text-sm font-medium mb-1">Payroll Period</label>
                            <input type="text" id="payroll_period" name="payroll_period" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" placeholder="Select month and year" required readonly>
                        </div>
                        <div>
                            <label for="employee_selection" class="block text-gray-600 text-sm font-medium mb-1">Employee Selection</label>
                            <select id="employee_selection" name="employee_selection" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required>
                                <option value="all">All Active Employees</option>
                                <option value="single">Single Employee</option>
                                <option value="multiple">Multiple Employees</option>
                            </select>
                        </div>
                        <div id="employee_id_single" class="hidden">
                            <label for="employee_id_select" class="block text-gray-600 text-sm font-medium mb-1">Select Employee</label>
                            <select id="employee_id_select" name="employee_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                                @foreach($employees->where('status', 'active') as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->department ?? 'N/A' }} - {{ $employee->employee_id ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="employee_id_multiple" class="hidden">
                            <label for="employee_ids_select" class="block text-gray-600 text-sm font-medium mb-1">Select Employees</label>
                            <select id="employee_ids_select" name="employee_ids[]" multiple class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" size="4">
                                @foreach($employees->where('status', 'active') as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->department ?? 'N/A' }} - {{ $employee->employee_id ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple employees</p>
                        </div>
                        <div>
                            <label for="nssf_rate" class="block text-gray-600 text-sm font-medium mb-1">NSSF Rate (%)</label>
                            <input type="number" id="nssf_rate" name="nssf_rate" step="0.1" value="{{ $settings['nssf_employee_rate'] ?? 10.0 }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required>
                        </div>
                        <div>
                            <label for="nhif_rate" class="block text-gray-600 text-sm font-medium mb-1">NHIF Rate (%)</label>
                            <input type="number" id="nhif_rate" name="nhif_rate" step="0.1" value="{{ $settings['nhif_employee_rate'] ?? 3.0 }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('runPayrollModal')">
                            <i class="fas fa-times mr-2"></i> Close
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-calculator mr-2"></i> Run Payroll
                            <svg id="runPayrollSpinner" class="hidden w-5 h-5 ml-2 animate-spin text-white" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Retroactive Pay Modal -->
    <div id="retroactivePayModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="retroactivePayModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="retroactivePayModalTitle">
                        <i class="fas fa-history mr-2"></i> Retroactive Pay
                    </h3>
                    <button type="button" onclick="closeModal('retroactivePayModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close retroactive pay modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="retroactivePayForm" action="{{ route('payroll.retro') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="retro_period" class="block text-gray-600 text-sm font-medium mb-1">Retroactive Period</label>
                            <input type="text" id="retro_period" name="retro_period" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" placeholder="Select month and year" required readonly>
                        </div>
                        <div>
                            <label for="retro_employee_selection" class="block text-gray-600 text-sm font-medium mb-1">Employee Selection</label>
                            <select id="retro_employee_selection" name="employee_selection" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required>
                                <option value="all">All Employees with Payroll</option>
                                <option value="single">Single Employee</option>
                                <option value="multiple">Multiple Employees</option>
                            </select>
                        </div>
                        <div id="retro_employee_single" class="hidden">
                            <label for="retro_employee_id" class="block text-gray-600 text-sm font-medium mb-1">Select Employee</label>
                            <select id="retro_employee_id" name="employee_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->department ?? 'N/A' }} - {{ $employee->employee_id ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="retro_employee_multiple" class="hidden">
                            <label for="retro_employee_ids" class="block text-gray-600 text-sm font-medium mb-1">Select Employees</label>
                            <select id="retro_employee_ids" name="employee_ids[]" multiple class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" size="4">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->department ?? 'N/A' }} - {{ $employee->employee_id ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple employees</p>
                        </div>
                        <div>
                            <label for="adjustment_type" class="block text-gray-600 text-sm font-medium mb-1">Adjustment Type</label>
                            <select id="adjustment_type" name="adjustment_type" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required>
                                <option value="bonus">Bonus Payment</option>
                                <option value="deduction">Deduction</option>
                                <option value="salary_adjustment">Salary Adjustment</option>
                                <option value="allowance">Allowance</option>
                            </select>
                        </div>
                        <div>
                            <label for="adjustment_amount" class="block text-gray-600 text-sm font-medium mb-1">Adjustment Amount (TZS)</label>
                            <input type="number" id="adjustment_amount" name="adjustment_amount" step="0.01" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required>
                        </div>
                        <div>
                            <label for="adjustment_reason" class="block text-gray-600 text-sm font-medium mb-1">Reason for Adjustment</label>
                            <textarea id="adjustment_reason" name="adjustment_reason" rows="3" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" placeholder="Enter reason for retroactive payment..." required></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('retroactivePayModal')">
                            <i class="fas fa-times mr-2"></i> Close
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-history mr-2"></i> Process Retroactive Pay
                            <svg id="retroactivePaySpinner" class="hidden w-5 h-5 ml-2 animate-spin text-white" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Revert Payroll Modal -->
    <div id="revertPayrollModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="revertPayrollModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="revertPayrollModalTitle">
                        <i class="fas fa-undo mr-2"></i> Revert Payroll
                    </h3>
                    <button type="button" onclick="closeModal('revertPayrollModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close revert payroll modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="revertPayrollForm" action="{{ route('payroll.revert') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="revert_period" class="block text-gray-600 text-sm font-medium mb-1">Payroll Period (Optional - Revert All)</label>
                            <select id="revert_period" name="period" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                                <option value="" selected>Select period to revert all</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select a period to revert ALL payrolls for that period</p>
                        </div>
                        <div>
                            <label for="payroll_id" class="block text-gray-600 text-sm font-medium mb-1">Or Select Specific Payroll</label>
                            <select id="payroll_id" name="payroll_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                                <option value="" disabled selected>Select a payroll record</option>
                                @foreach($payrolls as $payroll)
                                    <option value="{{ $payroll->payroll_id }}" data-period="{{ $payroll->period }}">{{ $payroll->payroll_id }} - {{ $payroll->employee_name }} ({{ $payroll->period }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Select a specific payroll to revert</p>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('revertPayrollModal')">
                            <i class="fas fa-times mr-2"></i> Close
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-undo mr-2"></i> Revert Payroll
                            <svg id="revertPayrollSpinner" class="hidden w-5 h-5 ml-2 animate-spin text-white" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Revert All Data Modal -->
    <div id="revertAllModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="revertAllModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-red-50 border-b border-red-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-red-600 flex items-center" id="revertAllModalTitle">
                        <i class="fas fa-trash-alt mr-2"></i> Revert All Data
                    </h3>
                    <button type="button" onclick="closeModal('revertAllModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close revert all modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="revertAllForm" action="{{ route('payroll.revert.all') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2"></i>
                            <span class="text-yellow-800 font-medium">Warning: This action cannot be undone!</span>
                        </div>
                        <p class="text-yellow-700 text-sm mt-1">All selected data will be permanently deleted.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Select Data to Revert</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" name="revert_types[]" value="payroll" class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2">
                                    <span class="text-gray-700">All Payroll Records</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="revert_types[]" value="transactions" class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2">
                                    <span class="text-gray-700">All Transactions</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="revert_types[]" value="alerts" class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2">
                                    <span class="text-gray-700">All Alerts</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="revert_types[]" value="retroactive" class="rounded border-gray-300 text-red-600 focus:ring-red-500 mr-2">
                                    <span class="text-gray-700">All Retroactive Payments</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="revert_period_all" class="block text-gray-600 text-sm font-medium mb-1">Select Period (Optional)</label>
                            <select id="revert_period_all" name="period" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 block w-full p-2.5 transition-all duration-200">
                                <option value="" selected>All Periods</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period }}">{{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="revert_employee_all" class="block text-gray-600 text-sm font-medium mb-1">Select Employee (Optional)</label>
                            <select id="revert_employee_all" name="employee_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 block w-full p-2.5 transition-all duration-200">
                                <option value="" selected>All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->department ?? 'N/A' }} - {{ $employee->employee_id ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('revertAllModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-trash-alt mr-2"></i> Revert All Selected
                            <svg id="revertAllSpinner" class="hidden w-5 h-5 ml-2 animate-spin text-white" fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payroll Details Modal -->
    <div id="payrollDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="payrollDetailsModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="payrollDetailsModalTitle">
                        <i class="fas fa-file-invoice-dollar mr-2"></i> Payroll Details
                    </h3>
                    <button type="button" onclick="closeModal('payrollDetailsModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close payroll details modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Payroll ID</label>
                        <p id="payrollDetailsId" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Employee</label>
                        <p id="payrollDetailsEmployee" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Period</label>
                        <p id="payrollDetailsPeriod" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Base Salary (TZS)</label>
                        <p id="payrollDetailsBaseSalary" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Allowances (TZS)</label>
                        <p id="payrollDetailsAllowances" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Deductions (TZS)</label>
                        <p id="payrollDetailsDeductions" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Net Salary (TZS)</label>
                        <p id="payrollDetailsNetSalary" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Status</label>
                        <p id="payrollDetailsStatus" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Payment Method</label>
                        <p id="payrollDetailsPaymentMethod" class="text-gray-900"></p>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('payrollDetailsModal')">
                        <i class="fas fa-times mr-2"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Details Modal -->
    <div id="transactionDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="transactionDetailsModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="transactionDetailsModalTitle">
                        <i class="fas fa-exchange-alt mr-2"></i> Transaction Details
                    </h3>
                    <button type="button" onclick="closeModal('transactionDetailsModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close transaction details modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Transaction ID</label>
                        <p id="transactionDetailsId" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Employee</label>
                        <p id="transactionDetailsEmployee" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Amount (TZS)</label>
                        <p id="transactionDetailsAmount" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Date</label>
                        <p id="transactionDetailsDate" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Type</label>
                        <p id="transactionDetailsType" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Status</label>
                        <p id="transactionDetailsStatus" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Payment Method</label>
                        <p id="transactionDetailsPaymentMethod" class="text-gray-900"></p>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-gray-600 text-sm font-medium mb-1">Description</label>
                        <p id="transactionDetailsDescription" class="text-gray-900"></p>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('transactionDetailsModal')">
                        <i class="fas fa-times mr-2"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Details Modal -->
    <div id="alertDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="alertDetailsModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="alertDetailsModalTitle">
                        <i class="fas fa-bell mr-2"></i> Alert Details
                    </h3>
                    <button type="button" onclick="closeModal('alertDetailsModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close alert details modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Alert ID</label>
                        <p id="alertDetailsId" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Employee</label>
                        <p id="alertDetailsEmployee" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Type</label>
                        <p id="alertDetailsType" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Message</label>
                        <p id="alertDetailsMessage" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Status</label>
                        <p id="alertDetailsStatus" class="text-gray-900"></p>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-1">Date Created</label>
                        <p id="alertDetailsDate" class="text-gray-900"></p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('alertDetailsModal')">
                        <i class="fas fa-times mr-2"></i> Close
                    </button>
                    <button id="markAlertRead" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" data-alert-id="">
                        <i class="fas fa-check mr-2"></i> Mark as Read
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Flatpickr Initialization -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/plugins/monthSelect/index.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar {
            z-index: 10000 !important;
            background: #ffffff !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        }
        .modal-content {
            position: relative;
            overflow: visible !important;
        }
        .fixed.inset-0 {
            overflow: visible !important;
        }
        #runPayrollModal, #retroactivePayModal, #revertPayrollModal, #revertAllModal {
            z-index: 1000 !important;
        }
        .success-modal, .error-modal {
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            animation: slideIn 0.3s ease-out;
        }
        .success-modal {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .error-modal {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translate(-50%, -60%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }
        .tick-icon, .cross-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            animation: bounce 0.6s ease-in-out;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
    </style>

<script>
    // Tab Management & Search
document.addEventListener('DOMContentLoaded', function() {
    
    const tabs = {
        payroll: { tab: document.getElementById('payrollTab'), container: document.getElementById('payrollContainer') },
        transactions: { tab: document.getElementById('transactionsTab'), container: document.getElementById('transactionsContainer') },
        alerts: { tab: document.getElementById('alertsTab'), container: document.getElementById('alertsContainer') }
    };
    
    // Initialize tabs from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab') || 'payroll';
    switchTab(activeTab);

    // Tab click events
    Object.keys(tabs).forEach(tabKey => {
        tabs[tabKey].tab.addEventListener('click', () => switchTab(tabKey));
    });

    // Search functionality
    const searchElements = [
        { inputId: 'searchPayroll', tableId: 'payrollTable' },
        { inputId: 'searchTransaction', tableId: 'transactionTable' },
        { inputId: 'searchAlerts', tableId: 'alertTable' }
    ];

    searchElements.forEach(item => {
        const input = document.getElementById(item.inputId);
        if (input) {
            input.addEventListener('input', function() {
                filterTable(item.tableId, this.value.toLowerCase());
            });
        }
    });

    // Initialize all date pickers
    initializeDatePickers();

    // Initialize month filter calendar
    initializeMonthFilter();
});

// Tab switch
function switchTab(tabName) {
    document.querySelectorAll('[id$="Container"]').forEach(c => c.classList.add('hidden'));
    document.querySelectorAll('[role="tab"]').forEach(t => {
        t.classList.remove('text-white', 'bg-green-600');
        t.classList.add('text-gray-700', 'bg-gray-100', 'hover:bg-gray-200');
        t.setAttribute('aria-selected', 'false');
    });

    const activeContainer = document.getElementById(tabName + 'Container');
    const activeTab = document.getElementById(tabName + 'Tab');
    if (activeContainer && activeTab) {
        activeContainer.classList.remove('hidden');
        activeTab.classList.remove('text-gray-700', 'bg-gray-100', 'hover:bg-gray-200');
        activeTab.classList.add('text-white', 'bg-green-600');
        activeTab.setAttribute('aria-selected', 'true');
    }

    const url = new URL(window.location);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
}

// Table filter
function filterTable(tableId, searchTerm) {
    const rows = document.querySelectorAll(`#${tableId} tr`);
    let visibleCount = 0;
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
            visibleCount++;
        } else row.style.display = 'none';
    });

    const countElement = document.getElementById(tableId.replace('Table', 'Count'));
    if (countElement) countElement.textContent = visibleCount;
}

// Modals
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Month Filter (Basic flatpickr)
function initializeMonthFilter() {
    const monthFilter = document.getElementById('monthFilter');
    if (!monthFilter) return;

    let calendar = null;
    let selectedDate = null;

    monthFilter.addEventListener('focus', function() {
        if (calendar) return;

        calendar = flatpickr(monthFilter, {
            mode: "single",
            dateFormat: "Y-m-d",
            defaultDate: selectedDate,
            static: true,
            position: "auto",
            animate: true,
            onReady: function(selectedDates, dateStr, instance) {
                instance.calendarContainer.style.zIndex = 50;
            },
            onChange: function(selectedDates) {
                if (selectedDates.length > 0) {
                    selectedDate = selectedDates[0];
                    filterByMonth(selectedDate);
                }
            },
            onOpen: function(selectedDates, dateStr, instance) {
                document.addEventListener('click', function closeCalendar(e) {
                    if (!instance.calendarContainer.contains(e.target) && e.target !== monthFilter) {
                        instance.close();
                        document.removeEventListener('click', closeCalendar);
                    }
                });
            }
        });

        calendar.open();
    });

    monthFilter.addEventListener('click', function() {
        if (calendar) calendar.open();
    });
}

// Filter by selected month
function filterByMonth(date) {
    const selectedMonth = date.getMonth() + 1;
    const selectedYear = date.getFullYear();
    const monthYear = `${selectedYear}-${selectedMonth.toString().padStart(2, '0')}`;

    ['payroll-row', 'transaction-row', 'alert-row'].forEach(cls => {
        let visible = 0;
        document.querySelectorAll(`.${cls}`).forEach(row => {
            const rowPeriod = row.getAttribute('data-period');
            if (rowPeriod === monthYear) {
                row.style.display = '';
                visible++;
            } else row.style.display = 'none';
        });
        const countEl = document.getElementById(cls.replace('-row','Count'));
        if (countEl) countEl.textContent = visible;
    });
}

// Clear month filter
function clearMonthFilter() {
    const monthFilter = document.getElementById('monthFilter');
    if (monthFilter) monthFilter.value = '';

    const allRows = document.querySelectorAll('.payroll-row, .transaction-row, .alert-row');
    allRows.forEach(row => row.style.display = '');

    ['payroll', 'transaction', 'alert'].forEach(id => {
        const countEl = document.getElementById(id + 'Count');
        if (countEl) countEl.textContent = document.querySelectorAll(`.${id}-row`).length;
    });
}

// Initialize basic date pickers for all inputs that need calendar
function initializeDatePickers() {
    const dateInputs = ['monthFilter', 'payroll_period', 'retro_period'];
    dateInputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            flatpickr(el, {
                dateFormat: "Y-m-d"
            });
        }
    });
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) closeModal(event.target.id);
});

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal-overlay:not(.hidden)').forEach(modal => closeModal(modal.id));
    }
});

</script>
@endsection