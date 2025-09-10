@extends('layout.global')

@section('title', 'Reports')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">Reports</span>
        <span class="payroll-badge inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-bolt mr-1.5"></i> Premium Plan
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Generate and view payroll and compliance reports for {{ $settings['company_name'] }}.</span>
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="card hover:shadow-lg transition-all flex flex-col items-center text-center bg-white rounded-xl p-6 border border-gray-200 hover:border-green-300 cursor-pointer" onclick="openModal('generateReportModal')">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center mb-4">
                    <i class="fas fa-file-pdf text-purple-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Generate Report</h4>
                <p class="text-sm text-gray-500 mt-1">Create new payroll or tax reports.</p>
            </div>
            <a href="{{ route('reports') }}" class="card hover:shadow-lg transition-all flex flex-col items-center text-center bg-white rounded-xl p-6 border border-gray-200 hover:border-green-300">
                <div class="w-12 h-12 rounded-lg bg-pink-100 flex items-center justify-center mb-4">
                    <i class="fas fa-list text-pink-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900">View Reports</h4>
                <p class="text-sm text-gray-500 mt-1">Review all generated reports.</p>
            </a>
            <div class="card hover:shadow-lg transition-all flex flex-col items-center text-center bg-white rounded-xl p-6 border border-gray-200 hover:border-green-300 cursor-pointer" onclick="alert('This feature is coming soon!')">
                <div class="w-12 h-12 rounded-lg bg-orange-100 flex items-center justify-center mb-4">
                    <i class="fas fa-chart-line text-orange-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Analytics</h4>
                <p class="text-sm text-gray-500 mt-1">Analyze payroll trends and insights.</p>
            </div>
            <div class="card hover:shadow-lg transition-all flex flex-col items-center text-center bg-white rounded-xl p-6 border border-gray-200 hover:border-green-300 cursor-pointer" onclick="alert('This feature is coming soon!')">
                <div class="w-12 h-12 rounded-lg bg-teal-100 flex items-center justify-center mb-4">
                    <i class="fas fa-cogs text-teal-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Settings</h4>
                <p class="text-sm text-gray-500 mt-1">Configure report generation settings.</p>
            </div>
        </div>
    </div>

    <!-- Search Input -->
    <div class="mb-6 relative">
        <div class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input id="searchReport" type="text" placeholder="Search by report ID or employee..." class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-150 bg-white shadow-sm text-gray-900 placeholder-gray-500">
        </div>
    </div>

    <!-- Recent Reports Table -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex justify-between items-center px-6 pt-6 mb-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-file-alt text-green-500 mr-2"></i> Recent Reports
                <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $reports->total() }} reports</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 text-sm">
                        <th class="py-3.5 px-6 text-left font-semibold">Report ID</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Type</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Period</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Employee</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Format</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="reportsTable" class="divide-y divide-gray-100">
                    @foreach($reports as $report)
                    @php
                        $formatColors = [
                            'pdf' => 'bg-purple-100 text-purple-800',
                            'excel' => 'bg-green-100 text-green-800'
                        ];
                        $formatColor = $formatColors[strtolower($report->export_format)] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <tr id="report-{{ $report->id }}" class="bg-white hover:bg-gray-50/50 transition duration-150 report-row" data-report-id="{{ strtolower($report->report_id) }}" data-employee="{{ strtolower($report->employee->name ?? 'all') }}">
                        <td class="py-4 px-6 text-sm text-gray-900 font-mono">{{ $report->report_id }}</td>
                        <td class="py-4 px-6 text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $report->type)) }}</td>
                        <td class="py-4 px-6 text-sm text-gray-900">{{ $report->period }}</td>
                        <td class="py-4 px-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="font-medium text-green-800">{{ $report->employee ? substr($report->employee->name, 0, 1) : 'A' }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $report->employee->name ?? 'All' }}</div>
                                    <div class="text-sm text-gray-500">{{ $report->employee->email ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $formatColor }}">
                                <span class="w-2 h-2 bg-{{ strtolower($report->export_format) == 'pdf' ? 'purple' : 'green' }}-500 rounded-full mr-1.5"></span>
                                {{ strtoupper($report->export_format) }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('reports.download', ['id' => $report->id]) }}" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-md hover:bg-blue-50 transition duration-150" title="Download">
                                    <i class="fas fa-download text-sm"></i>
                                </a>
                                <form action="{{ route('reports.destroy', ['id' => $report->id]) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this report?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-1.5 rounded-md hover:bg-red-50 transition duration-150" title="Delete">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Empty State -->
        @if($reports->count() == 0)
        <div class="text-center py-12">
            <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No reports found</h3>
            <p class="text-gray-500 mb-6">Get started by generating your first report.</p>
            <button class="text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-4 focus:ring-green-200 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="openModal('generateReportModal')">
                <i class="fas fa-file-pdf mr-2"></i> Generate Report
            </button>
        </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($reports->hasPages())
    <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5">
        <div class="text-sm text-gray-700">
            Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} of {{ $reports->total() }} results
        </div>
        <div class="flex space-x-2">
            @if($reports->onFirstPage())
            <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Previous</span>
            @else
            <a href="{{ $reports->previousPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-gray-700 text-sm hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Previous</a>
            @endif
            @if($reports->hasMorePages())
            <a href="{{ $reports->nextPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-gray-700 text-sm hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Next</a>
            @else
            <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Next</span>
            @endif
        </div>
    </div>
    @endif

    <!-- Generate Report Modal -->
    <div id="generateReportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="generateReportModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="generateReportModalTitle">
                        <i class="fas fa-file-pdf mr-2"></i> Generate Report
                    </h3>
                    <button type="button" onclick="closeModal('generateReportModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="generateReportForm" action="{{ route('reports.generate') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="report_type" class="block text-gray-600 text-sm font-medium mb-2">Report Type</label>
                            <select name="report_type" id="report_type" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                                <option value="">Select a report type</option>
                                <option value="payroll_summary">Payroll Summary</option>
                                <option value="tax_report">Tax Report</option>
                                <option value="payslip">Payslip</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="reportTypeError">Report Type is required</span>
                            @error('report_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="report_period" class="block text-gray-600 text-sm font-medium mb-2">Report Period</label>
                            <input type="month" name="report_period" id="report_period" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                            <span class="text-red-500 text-sm hidden" id="reportPeriodError">Report Period is required</span>
                            @error('report_period')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="employee_id" class="block text-gray-600 text-sm font-medium mb-2">Specific Employee (Optional)</label>
                            <select name="employee_id" id="employee_id" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                                <option value="">All Employees</option>
                                @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="export_format" class="block text-gray-600 text-sm font-medium mb-2">Export Format</label>
                            <select name="export_format" id="export_format" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="exportFormatError">Export Format is required</span>
                            @error('export_format')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-4">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('generateReportModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-4 focus:ring-green-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                            <span id="formSpinner" class="hidden animate-spin h-4 w-4 mr-2 border-t-2 border-r-2 border-white rounded-full"></span>
                            <i class="fas fa-file-pdf mr-2"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.remove('scale-95');
                    modalContent.classList.add('scale-100');
                }
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

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('generateReportForm');
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
                    const spinner = form.querySelector('#formSpinner');
                    submitButton.disabled = true;
                    if (spinner) spinner.classList.remove('hidden');
                });
            }

            const searchInput = document.getElementById('searchReport');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.report-row');
                    rows.forEach(row => {
                        const reportId = row.dataset.reportId || '';
                        const employee = row.dataset.employee || '';
                        const matches = reportId.includes(searchValue) || employee.includes(searchValue);
                        row.style.display = matches ? '' : 'none';
                    });
                });
            }
        });
    </script>
@endsection