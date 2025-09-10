@extends('layout.global')
@section('title', 'Employees')
@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">Employees</span>
        <span class="payroll-badge inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-bolt mr-1.5"></i> Premium Plan
        </span>
    </div>
@endsection
@section('header-subtitle')
    <span class="text-gray-600">Manage employee records and details for {{ $currentPeriod }}.</span>
@endsection
@section('content')
<!-- Success Message -->
@if(session('success'))
<div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
    <span class="block sm:inline">{{ session('success') }}</span>
</div>
@endif

<!-- Search Input (shadcn style) -->
<div class="mb-6 relative">
    <div class="relative max-w-md">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input id="searchEmployee" type="text" placeholder="Search by name or ID..." class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-150 bg-white shadow-sm text-gray-900 placeholder-gray-500">
    </div>
</div>

<!-- Employee Table Header -->
<div class="flex justify-between items-center mb-4">
    <h3 class="text-lg font-medium text-gray-700 flex items-center">
        <i class="fas fa-users text-green-500 mr-2"></i> All Employees
        <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $employees->total() }} employees</span>
    </h3>
    <div class="flex space-x-2">
        <button class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-200 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md" onclick="openModal('addEmployeeModal')">
            <i class="fas fa-user-plus mr-2"></i> Add Employee
        </button>
        <button class="text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 focus:ring-4 focus:ring-green-100 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md" onclick="openModal('bulkImportModal')">
            <i class="fas fa-upload mr-2"></i> Bulk Import
        </button>
    </div>
</div>

<!-- Table Container -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 text-sm">
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
                @php
                    $deptColors = [
                        'IT' => 'bg-purple-100 text-purple-800',
                        'HR' => 'bg-blue-100 text-blue-800',
                        'Finance' => 'bg-green-100 text-green-800',
                        'Marketing' => 'bg-pink-100 text-pink-800',
                        'Teaching' => 'bg-yellow-100 text-yellow-800'
                    ];
                    $deptColor = $deptColors[$employee->department] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <tr id="employee-{{ $employee->id }}" class="bg-white hover:bg-gray-50/50 transition duration-150 employee-row group" data-name="{{ strtolower($employee->name) }}" data-id="{{ strtolower($employee->employee_id) }}">
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
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $deptColor }}">
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
                                <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></span> Inactive
                            </span>
                        @endif
                    </td>
                    <td class="py-4 px-6">
                        <div class="flex items-center space-x-2">
                            <button onclick="editEmployee({{ $employee->id }})" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition duration-150" title="Edit">
                                <i class="fas fa-edit text-sm"></i>
                            </button>
                            <button onclick="openDeleteModal({{ $employee->id }})" class="text-red-600 hover:text-red-800 p-1.5 rounded-md hover:bg-red-50 transition duration-150" title="Delete">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                            <button class="text-gray-400 hover:text-gray-600 p-1.5 rounded-md hover:bg-gray-100 transition duration-150" title="View Details">
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
        <button class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-200 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="openModal('addEmployeeModal')">
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
        <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Previous</span>
        @else
        <a href="{{ $employees->previousPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">Previous</a>
        @endif
        
        @if($employees->hasMorePages())
        <a href="{{ $employees->nextPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-gray-600 text-sm hover:bg-green-600  hover:text-white">Next</a>
        @else
        <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Next</span>
        @endif
    </div>
</div>
@endif

<!-- Add Employee Modal -->
<div id="addEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
        <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-user-plus mr-2"></i> Add New Employee
                </h3>
                <button type="button" onclick="closeModal('addEmployeeModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="p-6">
            <form action="{{ route('employees.store') }}" method="POST" class="space-y-4" id="addEmployeeForm">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-gray-600 text-sm font-medium mb-2">Full Name</label>
                        <input type="text" name="name" id="name" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="John Doe">
                        <span class="text-red-500 text-sm hidden" id="nameError">Full Name is required</span>
                    </div>
                    <div>
                        <label for="employee_id" class="block text-gray-600 text-sm font-medium mb-2">Employee ID</label>
                        <input type="text" name="employee_id" id="employee_id" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="EMP-001">
                        <span class="text-red-500 text-sm hidden" id="employeeIdError">Employee ID is required</span>
                    </div>
                    <div>
                        <label for="email" class="block text-gray-600 text-sm font-medium mb-2">Email Address</label>
                        <input type="email" name="email" id="email" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="john@company.com">
                        <span class="text-red-500 text-sm hidden" id="emailError">Valid email is required</span>
                    </div>
                    <div>
                        <label for="department" class="block text-gray-600 text-sm font-medium mb-2">Department</label>
                        <select name="department" id="department" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <option value="">Select Department</option>
                            <option value="IT">Information Technology</option>
                            <option value="HR">Human Resources</option>
                            <option value="Finance">Finance</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Teaching">Teaching</option>
                        </select>
                        <span class="text-red-500 text-sm hidden" id="departmentError">Department is required</span>
                    </div>
                    <div>
                        <label for="position" class="block text-gray-600 text-sm font-medium mb-2">Position</label>
                        <input type="text" name="position" id="position" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Software Developer">
                        <span class="text-red-500 text-sm hidden" id="positionError">Position is required</span>
                    </div>
                    <div>
                        <label for="base_salary" class="block text-gray-600 text-sm font-medium mb-2">Base Salary (TZS)</label>
                        <input type="number" name="base_salary" id="base_salary" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="570000">
                        <span class="text-red-500 text-sm hidden" id="baseSalaryError">Base Salary is required</span>
                    </div>
                    <div>
                        <label for="allowances" class="block text-gray-600 text-sm font-medium mb-2">Allowances (TZS)</label>
                        <input type="number" name="allowances" id="allowances" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="130000">
                    </div>
                    <div>
                        <label for="bank_name" class="block text-gray-600 text-sm font-medium mb-2">Bank Name</label>
                        <select name="bank_name" id="bank_name" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="">Select Bank</option>
                            <option value="Absa Bank">Absa Bank</option>
                            <option value="Access Bank">Access Bank</option>
                            <option value="Akiba Commercial Bank (ACB)">Akiba Commercial Bank (ACB)</option>
                            <option value="Amana Bank">Amana Bank</option>
                            <option value="Azania Bank">Azania Bank</option>
                            <option value="Bank of Africa (BOA)">Bank of Africa (BOA)</option>
                            <option value="Bank of Baroda">Bank of Baroda</option>
                            <option value="Bank of India">Bank of India</option>
                            <option value="China Dasheng Bank">China Dasheng Bank</option>
                            <option value="Citibank">Citibank</option>
                            <option value="CRDB Bank">CRDB Bank</option>
                            <option value="DCB Commercial Bank">DCB Commercial Bank</option>
                            <option value="Diamond Trust Bank (DTB)">Diamond Trust Bank (DTB)</option>
                            <option value="Ecobank">Ecobank</option>
                            <option value="Equity Bank">Equity Bank</option>
                            <option value="Exim Bank">Exim Bank</option>
                            <option value="Guaranty Trust Bank (GTBank)">Guaranty Trust Bank (GTBank)</option>
                            <option value="Habib African Bank">Habib African Bank</option>
                            <option value="I&M Bank">I&M Bank</option>
                            <option value="International Commercial Bank (ICB)">International Commercial Bank (ICB)</option>
                            <option value="KCB Bank">KCB Bank</option>
                            <option value="Letshego Faidika Bank">Letshego Faidika Bank</option>
                            <option value="Maendeleo Bank">Maendeleo Bank</option>
                            <option value="Mkombozi Commercial Bank (MKCB)">Mkombozi Commercial Bank (MKCB)</option>
                            <option value="Mwalimu Commercial Bank (MCB)">Mwalimu Commercial Bank (MCB)</option>
                            <option value="Mwanga Hakika Bank">Mwanga Hakika Bank</option>
                            <option value="National Bank of Commerce (NBC)">National Bank of Commerce (NBC)</option>
                            <option value="NCBA">NCBA</option>
                            <option value="National Microfinance Bank (NMB)">National Microfinance Bank (NMB)</option>
                            <option value="Peoples' Bank of Zanzibar">Peoples' Bank of Zanzibar</option>
                            <option value="Stanbic Bank">Stanbic Bank</option>
                            <option value="Standard Chartered Bank">Standard Chartered Bank</option>
                            <option value="Tanzania Commercial Bank (TCB)">Tanzania Commercial Bank (TCB)</option>
                            <option value="United Bank for Africa (UBA)">United Bank for Africa (UBA)</option>
                            <option value="Co-operative Bank of Tanzania (CBT)">Co-operative Bank of Tanzania (CBT)</option>
                            <option value="Mufindi Community Bank (MuCoBa)">Mufindi Community Bank (MuCoBa)</option>
                            <option value="Uchumi Commercial Bank">Uchumi Commercial Bank</option>
                            <option value="Finca Microfinance Bank">Finca Microfinance Bank</option>
                            <option value="Selcom Microfinance Bank">Selcom Microfinance Bank</option>
                            <option value="VisionFund Tanzania Microfinance Bank">VisionFund Tanzania Microfinance Bank</option>
                            <option value="Yetu Microfinance Bank">Yetu Microfinance Bank</option>
                            <option value="TIB Development Bank">TIB Development Bank</option>
                            <option value="Tanzania Agricultural Development Bank (TADB)">Tanzania Agricultural Development Bank (TADB)</option>
                            <option value="First Housing Company Tanzania">First Housing Company Tanzania</option>
                            <option value="Tanzania Mortgage Refinance Company (TMRC)">Tanzania Mortgage Refinance Company (TMRC)</option>
                        </select>
                    </div>
                    <div>
                        <label for="account_number" class="block text-gray-600 text-sm font-medium mb-2">Account Number</label>
                        <input type="text" name="account_number" id="account_number" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="0152286559700">
                    </div>
                    <div>
                        <label for="hire_date" class="block text-gray-600 text-sm font-medium mb-2">Hire Date</label>
                        <input type="date" name="hire_date" id="hire_date" required class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        <span class="text-red-500 text-sm hidden" id="hireDateError">Hire Date is required</span>
                    </div>
                    <div>
                        <label for="status" class="block text-gray-600 text-sm font-medium mb-2">Status</label>
                        <select name="status" id="status" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-4">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('addEmployeeModal')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                        <i class="fas fa-save mr-2"></i> Add Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Import Modal -->
<div id="bulkImportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-md w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
        <div class="p-6 bg-green-50 border-b border-green-200">
            <h3 class="text-xl font-semibold text-green-700 flex items-center">
                <i class="fas fa-upload mr-2"></i> Bulk Import Employees
            </h3>
        </div>
        <div class="p-6">
            <form action="{{ route('employees.bulk-import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Upload CSV File</label>
                    <input type="file" name="csv_file" accept=".csv" required class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                    <span class="text-red-500 text-sm hidden" id="csvFileError">CSV file is required</span>
                    <p class="text-gray-500 text-sm mt-2">The CSV should have columns: name, employee_id, email, department, position, base_salary, allowances, bank_name, account_number, hire_date, status</p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="text-gray-700 bg-gray-100 hover:bg-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200" onclick="closeModal('bulkImportModal')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-200 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200">
                        <i class="fas fa-upload mr-2"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div id="editEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-md w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
        <div class="p-6 bg-green-50 border-b border-green-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-green-700 flex items-center">
                    <i class="fas fa-user-edit mr-2"></i> Edit Employee
                </h3>
                <button type="button" onclick="closeModal('editEmployeeModal')" class="text-gray-500 hover:text-gray-700 transition duration-150">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="p-6">
            <form id="editEmployeeForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Full Name</label>
                    <input type="text" id="edit_name" name="name" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    <span class="text-red-500 text-sm hidden" id="editNameError">Full Name is required</span>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Employee ID</label>
                    <input type="text" id="edit_employee_id" name="employee_id" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    <span class="text-red-500 text-sm hidden" id="editEmployeeIdError">Employee ID is required</span>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Email</label>
                    <input type="email" id="edit_email" name="email" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    <span class="text-red-500 text-sm hidden" id="editEmailError">Valid email is required</span>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Department</label>
                    <select id="edit_department" name="department" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                        <option value="">Select Department</option>
                        <option value="IT">Information Technology</option>
                        <option value="HR">Human Resources</option>
                        <option value="Finance">Finance</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Teaching">Teaching</option>
                    </select>
                    <span class="text-red-500 text-sm hidden" id="editDepartmentError">Department is required</span>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Position</label>
                    <input type="text" id="edit_position" name="position" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    <span class="text-red-500 text-sm hidden" id="editPositionError">Position is required</span>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Base Salary (TZS)</label>
                    <input type="number" id="edit_base_salary" name="base_salary" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    <span class="text-red-500 text-sm hidden" id="editBaseSalaryError">Base Salary is required</span>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Allowances (TZS)</label>
                    <input type="number" id="edit_allowances" name="allowances" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Bank Name</label>
                    <select id="edit_bank_name" name="bank_name" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        <option value="">Select Bank</option>
                        <option value="Absa Bank">Absa Bank</option>
                        <option value="Access Bank">Access Bank</option>
                        <option value="Akiba Commercial Bank (ACB)">Akiba Commercial Bank (ACB)</option>
                        <option value="Amana Bank">Amana Bank</option>
                        <option value="Azania Bank">Azania Bank</option>
                        <option value="Bank of Africa (BOA)">Bank of Africa (BOA)</option>
                        <option value="Bank of Baroda">Bank of Baroda</option>
                        <option value="Bank of India">Bank of India</option>
                        <option value="China Dasheng Bank">China Dasheng Bank</option>
                        <option value="Citibank">Citibank</option>
                        <option value="CRDB Bank">CRDB Bank</option>
                        <option value="DCB Commercial Bank">DCB Commercial Bank</option>
                        <option value="Diamond Trust Bank (DTB)">Diamond Trust Bank (DTB)</option>
                        <option value="Ecobank">Ecobank</option>
                        <option value="Equity Bank">Equity Bank</option>
                        <option value="Exim Bank">Exim Bank</option>
                        <option value="Guaranty Trust Bank (GTBank)">Guaranty Trust Bank (GTBank)</option>
                        <option value="Habib African Bank">Habib African Bank</option>
                        <option value="I&M Bank">I&M Bank</option>
                        <option value="International Commercial Bank (ICB)">International Commercial Bank (ICB)</option>
                        <option value="KCB Bank">KCB Bank</option>
                        <option value="Letshego Faidika Bank">Letshego Faidika Bank</option>
                        <option value="Maendeleo Bank">Maendeleo Bank</option>
                        <option value="Mkombozi Commercial Bank (MKCB)">Mkombozi Commercial Bank (MKCB)</option>
                        <option value="Mwalimu Commercial Bank (MCB)">Mwalimu Commercial Bank (MCB)</option>
                        <option value="Mwanga Hakika Bank">Mwanga Hakika Bank</option>
                        <option value="National Bank of Commerce (NBC)">National Bank of Commerce (NBC)</option>
                        <option value="NCBA">NCBA</option>
                        <option value="National Microfinance Bank (NMB)">National Microfinance Bank (NMB)</option>
                        <option value="Peoples' Bank of Zanzibar">Peoples' Bank of Zanzibar</option>
                        <option value="Stanbic Bank">Stanbic Bank</option>
                        <option value="Standard Chartered Bank">Standard Chartered Bank</option>
                        <option value="Tanzania Commercial Bank (TCB)">Tanzania Commercial Bank (TCB)</option>
                        <option value="United Bank for Africa (UBA)">United Bank for Africa (UBA)</option>
                        <option value="Co-operative Bank of Tanzania (CBT)">Co-operative Bank of Tanzania (CBT)</option>
                        <option value="Mufindi Community Bank (MuCoBa)">Mufindi Community Bank (MuCoBa)</option>
                        <option value="Uchumi Commercial Bank">Uchumi Commercial Bank</option>
                        <option value="Finca Microfinance Bank">Finca Microfinance Bank</option>
                        <option value="Selcom Microfinance Bank">Selcom Microfinance Bank</option>
                        <option value="VisionFund Tanzania Microfinance Bank">VisionFund Tanzania Microfinance Bank</option>
                        <option value="Yetu Microfinance Bank">Yetu Microfinance Bank</option>
                        <option value="TIB Development Bank">TIB Development Bank</option>
                        <option value="Tanzania Agricultural Development Bank (TADB)">Tanzania Agricultural Development Bank (TADB)</option>
                        <option value="First Housing Company Tanzania">First Housing Company Tanzania</option>
                        <option value="Tanzania Mortgage Refinance Company (TMRC)">Tanzania Mortgage Refinance Company (TMRC)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Account Number</label>
                    <input type="text" id="edit_account_number" name="account_number" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Hire Date</label>
                    <input type="date" id="edit_hire_date" name="hire_date" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5" required>
                    <span class="text-red-500 text-sm hidden" id="editHireDateError">Hire Date is required</span>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-600 text-sm font-medium mb-2">Status</label>
                    <select id="edit_status" name="status" class="bg-gray-50 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="text-gray-700 bg-gray-100 hover:bg-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200" onclick="closeModal('editEmployeeModal')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-200 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200">
                        <i class="fas fa-save mr-2"></i> Update Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="bg-white rounded-md w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
        <div class="p-6 bg-green-50 border-b border-green-200">
            <div class="flex justify-between items-center">
                <h3 class="text-xl font-semibold text-red-600 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Confirm Deletion
                </h3>
                <button type="button" onclick="closeModal('deleteEmployeeModal')" class="text-gray-500 hover:text-gray-700 transition duration-150">
                    <i class="fas fa-times"></i>
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
                <button type="button" class="text-gray-700 bg-gray-100 hover:bg-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200" onclick="closeModal('deleteEmployeeModal')">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button id="confirmDeleteButton" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-200 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentDeleteId = null;

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
            ['name', 'employee_id', 'email', 'department', 'position', 'base_salary', 'allowances', 'bank_name', 'account_number', 'hire_date', 'status'].forEach(f => {
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
    ['addEmployeeForm', 'editEmployeeForm'].forEach(formId => {
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
                if (!valid) e.preventDefault();
            });
        }
    });

    const confirmDeleteButton = document.getElementById('confirmDeleteButton');
    if (confirmDeleteButton) {
        confirmDeleteButton.addEventListener('click', function() {
            if (currentDeleteId) {
                deleteEmployee(currentDeleteId);
            }
        });
    }

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
});
</script>
@endsection