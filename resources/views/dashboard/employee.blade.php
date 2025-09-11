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
        <!-- Search Input -->
        <div class="mb-6 relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input id="searchEmployee" type="text" placeholder="Search by name or ID..." class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" aria-label="Search employees by name or ID">
        </div>

        <!-- Employee Table Header -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-users text-green-500 mr-2"></i> All Employees
                <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $employees->total() }} employees</span>
            </h3>
            <div class="flex space-x-2">

                <button class="text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 focus:ring-4 focus:ring-green-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md" onclick="openModal('bulkImportModal')">
                    <i class="fas fa-upload mr-2"></i> Bulk Import
                </button>
            </div>
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
                    <tbody id="employeesTable" class="divide-y divide-gray-100">
                        @foreach($employees as $employee)
                        <tr id="employee-{{ $employee->id }}" class="bg-white hover:bg-gray-50 transition-all duration-200 employee-row group" data-name="{{ strtolower($employee->name) }}" data-id="{{ strtolower($employee->employee_id) }}">
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
                            <td class="py-4 px-6 text-sm text-gray-700">{{ $employee->position }}</td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $employee->department }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-sm text-gray-900 font-medium">TZS {{ number_format($employee->base_salary, 0) }}</td>
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
                                    <button onclick="editEmployee({{ $employee->id }})" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition-all duration-200" title="Edit" aria-label="Edit employee {{ $employee->name }}">
                                        <i class="fas fa-edit text-sm"></i>
                                    </button>
                                    <button onclick="openDeleteModal({{ $employee->id }})" class="text-red-600 hover:text-red-800 p-1.5 rounded-md hover:bg-red-50 transition-all duration-200" title="Delete" aria-label="Delete employee {{ $employee->name }}">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                    <button class="text-gray-400 hover:text-gray-600 p-1.5 rounded-md hover:bg-gray-100 transition-all duration-200" title="View Details" aria-label="View details for {{ $employee->name }}">
                                        <i class="fas fa-ellipsis-h text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Empty State -->
            @if($employees->count() == 0)
            <div class="text-center py-12">
                <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                    <i class="fas fa-users text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-1">No employees found</h3>
                <p class="text-gray-500 mb-6">Get started by adding your first employee.</p>
                <button class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="toggleTab('addEmployeeTab')">
                    <i class="fas fa-user-plus mr-2"></i> Add Employee
                </button>
            </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($employees->hasPages())
        <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5">
            <div class="text-sm text-gray-700">
                Showing {{ $employees->firstItem() }} to {{ $employees->lastItem() }} of {{ $employees->total() }} results
            </div>
            <div class="flex space-x-2">
                @if($employees->onFirstPage())
                <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-sm">Previous</span>
                @else
                <a href="{{ $employees->previousPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Previous</a>
                @endif
                @if($employees->hasMorePages())
                <a href="{{ $employees->nextPageUrl() }}" class="px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-green-600 hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Next</a>
                @else
                <span class="px-3 py-1.5 rounded-lg bg-gray-100 text-gray-400 text-sm">Next</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Add Employee Form Container -->
<div id="addEmployeeFormContainer" class="hidden">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <h3 class="text-xl font-semibold text-green-600 flex items-center mb-6">
            <i class="fas fa-user-plus mr-2"></i> Add New Employee
        </h3>
        <form action="{{ route('employees.store') }}" method="POST" class="space-y-6" id="addEmployeeForm">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="space-y-6">
                    <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Personal Information</h4>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="name">Full Name</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-user text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="name" id="name" required
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="John Doe" aria-describedby="nameError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="nameError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Full Name is required
                        </span>
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="email">Email Address</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-envelope text-gray-400 text-base"></i>
                        </div>
                        <input type="email" name="email" id="email" required
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="john@company.com" aria-describedby="emailError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="emailError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Valid email is required
                        </span>
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="phone">Phone Number</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-phone text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="phone" id="phone"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="+255 123 456 789">
                    </div>

                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="gender">Gender</label>
                        <select name="gender" id="gender"
                                class="bg-gray-50 border border-gray-200 rounded-lg
                                       focus:ring-2 focus:ring-green-500 focus:border-green-500
                                       block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                                aria-describedby="genderError">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="genderError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Gender is required
                        </span>
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="dob">Date of Birth</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="dob" id="dob"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr"
                               placeholder="Select date" aria-describedby="dobError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="dobError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Invalid date format
                        </span>
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="nationality">Nationality</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-globe text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="nationality" id="nationality"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="Tanzanian">
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="address">Address</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-map-marker-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="address" id="address"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="123 Main St, Dar es Salaam">
                    </div>
                </div>

                <div class="space-y-6">
                    <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Employment Details</h4>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="employee_id">Employee ID</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-id-badge text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="employee_id" id="employee_id" required
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="EMP-001" aria-describedby="employeeIdError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="employeeIdError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Employee ID is required
                        </span>
                    </div>

                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="department">Department</label>
                        <select name="department" id="department"
                                class="bg-gray-50 border border-gray-200 rounded-lg
                                       focus:ring-2 focus:ring-green-500 focus:border-green-500
                                       block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                                required aria-describedby="departmentError">
                            <option value="">Select Department</option>
                            @if(isset($departments) && $departments->isNotEmpty())
                                @foreach($departments as $department)
                                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                                @endforeach
                            @else
                                <option value="IT">IT</option>
                                <option value="HR">HR</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Operations">Operations</option>
                            @endif
                        </select>
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="departmentError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Department is required
                        </span>
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="position">Position</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-briefcase text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="position" id="position" required
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="Software Developer" aria-describedby="positionError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="positionError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Position is required
                        </span>
                    </div>

                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="role">Role</label>
                        <select name="role" id="role"
                                class="bg-gray-50 border border-gray-200 rounded-lg
                                       focus:ring-2 focus:ring-green-500 focus:border-green-500
                                       block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                                required aria-describedby="roleError">
                            <option value="">Select Role</option>
                            <option value="admin">Admin</option>
                            <option value="hr">HR</option>
                            <option value="manager">Manager</option>
                            <option value="employee">Employee</option>
                        </select>
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="roleError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Role is required
                        </span>
                    </div>

                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="employment_type">Employment Type</label>
                        <select name="employment_type" id="employment_type"
                                class="bg-gray-50 border border-gray-200 rounded-lg
                                       focus:ring-2 focus:ring-green-500 focus:border-green-500
                                       block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                                required aria-describedby="employmentTypeError">
                            <option value="">Select Type</option>
                            <option value="full-time">Full-Time</option>
                            <option value="part-time">Part-Time</option>
                            <option value="contract">Contract</option>
                        </select>
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="employmentTypeError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Employment Type is required
                        </span>
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="hire_date">Hire Date</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="hire_date" id="hire_date" required
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr"
                               placeholder="Select date" aria-describedby="hireDateError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="hireDateError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Hire Date is required
                        </span>
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="contract_end_date">Contract End Date</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="contract_end_date" id="contract_end_date"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr"
                               placeholder="Select date" aria-describedby="contractEndDateError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="contractEndDateError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Invalid date format
                        </span>
                    </div>
                </div>

                <div class="space-y-6">
                    <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Banking & Compliance</h4>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="base_salary">Base Salary (TZS)</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-money-bill text-gray-400 text-base"></i>
                        </div>
                        <input type="number" name="base_salary" id="base_salary" required
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="5000000" aria-describedby="baseSalaryError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="baseSalaryError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Base Salary is required
                        </span>
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="allowances">Allowances (TZS)</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-money-bill text-gray-400 text-base"></i>
                        </div>
                        <input type="number" name="allowances" id="allowances"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="1000000">
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="deductions">Deductions (TZS)</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-money-bill text-gray-400 text-base"></i>
                        </div>
                        <input type="number" name="deductions" id="deductions"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="500000">
                    </div>

                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="bank_name">Bank Name</label>
                        <select name="bank_name" id="bank_name"
                                class="bg-gray-50 border border-gray-200 rounded-lg
                                       focus:ring-2 focus:ring-green-500 focus:border-green-500
                                       block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                            <option value="">Select Bank (Optional)</option>
                            @if(isset($banks) && $banks->isNotEmpty())
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                @endforeach
                            @else
                                <option value="CRDB Bank">CRDB Bank</option>
                                <option value="NMB Bank">NMB Bank</option>
                                <option value="Standard Chartered">Standard Chartered</option>
                                <option value="Stanbic Bank">Stanbic Bank</option>
                                <option value="NBC Bank">NBC Bank</option>
                            @endif
                        </select>
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="account_number">Account Number</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-university text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="account_number" id="account_number"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="0152286559700">
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="nssf_number">NSSF Number</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-id-card text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="nssf_number" id="nssf_number"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="NSSF123456">
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="nhif_number">NHIF Number</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-id-card text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="nhif_number" id="nhif_number"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="NHIF123456">
                    </div>

                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="tin_number">TIN Number</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-id-card text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="tin_number" id="tin_number"
                               class="pl-10 bg-gray-50 border border-gray-200 rounded-lg
                                      focus:ring-2 focus:ring-green-500 focus:border-green-500
                                      block w-full py-2.5 px-3 leading-6 transition-all duration-200"
                               placeholder="TIN123456789">
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6">
                <button type="button"
                        class="text-white bg-gradient-to-r from-gray-500 to-gray-600
                               hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300
                               font-medium rounded-lg text-sm px-5 py-2.5 text-center
                               transition-all duration-200 flex items-center"
                        onclick="toggleTab('allEmployeesTab')">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button type="submit"
                        class="text-white bg-gradient-to-r from-green-600 to-green-700
                               hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300
                               font-medium rounded-lg text-sm px-5 py-2.5 text-center
                               transition-all duration-200 flex items-center">
                    <i class="fas fa-check mr-2"></i> Add Employee
                </button>
            </div>
        </form>
    </div>
</div>



    <!-- Bulk Import Modal -->
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
                <form action="{{ route('employees.bulk-import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="csv_file">Upload CSV File</label>
                        <input type="file" name="csv_file" id="csv_file" accept=".csv" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" aria-describedby="csvFileError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="csvFileError"><i class="fas fa-exclamation-circle mr-1"></i> CSV file is required</span>
                        <p class="text-gray-500 text-sm mt-2">The CSV should have columns: name, employee_id, email, department, position, role, employment_type, hire_date, contract_end_date, base_salary, allowances, deductions, bank_name, account_number, nssf_number, nhif_number, tin_number, phone, gender, dob, nationality, address, status</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="closeModal('bulkImportModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-upload mr-2"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-4xl transform transition-all duration-300 scale-95 modal-content">
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
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <form id="editEmployeeForm" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Personal Information -->
                        <div class="space-y-6">
                            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Personal Information</h4>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_name">Full Name</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_name" name="name" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required aria-describedby="editNameError">
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editNameError"><i class="fas fa-exclamation-circle mr-1"></i> Full Name is required</span>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_email">Email Address</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-envelope text-gray-400"></i>
                                </div>
                                <input type="email" id="edit_email" name="email" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required aria-describedby="editEmailError">
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editEmailError"><i class="fas fa-exclamation-circle mr-1"></i> Valid email is required</span>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_phone">Phone Number</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-phone text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_phone" name="phone" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_gender">Gender</label>
                                <select id="edit_gender" name="gender" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" aria-describedby="editGenderError">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editGenderError"><i class="fas fa-exclamation-circle mr-1"></i> Gender is required</span>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_dob">Date of Birth</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_dob" name="dob" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200 flatpickr" placeholder="Select date" aria-describedby="editDobError">
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editDobError"><i class="fas fa-exclamation-circle mr-1"></i> Invalid date format</span>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_nationality">Nationality</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-globe text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_nationality" name="nationality" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_address">Address</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-map-marker-alt text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_address" name="address" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                            </div>
                        </div>
                        <!-- Employment Details -->
                        <div class="space-y-6">
                            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Employment Details</h4>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_employee_id">Employee ID</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-id-badge text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_employee_id" name="employee_id" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required aria-describedby="editEmployeeIdError">
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editEmployeeIdError"><i class="fas fa-exclamation-circle mr-1"></i> Employee ID is required</span>
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_department">Department</label>
                                <select id="edit_department" name="department" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required aria-describedby="editDepartmentError">
                                    <option value="">Select Department</option>
                                    <!-- Populated dynamically via JavaScript -->
                                </select>
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editDepartmentError"><i class="fas fa-exclamation-circle mr-1"></i> Department is required</span>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_position">Position</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-briefcase text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_position" name="position" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required aria-describedby="editPositionError">
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editPositionError"><i class="fas fa-exclamation-circle mr-1"></i> Position is required</span>
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_role">Role</label>
                                <select id="edit_role" name="role" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required aria-describedby="editRoleError">
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="hr">HR</option>
                                    <option value="manager">Manager</option>
                                    <option value="employee">Employee</option>
                                </select>
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editRoleError"><i class="fas fa-exclamation-circle mr-1"></i> Role is required</span>
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_employment_type">Employment Type</label>
                                <select id="edit_employment_type" name="employment_type" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required aria-describedby="editEmploymentTypeError">
                                    <option value="">Select Type</option>
                                    <option value="full-time">Full-Time</option>
                                    <option value="part-time">Part-Time</option>
                                    <option value="contract">Contract</option>
                                </select>
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editEmploymentTypeError"><i class="fas fa-exclamation-circle mr-1"></i> Employment Type is required</span>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_hire_date">Hire Date</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_hire_date" name="hire_date" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200 flatpickr" required aria-describedby="editHireDateError">
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editHireDateError"><i class="fas fa-exclamation-circle mr-1"></i> Hire Date is required</span>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_contract_end_date">Contract End Date</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_contract_end_date" name="contract_end_date" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200 flatpickr" aria-describedby="editContractEndDateError">
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editContractEndDateError"><i class="fas fa-exclamation-circle mr-1"></i> Invalid date format</span>
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_status">Status</label>
                                <select id="edit_status" name="status" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required aria-describedby="editStatusError">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editStatusError"><i class="fas fa-exclamation-circle mr-1"></i> Status is required</span>
                            </div>
                        </div>
                        <!-- Banking & Compliance -->
                        <div class="space-y-6">
                            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Banking & Compliance</h4>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_base_salary">Base Salary (TZS)</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-money-bill text-gray-400"></i>
                                </div>
                                <input type="number" id="edit_base_salary" name="base_salary" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required aria-describedby="editBaseSalaryError">
                                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="editBaseSalaryError"><i class="fas fa-exclamation-circle mr-1"></i> Base Salary is required</span>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_allowances">Allowances (TZS)</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-money-bill text-gray-400"></i>
                                </div>
                                <input type="number" id="edit_allowances" name="allowances" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_deductions">Deductions (TZS)</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-money-bill text-gray-400"></i>
                                </div>
                                <input type="number" id="edit_deductions" name="deductions" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_bank_name">Bank Name</label>
                                <select id="edit_bank_name" name="bank_name" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                                    <option value="">Select Bank (Optional)</option>
                                    <!-- Populated dynamically via JavaScript -->
                                </select>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_account_number">Account Number</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-university text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_account_number" name="account_number" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_nssf_number">NSSF Number</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_nssf_number" name="nssf_number" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_nhif_number">NHIF Number</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_nhif_number" name="nhif_number" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_tin_number">TIN Number</label>
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-id-card text-gray-400"></i>
                                </div>
                                <input type="text" id="edit_tin_number" name="tin_number" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="closeModal('editEmployeeModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-check mr-2"></i> Update Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-lg w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-green-50 border-b border-green-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-red-600 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-2"></i> Confirm Deletion
                    </h3>
                    <button type="button" onclick="closeModal('deleteEmployeeModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200" aria-label="Close delete confirmation modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="text-center">
                    <i class="fas fa-exclamation-circle text-red-500 text-4xl mb-4"></i>
                    <p class="text-gray-700 text-lg font-medium mb-2">Are you sure you want to delete this employee?</p>
                    <p class="text-gray-500 text-sm">This action cannot be undone. All associated data will be permanently removed.</p>
                </div>
                <div class="flex justify-center space-x-3 mt-6">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="closeModal('deleteEmployeeModal')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button id="confirmDeleteButton" class="text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <i class="fas fa-trash mr-2"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css">
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>

    <script>
        let currentDeleteId = null;

        // Initialize Flatpickr for date inputs
        document.addEventListener('DOMContentLoaded', function() {
            flatpickr('.flatpickr', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                altInput: true,
                altFormat: 'F j, Y', // Display format: e.g., September 10, 2025
                maxDate: ['dob', 'edit_dob'].includes(this.id) ? 'today' : null, // Restrict DOB to past/present
                wrap: false,
                onReady: function(selectedDates, dateStr, instance) {
                    instance.element.style.cursor = 'pointer';
                }
            });

            // Tab navigation
            document.getElementById('allEmployeesTab').addEventListener('click', () => toggleTab('allEmployeesTab'));
            document.getElementById('addEmployeeTab').addEventListener('click', () => toggleTab('addEmployeeTab'));

            // Form validation for Add Employee
            document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
                let valid = true;
                const fields = [
                    { id: 'name', errorId: 'nameError', message: 'Full Name is required' },
                    { id: 'employee_id', errorId: 'employeeIdError', message: 'Employee ID is required' },
                    { id: 'email', errorId: 'emailError', message: 'Valid email is required' },
                    { id: 'department', errorId: 'departmentError', message: 'Department is required' },
                    { id: 'position', errorId: 'positionError', message: 'Position is required' },
                    { id: 'role', errorId: 'roleError', message: 'Role is required' },
                    { id: 'base_salary', errorId: 'baseSalaryError', message: 'Base Salary is required' },
                    { id: 'employment_type', errorId: 'employmentTypeError', message: 'Employment Type is required' },
                    { id: 'hire_date', errorId: 'hireDateError', message: 'Hire Date is required' }
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

                const email = document.getElementById('email');
                const emailError = document.getElementById('emailError');
                if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                    emailError.classList.remove('hidden');
                    valid = false;
                } else {
                    emailError.classList.add('hidden');
                }

                const dob = document.getElementById('dob');
                const dobError = document.getElementById('dobError');
                if (dob.value && !/^\d{4}-\d{2}-\d{2}$/.test(dob.value)) {
                    dobError.classList.remove('hidden');
                    valid = false;
                } else {
                    dobError.classList.add('hidden');
                }

                const contractEndDate = document.getElementById('contract_end_date');
                const contractEndDateError = document.getElementById('contractEndDateError');
                if (contractEndDate.value && !/^\d{4}-\d{2}-\d{2}$/.test(contractEndDate.value)) {
                    contractEndDateError.classList.remove('hidden');
                    valid = false;
                } else {
                    contractEndDateError.classList.add('hidden');
                }

                if (!valid) {
                    e.preventDefault();
                }
            });

            // Form validation for Edit Employee
            document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
                let valid = true;
                const fields = [
                    { id: 'edit_name', errorId: 'editNameError', message: 'Full Name is required' },
                    { id: 'edit_employee_id', errorId: 'editEmployeeIdError', message: 'Employee ID is required' },
                    { id: 'edit_email', errorId: 'editEmailError', message: 'Valid email is required' },
                    { id: 'edit_department', errorId: 'editDepartmentError', message: 'Department is required' },
                    { id: 'edit_position', errorId: 'editPositionError', message: 'Position is required' },
                    { id: 'edit_role', errorId: 'editRoleError', message: 'Role is required' },
                    { id: 'edit_base_salary', errorId: 'editBaseSalaryError', message: 'Base Salary is required' },
                    { id: 'edit_employment_type', errorId: 'editEmploymentTypeError', message: 'Employment Type is required' },
                    { id: 'edit_hire_date', errorId: 'editHireDateError', message: 'Hire Date is required' },
                    { id: 'edit_status', errorId: 'editStatusError', message: 'Status is required' }
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

                const email = document.getElementById('edit_email');
                const emailError = document.getElementById('editEmailError');
                if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                    emailError.classList.remove('hidden');
                    valid = false;
                } else {
                    emailError.classList.add('hidden');
                }

                const dob = document.getElementById('edit_dob');
                const dobError = document.getElementById('editDobError');
                if (dob.value && !/^\d{4}-\d{2}-\d{2}$/.test(dob.value)) {
                    dobError.classList.remove('hidden');
                    valid = false;
                } else {
                    dobError.classList.add('hidden');
                }

                const contractEndDate = document.getElementById('edit_contract_end_date');
                const contractEndDateError = document.getElementById('editContractEndDateError');
                if (contractEndDate.value && !/^\d{4}-\d{2}-\d{2}$/.test(contractEndDate.value)) {
                    contractEndDateError.classList.remove('hidden');
                    valid = false;
                } else {
                    contractEndDateError.classList.add('hidden');
                }

                if (!valid) {
                    e.preventDefault();
                }
            });

            // Delete button
            const confirmDeleteButton = document.getElementById('confirmDeleteButton');
            if (confirmDeleteButton) {
                confirmDeleteButton.addEventListener('click', function() {
                    if (currentDeleteId) {
                        deleteEmployee(currentDeleteId);
                    }
                });
            }

            // Search functionality
            const searchInput = document.getElementById('searchEmployee');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.employee-row');
                    rows.forEach(row => {
                        const name = row.dataset.name || '';
                        const id = row.dataset.id || '';
                        const matches = name.includes(searchValue) || id.includes(searchValue);
                        row.style.display = matches ? '' : 'none';
                    });
                });
            }

            // Reset form on cancel or tab switch
            document.querySelectorAll('#addEmployeeForm button[type="button"]').forEach(button => {
                button.addEventListener('click', () => {
                    document.getElementById('addEmployeeForm').reset();
                    document.querySelectorAll('#addEmployeeForm .text-red-500').forEach(error => error.classList.add('hidden'));
                    document.querySelectorAll('#addEmployeeForm .flatpickr').forEach(input => {
                        if (input._flatpickr) input._flatpickr.clear();
                    });
                });
            });

            document.querySelectorAll('#editEmployeeModal button[onclick*="closeModal"]').forEach(button => {
                button.addEventListener('click', () => {
                    document.getElementById('editEmployeeForm').reset();
                    document.querySelectorAll('#editEmployeeForm .text-red-500').forEach(error => error.classList.add('hidden'));
                    document.querySelectorAll('#editEmployeeForm .flatpickr').forEach(input => {
                        if (input._flatpickr) input._flatpickr.clear();
                    });
                });
            });

            // Reset Add Employee form when switching to the tab
            document.getElementById('addEmployeeTab').addEventListener('click', () => {
                document.getElementById('addEmployeeForm').reset();
                document.querySelectorAll('#addEmployeeForm .text-red-500').forEach(error => error.classList.add('hidden'));
                document.querySelectorAll('#addEmployeeForm .flatpickr').forEach(input => {
                    if (input._flatpickr) input._flatpickr.clear();
                });
            });
        });

        function toggleTab(tabId) {
            const tabs = ['allEmployeesTab', 'addEmployeeTab'];
            const containers = ['employeesTableContainer', 'addEmployeeFormContainer'];

            tabs.forEach(id => {
                const tab = document.getElementById(id);
                tab.classList.remove('bg-green-600', 'text-white');
                tab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                tab.setAttribute('aria-selected', 'false');
            });

            containers.forEach(id => {
                document.getElementById(id).classList.add('hidden');
            });

            const activeTab = document.getElementById(tabId);
            activeTab.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            activeTab.classList.add('bg-green-600', 'text-white');
            activeTab.setAttribute('aria-selected', 'true');

            const containerId = tabId === 'allEmployeesTab' ? 'employeesTableContainer' : 'addEmployeeFormContainer';
            document.getElementById(containerId).classList.remove('hidden');
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

        function editEmployee(id) {
            fetch(`/dashboard/employee/${id}/edit`)
                .then(res => {
                    if (!res.ok) throw new Error('Failed to fetch employee data');
                    return res.json();
                })
                .then(data => {
                    ['name', 'employee_id', 'email', 'department', 'position', 'role', 'employment_type', 'hire_date', 'contract_end_date', 'base_salary', 'allowances', 'deductions', 'bank_name', 'account_number', 'nssf_number', 'nhif_number', 'tin_number', 'phone', 'gender', 'dob', 'nationality', 'address', 'status'].forEach(f => {
                        const element = document.getElementById(`edit_${f}`);
                        if (element) {
                            element.value = data[f] || '';
                        }
                    });
                    document.getElementById('editEmployeeForm').action = `/dashboard/employee/${id}`;
                    openModal('editEmployeeModal');
                })
                .catch(error => {
                    console.error('Error fetching employee:', error);
                    alert('Failed to load employee data. Please try again.');
                });
        }

        function openDeleteModal(id) {
            currentDeleteId = id;
            openModal('deleteEmployeeModal');
        }

        function deleteEmployee(id) {
            fetch(`/dashboard/employee/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(res => {
                    if (!res.ok) throw new Error('Failed to delete employee');
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById(`employee-${id}`).remove();
                        closeModal('deleteEmployeeModal');
                    }
                })
                .catch(error => {
                    console.error('Error deleting employee:', error);
                    alert('Failed to delete employee. Please try again.');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Tab navigation
            document.getElementById('allEmployeesTab').addEventListener('click', () => toggleTab('allEmployeesTab'));
            document.getElementById('addEmployeeTab').addEventListener('click', () => toggleTab('addEmployeeTab'));

            // Form validation for Add Employee
            document.getElementById('addEmployeeForm').addEventListener('submit', function(e) {
                let valid = true;
                const fields = [
                    { id: 'name', errorId: 'nameError', message: 'Full Name is required' },
                    { id: 'employee_id', errorId: 'employeeIdError', message: 'Employee ID is required' },
                    { id: 'email', errorId: 'emailError', message: 'Valid email is required' },
                    { id: 'department', errorId: 'departmentError', message: 'Department is required' },
                    { id: 'position', errorId: 'positionError', message: 'Position is required' },
                    { id: 'role', errorId: 'roleError', message: 'Role is required' },
                    { id: 'base_salary', errorId: 'baseSalaryError', message: 'Base Salary is required' },
                    { id: 'employment_type', errorId: 'employmentTypeError', message: 'Employment Type is required' },
                    { id: 'hire_date', errorId: 'hireDateError', message: 'Hire Date is required' }
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

                const email = document.getElementById('email');
                const emailError = document.getElementById('emailError');
                if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                    emailError.classList.remove('hidden');
                    valid = false;
                } else {
                    emailError.classList.add('hidden');
                }

                const dob = document.getElementById('dob');
                const dobError = document.getElementById('dobError');
                if (dob.value && !/^\d{4}-\d{2}-\d{2}$/.test(dob.value)) {
                    dobError.classList.remove('hidden');
                    valid = false;
                } else {
                    dobError.classList.add('hidden');
                }

                const contractEndDate = document.getElementById('contract_end_date');
                const contractEndDateError = document.getElementById('contractEndDateError');
                if (contractEndDate.value && !/^\d{4}-\d{2}-\d{2}$/.test(contractEndDate.value)) {
                    contractEndDateError.classList.remove('hidden');
                    valid = false;
                } else {
                    contractEndDateError.classList.add('hidden');
                }

                if (!valid) {
                    e.preventDefault();
                }
            });

            // Form validation for Edit Employee
            document.getElementById('editEmployeeForm').addEventListener('submit', function(e) {
                let valid = true;
                const fields = [
                    { id: 'edit_name', errorId: 'editNameError', message: 'Full Name is required' },
                    { id: 'edit_employee_id', errorId: 'editEmployeeIdError', message: 'Employee ID is required' },
                    { id: 'edit_email', errorId: 'editEmailError', message: 'Valid email is required' },
                    { id: 'edit_department', errorId: 'editDepartmentError', message: 'Department is required' },
                    { id: 'edit_position', errorId: 'editPositionError', message: 'Position is required' },
                    { id: 'edit_role', errorId: 'editRoleError', message: 'Role is required' },
                    { id: 'edit_base_salary', errorId: 'editBaseSalaryError', message: 'Base Salary is required' },
                    { id: 'edit_employment_type', errorId: 'editEmploymentTypeError', message: 'Employment Type is required' },
                    { id: 'edit_hire_date', errorId: 'editHireDateError', message: 'Hire Date is required' },
                    { id: 'edit_status', errorId: 'editStatusError', message: 'Status is required' }
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

                const email = document.getElementById('edit_email');
                const emailError = document.getElementById('editEmailError');
                if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                    emailError.classList.remove('hidden');
                    valid = false;
                } else {
                    emailError.classList.add('hidden');
                }

                const dob = document.getElementById('edit_dob');
                const dobError = document.getElementById('editDobError');
                if (dob.value && !/^\d{4}-\d{2}-\d{2}$/.test(dob.value)) {
                    dobError.classList.remove('hidden');
                    valid = false;
                } else {
                    dobError.classList.add('hidden');
                }

                const contractEndDate = document.getElementById('edit_contract_end_date');
                const contractEndDateError = document.getElementById('editContractEndDateError');
                if (contractEndDate.value && !/^\d{4}-\d{2}-\d{2}$/.test(contractEndDate.value)) {
                    contractEndDateError.classList.remove('hidden');
                    valid = false;
                } else {
                    contractEndDateError.classList.add('hidden');
                }

                if (!valid) {
                    e.preventDefault();
                }
            });

            // Delete button
            const confirmDeleteButton = document.getElementById('confirmDeleteButton');
            if (confirmDeleteButton) {
                confirmDeleteButton.addEventListener('click', function() {
                    if (currentDeleteId) {
                        deleteEmployee(currentDeleteId);
                    }
                });
            }

            // Search functionality
            const searchInput = document.getElementById('searchEmployee');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.employee-row');
                    rows.forEach(row => {
                        const name = row.dataset.name || '';
                        const id = row.dataset.id || '';
                        const matches = name.includes(searchValue) || id.includes(searchValue);
                        row.style.display = matches ? '' : 'none';
                    });
                });
            }

            // Make date inputs readonly but allow picker
            ['hire_date', 'edit_hire_date', 'dob', 'edit_dob', 'contract_end_date', 'edit_contract_end_date'].forEach(id => {
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
    </script>
@endsection