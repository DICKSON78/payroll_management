@extends('layout.global')

@section('title', 'Employees')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">Employees</span>
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-bolt mr-1.5"></i> Premium Plan
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Manage employee records and details for {{ $currentPeriod }}.</span>
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:bg-green-50 hover:shadow-md transition-all duration-200 p-4 cursor-pointer" onclick="openModal('bulkImportModal')">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-upload text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">Bulk Import</div>
                        <div class="text-sm text-gray-500">Import multiple employees</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:bg-green-50 hover:shadow-md transition-all duration-200 p-4 cursor-pointer" onclick="exportEmployees()">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-download text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">Export Employees</div>
                        <div class="text-sm text-gray-500">Download employee data</div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:bg-green-50 hover:shadow-md transition-all duration-200 p-4 cursor-pointer" onclick="openModal('templateInstructionsModal')">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-file-download text-green-600 text-lg"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">Download Template</div>
                        <div class="text-sm text-gray-500">Get import template</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-4" id="filterSection">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-filter text-green-500 mr-2"></i> Filter Employees
            </h3>
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" id="searchEmployee" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Search by name, email, or ID..." value="{{ $search ?? '' }}">
                </div>
                <select id="departmentFilter" class="bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 py-2 px-3 text-sm w-full sm:w-48">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ strtolower($department->name) }}" {{ $request->input('department') == strtolower($department->name) ? 'selected' : '' }}>{{ $department->name }}</option>
                    @endforeach
                </select>
                <select id="statusFilter" class="bg-white border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 py-2 px-3 text-sm w-full sm:w-48">
                    <option value="">All Status</option>
                    <option value="active" {{ $request->input('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $request->input('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="terminated" {{ $request->input('status') == 'terminated' ? 'selected' : '' }}>Terminated</option>
                </select>
                <button onclick="clearFilters()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition-all duration-200 flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-6">
        <div class="flex space-x-4 border-b border-gray-200" role="tablist">
            <button id="allEmployeesTab" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-t-md focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="true" aria-controls="employeesTableContainer">
                All Employees
            </button>
            <button id="addEmployeeTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="addEmployeeFormContainer">
                Add Employee
            </button>
        </div>
    </div>

    <!-- Employees Table Container -->
    <div id="employeesTableContainer" class="block">
        @fragment('employeesTable')
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-users text-green-500 mr-2"></i> Employee List
                <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $employees->total() }} employees</span>
            </h3>
        </div>

        <!-- Table Container -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Name</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Employee ID</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Position</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Department</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Base Salary</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeesTableBody" class="divide-y divide-gray-100">
                        @foreach($employees as $employee)
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'inactive' => 'bg-red-100 text-red-800',
                                    'terminated' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusColor = $statusColors[$employee->status ?? 'active'] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <tr class="bg-white hover:bg-gray-50 transition-all duration-200 employee-row"
                                data-name="{{ strtolower($employee->name) }}"
                                data-email="{{ strtolower($employee->email) }}"
                                data-employee-id="{{ strtolower($employee->employee_id) }}"
                                data-department="{{ strtolower($employee->department) }}"
                                data-status="{{ strtolower($employee->status) }}"
                                data-position="{{ strtolower($employee->position) }}">
                                <td class="py-4 px-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                            <span class="font-medium text-green-800">{{ substr($employee->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $employee->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $employee->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-sm text-gray-900 font-mono">{{ $employee->employee_id }}</td>
                                <td class="py-4 px-6 text-sm text-gray-700">{{ $employee->position ?? 'N/A' }}</td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $employee->department ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-sm font-medium text-gray-900">TZS {{ number_format($employee->base_salary, 0) }}</td>
                                <td class="py-4 px-6">
                                    @if($employee->status == 'active')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="w-2 h-2 bg-green-500 rounded-full mr-1.5"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></span> {{ ucfirst($employee->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center space-x-2">
                                        <button onclick="viewEmployeeDetails('{{ $employee->employee_id }}')" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-md hover:bg-blue-50 transition-all duration-200" title="View Details">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        <button onclick="editEmployee('{{ $employee->employee_id }}')" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition-all duration-200" title="Edit Employee">
                                            <i class="fas fa-edit text-sm"></i>
                                        </button>
                                        <button onclick="toggleStatus('{{ $employee->employee_id }}', '{{ $employee->status }}')" class="text-gray-600 hover:text-gray-800 p-1.5 rounded-md hover:bg-gray-50 transition-all duration-200" title="{{ $employee->status === 'active' ? 'Deactivate' : 'Activate' }} Employee">
                                            <i class="fas {{ $employee->status === 'active' ? 'fa-power-off' : 'fa-play' }} text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4" id="paginationContainer">
                {{ $employees->links() }}
            </div>
        </div>
        @endfragment
    </div>

    <!-- Add Employee Form -->
    <div id="addEmployeeFormContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Add New Employee</h3>
            <form id="addEmployeeForm" action="{{ route('employees.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <h4 class="text-lg font-medium text-gray-700 border-b pb-2">Personal Information</h4>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Full Name *</label>
                            <input type="text" name="name" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Email Address *</label>
                            <input type="email" name="email" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Phone Number</label>
                            <input type="text" name="phone" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Gender</label>
                            <select name="gender" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Date of Birth</label>
                            <input type="date" name="dob" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Nationality</label>
                            <input type="text" name="nationality" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Address</label>
                            <input type="text" name="address" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="text-lg font-medium text-gray-700 border-b pb-2">Employment Information</h4>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Department *</label>
                            <select name="department" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Position *</label>
                            <input type="text" name="position" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Role *</label>
                            <select name="role" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                <option value="">Select Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->slug }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Employment Type *</label>
                            <select name="employment_type" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                <option value="">Select Type</option>
                                <option value="full-time">Full Time</option>
                                <option value="part-time">Part Time</option>
                                <option value="contract">Contract</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Hire Date *</label>
                            <input type="date" name="hire_date" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        <div id="contractEndDateContainer" class="hidden">
                            <label class="block text-gray-600 text-sm font-medium mb-2">Contract End Date *</label>
                            <input type="date" name="contract_end_date" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Status *</label>
                            <select name="status" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-4 col-span-1 md:col-span-2">
                        <h4 class="text-lg font-medium text-gray-700 border-b pb-2">Salary Information</h4>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Base Salary (TZS) *</label>
                            <input type="number" name="base_salary" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm font-medium mb-2">Allowances</label>
                            <div class="space-y-2">
                                @foreach($allowances as $allowance)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="allowances[]" value="{{ $allowance->id }}" class="mr-2">
                                        <span>{{ $allowance->name }} (TZS {{ number_format($allowance->amount, 0) }})</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4 col-span-1 md:col-span-2">
                        <h4 class="text-lg font-medium text-gray-700 border-b pb-2">Additional Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Bank Name</label>
                                <select name="bank_name" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Account Number</label>
                                <input type="text" name="account_number" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">NSSF Number</label>
                                <input type="text" name="nssf_number" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">TIN Number</label>
                                <input type="text" name="tin_number" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">NHIF Number</label>
                                <input type="text" name="nhif_number" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="switchToAllTab()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" id="addEmployeeSubmit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i> Save Employee
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Template Instructions Modal -->
    <div id="templateInstructionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-4xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-file-download mr-2"></i> Import Template Instructions
                </h3>
                <button type="button" onclick="closeModal('templateInstructionsModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close template instructions modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 text-lg mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-blue-800 mb-1">Important Notes</h4>
                            <p class="text-blue-700 text-sm">The template is an Excel sheet for bulk importing employees. Fill in the following columns carefully to ensure successful import.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h4 class="text-lg font-medium text-gray-800 mb-3">Required Fields</h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">name</span>
                                <span class="text-red-500 text-sm font-medium">Required</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">email</span>
                                <span class="text-red-500 text-sm font-medium">Required</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">department</span>
                                <span class="text-red-500 text-sm font-medium">Required</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">position</span>
                                <span class="text-red-500 text-sm font-medium">Required</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">employment_type</span>
                                <span class="text-red-500 text-sm font-medium">Required</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">hire_date</span>
                                <span class="text-red-500 text-sm font-medium">Required</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">base_salary</span>
                                <span class="text-red-500 text-sm font-medium">Required</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">role</span>
                                <span class="text-red-500 text-sm font-medium">Required</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-lg font-medium text-gray-800 mb-3">Optional Fields</h4>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">phone</span>
                                <span class="text-green-500 text-sm font-medium">Optional</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">gender</span>
                                <span class="text-green-500 text-sm font-medium">Optional</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">dob</span>
                                <span class="text-green-500 text-sm font-medium">Optional</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">nationality</span>
                                <span class="text-green-500 text-sm font-medium">Optional</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">address</span>
                                <span class="text-green-500 text-sm font-medium">Optional</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">bank_name</span>
                                <span class="text-green-500 text-sm font-medium">Optional</span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="font-medium text-gray-700">account_number</span>
                                <span class="text-green-500 text-sm font-medium">Optional</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-lg mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-medium text-yellow-800 mb-1">Special Notes</h4>
                            <ul class="text-yellow-700 text-sm space-y-1">
                                <li>• Employee ID is generated automatically during import</li>
                                <li>• For contract employees, contract_end_date is required</li>
                                <li>• Date formats should be YYYY-MM-DD (e.g., 2023-01-15)</li>
                                <li>• Department names must exist in the system</li>
                                <li>• Role slugs must exist in the roles table</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('templateInstructionsModal')" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200">
                    Close
                </button>
                <a href="{{ route('employees.download-template') }}" class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all duration-200 flex items-center">
                    <i class="fas fa-download mr-2"></i> Download Template
                </a>
            </div>
        </div>
    </div>

    <!-- Bulk Import Modal with Drag & Drop -->
    <div id="bulkImportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-upload mr-2"></i> Bulk Import Employees
                </h3>
                <button type="button" onclick="closeModal('bulkImportModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close bulk import modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <form id="bulkImportForm" action="{{ route('employees.bulk-import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <!-- Drag & Drop Area -->
                    <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center transition-all duration-300 hover:border-green-400 hover:bg-green-50">
                        <div class="flex flex-col items-center justify-center space-y-4">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-cloud-upload-alt text-green-500 text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-lg font-medium text-gray-700 mb-1">Drop your file here</p>
                                <p class="text-sm text-gray-500">or click to browse</p>
                            </div>
                            <input type="file" id="fileInput" name="file" accept=".xlsx,.xls,.csv" class="hidden" required>
                            <button type="button" onclick="document.getElementById('fileInput').click()" class="px-6 py-2 text-sm font-medium text-green-600 bg-green-100 rounded-lg hover:bg-green-200 transition-all duration-200">
                                Choose File
                            </button>
                        </div>
                    </div>

                    <!-- File Info -->
                    <div id="fileInfo" class="hidden bg-gray-50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file-excel text-green-500 text-xl"></i>
                                <div>
                                    <p id="fileName" class="font-medium text-gray-800"></p>
                                    <p id="fileSize" class="text-sm text-gray-500"></p>
                                </div>
                            </div>
                            <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Supported Formats -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 text-lg mt-1 mr-3"></i>
                            <div>
                                <h4 class="font-medium text-blue-800 mb-1">Supported Formats</h4>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-blue-700 border border-blue-200">
                                        <i class="fas fa-file-excel mr-1.5"></i> XLSX
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-blue-700 border border-blue-200">
                                        <i class="fas fa-file-excel mr-1.5"></i> XLS
                                    </span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-blue-700 border border-blue-200">
                                        <i class="fas fa-file-csv mr-1.5"></i> CSV
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('bulkImportModal')" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200">
                    Cancel
                </button>
                <button type="submit" form="bulkImportForm" class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all duration-200 flex items-center">
                    <i class="fas fa-upload mr-2"></i> Import Employees
                </button>
            </div>
        </div>
    </div>

    <!-- Status Confirm Modal -->
    <div id="statusConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <h3 class="text-xl font-semibold text-gray-800">Confirm Status Change</h3>
                <button type="button" onclick="closeModal('statusConfirmModal')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close status confirmation modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                </div>
                <p id="statusConfirmMessage" class="text-center text-gray-700 mb-2 text-lg font-medium"></p>
                <p class="text-center text-gray-500 text-sm">This action will immediately update the employee's status in the system.</p>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <button onclick="closeModal('statusConfirmModal')" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200">
                    Cancel
                </button>
                <button id="confirmStatusAction" class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all duration-200">
                    Confirm Change
                </button>
            </div>
        </div>
    </div>

    <!-- View Employee Modal -->
    <div id="viewEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-4xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-eye mr-2"></i> Employee Details
                    </h3>
                    <button type="button" onclick="closeModal('viewEmployeeModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close view employee modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <div id="viewEmployeeContent"></div>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end">
                <button onclick="closeModal('viewEmployeeModal')" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200">
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-[95%] max-w-[95%] transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-user-edit mr-2"></i> Edit Employee
                    </h3>
                    <button type="button" onclick="closeModal('editEmployeeModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close edit employee modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6 max-h-[80vh] overflow-y-auto">
                <div id="editEmployeeContent"></div>
            </div>
            <div class="border-t border-gray-200 px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <button type="button" onclick="closeModal('editEmployeeModal')" class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200">
                    Cancel
                </button>
                <button type="submit" form="editEmployeeForm" class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-all duration-200 flex items-center">
                    <i class="fas fa-check mr-2"></i> Save Changes
                </button>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Modal Functions
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

        // Drag & Drop Functionality
        function initializeDragAndDrop() {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            // Prevent default drag behaviors
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            // Highlight drop zone when item is dragged over it
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            // Handle dropped files
            dropZone.addEventListener('drop', handleDrop, false);

            // Handle file input change
            fileInput.addEventListener('change', handleFileSelect, false);

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            function highlight() {
                dropZone.classList.add('border-green-400', 'bg-green-50');
            }

            function unhighlight() {
                dropZone.classList.remove('border-green-400', 'bg-green-50');
            }

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }

            function handleFileSelect(e) {
                const files = e.target.files;
                handleFiles(files);
            }

            function handleFiles(files) {
                if (files.length > 0) {
                    const file = files[0];
                    if (isValidFileType(file)) {
                        displayFileInfo(file);
                        fileInput.files = files;
                    } else {
                        showErrorModal('Please select a valid file type (XLSX, XLS, or CSV).');
                    }
                }
            }

            function isValidFileType(file) {
                const validTypes = [
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/csv'
                ];
                const validExtensions = ['.xlsx', '.xls', '.csv'];
                const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                
                return validTypes.includes(file.type) || validExtensions.includes(fileExtension);
            }

            function displayFileInfo(file) {
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                fileInfo.classList.remove('hidden');
                dropZone.classList.add('hidden');
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        }

        function removeFile() {
            const fileInput = document.getElementById('fileInput');
            const fileInfo = document.getElementById('fileInfo');
            const dropZone = document.getElementById('dropZone');
            
            fileInput.value = '';
            fileInfo.classList.add('hidden');
            dropZone.classList.remove('hidden');
        }

        // Tab Switching
        function switchToAllTab() {
            document.getElementById('employeesTableContainer').classList.remove('hidden');
            document.getElementById('addEmployeeFormContainer').classList.add('hidden');
            document.getElementById('allEmployeesTab').classList.add('bg-green-600', 'text-white');
            document.getElementById('allEmployeesTab').classList.remove('bg-gray-100', 'text-gray-700');
            document.getElementById('addEmployeeTab').classList.add('bg-gray-100', 'text-gray-700');
            document.getElementById('addEmployeeTab').classList.remove('bg-green-600', 'text-white');
        }

        function switchToAddTab() {
            document.getElementById('employeesTableContainer').classList.add('hidden');
            document.getElementById('addEmployeeFormContainer').classList.remove('hidden');
            document.getElementById('addEmployeeTab').classList.add('bg-green-600', 'text-white');
            document.getElementById('addEmployeeTab').classList.remove('bg-gray-100', 'text-gray-700');
            document.getElementById('allEmployeesTab').classList.add('bg-gray-100', 'text-gray-700');
            document.getElementById('allEmployeesTab').classList.remove('bg-green-600', 'text-white');
        }

        // Filter and Sort Functions (AJAX)
        function filterTable(page = 1) {
            const search = document.getElementById('searchEmployee').value;
            const dept = document.getElementById('departmentFilter').value;
            const status = document.getElementById('statusFilter').value;
            const sort = document.querySelector('[data-sort-column]')?.dataset.sortColumn || 'name';
            const direction = document.querySelector('[data-sort-column="' + sort + '"]')?.dataset.sortDirection === 'asc' ? 'desc' : 'asc';

            const params = new URLSearchParams({
                search: search,
                department: dept,
                status: status,
                sort: sort,
                direction: direction,
                page: page,
                ajax: 1
            });

            fetch(window.location.pathname + '?' + params.toString(), {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('#employeesTableBody');
                const newPagination = doc.querySelector('#paginationContainer');
                if (newTableBody) {
                    document.getElementById('employeesTableBody').innerHTML = newTableBody.innerHTML;
                }
                if (newPagination) {
                    document.getElementById('paginationContainer').innerHTML = newPagination.innerHTML;
                }
                attachPaginationListeners();
                updateSortIndicators(sort, direction);
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorModal('Failed to load employees.');
            });
        }

        function sortTable(column) {
            const currentSort = document.querySelector('[data-sort-column="' + column + '"]')?.dataset.sortColumn || 'name';
            const currentDirection = document.querySelector('[data-sort-column="' + column + '"]')?.dataset.sortDirection || 'asc';
            const newDirection = (currentSort === column && currentDirection === 'asc') ? 'desc' : 'asc';
            
            document.querySelectorAll('[data-sort-column]').forEach(th => {
                th.dataset.sortDirection = 'asc';
            });
            document.querySelector('[data-sort-column="' + column + '"]').dataset.sortDirection = newDirection;
            
            filterTable();
        }

        function updateSortIndicators(sort, direction) {
            document.querySelectorAll('[data-sort-column]').forEach(th => {
                const icon = th.querySelector('.fas.fa-sort');
                if (icon) {
                    icon.classList.remove('fa-sort-up', 'fa-sort-down');
                    if (th.dataset.sortColumn === sort) {
                        icon.classList.add(direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down');
                    } else {
                        icon.classList.add('fa-sort');
                    }
                }
            });
        }

        function clearFilters() {
            document.getElementById('searchEmployee').value = '';
            document.getElementById('departmentFilter').value = '';
            document.getElementById('statusFilter').value = '';
            filterTable();
        }

        function attachPaginationListeners() {
            const paginationLinks = document.querySelectorAll('.pagination a');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page') || 1;
                    filterTable(page);
                });
            });
        }

        function debounce(func, delay) {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(func, delay);
            };
        }

        const debouncedFilter = debounce(filterTable, 300);

        // Quick Actions Functions
        function exportEmployees() {
            window.location.href = '{{ route('employees.export') }}';
        }

        function downloadTemplate() {
            openModal('templateInstructionsModal');
        }

        // Handle Employment Type Change
        function handleEmploymentTypeChange(form) {
            const employmentTypeSelect = form.querySelector('select[name="employment_type"]');
            const contractEndDateContainer = form.querySelector('#contractEndDateContainer');
            
            if (employmentTypeSelect && contractEndDateContainer) {
                employmentTypeSelect.addEventListener('change', function() {
                    if (this.value === 'contract') {
                        contractEndDateContainer.classList.remove('hidden');
                        const input = contractEndDateContainer.querySelector('input[name="contract_end_date"]');
                        if (input) input.setAttribute('required', 'required');
                    } else {
                        contractEndDateContainer.classList.add('hidden');
                        const input = contractEndDateContainer.querySelector('input[name="contract_end_date"]');
                        if (input) input.removeAttribute('required');
                    }
                });
                employmentTypeSelect.dispatchEvent(new Event('change'));
            }
        }

        // Employee Management Functions
        function toggleStatus(employeeId, currentStatus) {
            const message = currentStatus === 'active' 
                ? `Are you sure you want to deactivate employee ${employeeId}?`
                : `Are you sure you want to activate employee ${employeeId}?`;
            
            document.getElementById('statusConfirmMessage').textContent = message;
            document.getElementById('confirmStatusAction').dataset.employeeId = employeeId;
            openModal('statusConfirmModal');
        }

        function confirmStatusAction() {
            const employeeId = document.getElementById('confirmStatusAction').dataset.employeeId;
            const url = '{{ route("employees.toggle.status", ["employeeId" => ":employeeId"]) }}'.replace(':employeeId', employeeId);
            
            fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                closeModal('statusConfirmModal');
                if (data.success) {
                    showSuccessModal(data.message || 'Status updated successfully');
                    filterTable();
                } else {
                    showErrorModal(data.message || 'Failed to update status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeModal('statusConfirmModal');
                showErrorModal('Failed to update status');
            });
        }

        function viewEmployeeDetails(employeeId) {
            const url = '{{ route("employees.show", ["employeeId" => ":employeeId"]) }}'.replace(':employeeId', employeeId) + '?mode=view';
            
            fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('viewEmployeeContent').innerHTML = html;
                openModal('viewEmployeeModal');
            })
            .catch(error => {
                showErrorModal('Failed to load employee details.');
                console.error('Error:', error);
            });
        }

        function editEmployee(employeeId) {
            const url = '{{ route("employees.show", ["employeeId" => ":employeeId"]) }}'.replace(':employeeId', employeeId) + '?mode=edit';
            
            fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.text())
            .then(html => {
                document.getElementById('editEmployeeContent').innerHTML = html;
                const form = document.getElementById('editEmployeeForm');
                if (form) handleEmploymentTypeChange(form);
                openModal('editEmployeeModal');
            })
            .catch(error => {
                showErrorModal('Failed to load employee details.');
                console.error('Error:', error);
            });
        }

        // Notification Functions
        function showSuccessModal(message) {
            showNotification(message, 'success');
        }

        function showErrorModal(message) {
            showNotification(message, 'error');
        }

        function showNotification(message, type) {
            const icon = type === 'success' ? 'fa-check-circle text-green-500' : 'fa-times-circle text-red-500';
            const bgColor = type === 'success' ? 'bg-green-50' : 'bg-red-50';
            const borderColor = type === 'success' ? 'border-green-400' : 'border-red-400';
            const textColor = type === 'success' ? 'text-green-700' : 'text-red-700';

            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 ${bgColor} border-l-4 ${borderColor} ${textColor} p-4 rounded-lg shadow-lg z-50 max-w-sm transform translate-x-full transition-transform duration-300`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${icon} text-xl mr-3"></i>
                    <span class="font-medium">${message}</span>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 10);

            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Initialization
        document.addEventListener('DOMContentLoaded', function() {
            const allTab = document.getElementById('allEmployeesTab');
            const addTab = document.getElementById('addEmployeeTab');
            if (allTab) allTab.addEventListener('click', switchToAllTab);
            if (addTab) addTab.addEventListener('click', switchToAddTab);

            const searchInput = document.getElementById('searchEmployee');
            const departmentFilter = document.getElementById('departmentFilter');
            const statusFilter = document.getElementById('statusFilter');

            if (searchInput) searchInput.addEventListener('input', debouncedFilter);
            if (departmentFilter) departmentFilter.addEventListener('change', filterTable);
            if (statusFilter) statusFilter.addEventListener('change', filterTable);

            const addForm = document.getElementById('addEmployeeForm');
            if (addForm) handleEmploymentTypeChange(addForm);

            const confirmButton = document.getElementById('confirmStatusAction');
            if (confirmButton) confirmButton.addEventListener('click', confirmStatusAction);

            // Initialize drag and drop
            initializeDragAndDrop();

            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    const requiredFields = addForm.querySelectorAll('[required]');
                    let isValid = true;
                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            isValid = false;
                            field.classList.add('border-red-500');
                        } else {
                            field.classList.remove('border-red-500');
                        }
                    });
                    if (!isValid) {
                        e.preventDefault();
                        showErrorModal('Please fill in all required fields');
                    }
                });
            }

            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || 'all';
            if (activeTab === 'add') switchToAddTab();

            attachPaginationListeners();

            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('fixed') && e.target.id.includes('Modal')) {
                    closeModal(e.target.id);
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const openModals = document.querySelectorAll('.fixed:not(.hidden)');
                    openModals.forEach(modal => closeModal(modal.id));
                }
            });
        });
    </script>
@endsection