@extends('layout.global')

@section('title', 'Reports')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">Reports</span>
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-bolt mr-1.5"></i> Premium Plan
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Generate and manage payroll and compliance reports for {{ $settings->company_name ?? 'Your Company' }}.</span>
@endsection

@section('content')
    <!-- Include Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

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
            <button id="allReportsTab" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-t-md focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="true" aria-controls="reportsTableContainer">
                All Reports
            </button>
            <button id="generateReportTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="generateReportFormContainer">
                Generate Report
            </button>
        </div>
    </div>

    <!-- Reports Table Container -->
    <div id="reportsTableContainer" class="block">
        <!-- Search Input -->
        <div class="mb-6 relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input id="searchReport" type="text" placeholder="Search by report ID, employee, or type..." class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" aria-label="Search reports by ID, employee, or type">
        </div>

        <!-- Reports Table Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-file-alt text-green-500 mr-2"></i> Recent Reports
                <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $reports->total() }} reports</span>
            </h3>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Report ID</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Type</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Period</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Employee</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Format</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTable" class="divide-y divide-gray-100">
                        @foreach($reports as $report)
                            @php
                                $formatColors = [
                                    'pdf' => 'bg-purple-100 text-purple-800',
                                    'csv' => 'bg-green-100 text-green-800'
                                ];
                                $formatColor = $formatColors[strtolower($report->export_format ?? 'pdf')] ?? 'bg-gray-100 text-gray-800';
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800'
                                ];
                                $statusColor = $statusColors[$report->status ?? 'completed'] ?? 'bg-gray-100 text-gray-800';
                                $filePath = 'reports/' . "{$report->report_id}_{$report->type}_{$report->period}.{$report->export_format}";
                                $fileExists = Storage::disk('public')->exists($filePath);
                            @endphp
                            <tr id="report-{{ $report->id }}" class="bg-white hover:bg-gray-50 transition-all duration-200 report-row group" data-report-id="{{ strtolower($report->report_id ?? '') }}" data-employee="{{ strtolower($report->employee->name ?? 'all') }}" data-type="{{ strtolower($report->type ?? '') }}">
                                <td class="py-4 px-6 text-sm text-gray-900 font-mono">{{ $report->report_id ?? 'N/A' }}</td>
                                <td class="py-4 px-6 text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $report->type ?? 'unknown')) }}</td>
                                <td class="py-4 px-6 text-sm text-gray-900">{{ $report->period ?? 'N/A' }}</td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="font-medium text-green-800">{{ $report->employee ? substr($report->employee->name, 0, 1) : 'A' }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $report->employee->name ?? 'All Employees' }}</div>
                                            <div class="text-sm text-gray-500">{{ $report->employee->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $formatColor }}">
                                        {{ strtoupper($report->export_format ?? 'PDF') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                        {{ ucfirst($report->status ?? 'Completed') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        @if($report->status === 'completed' && $fileExists)
                                            <a href="{{ route('reports.download', $report->id) }}" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition-all duration-200" title="Download Report" aria-label="Download {{ $report->type }} report">
                                                <i class="fas fa-download text-sm"></i>
                                            </a>
                                        @else
                                            <span class="text-gray-400 p-1.5 cursor-not-allowed" title="Download Unavailable" aria-label="Download unavailable for {{ $report->type }} report">
                                                <i class="fas fa-download text-sm"></i>
                                            </span>
                                        @endif
                                        @if($isAdminOrHR)
                                        <button onclick="openDeleteModal({{ $report->id }}, '{{ $report->report_id }}')" class="text-red-600 hover:text-red-800 p-1.5 rounded-md hover:bg-red-50 transition-all duration-200" title="Delete Report" aria-label="Delete {{ $report->type }} report">
                                            <i class="fas fa-trash-alt text-sm"></i>
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
            @if($reports->count() == 0)
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No reports found</h3>
                    <p class="text-gray-500 mb-6">Get started by generating your first report.</p>
                    <button class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="toggleTab('generateReportTab')">
                        <i class="fas fa-plus mr-2"></i> Generate Report
                    </button>
                </div>
            @endif
        </div>

        <!-- Custom Pagination -->
        @if($reports->lastPage() > 1)
            <div class="mt-6 flex justify-center">
                <nav class="flex items-center space-x-2" aria-label="Pagination">
                    <!-- Previous Button -->
                    <a href="{{ $reports->previousPageUrl() ? $reports->previousPageUrl() . ($reports->previousPageUrl() && $reports->url($reports->currentPage()) ? '&' : '?') . http_build_query(request()->query()) : '#' }}"
                       class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $reports->onFirstPage() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' }}"
                       aria-label="Previous page"
                       {{ $reports->onFirstPage() ? 'disabled' : '' }}>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>

                    <!-- Page Numbers -->
                    @php
                        $currentPage = $reports->currentPage();
                        $lastPage = $reports->lastPage();
                        $range = 2; // Show 2 pages before and after current page
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
                        <a href="{{ $reports->url(1) . ($reports->url(1) ? '&' : '?') . http_build_query(request()->query()) }}"
                           class="px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800 rounded-md transition-all duration-200"
                           aria-label="Page 1">1</a>
                        @if($start > 2)
                            <span class="px-3 py-2 text-sm text-gray-500">...</span>
                        @endif
                    @endif

                    @for($page = $start; $page <= $end; $page++)
                        <a href="{{ $reports->url($page) . ($reports->url($page) ? '&' : '?') . http_build_query(request()->query()) }}"
                           class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $page == $currentPage ? 'text-white bg-green-600' : 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' }}"
                           aria-label="Page {{ $page }}"
                           aria-current="{{ $page == $currentPage ? 'page' : 'false' }}">{{ $page }}</a>
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="px-3 py-2 text-sm text-gray-500">...</span>
                        @endif
                        <a href="{{ $reports->url($lastPage) . ($reports->url($lastPage) ? '&' : '?') . http_build_query(request()->query()) }}"
                           class="px-3 py-2 text-sm font-medium text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800 rounded-md transition-all duration-200"
                           aria-label="Page {{ $lastPage }}">{{ $lastPage }}</a>
                    @endif

                    <!-- Next Button -->
                    <a href="{{ $reports->nextPageUrl() ? $reports->nextPageUrl() . ($reports->nextPageUrl() && $reports->url($reports->currentPage()) ? '&' : '?') . http_build_query(request()->query()) : '#' }}"
                       class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $reports->hasMorePages() ? 'text-green-600 bg-green-50 hover:bg-green-100 hover:text-green-800' : 'text-gray-400 bg-gray-100 cursor-not-allowed' }}"
                       aria-label="Next page"
                       {{ !$reports->hasMorePages() ? 'disabled' : '' }}>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </nav>
            </div>
        @endif
    </div>

    <!-- Generate Report Form Container -->
    <div id="generateReportFormContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-xl font-semibold text-green-600 flex items-center mb-6">
                <i class="fas fa-plus mr-2"></i> Generate New Report
            </h3>
            <form id="generateReportForm" action="{{ route('reports.generate') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="report_type" class="block text-gray-600 text-sm font-medium mb-2">Report Type</label>
                        <select name="report_type" id="report_type" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 text-gray-900">
                            <option value="">Select a report type</option>
                            <option value="payslip">Payslip</option>
                            @if($isAdminOrHR)
                            <option value="payroll_summary">Payroll Summary</option>
                            <option value="tax_report">Tax Report</option>
                            <option value="nssf_report">NSSF Report</option>
                            <option value="nhif_report">NHIF Report</option>
                            <option value="wcf_report">WCF Report</option>
                            <option value="sdl_report">SDL Report</option>
                            <option value="year_end_summary">Year-End Summary</option>
                            @endif
                        </select>
                        <span class="text-red-500 text-xs mt-1 hidden" id="reportTypeError">Report Type is required</span>
                        @error('report_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="report_period" class="block text-gray-600 text-sm font-medium mb-2">Report Period</label>
                        <input type="text" name="report_period" id="report_period" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 text-gray-900" placeholder="Select period">
                        <span class="text-red-500 text-xs mt-1 hidden" id="reportPeriodError">Report Period is required</span>
                        @error('report_period')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="employee_id" class="block text-gray-600 text-sm font-medium mb-2">Specific Employee (Optional)</label>
                        <select name="employee_id" id="employee_id" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 text-gray-900">
                            <option value="">All Employees</option>
                            @foreach($employees ?? [] as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="export_format" class="block text-gray-600 text-sm font-medium mb-2">Export Format</label>
                        <select name="export_format" id="export_format" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 text-gray-900">
                            <option value="pdf">PDF</option>
                            <option value="csv">CSV</option>
                        </select>
                        <span class="text-red-500 text-xs mt-1 hidden" id="exportFormatError">Export Format is required</span>
                        @error('export_format')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="toggleTab('allReportsTab')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" id="generateReportSubmit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <span id="formSpinner" class="hidden animate-spin h-4 w-4 mr-2 border-t-2 border-r-2 border-white rounded-full"></span>
                        <i class="fas fa-check mr-2"></i> Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Report Modal -->
    @if($isAdminOrHR)
    <div id="deleteReportModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 hidden z-50" aria-hidden="true">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content" role="dialog" aria-labelledby="deleteModalTitle" aria-describedby="deleteModalDesc">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-red-50 to-red-100">
                <div class="flex justify-between items-center">
                    <h3 id="deleteModalTitle" class="text-xl font-semibold text-red-700 flex items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i> Delete Report
                    </h3>
                    <button type="button" onclick="closeModal('deleteReportModal')" class="text-gray-500 hover:text-gray-700 rounded-full p-2 hover:bg-gray-200 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-300" aria-label="Close delete modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <p id="deleteModalDesc" class="text-gray-700 mb-4">Are you sure you want to delete report <span id="deleteReportId" class="font-semibold"></span>? This action is permanent and cannot be undone.</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="text-gray-700 bg-gray-100 hover:bg-gray-200 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 transition-all duration-200 flex items-center" onclick="closeModal('deleteReportModal')" aria-label="Cancel deletion">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <form id="deleteReportForm" method="POST" action="" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 transition-all duration-200 flex items-center">
                            <i class="fas fa-trash-alt mr-2"></i> Delete Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection

@section('modals')
    @parent
@endsection

<!-- Include Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Flatpickr
        const reportTypeSelect = document.getElementById('report_type');
        const reportPeriodInput = document.getElementById('report_period');
        let flatpickrInstance;

        function initializeFlatpickr() {
            const isYearly = reportTypeSelect.value === 'year_end_summary';
            const dateFormat = isYearly ? 'Y' : 'Y-m';
            const mode = isYearly ? 'single' : 'single';
            const maxDate = new Date();

            if (flatpickrInstance) {
                flatpickrInstance.destroy();
            }

            flatpickrInstance = flatpickr(reportPeriodInput, {
                dateFormat: dateFormat,
                mode: mode,
                maxDate: maxDate,
                disableMobile: true,
                altInput: true,
                altFormat: isYearly ? 'Y' : 'F Y',
                allowInput: false,
                static: true,
                onOpen: function() {
                    document.querySelector('.flatpickr-calendar').classList.add('bg-white', 'shadow-lg', 'rounded-lg', 'border', 'border-gray-200');
                }
            });

            // Ensure Flatpickr altInput matches select input styles
            const altInput = reportPeriodInput.nextElementSibling;
            if (altInput && altInput.classList.contains('flatpickr-input')) {
                altInput.classList.add('bg-gray-50', 'border', 'border-gray-200', 'rounded-lg', 'focus:ring-2', 'focus:ring-green-500', 'focus:border-green-500', 'block', 'w-full', 'py-2.5', 'px-3', 'leading-6', 'transition-all', 'duration-200', 'text-gray-900');
                altInput.classList.remove('flatpickr-input'); // Remove default Flatpickr class to avoid conflicts
            }
        }

        // Initialize Flatpickr on page load
        initializeFlatpickr();

        // Update Flatpickr on report type change
        if (reportTypeSelect && reportPeriodInput) {
            reportTypeSelect.addEventListener('change', function() {
                reportPeriodInput.value = ''; // Clear previous selection
                initializeFlatpickr();
            });
        }

        // Tab navigation
        document.getElementById('allReportsTab').addEventListener('click', () => toggleTab('allReportsTab'));
        document.getElementById('generateReportTab').addEventListener('click', () => toggleTab('generateReportTab'));

        // Debounced search
        let searchTimeout;
        const searchInput = document.getElementById('searchReport');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const searchValue = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.report-row');
                    rows.forEach(row => {
                        const reportId = row.dataset.reportId || '';
                        const employee = row.dataset.employee || '';
                        const type = row.dataset.type || '';
                        const matches = reportId.includes(searchValue) || employee.includes(searchValue) || type.includes(searchValue);
                        row.style.display = matches ? '' : 'none';
                    });
                }, 300);
            });
        }

        // Form validation and spinner
        const form = document.getElementById('generateReportForm');
        const submitButton = document.getElementById('generateReportSubmit');
        const spinner = document.getElementById('formSpinner');
        if (form) {
            form.addEventListener('submit', function(e) {
                let valid = true;
                const fields = [
                    { id: 'report_type', errorId: 'reportTypeError', message: 'Report Type is required' },
                    { id: 'report_period', errorId: 'reportPeriodError', message: 'Report Period is required' },
                    { id: 'export_format', errorId: 'exportFormatError', message: 'Export Format is required' },
                ];

                fields.forEach(field => {
                    const input = document.getElementById(field.id);
                    const error = document.getElementById(field.errorId);
                    if (!input.value.trim()) {
                        error.classList.remove('hidden');
                        valid = false;
                    } else {
                        error.classList.add('hidden');
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    return;
                }

                submitButton.disabled = true;
                spinner.classList.remove('hidden');
            });
        }

        // Delete modal
        window.openDeleteModal = function(id, reportId) {
            document.getElementById('deleteReportForm').action = `/dashboard/reports/${id}`;
            document.getElementById('deleteReportId').textContent = reportId || 'Unknown';
            openModal('deleteReportModal');
        };

        function toggleTab(tabId) {
            const tabs = ['allReportsTab', 'generateReportTab'];
            const containers = ['reportsTableContainer', 'generateReportFormContainer'];

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

            const containerId = tabId === 'allReportsTab' ? 'reportsTableContainer' : 'generateReportFormContainer';
            const container = document.getElementById(containerId);
            if (container) container.classList.remove('hidden');
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                setTimeout(() => {
                    const modalContent = modal.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.classList.remove('scale-95');
                        modalContent.classList.add('scale-100');
                    }
                }, 10);
                modal.focus();
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.remove('scale-100');
                    modalContent.classList.add('scale-95');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.setAttribute('aria-hidden', 'true');
                    }, 300);
                }
            }
        }

        // Keyboard navigation for modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('deleteReportModal');
            }
        });
    });
</script>
