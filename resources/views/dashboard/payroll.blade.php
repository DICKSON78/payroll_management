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
    <span class="text-gray-600">Process and review payroll records for {{ $settings['company_name'] }}.</span>
@endsection

@section('content')
    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-md border border-green-200 shadow-sm hover:bg-green-50 hover:shadow-lg transition-all duration-200 p-4 cursor-pointer" onclick="openModal('runPayrollModal')">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-50 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-calculator text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-green-600">Run Payroll</div>
                        <div class="text-sm text-gray-500">Calculate and process employee salaries</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-md border border-green-200 shadow-sm hover:bg-green-50 hover:shadow-lg transition-all duration-200 p-4 cursor-pointer" onclick="openModal('retroactivePayModal')">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-50 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-history text-yellow-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-green-600">Retroactive Pay</div>
                        <div class="text-sm text-gray-500">Adjust payments for a past period</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-md border border-green-200 shadow-sm hover:bg-green-50 hover:shadow-lg transition-all duration-200 p-4 cursor-pointer" onclick="openModal('revertPayrollModal')">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-50 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-undo text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-green-600">Revert Payroll</div>
                        <div class="text-sm text-gray-500">Undo the last payroll run</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-md border border-green-200 shadow-sm hover:bg-green-50 hover:shadow-lg transition-all duration-200 p-4 cursor-pointer" onclick="openModal('transactionsModal')">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-50 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-exchange-alt text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-green-600">Transactions</div>
                        <div class="text-sm text-gray-500">View payroll transaction history</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll History and Alerts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Payroll History Table -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex justify-between items-center px-6 pt-6 mb-4">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-bolt text-yellow-500 mr-2"></i> Payroll History
                    <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $payrolls->total() }} records</span>
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">ID</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Period</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Total Amount</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($payrolls as $payroll)
                        <tr class="bg-white hover:bg-gray-50 transition duration-150">
                            <td class="py-4 px-6 text-sm text-gray-600 font-mono">{{ $payroll->payroll_id }}</td>
                            <td class="py-4 px-6 text-sm text-gray-600">{{ \Carbon\Carbon::parse($payroll->payroll_period)->format('F Y') }}</td>
                            <td class="py-4 px-6 text-sm text-gray-600 font-medium">TZS {{ number_format($payroll->total_amount, 2) }}</td>
                            <td class="py-4 px-6 text-sm text-gray-600">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($payroll->status == 'Processed') bg-green-100 text-green-800 
                                    @elseif($payroll->status == 'Pending') bg-yellow-100 text-yellow-800 
                                    @elseif($payroll->status == 'Failed') bg-red-100 text-red-800 
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ $payroll->status }}
                                </span>
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
                    <i class="fas fa-bolt text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No payroll records found</h3>
                <p class="text-gray-500 mb-6">Get started by running your first payroll.</p>
                <button class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="openModal('runPayrollModal')">
                    <i class="fas fa-calculator mr-2"></i> Run Payroll
                </button>
            </div>
            @endif
            <!-- Pagination -->
            @if($payrolls->hasPages())
            <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5 px-6">
                <div class="text-sm text-gray-700">
                    Showing {{ $payrolls->firstItem() }} to {{ $payrolls->lastItem() }} of {{ $payrolls->total() }} results
                </div>
                <div class="flex space-x-2">
                    @if($payrolls->onFirstPage())
                    <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Previous</span>
                    @else
                    <a href="{{ $payrolls->previousPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Previous</a>
                    @endif
                    @if($payrolls->hasMorePages())
                    <a href="{{ $payrolls->nextPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Next</a>
                    @else
                    <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Next</span>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Payroll Alerts -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex justify-between items-center px-6 pt-6 mb-4">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i> Payroll Alerts
                    <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $payroll_alerts->count() }} alerts</span>
                </h3>
            </div>
            <div class="space-y-4 px-6 pb-6">
                @if($payroll_alerts->isEmpty())
                <div class="bg-green-50 rounded-lg p-4 text-center text-green-800">
                    <i class="fas fa-check-circle text-green-600 mr-2"></i> No pending alerts.
                </div>
                @else
                @foreach($payroll_alerts as $alert)
                <div class="flex items-center p-4 rounded-lg bg-yellow-50 border border-yellow-200 shadow-sm">
                    <div class="flex-shrink-0 text-yellow-600 mr-4">
                        <i class="fas fa-exclamation-triangle text-xl"></i>
                    </div>
                    <div class="flex-grow">
                        <p class="text-sm font-medium text-gray-800">{{ $alert->alert_type }} for {{ $alert->employee->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-600">{{ $alert->message }}</p>
                    </div>
                    <a href="#" class="text-white bg-yellow-600 hover:bg-yellow-700 focus:ring-4 focus:ring-yellow-300 font-medium rounded-md text-sm px-3 py-1.5 text-center transition-all duration-200 ml-4">
                        <i class="fas fa-eye mr-1"></i> View
                    </a>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>

    <!-- Run Payroll Modal -->
    <div id="runPayrollModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="runPayrollModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="runPayrollModalTitle">
                        <i class="fas fa-calculator mr-2"></i> Run Payroll
                    </h3>
                    <button type="button" onclick="closeModal('runPayrollModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="runPayrollForm" action="{{ route('payroll.run') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="payroll_period" class="block text-gray-600 text-sm font-medium mb-2">Payroll Period</label>
                            <input type="month" name="payroll_period" id="payroll_period" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 sm:text-sm" readonly>
                            <span class="text-red-500 text-sm hidden" id="payrollPeriodError">Payroll Period is required</span>
                            @error('payroll_period')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="employees" class="block text-gray-600 text-sm font-medium mb-2">Select Employees</label>
                            <select name="employee_ids[]" id="employees" multiple required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 sm:text-sm">
                                <option value="all">All Employees</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->department }})</option>
                                @endforeach
                            </select>
                            <span class="text-red-500 text-sm hidden" id="employeesError">At least one employee must be selected</span>
                            @error('employee_ids')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nssf_rate" class="block text-gray-600 text-sm font-medium mb-2">NSSF Rate (%)</label>
                            <input type="number" step="0.01" name="nssf_rate" id="nssf_rate" required value="10" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 sm:text-sm">
                            <span class="text-red-500 text-sm hidden" id="nssfRateError">NSSF Rate is required</span>
                            @error('nssf_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nhif_rate" class="block text-gray-600 text-sm font-medium mb-2">NHIF Rate (%)</label>
                            <input type="number" step="0.01" name="nhif_rate" id="nhif_rate" required value="6" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 sm:text-sm">
                            <span class="text-red-500 text-sm hidden" id="nhifRateError">NHIF Rate is required</span>
                            @error('nhif_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-4">
                        <button type="button" class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('runPayrollModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                            <span id="formSpinner" class="hidden animate-spin h-4 w-4 mr-2 border-t-2 border-r-2 border-white rounded-full"></span>
                            <i class="fas fa-calculator mr-2"></i> Run Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Revert Payroll Modal -->
    <div id="revertPayrollModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="revertPayrollModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-red-600 flex items-center" id="revertPayrollModalTitle">
                        <i class="fas fa-undo mr-2"></i> Revert Last Payroll
                    </h3>
                    <button type="button" onclick="closeModal('revertPayrollModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-4">Are you sure you want to revert the last payroll run? This action cannot be undone and will delete all associated payslips and transactions.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('revertPayrollModal')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" form="revertPayrollForm" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                        <i class="fas fa-undo mr-2"></i> Revert Payroll
                    </button>
                </div>
            </div>
        </div>
        <form id="revertPayrollForm" action="{{ route('payroll.revert') }}" method="POST" class="hidden">
            @csrf
        </form>
    </div>

    <!-- Retroactive Pay Modal -->
    <div id="retroactivePayModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="retroactivePayModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="retroactivePayModalTitle">
                        <i class="fas fa-history mr-2"></i> Retroactive Pay
                    </h3>
                    <button type="button" onclick="closeModal('retroactivePayModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="retroactivePayForm" action="{{ route('payroll.retro') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="retro_period" class="block text-gray-600 text-sm font-medium mb-2">Retroactive Period</label>
                            <input type="month" name="retro_period" id="retro_period" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 sm:text-sm" readonly>
                            <span class="text-red-500 text-sm hidden" id="retroPeriodError">Retroactive Period is required</span>
                            @error('retro_period')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="retro_employees" class="block text-gray-600 text-sm font-medium mb-2">Select Employees</label>
                            <select name="employee_ids[]" id="retro_employees" multiple required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5 sm:text-sm">
                                <option value="all">All Employees</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->department }})</option>
                                @endforeach
                            </select>
                            <span class="text-red-500 text-sm hidden" id="retroEmployeesError">At least one employee must be selected</span>
                            @error('employee_ids')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-4">
                        <button type="button" class="text-white bg-gray-500 hover:bg-gray-600 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('retroactivePayModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                            <i class="fas fa-history mr-2"></i> Process Retroactive Pay
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Transactions Modal -->
    <div id="transactionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="transactionsModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-4xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="transactionsModalTitle">
                        <i class="fas fa-exchange-alt mr-2"></i> Payroll Transactions
                    </h3>
                    <button type="button" onclick="closeModal('transactionsModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 text-gray-700 text-sm">
                                    <th class="py-3.5 px-6 text-left font-semibold">Transaction ID</th>
                                    <th class="py-3.5 px-6 text-left font-semibold">Employee</th>
                                    <th class="py-3.5 px-6 text-left font-semibold">Amount</th>
                                    <th class="py-3.5 px-6 text-left font-semibold">Date</th>
                                    <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($transactions as $transaction)
                                <tr class="bg-white hover:bg-gray-50 transition duration-150">
                                    <td class="py-4 px-6 text-sm text-gray-600 font-mono">{{ $transaction->id }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-600">{{ $transaction->employee->name ?? 'N/A' }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-600 font-medium">TZS {{ number_format($transaction->amount, 2) }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-600">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('F d, Y') }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-600">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                            @if($transaction->status == 'Processed') bg-green-100 text-green-800 
                                            @elseif($transaction->status == 'Pending') bg-yellow-100 text-yellow-800 
                                            @elseif($transaction->status == 'Failed') bg-red-100 text-red-800 
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ $transaction->status }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($transactions->count() == 0)
                    <div class="text-center py-12">
                        <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                            <i class="fas fa-exchange-alt text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No transactions found</h3>
                        <p class="text-gray-500 mb-6">Transaction history will appear here after running payroll.</p>
                    </div>
                    @endif
                    @if($transactions->hasPages())
                    <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5 px-6">
                        <div class="text-sm text-gray-700">
                            Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} results
                        </div>
                        <div class="flex space-x-2">
                            @if($transactions->onFirstPage())
                            <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Previous</span>
                            @else
                            <a href="{{ $transactions->previousPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Previous</a>
                            @endif
                            @if($transactions->hasMorePages())
                            <a href="{{ $transactions->nextPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Next</a>
                            @else
                            <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Next</span>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        // Make month inputs readonly but allow picker
        document.addEventListener('DOMContentLoaded', function() {
            const monthInputs = ['payroll_period', 'retro_period'];
            monthInputs.forEach(id => {
                const input = document.getElementById(id);
                if (input) {
                    input.setAttribute('readonly', 'readonly');
                    input.style.cursor = 'pointer';
                    input.addEventListener('focus', function() {
                        this.removeAttribute('readonly');
                    });
                    input.addEventListener('blur', function() {
                        this.setAttribute('readonly', 'readonly');
                    });
                    input.addEventListener('click', function() {
                        this.showPicker();
                    });
                }
            });
        });

        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.querySelector('.modal-content').classList.add('scale-100');
                modal.querySelector('.modal-content').classList.remove('scale-95');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.querySelector('.modal-content').classList.add('scale-95');
                modal.querySelector('.modal-content').classList.remove('scale-100');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            ['runPayrollForm', 'retroactivePayForm'].forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener('submit', function(e) {
                        let valid = true;
                        form.querySelectorAll('[required]').forEach(input => {
                            const errorElement = document.getElementById(`${input.id}Error`);
                            if (!input.value.trim()) {
                                valid = false;
                                if (errorElement) errorElement.classList.remove('hidden');
                            } else {
                                if (errorElement) errorElement.classList.add('hidden');
                            }
                        });
                        if (!valid) {
                            e.preventDefault();
                            return;
                        }
                        const submitButton = form.querySelector('button[type="submit"]');
                        const spinner = document.getElementById('formSpinner');
                        submitButton.disabled = true;
                        if (spinner) spinner.classList.remove('hidden');
                    });
                }
            });

            const revertPayrollForm = document.getElementById('revertPayrollForm');
            if (revertPayrollForm) {
                revertPayrollForm.addEventListener('submit', function(e) {
                    const submitButton = revertPayrollForm.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Reverting...';
                });
            }
        });
    </script>
@endsection