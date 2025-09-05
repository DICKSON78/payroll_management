@extends('layout.global')

@section('title', 'Reports')

@section('header-title')
    Reports
    <span class="payroll-badge text-xs font-semibold px-2 py-1 rounded-full ml-3 bg-green-100 text-green-800">
        <i class="fas fa-bolt mr-1"></i> Premium Plan
    </span>
@endsection

@section('header-subtitle')
    {{-- Generate and view payroll and compliance reports for {{ $settings['company_name'] }}. --}}
@endsection

@section('content')
    <!-- Quick Actions -->
    <div>
        <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="#" class="card hover:shadow-lg transition-all flex flex-col items-center text-center bg-white rounded-xl p-6 border border-gray-200 hover:border-green-300" onclick="openModal('generateReportModal')">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center mb-4">
                    <i class="fas fa-file-pdf text-purple-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Generate Report</h4>
                <p class="text-sm text-gray-500 mt-1">Create payroll or compliance reports</p>
            </a>
        </div>
    </div>

    <!-- Reports List -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4 flex-col sm:flex-row gap-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-file-alt text-blue-500 mr-2"></i> Generated Reports
            </h3>
            <div class="flex items-center space-x-4 w-full sm:w-auto">
                <input type="text" id="searchReport" class="bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full sm:w-64 p-2.5 text-sm" placeholder="Search by Report ID, Type, or Employee">
                <select id="reportTypeFilter" class="bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full sm:w-48 p-2.5 text-sm">
                    <option value="">All Report Types</option>
                    <option value="payslip">Payslip</option>
                    <option value="payroll_summary">Payroll Summary</option>
                    <option value="tax_report">Tax Report</option>
                    <option value="nssf_report">NSSF Report</option>
                    <option value="nhif_report">NHIF Report</option>
                    <option value="year_end_summary">Year-End Summary</option>
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
                <table class="w-full" id="reportsTable">
                    <thead>
                        <tr class="text-left text-gray-500 text-sm">
                            <th class="pb-3 font-medium cursor-pointer" data-sort="report_id">Report ID <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="type">Type <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="employee">Employee <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="period">Period <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="generated_at">Generated At <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($reports as $report)
                            <tr class="report-row" data-type="{{ $report->type }}">
                                <td class="py-4">{{ $report->report_id }}</td>
                                <td class="py-4">{{ str_replace('_', ' ', ucfirst($report->type)) }}</td>
                                <td class="py-4">{{ $report->employee ? $report->employee->name . ' (' . $report->employee->employee_id . ')' : 'All Employees' }}</td>
                                <td class="py-4">{{ $report->period }}</td>
                                <td class="py-4">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                                <td class="py-4 flex space-x-2">
                                    <a href="{{ route('reports.download', [$report->id, 'pdf']) }}" class="text-blue-600 text-sm font-medium hover:underline" title="Download PDF">PDF</a>
                                    <a href="{{ route('reports.download', [$report->id, 'excel']) }}" class="text-blue-600 text-sm font-medium hover:underline" title="Download Excel">Excel</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-4 text-center text-gray-500">No reports available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="generateReportModal">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Generate Report
                </h3>
            </div>
            <div class="p-6">
                <form id="generateReportForm" action="{{ route('reports.generate') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="">Select Report Type</option>
                                <option value="payslip">Payslip</option>
                                <option value="payroll_summary">Payroll Summary</option>
                                <option value="tax_report">Tax Report</option>
                                <option value="nssf_report">NSSF Report</option>
                                <option value="nhif_report">NHIF Report</option>
                                <option value="year_end_summary">Year-End Summary</option>
                            </select>
                            <span class="text-red-500 text-sm mt-1 hidden" id="reportTypeError">Report Type is required</span>
                            @error('report_type')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="report_period">Report Period</label>
                            <input type="month" id="report_period" name="report_period" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm mt-1 hidden" id="reportPeriodError">Report Period is required</span>
                            @error('report_period')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="employee_id">Employee (Optional)</label>
                            <select id="employee_id" name="employee_id" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="export_format">Export Format</label>
                            <select id="export_format" name="export_format" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="">Select Export Format</option>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                            <span class="text-red-500 text-sm mt-1 hidden" id="exportFormatError">Export Format is required</span>
                            @error('export_format')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('generateReportModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-file-export mr-2"></i> Generate Report
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

    <style>
        .status-badge {
            @apply px-2 py-1 rounded-full text-xs font-medium;
        }
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
                        const modalContent = modal.querySelector('.modal-content');
                        if (modalContent) {
                            modalContent.classList.remove('scale-95');
                            modalContent.classList.add('scale-100');
                        }
                    }, 10);
                }
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    const modalContent = modal.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.classList.remove('scale-100');
                        modalContent.classList.add('scale-95');
                        setTimeout(() => {
                            modal.classList.add('hidden');
                        }, 300);
                    }
                }
            }

            // Search and Filter with Debounce
            let searchTimeout;
            const searchInput = document.getElementById('searchReport');
            const typeFilter = document.getElementById('reportTypeFilter');
            if (searchInput && typeFilter) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const searchValue = this.value.toLowerCase();
                        const typeValue = typeFilter.value;
                        filterReportsTable(searchValue, typeValue);
                    }, 300);
                });

                typeFilter.addEventListener('change', function() {
                    const searchValue = searchInput.value.toLowerCase();
                    const typeValue = this.value;
                    filterReportsTable(searchValue, typeValue);
                });
            }

            function filterReportsTable(searchValue, typeFilter) {
                const rows = document.querySelectorAll('#reportsTable .report-row');
                rows.forEach(row => {
                    const id = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                    const type = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    const employee = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                    const period = row.querySelector('td:nth-child(4)')?.textContent.toLowerCase() || '';
                    const matchesSearch = id.includes(searchValue) || type.includes(searchValue) || employee.includes(searchValue) || period.includes(searchValue);
                    const matchesType = !typeFilter || row.getAttribute('data-type') === typeFilter;
                    row.style.display = matchesSearch && matchesType ? '' : 'none';
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
                const tbody = document.querySelector('#reportsTable tbody');
                const rows = Array.from(tbody.querySelectorAll('.report-row'));
                rows.sort((a, b) => {
                    let aValue = a.querySelector(`td:nth-child(${key === 'report_id' ? 1 : key === 'type' ? 2 : key === 'employee' ? 3 : key === 'period' ? 4 : 5})`)?.textContent || '';
                    let bValue = b.querySelector(`td:nth-child(${key === 'report_id' ? 1 : key === 'type' ? 2 : key === 'employee' ? 3 : key === 'period' ? 4 : 5})`)?.textContent || '';
                    if (key === 'generated_at') {
                        aValue = new Date(aValue.split('/').reverse().join('-') + ' 00:00:00').getTime();
                        bValue = new Date(bValue.split('/').reverse().join('-') + ' 00:00:00').getTime();
                    }
                    return direction === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                });
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
            }

            // Export to CSV with Loading State
            document.getElementById('exportCsvButton').addEventListener('click', function() {
                const button = this;
                const spinner = document.getElementById('exportSpinner');
                button.disabled = true;
                spinner.classList.remove('hidden');
                setTimeout(() => {
                    try {
                        const reports = JSON.parse('{{ $reports->map(function($report) {
                            return [
                                "report_id" => $report->report_id,
                                "type" => str_replace("_", " ", ucfirst($report->type)),
                                "employee" => $report->employee ? $report->employee->name . " (" . $report->employee->employee_id . ")" : "All Employees",
                                "period" => $report->period,
                                "generated_at" => $report->created_at->format("d/m/Y H:i")
                            ];
                        })->toJson() }}');
                        const headers = ['Report ID', 'Type', 'Employee', 'Period', 'Generated At'];
                        const csvRows = [headers.join(',')];
                        reports.forEach(report => {
                            const row = [
                                report.report_id || '',
                                `"${(report.type || '').replace(/"/g, '""')}"`,
                                `"${(report.employee || '').replace(/"/g, '""')}"`,
                                report.period || '',
                                report.generated_at || ''
                            ];
                            csvRows.push(row.join(','));
                        });
                        const csvContent = csvRows.join('\n');
                        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = 'reports.csv';
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

            // Form Validation with Loading State
            const form = document.getElementById('generateReportForm');
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
