@extends('layout.global')

@section('title', 'Dashboard')

@section('header-title')
    Dashboard
    <span class="payroll-badge text-xs font-semibold px-2 py-1 rounded-full ml-3">
        <i class="fas fa-bolt mr-1"></i> Premium Plan
    </span>
@endsection

@section('header-subtitle')
    Manage employee records and details for {{ $currentPeriod }}.
@endsection

@section('content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Employees</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-600">{{ $totalEmployees }}</p>
                    <p class="mt-1 text-sm text-gray-500 flex items-center">
                        <span class="text-green-500 mr-1">
                            <i class="fas fa-arrow-up"></i>
                        </span>
                        <span>{{ $employeeGrowth }}% from last month</span>
                    </p>
                </div>
                <div class="stat-card-icon bg-blue-100">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Monthly Payroll</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-600">TZS <span class='text-green-500'>{{ number_format($monthlyPayroll, 0) }}</span></p>
                    <p class="mt-1 text-sm text-gray-500 flex items-center">
                        <span class="text-green-500 mr-1">
                            <i class="fas fa-arrow-up"></i>
                        </span>
                        <span>{{ $payrollGrowth }}% from last month</span>
                    </p>
                </div>
                <div class="stat-card-icon bg-green-100">
                    <i class="fas fa-coins text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Payslips Generated</p>
                    <p class="mt-1 text-3xl font-semibold text-gray-600">{{ $payslipsGenerated }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        <span>All employees processed</span>
                    </p>
                </div>
                <div class="stat-card-icon bg-purple-100">
                    <i class="fas fa-file-invoice text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pending Tasks</p>
                    <p class="mt-1 text-3xl font-semibold text-yellow-600">{{ $pendingTasks }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        <span>To be completed</span>
                    </p>
                </div>
                <div class="stat-card-icon bg-yellow-100">
                    <i class="fas fa-tasks text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div>
        <h3 class="text-lg font-medium text-gray-600 mb-4 flex items-center">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="#" class="card hover:shadow-md transition-all flex flex-col items-center text-center" onclick="openModal('addEmployeeModal')">
                <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                    <i class="fas fa-user-plus text-blue-600 text-xl"></i>
                </div>
                <h4 class="font-medium text-gray-900">Add Employee</h4>
                <p class="text-sm text-gray-500 mt-1">Register new employee</p>
            </a>

            <a href="#" class="card hover:shadow-md transition-all flex flex-col items-center text-center" onclick="openModal('runPayrollModal')">
                <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mb-4">
                    <i class="fas fa-calculator text-green-600 text-xl"></i>
                </div>
                <h4 class="font-medium text-gray-900">Run Payroll</h4>
                <p class="text-sm text-gray-500 mt-1">Process salary payments</p>
            </a>

            <a href="#" class="card hover:shadow-md transition-all flex flex-col items-center text-center" onclick="openModal('generateReportModal')">
                <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center mb-4">
                    <i class="fas fa-file-pdf text-purple-600 text-xl"></i>
                </div>
                <h4 class="font-medium text-gray-900">Generate Reports</h4>
                <p class="text-sm text-gray-500 mt-1">Payroll summaries</p>
            </a>

            <a href="#" class="card hover:shadow-md transition-all flex flex-col items-center text-center" onclick="openModal('addComplianceModal')">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mb-4">
                    <i class="fas fa-shield-alt text-yellow-600 text-xl"></i>
                </div>
                <h4 class="font-medium text-gray-900">Add Compliance Task</h4>
                <p class="text-sm text-gray-500 mt-1">Tax & statutory tasks</p>
            </a>
        </div>
    </div>

    <!-- Payroll Chart & Recent Payslips -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Payroll Chart -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-600">Payroll Overview</h3>
                    <select id="chartPeriod" class="text-sm border border-gray-300 rounded-md px-3 py-1 bg-white">
                        <option value="6">Last 6 Months</option>
                        <option value="12">This Year</option>
                        <option value="24">Last Year</option>
                    </select>
                </div>
                <div class="h-64">
                    <canvas id="payrollChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Payslips -->
        <div>
            <div class="card h-full">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium text-gray-600">Recent Payslips</h3>
                    <a href="{{ route('payroll') }}" class="text-sm text-green-600 hover:text-green-800">View All</a>
                </div>
                <div class="space-y-4">
                    @foreach($recentPayslips as $payslip)
                        <div class="flex items-start">
                            <div class="mr-4">
                                <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium">{{ optional($payslip->employee)->name ?? 'N/A' }}</p>
                                <p class="text-sm text-gray-500">{{ optional($payslip->employee)->department ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-green-600">TZS {{ number_format($payslip->net_salary ?? 0) }}</p>
                                <span class="status-{{ strtolower($payslip->status ?? '') }} status-badge">{{ $payslip->status ?? 'N/A' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="addEmployeeModal">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-user-plus mr-2"></i> Add New Employee
                </h3>
            </div>
            <div class="p-6">
                <form id="addEmployeeForm" action="{{ route('employees.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="John Doe" required>
                            <span class="text-red-500 text-sm hidden" id="nameError">Full Name is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="employee_id">Employee ID</label>
                            <input type="text" id="employee_id" name="employee_id" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="EMP-001" required>
                            <span class="text-red-500 text-sm hidden" id="employeeIdError">Employee ID is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="john@company.com" required>
                            <span class="text-red-500 text-sm hidden" id="emailError">Valid email is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="department">Department</label>
                            <select id="department" name="department" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="">Select Department</option>
                                <option value="IT">Information Technology</option>
                                <option value="HR">Human Resources</option>
                                <option value="Finance">Finance</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Teaching">Teaching</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="departmentError">Department is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="position">Position</label>
                            <input type="text" id="position" name="position" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Software Developer" required>
                            <span class="text-red-500 text-sm hidden" id="positionError">Position is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="base_salary">Base Salary (TZS)</label>
                            <input type="number" id="base_salary" name="base_salary" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="570000" required>
                            <span class="text-red-500 text-sm hidden" id="baseSalaryError">Base Salary is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="allowances">Allowances (TZS)</label>
                            <input type="number" id="allowances" name="allowances" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="130000">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="bank_name">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="CRDB Bank">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="account_number">Account Number</label>
                            <input type="text" id="account_number" name="account_number" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="0152286559700">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="hire_date">Hire Date</label>
                            <input type="date" id="hire_date" name="hire_date" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="hireDateError">Hire Date is required</span>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
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

    <!-- Edit Employee Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="editEmployeeModal">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-user-edit mr-2"></i> Edit Employee
                </h3>
            </div>
            <div class="p-6">
                <form id="editEmployeeForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_name">Full Name</label>
                            <input type="text" id="edit_name" name="name" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="editNameError">Full Name is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_employee_id">Employee ID</label>
                            <input type="text" id="edit_employee_id" name="employee_id" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="editEmployeeIdError">Employee ID is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_email">Email Address</label>
                            <input type="email" id="edit_email" name="email" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="editEmailError">Valid email is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_department">Department</label>
                            <select id="edit_department" name="department" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
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
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_position">Position</label>
                            <input type="text" id="edit_position" name="position" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="editPositionError">Position is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_base_salary">Base Salary (TZS)</label>
                            <input type="number" id="edit_base_salary" name="base_salary" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="editBaseSalaryError">Base Salary is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_allowances">Allowances (TZS)</label>
                            <input type="number" id="edit_allowances" name="allowances" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_bank_name">Bank Name</label>
                            <input type="text" id="edit_bank_name" name="bank_name" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_account_number">Account Number</label>
                            <input type="text" id="edit_account_number" name="account_number" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_hire_date">Hire Date</label>
                            <input type="date" id="edit_hire_date" name="hire_date" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="editHireDateError">Hire Date is required</span>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('editEmployeeModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Update Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Run Payroll Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="runPayrollModal">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-calculator mr-2"></i> Run Payroll
                </h3>
            </div>
            <div class="p-6">
                <form id="runPayrollForm" action="{{ route('payroll.run') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="payroll_period">Payroll Period</label>
                            <input type="month" id="payroll_period" name="payroll_period" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="payrollPeriodError">Payroll Period is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="employee_ids">Select Employees</label>
                            <select id="employee_ids" name="employee_ids[]" multiple class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ optional($employee)->name ?? 'N/A' }} ({{ $employee->employee_id ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            <span class="text-red-500 text-sm hidden" id="employeeIdsError">At least one employee must be selected</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="nssf_rate">NSSF Rate (%)</label>
                            <input type="number" id="nssf_rate" name="nssf_rate" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="10" required>
                            <span class="text-red-500 text-sm hidden" id="nssfRateError">NSSF Rate is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="nhif_rate">NHIF Rate (TZS)</label>
                            <input type="number" id="nhif_rate" name="nhif_rate" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="20000" required>
                            <span class="text-red-500 text-sm hidden" id="nhifRateError">NHIF Rate is required</span>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('runPayrollModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                            <i class="fas fa-calculator mr-2"></i> Run Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="generateReportModal">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Generate Reports
                </h3>
            </div>
            <div class="p-6">
                <form id="generateReportForm" action="{{ route('reports.generate') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="payslip">Payslip</option>
                                <option value="payroll_summary">Payroll Summary</option>
                                <option value="tax_report">Tax Report</option>
                                <option value="nssf_report">NSSF Report</option>
                                <option value="nhif_report">NHIF Report</option>
                                <option value="year_end_summary">Year-End Summary</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="reportTypeError">Report Type is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="report_period">Report Period</label>
                            <input type="month" id="report_period" name="report_period" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="reportPeriodError">Report Period is required</span>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="export_format">Export Format</label>
                            <select id="export_format" name="export_format" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="exportFormatError">Export Format is required</span>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('generateReportModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                            <i class="fas fa-file-export mr-2"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Compliance Task Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="addComplianceModal">
        <div class="bg-white rounded-xl w-full max-w-2xl modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Compliance Task
                </h3>
            </div>
            <div class="p-6">
                <form id="addComplianceForm" action="{{ route('compliance.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="compliance_type" class="block text-sm font-medium text-gray-700">Compliance Type</label>
                            <select name="type" id="compliance_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                <option value="PAYE">PAYE</option>
                                <option value="NSSF">NSSF</option>
                                <option value="NHIF">NHIF</option>
                                <option value="WCF">WCF</option>
                                <option value="SDL">SDL</option>
                            </select>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee (Optional)</label>
                            <select name="employee_id" id="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                <option value="">None</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ optional($employee)->name ?? 'N/A' }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
                            <input type="date" name="due_date" id="due_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('due_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount (Optional)</label>
                            <input type="number" name="amount" id="amount" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-span-2">
                            <label for="details" class="block text-sm font-medium text-gray-700">Details (Optional)</label>
                            <textarea name="details" id="details" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"></textarea>
                            @error('details')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" class="btn-primary bg-gray-500 hover:bg-gray-600" onclick="closeModal('addComplianceModal')">Cancel</button>
                        <button type="submit" class="btn-primary">Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Employee Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="viewEmployeeModal">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-user mr-2"></i> Employee Details
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Full Name</p>
                        <p class="text-gray-900 font-medium" id="view_name"></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Employee ID</p>
                        <p class="text-gray-900 font-medium" id="view_employee_id"></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Email Address</p>
                        <p class="text-gray-900 font-medium" id="view_email"></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Department</p>
                        <p class="text-gray-900 font-medium" id="view_department"></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Position</p>
                        <p class="text-gray-900 font-medium" id="view_position"></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Gross Salary (TZS)</p>
                        <p class="text-gray-900 font-medium" id="view_gross_salary"></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Bank Name</p>
                        <p class="text-gray-900 font-medium" id="view_bank_name"></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Account Number</p>
                        <p class="text-gray-900 font-medium" id="view_account_number"></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Hire Date</p>
                        <p class="text-gray-900 font-medium" id="view_hire_date"></p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium">Status</p>
                        <p class="text-gray-900 font-medium" id="view_status"></p>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('viewEmployeeModal')">
                        <i class="fas fa-times mr-2"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .status-active {
            @apply bg-green-100 text-green-800;
        }
        .status-on-leave {
            @apply bg-yellow-100 text-yellow-800;
        }
        .status-terminated {
            @apply bg-red-100 text-red-800;
        }
        .status-paid {
            @apply bg-green-100 text-green-800;
        }
        .status-pending {
            @apply bg-yellow-100 text-yellow-800;
        }
        .status-badge {
            @apply px-2 py-1 rounded-full text-xs font-medium;
        }
        .stat-card-icon {
            @apply w-12 h-12 rounded-lg flex items-center justify-center;
        }
    </style>

    <script>
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

        document.addEventListener('DOMContentLoaded', function() {
            // Payroll Chart
            const payrollCtx = document.getElementById('payrollChart').getContext('2d');
            const payrollChart = new Chart(payrollCtx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Total Payroll (TZS Millions)',
                        data: @json($chartData),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'TZS ' + value + 'M';
                                }
                            }
                        }
                    }
                }
            });

            // Form Validation
            ['addEmployeeForm', 'editEmployeeForm', 'runPayrollForm', 'generateReportForm', 'addComplianceForm'].forEach(formId => {
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

            // Employee Table Search and Filter
            const searchInput = document.getElementById('searchEmployee');
            const statusFilter = document.getElementById('statusFilter');
            if (searchInput && statusFilter) {
                searchInput.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase();
                    const statusValue = statusFilter.value;
                    filterEmployeeTable(searchValue, statusValue);
                });

                statusFilter.addEventListener('change', function() {
                    const searchValue = searchInput.value.toLowerCase();
                    const statusValue = this.value;
                    filterEmployeeTable(searchValue, statusValue);
                });
            }

            function filterEmployeeTable(searchValue, statusFilter) {
                const rows = document.querySelectorAll('#employeeTable .employee-row');
                rows.forEach(row => {
                    const nameElement = row.querySelector('td:first-child p:first-child');
                    const idElement = row.querySelector('td:first-child p:last-child');
                    const name = nameElement ? nameElement.textContent.toLowerCase() : '';
                    const id = idElement ? idElement.textContent.toLowerCase() : '';
                    const status = row.getAttribute('data-status') || '';
                    const matchesSearch = name.includes(searchValue) || id.includes(searchValue);
                    const matchesStatus = !statusFilter || status === statusFilter;
                    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                });
            }

            function exportEmployeesToCSV() {
                try {
                    const employees = JSON.parse('{{ $employees->map(fn($employee) => [
                        "id" => $employee->employee_id ?? "",
                        "name" => $employee->name ?? "",
                        "department" => $employee->department ?? "",
                        "position" => $employee->position ?? "",
                        "gross_salary" => ($employee->base_salary ?? 0) + ($employee->allowances ?? 0),
                        "status" => $employee->status ?? ""
                    ])->toJson() }}');
                    const headers = ['Employee ID', 'Name', 'Department', 'Position', 'Gross Salary', 'Status'];
                    const csvRows = [headers.join(',')];
                    employees.forEach(employee => {
                        const row = [
                            employee.id || '',
                            `"${(employee.name || '').replace(/"/g, '""')}"`,
                            `"${(employee.department || '').replace(/"/g, '""')}"`,
                            `"${(employee.position || '').replace(/"/g, '""')}"`,
                            employee.gross_salary || 0,
                            employee.status || ''
                        ];
                        csvRows.push(row.join(','));
                    });
                    const csvContent = csvRows.join('\n');
                    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'employees.csv';
                    link.click();
                    URL.revokeObjectURL(link.href);
                } catch (e) {
                    console.error('Error exporting CSV:', e);
                    alert('Failed to export CSV. Please try again.');
                }
            }

            window.viewEmployee = function(id) {
                fetch(`/employees/${id}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to fetch employee data');
                        return response.json();
                    })
                    .then(data => {
                        const fields = ['name', 'employee_id', 'email', 'department', 'position', 'bank_name', 'account_number', 'hire_date', 'status'];
                        fields.forEach(field => {
                            const element = document.getElementById(`view_${field}`);
                            if (element) {
                                element.textContent = data[field] || 'N/A';
                            }
                        });
                        const grossSalaryElement = document.getElementById('view_gross_salary');
                        if (grossSalaryElement) {
                            grossSalaryElement.textContent = data.base_salary ? 'TZS ' + (parseFloat(data.base_salary) + (parseFloat(data.allowances) || 0)).toLocaleString() : 'N/A';
                        }
                        openModal('viewEmployeeModal');
                    })
                    .catch(error => {
                        console.error('Error fetching employee:', error);
                        alert('Failed to load employee data. Please try again.');
                    });
            };

            window.editEmployee = function(id) {
                fetch(`/employees/${id}/edit`)
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to fetch employee data');
                        return response.json();
                    })
                    .then(data => {
                        const fields = ['name', 'employee_id', 'email', 'department', 'position', 'base_salary', 'allowances', 'bank_name', 'account_number', 'hire_date'];
                        fields.forEach(field => {
                            const element = document.getElementById(`edit_${field}`);
                            if (element) {
                                element.value = data[field] || '';
                            }
                        });
                        const editForm = document.getElementById('editEmployeeForm');
                        if (editForm) {
                            editForm.action = `/employees/${id}`;
                        }
                        openModal('editEmployeeModal');
                    })
                    .catch(error => {
                        console.error('Error fetching employee for edit:', error);
                        alert('Failed to load employee data for editing. Please try again.');
                    });
            };
        });
    </script>
@endsection
