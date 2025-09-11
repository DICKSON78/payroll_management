@extends('layout.global')

@section('title', 'Attendance')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">Attendance Management</span>
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-bolt mr-1.5"></i> Track Time
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Log and manage employee attendance and leave requests for the current period.</span>
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

    <div class="mb-6">
        <div class="flex space-x-4 border-b border-gray-200" role="tablist">
            <button id="allAttendanceTab" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-t-md focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="true" aria-controls="attendanceTableContainer">
                All Records
            </button>
            @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                <button id="logAttendanceTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="logAttendanceFormContainer">
                    Log Attendance
                </button>
            @endif
            <button id="leaveRequestsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200 relative" role="tab" aria-selected="false" aria-controls="leaveRequestsContainer">
                Leave Requests
                @if((Auth::user()->role === 'Admin' || Auth::user()->role === 'HR') && isset($pendingLeaveCount) && $pendingLeaveCount > 0)
                    <span class="absolute -top-2 -right-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-500 text-white">
                        {{ $pendingLeaveCount }}
                    </span>
                @endif
            </button>
        </div>
    </div>

    <!-- Attendance Table Container -->
    <div id="attendanceTableContainer" class="block">
        <!-- Search Input -->
        <div class="mb-6 relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input id="searchAttendance" type="text" placeholder="Search by employee or date..." class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" aria-label="Search attendance by employee or date">
        </div>

        <!-- Attendance Table Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-clock text-green-500 mr-2"></i> Attendance Records
                <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $attendances->total() }} records</span>
            </h3>
            <div class="flex space-x-2">
                @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                    <form action="{{ route('attendance.export') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 focus:ring-4 focus:ring-green-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                            <i class="fas fa-download mr-2"></i> Export CSV
                        </button>
                    </form>
                @endif
                <button class="text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 focus:ring-4 focus:ring-green-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md" onclick="openModal('requestLeaveModal')">
                    <i class="fas fa-calendar-plus mr-2"></i> Request Leave
                </button>
            </div>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Employee</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Date</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Hours Worked</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Overtime Hours</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                            @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                                <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="attendanceTable" class="divide-y divide-gray-100">
                        @foreach($attendances as $attendance)
                            @if(Auth::user()->role !== 'Employee' || Auth::user()->id === $attendance->employee->user_id)
                                <tr id="attendance-{{ $attendance->id }}" class="bg-white hover:bg-gray-50 transition-all duration-200 attendance-row group" data-employee="{{ strtolower($attendance->employee->name) }}" data-date="{{ $attendance->date?->format('Y-m-d') ?? '' }}">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="font-medium text-green-800">{{ substr($attendance->employee->name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $attendance->employee->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $attendance->employee->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-900">{{ $attendance->date?->format('d/m/Y') ?? '-' }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-700">{{ number_format($attendance->hours_worked, 2) }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-700">{{ number_format($attendance->overtime_hours, 2) }}</td>
                                    <td class="py-4 px-6">
                                        <span class="status-badge {{ $attendance->status == 'Processed' ? 'status-paid' : 'status-pending' }}">
                                            {{ $attendance->status }}
                                        </span>
                                    </td>
                                    @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-2">
                                                <button onclick="editAttendance({{ $attendance->id }})" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition-all duration-200" title="Edit" aria-label="Edit attendance for {{ $attendance->employee->name }}">
                                                    <i class="fas fa-edit text-sm"></i>
                                                </button>
                                                <button class="text-gray-400 hover:text-gray-600 p-1.5 rounded-md hover:bg-gray-100 transition-all duration-200" title="View Details" aria-label="View details for {{ $attendance->employee->name }}">
                                                    <i class="fas fa-ellipsis-h text-sm"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            @if($attendances->count() == 0)
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-clock text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No attendance records found</h3>
                <p class="text-gray-500 mb-6">Get started by logging your first attendance record.</p>
                @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                    <button class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="toggleTab('logAttendanceTab')">
                        <i class="fas fa-plus mr-2"></i> Log Attendance
                    </button>
                @else
                    <button class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="openModal('requestLeaveModal')">
                        <i class="fas fa-calendar-plus mr-2"></i> Request Leave
                    </button>
                @endif
            </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($attendances->hasPages())
        <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5">
            <div class="text-sm text-gray-700">
                Showing {{ $attendances->firstItem() }} to {{ $attendances->lastItem() }} of {{ $attendances->total() }} results
            </div>
            <div class="flex space-x-2">
                @if($attendances->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-sm">Previous</span>
                @else
                <a href="{{ $attendances->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Previous</a>
                @endif
                @if($attendances->hasMorePages())
                <a href="{{ $attendances->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Next</a>
                @else
                <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-sm">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Log Attendance Form Container (only for Admin/HR) -->
    @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
    <div id="logAttendanceFormContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-xl font-semibold text-green-600 flex items-center mb-6">
                <i class="fas fa-plus mr-2"></i> Log New Attendance
            </h3>
            <form action="{{ route('attendance.store') }}" method="POST" class="space-y-6" id="logAttendanceForm">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <!-- Employee Selection -->
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="employee_id">Employee</label>
                        <select name="employee_id" id="employee_id" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                            <option value="">Select Employee</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Date -->
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="date">Date</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="date" id="date" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr" placeholder="Select date">
                    </div>
                    <!-- Hours Worked -->
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="hours_worked">Hours Worked</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-clock text-gray-400 text-base"></i>
                        </div>
                        <input type="number" step="0.01" name="hours_worked" id="hours_worked" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="8.00">
                    </div>
                    <!-- Overtime Hours -->
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="overtime_hours">Overtime Hours</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-clock text-gray-400 text-base"></i>
                        </div>
                        <input type="number" step="0.01" name="overtime_hours" id="overtime_hours" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="0.00">
                    </div>
                    <!-- Status -->
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="status">Status</label>
                        <select name="status" id="status" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                            <option value="Pending">Pending</option>
                            <option value="Processed">Processed</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="toggleTab('allAttendanceTab')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <i class="fas fa-check mr-2"></i> Log Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Leave Requests Container -->
    <div id="leaveRequestsContainer" class="hidden">
        <!-- Search Input -->
        <div class="mb-6 relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input id="searchLeaveRequests" type="text" placeholder="Search by employee or leave type..." class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" aria-label="Search leave requests by employee or leave type">
        </div>

        <!-- Leave Requests Table Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-calendar-alt text-green-500 mr-2"></i> Leave Requests
                <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $leaveRequests->total() }} requests</span>
            </h3>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Employee</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Leave Type</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Start Date</th>
                            <th class="py-3.5 px-6 text-left font-semibold">End Date</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Reason</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Feedback</th>
                            @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                                <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="leaveRequestsTable" class="divide-y divide-gray-100">
                        @foreach($leaveRequests as $leaveRequest)
                            @if(Auth::user()->role !== 'Employee' || Auth::user()->id === $leaveRequest->employee->user_id)
                                <tr id="leave-request-{{ $leaveRequest->id }}" class="bg-white hover:bg-gray-50 transition-all duration-200 leave-request-row group" data-employee="{{ strtolower($leaveRequest->employee->name) }}" data-leave-type="{{ strtolower($leaveRequest->leave_type) }}">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="font-medium text-green-800">{{ substr($leaveRequest->employee->name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                                                    <button onclick="reviewLeaveRequest({{ $leaveRequest->id }})" class="font-medium text-gray-900 hover:text-green-600 transition-colors">
                                                        {{ $leaveRequest->employee->name }}
                                                    </button>
                                                @else
                                                    <div class="font-medium text-gray-900">{{ $leaveRequest->employee->name }}</div>
                                                @endif
                                                <div class="text-sm text-gray-500">{{ $leaveRequest->employee->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-700">{{ ucfirst($leaveRequest->leave_type) }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-700">{{ $leaveRequest->start_date?->format('d/m/Y') }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-700">{{ $leaveRequest->end_date?->format('d/m/Y') }}</td>
                                    <td class="py-4 px-6 text-sm text-gray-700">{{ Str::limit($leaveRequest->reason, 50) }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $leaveRequest->status == 'Processed' ? 'bg-green-100 text-green-800' : ($leaveRequest->status == 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            <span class="w-2 h-2 {{ $leaveRequest->status == 'Processed' ? 'bg-green-500' : ($leaveRequest->status == 'Rejected' ? 'bg-red-500' : 'bg-yellow-500') }} rounded-full mr-1.5"></span>
                                            {{ $leaveRequest->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-700">{{ $leaveRequest->feedback ? Str::limit($leaveRequest->feedback, 30) : '-' }}</td>
                                    @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-2">
                                                <button onclick="reviewLeaveRequest({{ $leaveRequest->id }})" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition-all duration-200" title="Review" aria-label="Review leave request for {{ $leaveRequest->employee->name }}">
                                                    <i class="fas fa-eye text-sm"></i>
                                                </button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            @if($leaveRequests->count() == 0)
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No leave requests found</h3>
                <p class="text-gray-500 mb-6">Get started by submitting a leave request.</p>
                <button class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="openModal('requestLeaveModal')">
                    <i class="fas fa-calendar-plus mr-2"></i> Request Leave
                </button>
            </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($leaveRequests->hasPages())
        <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5">
            <div class="text-sm text-gray-700">
                Showing {{ $leaveRequests->firstItem() }} to {{ $leaveRequests->lastItem() }} of {{ $leaveRequests->total() }} results
            </div>
            <div class="flex space-x-2">
                @if($leaveRequests->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-sm">Previous</span>
                @else
                <a href="{{ $leaveRequests->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Previous</a>
                @endif
                @if($leaveRequests->hasMorePages())
                <a href="{{ $leaveRequests->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Next</a>
                @else
                <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-sm">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Request Leave Modal -->
    <div id="requestLeaveModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-calendar-plus mr-2"></i> Request Leave
                    </h3>
                    <button type="button" onclick="closeModal('requestLeaveModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close leave request modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="requestLeaveForm" action="{{ route('attendance.requestLeave') }}" method="POST" class="p-6 space-y-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="leave_employee_id">Employee</label>
                        @if(Auth::user()->role === 'employee')
                            <input type="hidden" name="employee_id" value="{{ Auth::user()->employee->id }}">
                            <input type="text" id="leave_employee_id" value="{{ Auth::user()->employee->name }}" class="bg-gray-50 border border-gray-200 rounded-lg block w-full py-2.5 px-3 leading-6 transition-all duration-200" readonly>
                        @else
                            <select name="employee_id" id="leave_employee_id" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                                <option value="">Select Employee</option>
                                @foreach($employees ?? [] as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_id }})</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    <div>

                        <label class="block text-gray-600 text-sm font-medium mb-2" for="leave_type">Leave Type</label>
                        
                        <select name="leave_type" id="leave_type" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                         @foreach ($leaveTypes as $type)
                            <option value="{{ $type->name }}">{{ $type->name }}</option>
                        @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="start_date">Start Date</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="start_date" id="start_date" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr" placeholder="Select date">
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="end_date">End Date</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="end_date" id="end_date" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr" placeholder="Select date">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="reason">Reason</label>
                        <textarea name="reason" id="reason" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Provide reason for leave" rows="4"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="closeModal('requestLeaveModal')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <i class="fas fa-check mr-2"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Review Leave Request Modal (Admin/HR only) -->
    @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
    <div id="reviewLeaveRequestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-eye mr-2"></i> Review Leave Request
                    </h3>
                    <button type="button" onclick="closeModal('reviewLeaveRequestModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close review modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="reviewLeaveRequestForm" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2">Employee</label>
                        <input type="text" id="review_employee_name" class="bg-gray-50 border border-gray-200 rounded-lg block w-full py-2.5 px-3 leading-6 transition-all duration-200" readonly>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2">Leave Type</label>
                        <input type="text" id="review_leave_type" class="bg-gray-50 border border-gray-200 rounded-lg block w-full py-2.5 px-3 leading-6 transition-all duration-200" readonly>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2">Start Date</label>
                        <input type="text" id="review_start_date" class="bg-gray-50 border border-gray-200 rounded-lg block w-full py-2.5 px-3 leading-6 transition-all duration-200" readonly>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2">End Date</label>
                        <input type="text" id="review_end_date" class="bg-gray-50 border border-gray-200 rounded-lg block w-full py-2.5 px-3 leading-6 transition-all duration-200" readonly>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-gray-600 text-sm font-medium mb-2">Reason</label>
                        <textarea id="review_reason" class="bg-gray-50 border border-gray-200 rounded-lg block w-full py-2.5 px-3 leading-6 transition-all duration-200" readonly rows="4"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="review_status">Status</label>
                        <select name="status" id="review_status" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                            <option value="">Select Status</option>
                            <option value="Processed">Processed</option>
                            <option value="Rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="review_feedback">Feedback (Optional)</label>
                        <textarea name="feedback" id="review_feedback" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Provide feedback for the employee" rows="4"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="closeModal('reviewLeaveRequestModal')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <i class="fas fa-check mr-2"></i> Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Edit Attendance Modal -->
    <div id="editAttendanceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-edit mr-2"></i> Edit Attendance
                    </h3>
                    <button type="button" onclick="closeModal('editAttendanceModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close edit modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="editAttendanceForm" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_employee_id">Employee</label>
                        <select id="edit_employee_id" name="employee_id" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                            <option value="">Select Employee</option>
                            @foreach($employees ?? [] as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_date">Date</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" id="edit_date" name="date" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr" placeholder="Select date">
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_hours_worked">Hours Worked</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-clock text-gray-400 text-base"></i>
                        </div>
                        <input type="number" step="0.01" id="edit_hours_worked" name="hours_worked" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="8.00">
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_overtime_hours">Overtime Hours</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-clock text-gray-400 text-base"></i>
                        </div>
                        <input type="number" step="0.01" id="edit_overtime_hours" name="overtime_hours" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="0.00">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_status">Status</label>
                        <select id="edit_status" name="status" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                            <option value="Pending">Pending</option>
                            <option value="Processed">Processed</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="closeModal('editAttendanceModal')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <i class="fas fa-check mr-2"></i> Update Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('modals')
    @parent
@endsection

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>

<script>
    // Initialize Flatpickr for date inputs
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('.flatpickr', {
            dateFormat: 'Y-m-d',
            allowInput: true,
            altInput: true,
            altFormat: 'F j, Y',
            maxDate: ['start_date', 'edit_date'].includes(this.id) ? 'today' : null,
            wrap: false,
            onReady: function(selectedDates, dateStr, instance) {
                instance.element.style.cursor = 'pointer';
            }
        });

        // Tab navigation
        document.getElementById('allAttendanceTab').addEventListener('click', () => toggleTab('allAttendanceTab'));
        @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
            document.getElementById('logAttendanceTab').addEventListener('click', () => toggleTab('logAttendanceTab'));
        @endif
        document.getElementById('leaveRequestsTab').addEventListener('click', () => toggleTab('leaveRequestsTab'));

        // Search functionality for attendance
        const searchAttendance = document.getElementById('searchAttendance');
        if (searchAttendance) {
            searchAttendance.addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                const rows = document.querySelectorAll('.attendance-row');
                rows.forEach(row => {
                    const employee = row.dataset.employee || '';
                    const date = row.dataset.date || '';
                    const matches = employee.includes(searchValue) || date.includes(searchValue);
                    row.style.display = matches ? '' : 'none';
                });
            });
        }

        // Search functionality for leave requests
        const searchLeaveRequests = document.getElementById('searchLeaveRequests');
        if (searchLeaveRequests) {
            searchLeaveRequests.addEventListener('input', function() {
                const searchValue = this.value.toLowerCase();
                const rows = document.querySelectorAll('.leave-request-row');
                rows.forEach(row => {
                    const employee = row.dataset.employee || '';
                    const leaveType = row.dataset.leaveType || '';
                    const matches = employee.includes(searchValue) || leaveType.includes(searchValue);
                    row.style.display = matches ? '' : 'none';
                });
            });
        }

        // Reset forms on tab switch or cancel
        @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
            document.getElementById('logAttendanceTab').addEventListener('click', () => {
                document.getElementById('logAttendanceForm').reset();
                document.querySelectorAll('#logAttendanceForm .flatpickr').forEach(input => {
                    if (input._flatpickr) input._flatpickr.clear();
                });
            });
        @endif
        document.getElementById('requestLeaveModal').addEventListener('click', () => {
            document.getElementById('requestLeaveForm').reset();
            document.querySelectorAll('#requestLeaveForm .flatpickr').forEach(input => {
                if (input._flatpickr) input._flatpickr.clear();
            });
        });
    });

    function toggleTab(tabId) {
        const tabs = ['allAttendanceTab', 'logAttendanceTab', 'leaveRequestsTab'];
        const containers = ['attendanceTableContainer', 'logAttendanceFormContainer', 'leaveRequestsContainer'];

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

        const containerId = tabId === 'allAttendanceTab' ? 'attendanceTableContainer' : (tabId === 'logAttendanceTab' ? 'logAttendanceFormContainer' : 'leaveRequestsContainer');
        const container = document.getElementById(containerId);
        if (container) container.classList.remove('hidden');
    }

    function openModal(id) {
        const modal = document.getElementById(id);
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

    function closeModal(id) {
        const modal = document.getElementById(id);
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

    function editAttendance(id) {
        fetch(`/dashboard/attendance/${id}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_employee_id').value = data.employee_id;
                document.getElementById('edit_date').value = data.date;
                document.getElementById('edit_hours_worked').value = parseFloat(data.hours_worked).toFixed(2);
                document.getElementById('edit_overtime_hours').value = parseFloat(data.overtime_hours).toFixed(2);
                document.getElementById('edit_status').value = data.status;
                document.getElementById('editAttendanceForm').action = `/dashboard/attendance/${id}`;
                openModal('editAttendanceModal');
            })
            .catch(error => {
                console.error('Error fetching attendance:', error);
                alert('Failed to fetch attendance data. Please try again.');
            });
    }

    function reviewLeaveRequest(id) {
        fetch(`/dashboard/leave-request/${id}/review`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                document.getElementById('review_employee_name').value = data.employee_name;
                document.getElementById('review_leave_type').value = data.leave_type;
                document.getElementById('review_start_date').value = data.start_date;
                document.getElementById('review_end_date').value = data.end_date;
                document.getElementById('review_reason').value = data.reason;
                document.getElementById('review_status').value = '';
                document.getElementById('review_feedback').value = data.feedback || '';
                document.getElementById('reviewLeaveRequestForm').action = `/dashboard/leave-request/${id}/review`;
                openModal('reviewLeaveRequestModal');
            })
            .catch(error => {
                console.error('Error fetching leave request:', error);
                alert('Failed to fetch leave request data. Please try again.');
            });
    }
</script>
