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

    @php $user = Auth::user(); @endphp

    @if($user && in_array(strtolower($user->role), ['admin','hr']))
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
                            <p class="text-sm text-gray-500">{{ $payslip->employee->department->name ?? 'N/A' }}</p>
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

    <!-- Spinner Component -->
    <div id="spinner" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-60 hidden">
        <div class="w-16 h-16 border-4 border-t-green-600 border-gray-200 rounded-full animate-spin"></div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-xl max-w-md w-full transform transition-all duration-300 scale-95">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-check-circle mr-2"></i> Success
                </h3>
            </div>
            <div class="p-6">
                <p id="successMessage" class="text-gray-700"></p>
                <div class="mt-6 flex justify-end">
                    <button type="button" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center" onclick="closeModal('successModal')">
                        <i class="fas fa-check mr-2"></i> OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 hidden">
        <div class="bg-white rounded-xl max-w-md w-full transform transition-all duration-300 scale-95">
            <div class="p-6 bg-gradient-to-r from-red-50 to-orange-50 border-b">
                <h3 class="text-xl font-semibold text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i> Error
                </h3>
            </div>
            <div class="p-6">
                <p id="errorMessage" class="text-gray-700"></p>
                <div class="mt-6 flex justify-end">
                    <button type="button" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 flex items-center" onclick="closeModal('errorModal')">
                        <i class="fas fa-times mr-2"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee Modal -->
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
                                <input type="text" id="dob" name="dob" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 flatpickr-date" placeholder="Select date">
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
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="department_id">Department</label>
                                <select id="department_id" name="department_id" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
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
                                    @foreach(['admin', 'hr', 'manager', 'employee'] as $role)
                                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                                <span class="text-red-500 text-sm hidden" id="roleError">Role is required</span>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="employment_type">Employment Type</label>
                                <select id="employment_type" name="employment_type" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                    <option value="">Select Type</option>
                                    @foreach(['full-time', 'part-time', 'contract'] as $type)
                                        <option value="{{ $type }}">{{ ucwords(str_replace('-', ' ', $type)) }}</option>
                                    @endforeach
                                </select>
                                <span class="text-red-500 text-sm hidden" id="employmentTypeError">Employment Type is required</span>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="hire_date">Hire Date</label>
                                <input type="text" id="hire_date" name="hire_date" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 flatpickr-date" placeholder="Select date" required>
                                <span class="text-red-500 text-sm hidden" id="hireDateError">Hire Date is required</span>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="contract_end_date">Contract End Date</label>
                                <input type="text" id="contract_end_date" name="contract_end_date" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 flatpickr-date" placeholder="Select date">
                            </div>
                        </div>
                        <!-- Compensation & Compliance -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Compensation & Compliance</h4>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="base_salary">Base Salary</label>
                                <input type="number" id="base_salary" name="base_salary" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="500000" required min="0" step="0.01">
                                <span class="text-red-500 text-sm hidden" id="baseSalaryError">Base Salary is required</span>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="allowances">Allowances</label>
                                <input type="number" id="allowances" name="allowances" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="0" min="0" step="0.01">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="deductions">Deductions</label>
                                <input type="number" id="deductions" name="deductions" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="0" min="0" step="0.01">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="bank_name">Bank Name</label>
                                <select id="bank_name" name="bank_name" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="account_number">Account Number</label>
                                <input type="text" id="account_number" name="account_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="1234567890">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="nssf_number">NSSF Number</label>
                                <input type="text" id="nssf_number" name="nssf_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="NSSF-123456">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="nhif_number">NHIF Number</label>
                                <input type="text" id="nhif_number" name="nhif_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="NHIF-123456">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="tin_number">TIN Number</label>
                                <input type="text" id="tin_number" name="tin_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="TIN-123456789">
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center" onclick="closeModal('addEmployeeModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center">
                            <i class="fas fa-check mr-2"></i> Add Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Run Payroll Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" id="runPayrollModal">
        <div class="bg-white rounded-xl max-w-md w-full transform transition-all duration-300 scale-95">
            <div class="p-6 bg-gradient-to-r from-purple-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-purple-600 flex items-center">
                    <i class="fas fa-calculator mr-2"></i> Run Payroll
                </h3>
            </div>
            <div class="p-6">
                <form id="runPayrollForm" action="{{ route('payroll.run') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="payroll_period_type">Payroll Period Type</label>
                            <select id="payroll_period_type" name="payroll_period_type" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" required>
                                <option value="">Select Period Type</option>
                                <option value="month">Month</option>
                                <option value="year">Year</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="payrollPeriodTypeError">Period Type is required</span>
                        </div>
                        <div id="payroll_month_input" class="hidden">
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="payroll_period_month">Payroll Period (Month)</label>
                            <input type="text" id="payroll_period_month" name="payroll_period" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500 flatpickr-month" placeholder="Select month" required>
                            <span class="text-red-500 text-sm hidden" id="payrollPeriodMonthError">Valid month (YYYY-MM) is required</span>
                        </div>
                        <div id="payroll_year_input" class="hidden">
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="payroll_period_year">Payroll Period (Year)</label>
                            <input type="text" id="payroll_period_year" name="payroll_period" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500 flatpickr-year" placeholder="Select year" required>
                            <span class="text-red-500 text-sm hidden" id="payrollPeriodYearError">Valid year (1900-2100) is required</span>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="nssf_rate">NSSF Rate (%)</label>
                            <input type="number" id="nssf_rate" name="nssf_rate" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" placeholder="10" required min="0" max="100" step="0.01">
                            <span class="text-red-500 text-sm hidden" id="nssfRateError">NSSF Rate is required</span>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="nhif_rate">NHIF Rate (%)</label>
                            <input type="number" id="nhif_rate" name="nhif_rate" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-purple-500 focus:border-purple-500" placeholder="3" required min="0" max="100" step="0.01">
                            <span class="text-red-500 text-sm hidden" id="nhifRateError">NHIF Rate is required</span>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center" onclick="closeModal('runPayrollModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 flex items-center">
                            <i class="fas fa-play mr-2"></i> Run Payroll
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" id="generateReportModal">
        <div class="bg-white rounded-xl max-w-md w-full transform transition-all duration-300 scale-95">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-file-pdf mr-2"></i> Generate Report
                </h3>
            </div>
            <div class="p-6">
                <form id="generateReportForm" action="{{ route('reports.generate') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="report_type">Report Type</label>
                            <select id="report_type" name="report_type" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                <option value="">Select Report Type</option>
                                @if($reportTypes->isNotEmpty())
                                    @foreach($reportTypes as $reportType)
                                        <option value="{{ $reportType->type }}">{{ $reportType->name }}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No report types available</option>
                                @endif
                            </select>
                            <span class="text-red-500 text-sm hidden" id="reportTypeError">Report Type is required</span>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="report_period_type">Report Period Type</label>
                            <select id="report_period_type" name="report_period_type" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                <option value="">Select Period Type</option>
                                <option value="month">Month</option>
                                <option value="year">Year</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="reportPeriodTypeError">Period Type is required</span>
                        </div>
                        <div id="report_month_input" class="hidden">
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="report_period_month">Report Period (Month)</label>
                            <input type="text" id="report_period_month" name="report_period" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 flatpickr-month" placeholder="Select month">
                            <span class="text-red-500 text-sm hidden" id="reportPeriodMonthError">Valid month (YYYY-MM) is required</span>
                        </div>
                        <div id="report_year_input" class="hidden">
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="report_period_year">Report Period (Year)</label>
                            <input type="text" id="report_period_year" name="report_period" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500 flatpickr-year" placeholder="Select year">
                            <span class="text-red-500 text-sm hidden" id="reportPeriodYearError">Valid year (1900-2100) is required</span>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="employee_id">Employee (Optional)</label>
                            <select id="employee_id" name="employee_id" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="export_format">Export Format</label>
                            <select id="export_format" name="export_format" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" required>
                                <option value="">Select Format</option>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="exportFormatError">Export Format is required</span>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center" onclick="closeModal('generateReportModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center">
                            <i class="fas fa-download mr-2"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Compliance Task Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" id="addComplianceModal">
        <div class="bg-white rounded-xl max-w-md w-full transform transition-all duration-300 scale-95">
            <div class="p-6 bg-gradient-to-r from-yellow-50 to-orange-50 border-b">
                <h3 class="text-xl font-semibold text-yellow-600 flex items-center">
                    <i class="fas fa-shield-alt mr-2"></i> Add Compliance Task
                </h3>
            </div>
            <div class="p-6">
                <form id="addComplianceForm" action="{{ route('compliance.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="compliance_type">Compliance Type</label>
                            <select id="compliance_type" name="type" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" required>
                                <option value="">Select Type</option>
                                @if($complianceTypes->isNotEmpty())
                                    @foreach($complianceTypes as $complianceType)
                                        <option value="{{ $complianceType->type }}">{{ $complianceType->name }}</option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No compliance types available</option>
                                @endif
                            </select>
                            <span class="text-red-500 text-sm hidden" id="complianceTypeError">Compliance Type is required</span>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="compliance_employee_id">Employee (Optional)</label>
                            <select id="compliance_employee_id" name="employee_id" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500">
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="due_date_type">Due Date Type</label>
                            <select id="due_date_type" name="due_date_type" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" required>
                                <option value="">Select Date Type</option>
                                <option value="today">Today</option>
                                <option value="date">Specific Date</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="dueDateTypeError">Due Date Type is required</span>
                        </div>
                        <div id="due_date_input" class="hidden">
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="due_date">Due Date</label>
                            <input type="text" id="due_date" name="due_date" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500 flatpickr-date" placeholder="Select date">
                            <span class="text-red-500 text-sm hidden" id="dueDateError">Valid date (YYYY-MM-DD) is required</span>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="amount">Amount</label>
                            <input type="number" id="amount" name="amount" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" placeholder="0" min="0" step="0.01">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-medium mb-2" for="details">Details</label>
                            <textarea id="details" name="details" rows="3" class="w-full p-2.5 border border-gray-300 rounded-md focus:ring-yellow-500 focus:border-yellow-500" placeholder="Additional details..."></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center" onclick="closeModal('addComplianceModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        let payrollChart;

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

        // Show spinner
        function showSpinner() {
            document.getElementById('spinner').classList.remove('hidden');
        }

        // Hide spinner
        function hideSpinner() {
            document.getElementById('spinner').classList.add('hidden');
        }

        // Show success modal with message
        function showSuccess(message) {
            document.getElementById('successMessage').textContent = message;
            openModal('successModal');
            hideSpinner();
        }

        // Show error modal with message
        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            openModal('errorModal');
            hideSpinner();
        }

        // Initialize Flatpickr with error handling
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Date pickers (YYYY-MM-DD)
                flatpickr('.flatpickr-date', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    minDate: '1900-01-01',
                    maxDate: '2100-12-31',
                    onOpen: function() {
                        this.input.style.cursor = 'pointer';
                    },
                    onClose: function() {
                        this.input.style.cursor = 'pointer';
                    }
                });

                // Special handling for due_date (minDate: today)
                flatpickr('#due_date', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    minDate: 'today',
                    maxDate: '2100-12-31',
                    onOpen: function() {
                        this.input.style.cursor = 'pointer';
                    },
                    onClose: function() {
                        this.input.style.cursor = 'pointer';
                    }
                });

                // Month pickers (YYYY-MM)
                flatpickr('.flatpickr-month', {
                    dateFormat: 'Y-m',
                    allowInput: true,
                    minDate: '1900-01',
                    maxDate: '2100-12',
                    static: true,
                    onOpen: function() {
                        this.input.style.cursor = 'pointer';
                    },
                    onClose: function() {
                        this.input.style.cursor = 'pointer';
                    }
                });

                // Year pickers (YYYY)
                flatpickr('.flatpickr-year', {
                    dateFormat: 'Y',
                    allowInput: true,
                    minDate: '1900',
                    maxDate: '2100',
                    static: true,
                    onOpen: function() {
                        this.input.style.cursor = 'pointer';
                    },
                    onClose: function() {
                        this.input.style.cursor = 'pointer';
                    },
                    onReady: function(selectedDates, dateStr, instance) {
                        const yearPicker = document.createElement('select');
                        yearPicker.className = 'flatpickr-year-select';
                        for (let year = 1900; year <= 2100; year++) {
                            const option = document.createElement('option');
                            option.value = year;
                            option.text = year;
                            yearPicker.appendChild(option);
                        }
                        yearPicker.value = dateStr || new Date().getFullYear();
                        instance.calendarContainer.appendChild(yearPicker);
                        yearPicker.addEventListener('change', () => {
                            instance.setDate(yearPicker.value);
                        });
                    },
                    onChange: function(selectedDates, dateStr, instance) {
                        const yearPicker = instance.calendarContainer.querySelector('.flatpickr-year-select');
                        if (yearPicker) yearPicker.value = dateStr;
                    }
                });
            } catch (error) {
                console.error('Failed to initialize Flatpickr:', error);
                showError('Error loading date pickers. Please check your internet connection or browser console for details.');
            }

            // Dynamic period input handling for Run Payroll
            const payrollPeriodType = document.getElementById('payroll_period_type');
            if (payrollPeriodType) {
                payrollPeriodType.addEventListener('change', function() {
                    const monthInput = document.getElementById('payroll_month_input');
                    const yearInput = document.getElementById('payroll_year_input');
                    const monthField = document.getElementById('payroll_period_month');
                    const yearField = document.getElementById('payroll_period_year');
                    monthInput.classList.add('hidden');
                    yearInput.classList.add('hidden');
                    monthField.removeAttribute('required');
                    yearField.removeAttribute('required');
                    monthField.value = '';
                    yearField.value = '';
                    if (this.value === 'month') {
                        monthInput.classList.remove('hidden');
                        monthField.setAttribute('required', 'required');
                    } else if (this.value === 'year') {
                        yearInput.classList.remove('hidden');
                        yearField.setAttribute('required', 'required');
                    }
                });
            }

            // Dynamic period input handling for Generate Report
            const reportType = document.getElementById('report_type');
            const reportPeriodType = document.getElementById('report_period_type');
            if (reportType && reportPeriodType) {
                reportType.addEventListener('change', function() {
                    const isYearEndSummary = this.value === 'year_end_summary';
                    const monthInput = document.getElementById('report_month_input');
                    const yearInput = document.getElementById('report_year_input');
                    const monthField = document.getElementById('report_period_month');
                    const yearField = document.getElementById('report_period_year');
                    reportPeriodType.value = isYearEndSummary ? 'year' : '';
                    reportPeriodType.disabled = isYearEndSummary;
                    monthInput.classList.add('hidden');
                    yearInput.classList.add('hidden');
                    monthField.removeAttribute('required');
                    yearField.removeAttribute('required');
                    monthField.value = '';
                    yearField.value = '';
                    if (isYearEndSummary) {
                        yearInput.classList.remove('hidden');
                        yearField.setAttribute('required', 'required');
                    }
                });

                reportPeriodType.addEventListener('change', function() {
                    const monthInput = document.getElementById('report_month_input');
                    const yearInput = document.getElementById('report_year_input');
                    const monthField = document.getElementById('report_period_month');
                    const yearField = document.getElementById('report_period_year');
                    if (reportType.value !== 'year_end_summary') {
                        monthInput.classList.add('hidden');
                        yearInput.classList.add('hidden');
                        monthField.removeAttribute('required');
                        yearField.removeAttribute('required');
                        monthField.value = '';
                        yearField.value = '';
                        if (this.value === 'month') {
                            monthInput.classList.remove('hidden');
                            monthField.setAttribute('required', 'required');
                        } else if (this.value === 'year') {
                            yearInput.classList.remove('hidden');
                            yearField.setAttribute('required', 'required');
                        }
                    }
                });
            }

            // Dynamic due date input handling for Compliance Task
            const dueDateType = document.getElementById('due_date_type');
            if (dueDateType) {
                dueDateType.addEventListener('change', function() {
                    const dueDateInput = document.getElementById('due_date_input');
                    const dueDate = document.getElementById('due_date');
                    dueDateInput.classList.add('hidden');
                    dueDate.removeAttribute('required');
                    dueDate.value = '';
                    if (this.value === 'date') {
                        dueDateInput.classList.remove('hidden');
                        dueDate.setAttribute('required', 'required');
                    } else if (this.value === 'today') {
                        dueDate.value = new Date().toISOString().split('T')[0];
                    }
                });
            }

            // Chart initialization
            const ctx = document.getElementById('payrollChart').getContext('2d');
            payrollChart = new Chart(ctx, {
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
            });

            // Chart period change handler
            document.getElementById('chartPeriod').addEventListener('change', function() {
                showSpinner();
                const period = this.value;
                fetch(`/payroll/data?period=${period}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch chart data');
                    return response.json();
                })
                .then(data => {
                    payrollChart.data.labels = data.labels;
                    payrollChart.data.datasets[0].data = data.values;
                    payrollChart.update();
                    hideSpinner();
                })
                .catch(error => {
                    console.error('Error updating chart:', error);
                    showError('Failed to update chart data. Please try again.');
                });
            });

            // Form submission handlers with spinner and success/error modals
            const forms = [
                { id: 'addEmployeeForm', successMessage: 'Employee added successfully!', route: '{{ route("employees.store") }}' },
                { id: 'runPayrollForm', successMessage: 'Payroll processed successfully!', route: '{{ route("payroll.run") }}' },
                { id: 'generateReportForm', successMessage: 'Report generated successfully!', route: '{{ route("reports.generate") }}' },
                { id: 'addComplianceForm', successMessage: 'Compliance task added successfully!', route: '{{ route("compliance.store") }}' }
            ];

            forms.forEach(form => {
                const formElement = document.getElementById(form.id);
                formElement.addEventListener('submit', function(e) {
                    e.preventDefault();
                    let valid = true;
                    const fields = formElement.querySelectorAll('[required]');
                    fields.forEach(field => {
                        const error = document.getElementById(`${field.id}Error`);
                        if (!field.value.trim()) {
                            error.classList.remove('hidden');
                            valid = false;
                        } else {
                            error.classList.add('hidden');
                        }
                    });

                    // Additional validation for specific forms
                    if (form.id === 'addEmployeeForm') {
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
                        }
                    } else if (form.id === 'runPayrollForm') {
                        const periodType = document.getElementById('payroll_period_type').value;
                        if (periodType === 'month') {
                            const monthInput = document.getElementById('payroll_period_month');
                            const monthError = document.getElementById('payrollPeriodMonthError');
                            if (!monthInput.value || !/^\d{4}-\d{2}$/.test(monthInput.value)) {
                                monthError.classList.remove('hidden');
                                valid = false;
                            }
                        } else if (periodType === 'year') {
                            const yearInput = document.getElementById('payroll_period_year');
                            const yearError = document.getElementById('payrollPeriodYearError');
                            if (!yearInput.value || isNaN(yearInput.value) || yearInput.value < 1900 || yearInput.value > 2100) {
                                yearError.classList.remove('hidden');
                                valid = false;
                            }
                        }
                    } else if (form.id === 'generateReportForm') {
                        const reportType = document.getElementById('report_type').value;
                        const periodType = document.getElementById('report_period_type').value;
                        if (reportType) {
                            if (periodType === 'month' && reportType !== 'year_end_summary') {
                                const monthInput = document.getElementById('report_period_month');
                                const monthError = document.getElementById('reportPeriodMonthError');
                                if (!monthInput.value || !/^\d{4}-\d{2}$/.test(monthInput.value)) {
                                    monthError.classList.remove('hidden');
                                    valid = false;
                                }
                            } else if (periodType === 'year' || reportType === 'year_end_summary') {
                                const yearInput = document.getElementById('report_period_year');
                                const yearError = document.getElementById('reportPeriodYearError');
                                if (!yearInput.value || isNaN(yearInput.value) || yearInput.value < 1900 || yearInput.value > 2100) {
                                    yearError.classList.remove('hidden');
                                    valid = false;
                                }
                            }
                        }
                    } else if (form.id === 'addComplianceForm') {
                        const dueDateType = document.getElementById('due_date_type').value;
                        if (dueDateType === 'date') {
                            const dueDate = document.getElementById('due_date');
                            const dueDateError = document.getElementById('dueDateError');
                            if (!dueDate.value || !/^\d{4}-\d{2}-\d{2}$/.test(dueDate.value)) {
                                dueDateError.classList.remove('hidden');
                                valid = false;
                            }
                        }
                        const amount = document.getElementById('amount');
                        if (amount.value && (isNaN(amount.value) || parseFloat(amount.value) < 0)) {
                            showError('Amount must be a valid non-negative number');
                            valid = false;
                        }
                    }

                    if (valid) {
                        showSpinner();
                        fetch(form.route, {
                            method: 'POST',
                            body: new FormData(formElement),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Request failed');
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                showSuccess(form.successMessage);
                                formElement.reset();
                                closeModal(form.id.replace('Form', 'Modal'));
                            } else {
                                showError(data.message || 'An error occurred. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error submitting form:', error);
                            showError('Failed to process request. Please try again.');
                        });
                    }
                });
            });

            // Export employees to CSV
            window.exportEmployeesToCSV = function() {
                showSpinner();
                try {
                    const employees = @json($employeesForExport);
                    const headers = ['Name', 'Department', 'Position', 'Gross Salary', 'Status'];
                    const rows = [headers.join(',')];

                    if (!Array.isArray(employees) || employees.length === 0) {
                        showError('No employees available to export.');
                        return;
                    }

                    employees.forEach(employee => {
                        const row = [
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
                    hideSpinner();
                } catch (error) {
                    console.error('Error exporting employees to CSV:', error);
                    showError('Failed to export employees. Please try again.');
                }
            };
        });
    </script>
@endsection
