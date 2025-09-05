@extends('layout.global')

@section('title', 'Payroll')

@section('header-title')
    Payroll Management
    <span class="payroll-badge text-xs font-semibold px-2 py-1 rounded-full ml-3 bg-green-100 text-green-800">
        <i class="fas fa-bolt mr-1"></i> Premium Plan
    </span>
@endsection

@section('header-subtitle')
    Process and review payroll records for {{ $settings['company_name'] }}.
@endsection

@section('content')
    <!-- Quick Actions -->
    <div>
        <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="#" class="card hover:shadow-lg transition-all flex flex-col items-center text-center bg-white rounded-xl p-6 border border-gray-200 hover:border-green-300" onclick="openModal('runPayrollModal')">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mb-4">
                    <i class="fas fa-calculator text-green-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Run Payroll</h4>
                <p class="text-sm text-gray-500 mt-1">Process salary payments</p>
            </a>
        </div>
    </div>

    <!-- Payroll History -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4 flex-col sm:flex-row gap-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-file-invoice-dollar text-blue-500 mr-2"></i> Payroll History
            </h3>
            <div class="flex items-center space-x-4 w-full sm:w-auto">
                <input type="text" id="searchPayroll" class="bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full sm:w-64 p-2.5 text-sm" placeholder="Search by Payroll ID or Period">
                <select id="statusFilter" class="bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full sm:w-48 p-2.5 text-sm">
                    <option value="">All Statuses</option>
                    <option value="Paid">Paid</option>
                    <option value="Pending">Pending</option>
                    <option value="Error">Error</option>
                </select>
                <button id="exportCsvButton" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                    <i class="fas fa-download mr-2"></i> Export to CSV
                    <svg class="hidden w-4 h-4 ml-2 animate-spin text-white" id="exportSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3.5-3.5L12 8v4a8 8 0 01-8-8z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="payrollTable">
                    <thead>
                        <tr class="text-left text-gray-500 text-sm">
                            <th class="pb-3 font-medium cursor-pointer" data-sort="payroll_id">Payroll ID <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="period">Period <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="total_amount">Total Amount ({{ $settings['currency'] }}) <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="status">Status <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($payrolls as $payroll)
                            <tr class="payroll-row" data-status="{{ $payroll->status }}">
                                <td class="py-4">{{ $payroll->payroll_id }}</td>
                                <td class="py-4">{{ $payroll->period }}</td>
                                <td class="py-4">{{ number_format($payroll->total_amount, 2) }}</td>
                                <td class="py-4">
                                    <span class="status-badge {{ $payroll->status == 'Paid' ? 'status-paid' : ($payroll->status == 'Pending' ? 'status-pending' : 'status-error') }}">
                                        {{ $payroll->status }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    <button class="text-blue-600 text-sm font-medium hover:underline" onclick="viewPayroll('{{ $payroll->id }}')" title="View Details">View</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-gray-500">No payroll records available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @section('modals')
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="runPayrollModal">
            <div class="bg-white rounded-xl w-full max-w-md">
                <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-calculator mr-2"></i> Run Payroll
                    </h3>
                </div>
                <div class="p-6">
                    <form id="runPayrollForm" action="{{ route('payroll.run') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="payroll_period">Payroll Period</label>
                            <input type="month" id="payroll_period" name="payroll_period" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm mt-1 hidden" id="payrollPeriodError">Payroll Period is required</span>
                            @error('payroll_period')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="employee_group">Employee Group</label>
                            <select id="employee_group" name="employee_group" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">All Employees</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department }}">{{ $department }}</option>
                                @endforeach
                            </select>
                            @error('employee_group')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="total_amount">Total Amount ({{ $settings['currency'] }})</label>
                            <input type="number" id="total_amount" name="total_amount" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm mt-1 hidden" id="totalAmountError">Total Amount is required</span>
                            @error('total_amount')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('runPayrollModal')">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                                <i class="fas fa-calculator mr-2"></i> Process Payroll
                                <svg class="hidden w-4 h-4 ml-2 animate-spin text-white" id="formSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3.5-3.5L12 8v4a8 8 0 01-8-8z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

    <style>
        .status-badge { @apply px-2 py-1 rounded-full text-xs font-medium; }
        .status-paid { @apply bg-green-100 text-green-800; }
        .status-pending { @apply bg-yellow-100 text-yellow-800; }
        .status-error { @apply bg-red-100 text-red-800; }
        th[data-sort] { @apply hover:text-green-600; }
        th.sorted-asc i.fa-sort::before { content: "\f0dd"; }
        th.sorted-desc i.fa-sort::before { content: "\f0de"; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functions
            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        const modalContent = modal.querySelector('.modal-content') || modal;
                        modalContent.classList.remove('scale-95');
                        modalContent.classList.add('scale-100');
                    }, 10);
                }
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    const modalContent = modal.querySelector('.modal-content') || modal;
                    modalContent.classList.remove('scale-100');
                    modalContent.classList.add('scale-95');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                }
            }

            // Search and Filter with Debounce
            let searchTimeout;
            const searchInput = document.getElementById('searchPayroll');
            const statusFilter = document.getElementById('statusFilter');
            if (searchInput && statusFilter) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const searchValue = this.value.toLowerCase();
                        const statusValue = statusFilter.value;
                        filterPayrollTable(searchValue, statusValue);
                    }, 300);
                });

                statusFilter.addEventListener('change', function() {
                    const searchValue = searchInput.value.toLowerCase();
                    const statusValue = this.value;
                    filterPayrollTable(searchValue, statusValue);
                });
            }

            function filterPayrollTable(searchValue, statusFilter) {
                const rows = document.querySelectorAll('#payrollTable .payroll-row');
                rows.forEach(row => {
                    const id = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                    const period = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    const status = row.getAttribute('data-status') || '';
                    const matchesSearch = id.includes(searchValue) || period.includes(searchValue);
                    const matchesStatus = !statusFilter || status === statusFilter;
                    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                });
            }

            // Table Sorting
            let sortDirection = {};
            document.querySelectorAll('th[data-sort]').forEach(header => {
                header.addEventListener('click', function() {
                    const sortKey = this.getAttribute('data-sort');
                    sortDirection[sortKey] = !sortDirection[sortKey] ? 'asc' : sortDirection[sortKey] === 'asc' ? 'desc' : 'asc';
                    document.querySelectorAll('th[data-sort]').forEach(th => {
                        th.classList.remove('sorted-asc', 'sorted-desc');
                        th.querySelector('i.fa-sort').className = 'fas fa-sort ml-1';
                    });
                    this.classList.add(`sorted-${sortDirection[sortKey]}`);
                    sortTable(sortKey, sortDirection[sortKey]);
                });
            });

            function sortTable(key, direction) {
                const tbody = document.querySelector('#payrollTable tbody');
                const rows = Array.from(tbody.querySelectorAll('.payroll-row'));
                rows.sort((a, b) => {
                    let aValue = a.querySelector(`td:nth-child(${key === 'payroll_id' ? 1 : key === 'period' ? 2 : key === 'total_amount' ? 3 : 4})`)?.textContent || '';
                    let bValue = b.querySelector(`td:nth-child(${key === 'payroll_id' ? 1 : key === 'period' ? 2 : key === 'total_amount' ? 3 : 4})`)?.textContent || '';
                    if (key === 'total_amount') {
                        aValue = parseFloat(aValue.replace(/[^0-9.-]+/g, '')) || 0;
                        bValue = parseFloat(bValue.replace(/[^0-9.-]+/g, '')) || 0;
                    }
                    return direction === 'asc' ? aValue.localeCompare(bValue, undefined, {numeric: true}) : bValue.localeCompare(aValue, undefined, {numeric: true});
                });
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
            }

            // Export to CSV
            document.getElementById('exportCsvButton').addEventListener('click', function() {
                const button = this;
                const spinner = document.getElementById('exportSpinner');
                button.disabled = true;
                spinner.classList.remove('hidden');
                setTimeout(() => {
                    try {
                        const payrolls = JSON.parse('{{ $payrolls->map(function($payroll) {
                            return [
                                "payroll_id" => $payroll->payroll_id,
                                "period" => $payroll->period,
                                "total_amount" => number_format($payroll->total_amount, 2),
                                "status" => $payroll->status
                            ];
                        })->toJson() }}');
                        const headers = ['Payroll ID', 'Period', 'Total Amount ({{ $settings['currency'] }})', 'Status'];
                        const csvRows = [headers.join(',')];
                        payrolls.forEach(payroll => {
                            const row = [
                                payroll.payroll_id || '',
                                payroll.period || '',
                                payroll.total_amount || 0,
                                payroll.status || ''
                            ];
                            csvRows.push(row.join(','));
                        });
                        const csvContent = csvRows.join('\n');
                        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = 'payrolls.csv';
                        link.click();
                        URL.revokeObjectURL(link.href);
                    } catch (e) {
                        console.error('Error exporting CSV:', e);
                        alert('Failed to export CSV. Please try again.');
                    } finally {
                        button.disabled = false;
                        spinner.classList.add('hidden');
                    }
                }, 500);
            });

            // Form Validation
            const form = document.getElementById('runPayrollForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let valid = true;
                    const spinner = document.getElementById('formSpinner');
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
                    } else {
                        e.preventDefault();
                        const submitButton = form.querySelector('button[type="submit"]');
                        submitButton.disabled = true;
                        spinner.classList.remove('hidden');
                        setTimeout(() => {
                            form.submit();
                        }, 500);
                    }
                });
            }
        });
    </script>
@endsection
