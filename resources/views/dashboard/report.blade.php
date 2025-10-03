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
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 mr-2"></i>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
    @endif
    @if(session('warning'))
        <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-500 mr-2"></i>
                <span class="block sm:inline">{{ session('warning') }}</span>
            </div>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="flex space-x-4 border-b border-gray-200" role="tablist">
            <button id="allReportsTab" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-t-md focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="true" aria-controls="reportsTableContainer">
                <i class="fas fa-list mr-2"></i>All Reports
            </button>
            <button id="generateReportTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="generateReportFormContainer">
                <i class="fas fa-plus mr-2"></i>Generate Report
            </button>
        </div>
    </div>

    <!-- Reports Table Container -->
    <div id="reportsTableContainer" class="block">
        <!-- Search and Filters -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <!-- Search Input -->
            <div class="relative max-w-md w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input id="searchReport" type="text" placeholder="Search by report ID, employee, or type..." class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" aria-label="Search reports by ID, employee, or type">
            </div>

            <!-- Quick Actions -->
            <div class="flex items-center space-x-2">
                <button onclick="refreshReports()" class="text-gray-600 hover:text-green-600 p-2 rounded-lg hover:bg-green-50 transition-all duration-200" title="Refresh Reports">
                    <i class="fas fa-sync-alt text-sm"></i>
                </button>
                @if($isAdminOrHR)
                <button onclick="toggleTab('generateReportTab')" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-3 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i> New Report
                </button>
                @endif
            </div>
        </div>

        <!-- Reports Table Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-file-alt text-green-500 mr-2"></i> Recent Reports
                <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $reports->total() }} reports</span>
            </h3>
            
            <!-- Export Options -->
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <span>Showing {{ $reports->firstItem() ?? 0 }}-{{ $reports->lastItem() ?? 0 }} of {{ $reports->total() }}</span>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-green-50 to-green-100 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Report ID</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Type</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Period</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Employee</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Format</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Generated</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="reportsTable" class="divide-y divide-gray-100">
                        @foreach($reports as $report)
                            @php
                                // Format colors with Excel support
                                $formatColors = [
                                    'pdf' => 'bg-purple-100 text-purple-800 border border-purple-200',
                                    'excel' => 'bg-green-100 text-green-800 border border-green-200',
                                    'csv' => 'bg-blue-100 text-blue-800 border border-blue-200'
                                ];
                                $formatColor = $formatColors[strtolower($report->export_format ?? 'pdf')] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                                
                                // Status colors
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-200',
                                    'processing' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                    'completed' => 'bg-green-100 text-green-800 border border-green-200',
                                    'failed' => 'bg-red-100 text-red-800 border border-red-200'
                                ];
                                $statusColor = $statusColors[$report->status ?? 'completed'] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                                
                                // File path with correct extensions
                                $fileExtensions = [
                                    'pdf' => 'pdf',
                                    'excel' => 'xlsx',
                                    'csv' => 'csv'
                                ];
                                $fileExtension = $fileExtensions[strtolower($report->export_format)] ?? 'pdf';
                                $filePath = 'reports/' . "{$report->report_id}_{$report->type}_{$report->period}.{$fileExtension}";
                                $fileExists = Storage::disk('public')->exists($filePath);
                                
                                // Format icons
                                $formatIcons = [
                                    'pdf' => 'fas fa-file-pdf',
                                    'excel' => 'fas fa-file-excel',
                                    'csv' => 'fas fa-file-csv'
                                ];
                                $formatIcon = $formatIcons[strtolower($report->export_format)] ?? 'fas fa-file';
                            @endphp
                            <tr id="report-{{ $report->id }}" class="bg-white hover:bg-gray-50 transition-all duration-200 report-row group" 
                                data-report-id="{{ strtolower($report->report_id ?? '') }}" 
                                data-employee="{{ strtolower($report->employee->name ?? 'all') }}" 
                                data-type="{{ strtolower($report->type ?? '') }}"
                                data-status="{{ strtolower($report->status ?? '') }}">
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-file text-green-400 text-sm"></i>
                                        <span class="text-sm text-gray-900 font-mono font-medium">{{ $report->report_id ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-chart-bar text-blue-500 text-sm"></i>
                                        <span class="text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $report->type ?? 'unknown')) }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        <i class="fas fa-calendar text-orange-500 text-sm"></i>
                                        <span class="text-sm text-gray-900 font-medium">{{ $report->period ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center mr-3 shadow-sm">
                                            <span class="font-medium text-white text-xs">{{ $report->employee ? substr($report->employee->name, 0, 1) : 'A' }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 text-sm">{{ $report->employee->name ?? 'All Employees' }}</div>
                                            <div class="text-xs text-gray-500">{{ $report->employee->employee_id ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $formatColor }}">
                                        <i class="{{ $formatIcon }} mr-1.5 text-xs"></i>
                                        {{ strtoupper($report->export_format ?? 'PDF') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColor }}">
                                        @if($report->status === 'completed')
                                            <i class="fas fa-check-circle mr-1.5 text-xs"></i>
                                        @elseif($report->status === 'pending')
                                            <i class="fas fa-clock mr-1.5 text-xs"></i>
                                        @elseif($report->status === 'processing')
                                            <i class="fas fa-sync-alt mr-1.5 text-xs animate-spin"></i>
                                        @elseif($report->status === 'failed')
                                            <i class="fas fa-exclamation-circle mr-1.5 text-xs"></i>
                                        @endif
                                        {{ ucfirst($report->status ?? 'Completed') }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="text-xs text-gray-500">
                                        <div>{{ $report->created_at->format('M j, Y') }}</div>
                                        <div>{{ $report->created_at->format('g:i A') }}</div>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-1">
                                        @if($report->status === 'completed' && $fileExists)
                                            <a href="{{ route('reports.download', $report->id) }}" 
                                               class="text-green-600 hover:text-green-800 p-2 rounded-lg hover:bg-green-50 transition-all duration-200 group relative" 
                                               title="Download Report" 
                                               aria-label="Download {{ $report->type }} report">
                                                <i class="fas fa-download text-sm"></i>
                                                <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                    Download {{ strtoupper($report->export_format) }}
                                                </span>
                                            </a>
                                            <button onclick="previewReport({{ $report->id }})" 
                                                    class="text-blue-600 hover:text-blue-800 p-2 rounded-lg hover:bg-blue-50 transition-all duration-200 group relative"
                                                    title="Preview Report"
                                                    aria-label="Preview {{ $report->type }} report">
                                                <i class="fas fa-eye text-sm"></i>
                                                <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                    Quick Preview
                                                </span>
                                            </button>
                                        @else
                                            <span class="text-gray-400 p-2 cursor-not-allowed group relative" 
                                                  title="Download Unavailable" 
                                                  aria-label="Download unavailable for {{ $report->type }} report">
                                                <i class="fas fa-download text-sm"></i>
                                                <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                    {{ $report->status === 'completed' ? 'File Not Found' : ucfirst($report->status) }}
                                                </span>
                                            </span>
                                        @endif
                                        
                                        @if($isAdminOrHR)
                                        <button onclick="openDeleteModal({{ $report->id }}, '{{ $report->report_id }}')" 
                                                class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-all duration-200 group relative" 
                                                title="Delete Report" 
                                                aria-label="Delete {{ $report->type }} report">
                                            <i class="fas fa-trash-alt text-sm"></i>
                                            <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                Delete Report
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
            @if($reports->count() == 0)
                <div class="text-center py-16">
                    <div class="mx-auto w-24 h-24 mb-6 rounded-full bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center shadow-sm">
                        <i class="fas fa-file-alt text-green-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No reports generated yet</h3>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">Start by generating your first payroll or compliance report to see it listed here.</p>
                    <button class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-6 py-3 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md transform hover:-translate-y-0.5" 
                            onclick="toggleTab('generateReportTab')">
                        <i class="fas fa-plus mr-2"></i> Generate Your First Report
                    </button>
                </div>
            @endif
        </div>

        <!-- Custom Pagination -->
        @if($reports->lastPage() > 1)
            <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-500">
                    Showing {{ $reports->firstItem() }} to {{ $reports->lastItem() }} of {{ $reports->total() }} results
                </div>
                
                <nav class="flex items-center space-x-1" aria-label="Pagination">
                    <!-- Previous Button -->
                    <a href="{{ $reports->previousPageUrl() ? $reports->previousPageUrl() . '&' . http_build_query(request()->except('page')) : '#' }}"
                       class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $reports->onFirstPage() ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' }}"
                       aria-label="Previous page"
                       {{ $reports->onFirstPage() ? 'disabled' : '' }}>
                        <i class="fas fa-chevron-left text-xs"></i>
                    </a>

                    <!-- Page Numbers -->
                    @php
                        $currentPage = $reports->currentPage();
                        $lastPage = $reports->lastPage();
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
                        <a href="{{ $reports->url(1) . '&' . http_build_query(request()->except('page')) }}"
                           class="px-3 py-2 text-sm font-medium text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300 rounded-lg transition-all duration-200"
                           aria-label="Page 1">1</a>
                        @if($start > 2)
                            <span class="px-2 py-2 text-sm text-gray-400">...</span>
                        @endif
                    @endif

                    @for($page = $start; $page <= $end; $page++)
                        <a href="{{ $reports->url($page) . '&' . http_build_query(request()->except('page')) }}"
                           class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $page == $currentPage ? 'text-white bg-green-600 border border-green-600 shadow-sm' : 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' }}"
                           aria-label="Page {{ $page }}"
                           aria-current="{{ $page == $currentPage ? 'page' : 'false' }}">{{ $page }}</a>
                    @endfor

                    @if($end < $lastPage)
                        @if($end < $lastPage - 1)
                            <span class="px-2 py-2 text-sm text-gray-400">...</span>
                        @endif
                        <a href="{{ $reports->url($lastPage) . '&' . http_build_query(request()->except('page')) }}"
                           class="px-3 py-2 text-sm font-medium text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300 rounded-lg transition-all duration-200"
                           aria-label="Page {{ $lastPage }}">{{ $lastPage }}</a>
                    @endif

                    <!-- Next Button -->
                    <a href="{{ $reports->nextPageUrl() ? $reports->nextPageUrl() . '&' . http_build_query(request()->except('page')) : '#' }}"
                       class="px-3 py-2 text-sm font-medium rounded-lg transition-all duration-200 {{ $reports->hasMorePages() ? 'text-green-600 bg-white border border-gray-200 hover:bg-green-50 hover:text-green-800 hover:border-green-300' : 'text-gray-400 bg-gray-100 cursor-not-allowed' }}"
                       aria-label="Next page"
                       {{ !$reports->hasMorePages() ? 'disabled' : '' }}>
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                </nav>
            </div>
        @endif
    </div>

    <!-- Generate Report Form Container -->
    <div id="generateReportFormContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-magic mr-2"></i> Generate New Report
                </h3>
                <button type="button" onclick="toggleTab('allReportsTab')" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <form id="generateReportForm" action="{{ route('reports.generate') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Report Type & Period -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="report_type" class="block text-gray-700 text-sm font-semibold mb-2 flex items-center">
                            <i class="fas fa-chart-bar text-green-500 mr-2 text-sm"></i>Report Type
                        </label>
                        <select name="report_type" id="report_type" required 
                                class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-3 px-4 leading-6 transition-all duration-200 text-gray-900 shadow-sm">
                            <option value="">Select a report type</option>
                            <option value="payslip">📄 Payslip</option>
                            @if($isAdminOrHR)
                            <option value="payroll_summary">💰 Payroll Summary</option>
                            <option value="tax_report">🧾 Tax Report</option>
                            <option value="nssf_report">🏦 NSSF Report</option>
                            <option value="nhif_report">🏥 NHIF Report</option>
                            <option value="wcf_report">🛡️ WCF Report</option>
                            <option value="sdl_report">🎯 SDL Report</option>
                            <option value="year_end_summary">📊 Year-End Summary</option>
                            @endif
                        </select>
                        <span class="text-red-500 text-xs mt-1 hidden" id="reportTypeError">
                            <i class="fas fa-exclamation-circle mr-1"></i>Report Type is required
                        </span>
                        @error('report_type')
                            <p class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="report_period" class="block text-gray-700 text-sm font-semibold mb-2 flex items-center">
                            <i class="fas fa-calendar text-green-500 mr-2 text-sm"></i>Report Period
                        </label>
                        <input type="text" name="report_period" id="report_period" required 
                               class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-3 px-4 leading-6 transition-all duration-200 text-gray-900 shadow-sm" 
                               placeholder="Select period">
                        <span class="text-red-500 text-xs mt-1 hidden" id="reportPeriodError">
                            <i class="fas fa-exclamation-circle mr-1"></i>Report Period is required
                        </span>
                        @error('report_period')
                            <p class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Employee & Format -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <label for="employee_id" class="block text-gray-700 text-sm font-semibold mb-2 flex items-center">
                            <i class="fas fa-user text-green-500 mr-2 text-sm"></i>Specific Employee (Optional)
                        </label>
                        <select name="employee_id" id="employee_id" 
                                class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-3 px-4 leading-6 transition-all duration-200 text-gray-900 shadow-sm">
                            <option value="">👥 All Employees</option>
                            @foreach($employees ?? [] as $employee)
                                <option value="{{ $employee->employee_id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <p class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="export_format" class="block text-gray-700 text-sm font-semibold mb-2 flex items-center">
                            <i class="fas fa-download text-green-500 mr-2 text-sm"></i>Export Format
                        </label>
                        <select name="export_format" id="export_format" required 
                                class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-3 px-4 leading-6 transition-all duration-200 text-gray-900 shadow-sm">
                            <option value="pdf">📊 PDF Document</option>
                            <option value="excel">💾 Excel Spreadsheet</option>
                            <option value="csv">📝 CSV File</option>
                        </select>
                        <span class="text-red-500 text-xs mt-1 hidden" id="exportFormatError">
                            <i class="fas fa-exclamation-circle mr-1"></i>Export Format is required
                        </span>
                        @error('export_format')
                            <p class="text-red-500 text-xs mt-1 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-6 border-t border-gray-100">
                    <div class="text-sm text-gray-500 flex items-center">
                        <i class="fas fa-info-circle text-green-500 mr-2"></i>
                        Reports are generated in the background and will appear in the list above.
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" 
                                class="text-gray-700 bg-gray-100 hover:bg-gray-200 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-6 py-3 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md"
                                onclick="toggleTab('allReportsTab')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" id="generateReportSubmit" 
                                class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-6 py-3 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                            <span id="formSpinner" class="hidden animate-spin h-4 w-4 mr-2 border-t-2 border-r-2 border-white rounded-full"></span>
                            <i class="fas fa-bolt mr-2"></i> Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Report Modal -->
    @if($isAdminOrHR)
    <div id="deleteReportModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 hidden z-50" aria-hidden="true">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content" role="dialog" aria-labelledby="deleteModalTitle" aria-describedby="deleteModalDesc">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-red-50 to-red-100 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <h3 id="deleteModalTitle" class="text-xl font-semibold text-red-700 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Delete Report
                    </h3>
                    <button type="button" onclick="closeModal('deleteReportModal')" class="text-gray-500 hover:text-gray-700 rounded-full p-2 hover:bg-gray-200 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-300" aria-label="Close delete modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100">
                    <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                </div>
                <p id="deleteModalDesc" class="text-gray-700 text-center mb-4">
                    Are you sure you want to delete report <span id="deleteReportId" class="font-semibold text-red-600"></span>? 
                    <br>This action is permanent and cannot be undone.
                </p>
                <div class="flex justify-center space-x-3">
                    <button type="button" 
                            class="text-gray-700 bg-gray-100 hover:bg-gray-200 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-6 py-2.5 transition-all duration-200 flex items-center shadow-sm"
                            onclick="closeModal('deleteReportModal')" 
                            aria-label="Cancel deletion">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <form id="deleteReportForm" method="POST" action="" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-6 py-2.5 transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                            <i class="fas fa-trash-alt mr-2"></i> Delete Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Preview Modal -->
    <div id="previewModal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center p-4 hidden z-50" aria-hidden="true">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl transform transition-all duration-300 scale-95 modal-content" role="dialog" aria-labelledby="previewModalTitle">
            <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 rounded-t-xl">
                <div class="flex justify-between items-center">
                    <h3 id="previewModalTitle" class="text-xl font-semibold text-blue-700 flex items-center">
                        <i class="fas fa-eye mr-2"></i> Report Preview
                    </h3>
                    <button type="button" onclick="closeModal('previewModal')" class="text-gray-500 hover:text-gray-700 rounded-full p-2 hover:bg-gray-200 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6 max-h-96 overflow-y-auto">
                <div id="previewContent" class="text-center text-gray-500">
                    <i class="fas fa-spinner fa-spin text-2xl mb-4 text-blue-500"></i>
                    <p>Loading preview...</p>
                </div>
            </div>
        </div>
    </div>

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
                altInput.classList.add('bg-gray-50', 'border', 'border-gray-200', 'rounded-lg', 'focus:ring-2', 'focus:ring-green-500', 'focus:border-green-500', 'block', 'w-full', 'py-3', 'px-4', 'leading-6', 'transition-all', 'duration-200', 'text-gray-900', 'shadow-sm');
                altInput.classList.remove('flatpickr-input');
            }
        }

        // Initialize Flatpickr on page load
        initializeFlatpickr();

        // Update Flatpickr on report type change
        if (reportTypeSelect && reportPeriodInput) {
            reportTypeSelect.addEventListener('change', function() {
                reportPeriodInput.value = '';
                initializeFlatpickr();
            });
        }

        // Tab navigation
        document.getElementById('allReportsTab').addEventListener('click', () => toggleTab('allReportsTab'));
        document.getElementById('generateReportTab').addEventListener('click', () => toggleTab('generateReportTab'));

        // Debounced search with better UX
        let searchTimeout;
        const searchInput = document.getElementById('searchReport');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const searchValue = this.value.toLowerCase();
                
                // Show loading state
                const rows = document.querySelectorAll('.report-row');
                rows.forEach(row => row.style.opacity = '0.6');
                
                searchTimeout = setTimeout(() => {
                    rows.forEach(row => {
                        const reportId = row.dataset.reportId || '';
                        const employee = row.dataset.employee || '';
                        const type = row.dataset.type || '';
                        const status = row.dataset.status || '';
                        
                        const matches = reportId.includes(searchValue) || 
                                      employee.includes(searchValue) || 
                                      type.includes(searchValue) ||
                                      status.includes(searchValue);
                        
                        row.style.display = matches ? '' : 'none';
                        row.style.opacity = '1';
                    });
                }, 300);
            });
        }

        // Enhanced form validation
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
                        input.classList.add('border-red-300', 'bg-red-50');
                        input.classList.remove('border-gray-200', 'bg-gray-50');
                        valid = false;
                    } else {
                        error.classList.add('hidden');
                        input.classList.remove('border-red-300', 'bg-red-50');
                        input.classList.add('border-gray-200', 'bg-gray-50');
                    }
                });

                if (!valid) {
                    e.preventDefault();
                    // Add shake animation to invalid fields
                    fields.forEach(field => {
                        const input = document.getElementById(field.id);
                        const error = document.getElementById(field.errorId);
                        if (!input.value.trim()) {
                            input.classList.add('animate-shake');
                            setTimeout(() => input.classList.remove('animate-shake'), 500);
                        }
                    });
                    return;
                }

                // Show loading state
                submitButton.disabled = true;
                spinner.classList.remove('hidden');
                submitButton.innerHTML = '<span class="animate-pulse">Generating...</span>';
            });
        }

        // Delete modal
        window.openDeleteModal = function(id, reportId) {
            document.getElementById('deleteReportForm').action = `/dashboard/reports/${id}`;
            document.getElementById('deleteReportId').textContent = reportId || 'Unknown';
            openModal('deleteReportModal');
        };

        // Preview report function
        window.previewReport = function(id) {
            const previewContent = document.getElementById('previewContent');
            previewContent.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-2xl mb-4 text-blue-500"></i>
                    <p class="text-gray-600">Loading report preview...</p>
                </div>
            `;
            
            openModal('previewModal');
            
            // Simulate API call - in real implementation, you'd fetch from server
            setTimeout(() => {
                previewContent.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-file-pdf text-4xl text-red-500 mb-4"></i>
                        <h4 class="font-semibold text-gray-700 mb-2">Report Preview</h4>
                        <p class="text-gray-600 text-sm mb-4">This is a preview of the generated report.</p>
                        <p class="text-gray-500 text-xs">For full details, please download the complete report file.</p>
                    </div>
                `;
            }, 1500);
        };

        // Refresh reports function
        window.refreshReports = function() {
            const refreshBtn = event.target.closest('button');
            refreshBtn.classList.add('animate-spin');
            setTimeout(() => {
                refreshBtn.classList.remove('animate-spin');
                window.location.reload();
            }, 1000);
        };

        function toggleTab(tabId) {
            const tabs = ['allReportsTab', 'generateReportTab'];
            const containers = ['reportsTableContainer', 'generateReportFormContainer'];

            tabs.forEach(id => {
                const tab = document.getElementById(id);
                if (tab) {
                    tab.classList.remove('bg-green-600', 'text-white', 'shadow-inner');
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
                activeTab.classList.add('bg-green-600', 'text-white', 'shadow-inner');
                activeTab.setAttribute('aria-selected', 'true');
            }

            const containerId = tabId === 'allReportsTab' ? 'reportsTableContainer' : 'generateReportFormContainer';
            const container = document.getElementById(containerId);
            if (container) {
                container.classList.remove('hidden');
                // Add fade-in animation
                container.style.opacity = '0';
                container.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    container.style.transition = 'all 0.3s ease';
                    container.style.opacity = '1';
                    container.style.transform = 'translateY(0)';
                }, 50);
            }
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                setTimeout(() => {
                    const modalContent = modal.querySelector('.modal-content');
                    if (modalContent) {
                        modalContent.classList.remove('scale-95', 'opacity-0');
                        modalContent.classList.add('scale-100', 'opacity-100');
                    }
                }, 10);
            }
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.classList.remove('scale-100', 'opacity-100');
                    modalContent.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.setAttribute('aria-hidden', 'true');
                        document.body.style.overflow = 'auto';
                    }, 300);
                }
            }
        }

        // Keyboard navigation for modals
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal('deleteReportModal');
                closeModal('previewModal');
            }
        });

        // Add CSS for shake animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
            .animate-shake {
                animation: shake 0.5s ease-in-out;
            }
        `;
        document.head.appendChild(style);
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
</style>