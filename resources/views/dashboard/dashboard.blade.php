@extends('layout.global')

@section('title', 'Dashboard')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">Dashboard</span>
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">
            <i class="fas fa-bolt mr-1"></i> Premium Plan
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Manage employee records for {{ $currentPeriod }}</span>
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Total Employees</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $totalEmployees }}</p>
                    <p class="mt-1 text-sm text-gray-500 flex items-center">
                        <span class="text-green-600 mr-1"><i class="fas fa-arrow-up"></i></span>
                        <span>{{ $employeeGrowth }}% from last month</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Monthly Payroll</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">TZS {{ number_format($monthlyPayroll, 0) }}</p>
                    <p class="mt-1 text-sm text-gray-500 flex items-center">
                        <span class="text-green-600 mr-1"><i class="fas fa-arrow-up"></i></span>
                        <span>{{ $payrollGrowth }}% from last month</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-coins text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Payslips Generated</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-900">{{ $payslipsGenerated }}</p>
                    <p class="mt-1 text-sm text-gray-500">All employees processed</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                    <i class="fas fa-file-invoice text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        <div class="card bg-white rounded-xl border border-gray-200 shadow-sm p-6 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-700">Pending Tasks</p>
                    <p class="mt-1 text-3xl font-semibold text-yellow-600">{{ $pendingTasks }}</p>
                    <p class="mt-1 text-sm text-gray-500">To be completed</p>
                </div>
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center">
                    <i class="fas fa-tasks text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    @if (in_array(auth()->user()->role, ['admin', 'hr']))
        <div class="mb-8">
            <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer" onclick="openModal('addEmployeeModal')">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-user-plus text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Add Employee</h4>
                            <p class="text-sm text-gray-500">Register new employee</p>
                        </div>
                    </div>
                </div>
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer" onclick="openModal('runPayrollModal')">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-calculator text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Run Payroll</h4>
                            <p class="text-sm text-gray-500">Process salary payments</p>
                        </div>
                    </div>
                </div>
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer" onclick="openModal('generateReportModal')">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-file-pdf text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Generate Reports</h4>
                            <p class="text-sm text-gray-500">Payroll summaries</p>
                        </div>
                    </div>
                </div>
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer" onclick="openModal('addComplianceModal')">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-shield-alt text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Add Compliance Task</h4>
                            <p class="text-sm text-gray-500">Tax & statutory tasks</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-700">Payroll Overview</h3>
                <select id="chartPeriod" class="text-sm border border-gray-300 rounded-md px-3 py-1 bg-white focus:ring-green-500 focus:border-green-500">
                    <option value="6">Last 6 Months</option>
                    <option value="12">This Year</option>
                    <option value="24">Last Year</option>
                </select>
            </div>
            <div class="h-64">
                <canvas id="payrollChart"></canvas>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-700">Recent Payslips</h3>
                <a href="{{ route('payroll') }}" class="text-sm text-green-600 hover:text-green-800">View All</a>
            </div>
            <div class="space-y-4">
                @foreach($recentPayslips as $payslip)
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $payslip->employee->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">{{ $payslip->employee->department ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-green-600">TZS {{ number_format($payslip->net_salary ?? 0, 0) }}</p>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $payslip->status == 'Paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $payslip->status ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if (in_array(auth()->user()->role, ['admin', 'hr']))
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" id="addEmployeeModal">
            <div class="bg-white rounded-xl w-[97%] h-[80vh] transform transition-all duration-300 scale-95">
                <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-user-plus mr-2"></i> Add New Employee
                    </h3>
                </div>
                <div class="p-6 h-full overflow-y-auto">
                    <form id="addEmployeeForm" action="{{ route('employees.store') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Personal Information</h4>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="name">Full Name</label>
                                    <input type="text" id="name" name="name" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="John Doe" required>
                                    <span class="text-red-500 text-sm hidden" id="nameError">Full Name is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="email">Email Address</label>
                                    <input type="email" id="email" name="email" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="john@company.com" required>
                                    <span class="text-red-500 text-sm hidden" id="emailError">Valid email is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="phone">Phone Number</label>
                                    <input type="text" id="phone" name="phone" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="+255 123 456 789">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="gender">Gender</label>
                                    <select id="gender" name="gender" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="dob">Date of Birth</label>
                                    <input type="date" id="dob" name="dob" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" readonly>
                                    <span class="text-red-500 text-sm hidden" id="dobError">Invalid date format</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="nationality">Nationality</label>
                                    <input type="text" id="nationality" name="nationality" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="Tanzanian">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="address">Address</label>
                                    <input type="text" id="address" name="address" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="123 Main St, Dar es Salaam">
                                </div>
                            </div>
                            <!-- Employment Details -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Employment Details</h4>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="employee_id">Employee ID</label>
                                    <input type="text" id="employee_id" name="employee_id" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="EMP-001" required>
                                    <span class="text-red-500 text-sm hidden" id="employeeIdError">Employee ID is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="department">Department</label>
                                    <select id="department" name="department" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->name }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-red-500 text-sm hidden" id="departmentError">Department is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="position">Position</label>
                                    <input type="text" id="position" name="position" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="Software Developer" required>
                                    <span class="text-red-500 text-sm hidden" id="positionError">Position is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="role">Role</label>
                                    <select id="role" name="role" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                        <option value="">Select Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="hr">HR</option>
                                        <option value="manager">Manager</option>
                                        <option value="employee">Employee</option>
                                    </select>
                                    <span class="text-red-500 text-sm hidden" id="roleError">Role is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="employment_type">Employment Type</label>
                                    <select id="employment_type" name="employment_type" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                        <option value="">Select Type</option>
                                        <option value="full-time">Full-Time</option>
                                        <option value="part-time">Part-Time</option>
                                        <option value="contract">Contract</option>
                                    </select>
                                    <span class="text-red-500 text-sm hidden" id="employmentTypeError">Employment Type is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="hire_date">Hire Date</label>
                                    <input type="date" id="hire_date" name="hire_date" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" readonly required>
                                    <span class="text-red-500 text-sm hidden" id="hireDateError">Hire Date is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="contract_end_date">Contract End Date</label>
                                    <input type="date" id="contract_end_date" name="contract_end_date" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" readonly>
                                    <span class="text-red-500 text-sm hidden" id="contractEndDateError">Invalid date format</span>
                                </div>
                            </div>
                            <!-- Banking & Compliance -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Banking & Compliance</h4>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="base_salary">Base Salary (TZS)</label>
                                    <input type="number" id="base_salary" name="base_salary" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="5000000" required>
                                    <span class="text-red-500 text-sm hidden" id="baseSalaryError">Base Salary is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="allowances">Allowances (TZS)</label>
                                    <input type="number" id="allowances" name="allowances" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="1000000">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="deductions">Deductions (TZS)</label>
                                    <input type="number" id="deductions" name="deductions" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="500000">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="bank_name">Bank Name</label>
                                    <select id="bank_name" name="bank_name" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                        <option value="">Select Bank (Optional)</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="account_number">Account Number</label>
                                    <input type="text" id="account_number" name="account_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="0152286559700">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="nssf_number">NSSF Number</label>
                                    <input type="text" id="nssf_number" name="nssf_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="NSSF123456">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="nhif_number">NHIF Number</label>
                                    <input type="text" id="nhif_number" name="nhif_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="NHIF123456">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="tin_number">TIN Number</label>
                                    <input type="text" id="tin_number" name="tin_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="TIN123456789">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" class="bg-gray-500 text-white px-5 py-2.5 rounded-md hover:bg-gray-600 flex items-center" onclick="closeModal('addEmployeeModal')">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button type="submit" class="bg-green-600 text-white px-5 py-2.5 rounded-md hover:bg-green-700 flex items-center">
                                <i class="fas fa-check mr-2"></i> Add Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if (in_array(auth()->user()->role, ['admin', 'hr']))
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" id="editEmployeeModal">
            <div class="bg-white rounded-xl w-full max-w-4xl sm:max-w-lg md:max-w-xl lg:max-w-2xl transform transition-all duration-300 scale-95">
                <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-user-edit mr-2"></i> Edit Employee
                    </h3>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <form id="editEmployeeForm" action="" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Personal Information -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Personal Information</h4>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_name">Full Name</label>
                                    <input type="text" id="edit_name" name="name" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                    <span class="text-red-500 text-sm hidden" id="editNameError">Full Name is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_email">Email Address</label>
                                    <input type="email" id="edit_email" name="email" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                    <span class="text-red-500 text-sm hidden" id="editEmailError">Valid email is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_phone">Phone Number</label>
                                    <input type="text" id="edit_phone" name="phone" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_gender">Gender</label>
                                    <select id="edit_gender" name="gender" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_dob">Date of Birth</label>
                                    <input type="date" id="edit_dob" name="dob" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" readonly>
                                    <span class="text-red-500 text-sm hidden" id="editDobError">Invalid date format</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_nationality">Nationality</label>
                                    <input type="text" id="edit_nationality" name="nationality" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_address">Address</label>
                                    <input type="text" id="edit_address" name="address" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                </div>
                            </div>
                            <!-- Employment Details -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Employment Details</h4>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_employee_id">Employee ID</label>
                                    <input type="text" id="edit_employee_id" name="employee_id" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                    <span class="text-red-500 text-sm hidden" personally identifiable information is redacted idError">Employee ID is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_department">Department</label>
                                    <select id="edit_department" name="department" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                        <option value="">Select Department</option>
                                        @foreach($departments as $department)
                                            <option value="{{ $department->name }}">{{ $department->name }}</option>
                                        @endforeach
                                    </select>
                                    <span class="text-red-500 text-sm hidden" id="editDepartmentError">Department is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_position">Position</label>
                                    <input type="text" id="edit_position" name="position" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                    <span class="text-red-500 text-sm hidden" id="editPositionError">Position is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_role">Role</label>
                                    <select id="edit_role" name="role" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                        <option value="">Select Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="hr">HR</option>
                                        <option value="manager">Manager</option>
                                        <option value="employee">Employee</option>
                                    </select>
                                    <span class="text-red-500 text-sm hidden" id="editRoleError">Role is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_employment_type">Employment Type</label>
                                    <select id="edit_employment_type" name="employment_type" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                        <option value="">Select Type</option>
                                        <option value="full-time">Full-Time</option>
                                        <option value="part-time">Part-Time</option>
                                        <option value="contract">Contract</option>
                                    </select>
                                    <span class="text-red-500 text-sm hidden" id="editEmploymentTypeError">Employment Type is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_hire_date">Hire Date</label>
                                    <input type="date" id="edit_hire_date" name="hire_date" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" readonly required>
                                    <span class="text-red-500 text-sm hidden" id="editHireDateError">Hire Date is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_contract_end_date">Contract End Date</label>
                                    <input type="date" id="edit_contract_end_date" name="contract_end_date" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" readonly>
                                    <span class="text-red-500 text-sm hidden" id="editContractEndDateError">Invalid date format</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_status">Status</label>
                                    <select id="edit_status" name="status" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="terminated">Terminated</option>
                                    </select>
                                    <span class="text-red-500 text-sm hidden" id="editStatusError">Status is required</span>
                                </div>
                            </div>
                            <!-- Banking & Compliance -->
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Banking & Compliance</h4>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_base_salary">Base Salary (TZS)</label>
                                    <input type="number" id="edit_base_salary" name="base_salary" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                    <span class="text-red-500 text-sm hidden" id="editBaseSalaryError">Base Salary is required</span>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_allowances">Allowances (TZS)</label>
                                    <input type="number" id="edit_allowances" name="allowances" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_deductions">Deductions (TZS)</label>
                                    <input type="number" id="edit_deductions" name="deductions" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_bank_name">Bank Name</label>
                                    <select id="edit_bank_name" name="bank_name" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                        <option value="">Select Bank (Optional)</option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_account_number">Account Number</label>
                                    <input type="text" id="edit_account_number" name="account_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_nssf_number">NSSF Number</label>
                                    <input type="text" id="edit_nssf_number" name="nssf_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_nhif_number">NHIF Number</label>
                                    <input type="text" id="edit_nhif_number" name="nhif_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                </div>
                                <div>
                                    <label class="block text-gray-700 text-sm font-medium mb-2" for="edit_tin_number">TIN Number</label>
                                    <input type="text" id="edit_tin_number" name="tin_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" class="bg-gray-500 text-white px-5 py-2.5 rounded-md hover:bg-gray-600 flex items-center" onclick="closeModal('editEmployeeModal')">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button type="submit" class="bg-green-600 text-white px-5 py-2.5 rounded-md hover:bg-green-700 flex items-center">
                                <i class="fas fa-check mr-2"></i> Update Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if (in_array(auth()->user()->role, ['admin', 'hr']))
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" id="addComplianceModal">
            <div class="bg-white rounded-xl w-full max-w-4xl sm:max-w-lg md:max-w-xl lg:max-w-2xl transform transition-all duration-300 scale-95">
                <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-shield-alt mr-2"></i> Add Compliance Task
                    </h3>
                </div>
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <form id="addComplianceForm" action="{{ route('compliance.store') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="compliance_type">Compliance Type</label>
                                <select id="compliance_type" name="type" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                    <option value="">Select Type</option>
                                    @foreach($complianceTypes as $type)
                                        <option value="{{ $type->name }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-red-500 text-sm hidden" id="complianceTypeError">Compliance Type is required</span>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="employee_id">Employee</label>
                                <select id="employee_id" name="employee_id" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                    <option value="">Select Employee (Optional)</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="due_date">Due Date</label>
                                <input type="date" id="due_date" name="due_date" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                <span class="text-red-500 text-sm hidden" id="dueDateError">Due Date is required</span>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="amount">Amount (TZS)</label>
                                <input type="number" id="amount" name="amount" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="1000000">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="details">Details</label>
                                <textarea id="details" name="details" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="Enter task details"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" class="bg-gray-500 text-white px-5 py-2.5 rounded-md hover:bg-gray-600 flex items-center" onclick="closeModal('addComplianceModal')">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button type="submit" class="bg-green-600 text-white px-5 py-2.5 rounded-md hover:bg-green-700 flex items-center">
                                <i class="fas fa-check mr-2"></i> Add Task
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" id="runPayrollModal">
        <div class="bg-white rounded-xl w-full max-w-4xl sm:max-w-lg md:max-w-xl lg:max-w-2xl transform transition-all duration-300 scale-95">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-calculator mr-2"></i> Run Payroll
                </h3>
            </div>
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <form id="runPayrollForm" action="{{ route('payroll.run') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="payroll_period">Payroll Period</label>
                            <input type="month" id="payroll_period" name="payroll_period" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="payroll_employees">Employees</label>
                            <select id="payroll_employees" name="employees[]" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" multiple>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="bg-gray-500 text-white px-5 py-2.5 rounded-md hover:bg-gray-600 flex items-center" onclick="closeModal('runPayrollModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="bg-green-600 text-white px-5 py-2.5 rounded-md hover:bg-green-700 flex items-center">
                            <i class="fas fa-check mr-2"></i> Run Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" id="generateReportModal">
        <div class="bg-white rounded-xl w-full max-w-4xl sm:max-w-lg md:max-w-xl lg:max-w-2xl transform transition-all duration-300 scale-95">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Generate Report
                </h3>
            </div>
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <form id="generateReportForm" action="{{ route('reports.generate') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                <option value="payroll_summary">Payroll Summary</option>
                                <option value="employee_summary">Employee Summary</option>
                                <option value="compliance_report">Compliance Report</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="report_period">Period</label>
                            <input type="month" id="report_period" name="report_period" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="bg-gray-500 text-white px-5 py-2.5 rounded-md hover:bg-gray-600 flex items-center" onclick="closeModal('generateReportModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="bg-green-600 text-white px-5 py-2.5 rounded-md hover:bg-green-700 flex items-center">
                            <i class="fas fa-check mr-2"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        // Modal handling
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('hidden');
            const modalContent = modal.querySelector('.scale-95');
            modalContent.classList.remove('scale-95');
            modalContent.classList.add('scale-100');
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            const modalContent = modal.querySelector('.scale-100');
            modalContent.classList.add('scale-95');
            modalContent.classList.remove('scale-100');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        // Make date inputs readonly but allow date picker
        document.addEventListener('DOMContentLoaded', function() {
            const dateInputs = [
                'dob', 'hire_date', 'contract_end_date',
                'edit_dob', 'edit_hire_date', 'edit_contract_end_date'
            ];
            dateInputs.forEach(id => {
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

        // Populate Edit Employee Modal
        window.populateEditEmployeeModal = function(employeeId) {
            fetch(`/employees/${employeeId}/edit`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch employee data');
                    return response.json();
                })
                .then(data => {
                    document.getElementById('editEmployeeForm').action = `/employees/${employeeId}`;
                    document.getElementById('edit_name').value = data.name || '';
                    document.getElementById('edit_employee_id').value = data.employee_id || '';
                    document.getElementById('edit_email').value = data.email || '';
                    document.getElementById('edit_department').value = data.department || '';
                    document.getElementById('edit_position').value = data.position || '';
                    document.getElementById('edit_role').value = data.user?.role || '';
                    document.getElementById('edit_base_salary').value = data.base_salary || '';
                    document.getElementById('edit_allowances').value = data.allowances || '';
                    document.getElementById('edit_deductions').value = data.deductions || '';
                    document.getElementById('edit_employment_type').value = data.employment_type || '';
                    document.getElementById('edit_hire_date').value = data.hire_date || '';
                    document.getElementById('edit_contract_end_date').value = data.contract_end_date || '';
                    document.getElementById('edit_bank_name').value = data.bank_name || '';
                    document.getElementById('edit_account_number').value = data.account_number || '';
                    document.getElementById('edit_phone').value = data.phone || '';
                    document.getElementById('edit_address').value = data.address || '';
                    document.getElementById('edit_nssf_number').value = data.nssf_number || '';
                    document.getElementById('edit_nhif_number').value = data.nhif_number || '';
                    document.getElementById('edit_tin_number').value = data.tin_number || '';
                    document.getElementById('edit_gender').value = data.gender || '';
                    document.getElementById('edit_dob').value = data.dob || '';
                    document.getElementById('edit_nationality').value = data.nationality || '';
                    document.getElementById('edit_status').value = data.status || '';
                    openModal('editEmployeeModal');
                })
                .catch(error => {
                    console.error('Error fetching employee data:', error);
                    alert('Failed to load employee data. Please try again.');
                });
        };

        // Export employees to CSV
        window.exportEmployeesToCSV = function() {
            try {
                const employees = @json($employeesForExport);
                const headers = ['Employee ID', 'Name', 'Department', 'Position', 'Gross Salary', 'Status'];
                const rows = [headers.join(',')];

                if (!Array.isArray(employees) || employees.length === 0) {
                    alert('No employees available to export.');
                    return;
                }

                employees.forEach(employee => {
                    const row = [
                        `"${(employee.id || '').replace(/"/g, '""')}"`,
                        `"${(employee.name || '').replace(/"/g, '""')}"`,
                        `"${(employee.department || '').replace(/"/g, '""')}"`,
                        `"${(employee.position || '').replace(/"/g, '""')}"`,
                        employee.gross_salary || 0,
                        `"${(employee.status || '').replace(/"/g, '""')}"`
                    ];
                    rows.push(row.join(','));
                });

                const csvContent = rows.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `employees_${new Date().toISOString().slice(0,10)}.csv`);
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (error) {
                console.error('Error exporting employees to CSV:', error);
                alert('Failed to export employees. Please try again.');
            }
        };

        // Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('payrollChart').getContext('2d');
            const chartData = {
                type: 'line',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Payroll (TZS)',
                        data: @json($chartData),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.2)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Amount (TZS Millions)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Period'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            };
            new Chart(ctx, chartData);

            // Chart period change handler
            document.getElementById('chartPeriod').addEventListener('change', function() {
                // Placeholder for dynamic chart updates
                console.log('Chart period changed to:', this.value);
                // Example: Fetch new data based on period
                /*
                fetch(`/payroll/data?period=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        chartData.data.labels = data.labels;
                        chartData.data.datasets[0].data = data.values;
                        chart.update();
                    });
                */
            });
        });
    </script>
@endsection