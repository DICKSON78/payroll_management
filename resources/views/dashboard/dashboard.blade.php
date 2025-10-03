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
                <!-- Add Employee -->
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer quick-action-btn" data-action="add_employee">
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

                <!-- Run Payroll -->
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer quick-action-btn" data-action="run_payroll">
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

                <!-- Generate Reports -->
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer quick-action-btn" data-action="generate_reports">
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

                <!-- Add Compliance Task -->
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer quick-action-btn" data-action="add_compliance">
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
                @forelse($recentPayslips as $payslip)
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $payslip->employee_name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">{{ $payslip->department ?? 'N/A' }} • {{ $payslip->position ?? 'N/A' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-green-600">TZS {{ number_format($payslip->net_salary ?? 0, 0) }}</p>
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $payslip->status == 'Generated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">{{ $payslip->status ?? 'N/A' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-file-invoice text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">No payslips generated yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- MODALS SECTION -->

    <!-- Add Employee Modal -->
    <div id="addEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl w-[97%] h-[95vh] transform transition-all duration-300 scale-95 overflow-hidden modal-content">
            <div class="p-6 bg-gradient-to-r from-blue-50 to-blue-100 border-b">
                <div class="flex justify-between items-center">
                    <h3 class="text-xl font-semibold text-blue-600 flex items-center">
                        <i class="fas fa-user-plus mr-2"></i> Add New Employee
                    </h3>
                    <button type="button" onclick="closeModal('addEmployeeModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            <div class="p-6 h-full overflow-y-auto">
                <form id="addEmployeeForm" action="{{ route('employees.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Personal Information Column -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Personal Information</h4>
                            
                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="name">
                                    Full Name *
                                </label>
                                <input type="text" id="name" name="name" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="John Doe" required>
                                <span class="text-red-500 text-sm hidden" id="nameError">Full Name is required</span>
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="email">
                                    Email Address *
                                </label>
                                <input type="email" id="email" name="email" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="john@company.com" required>
                                <span class="text-red-500 text-sm hidden" id="emailError">Valid email is required</span>
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="phone">
                                    Phone Number
                                </label>
                                <input type="text" id="phone" name="phone" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="+255 123 456 789">
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="gender">
                                    Gender
                                </label>
                                <select id="gender" name="gender" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="dob">
                                    Date of Birth
                                </label>
                                <input type="date" id="dob" name="dob" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="nationality">
                                    Nationality
                                </label>
                                <input type="text" id="nationality" name="nationality" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tanzanian">
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="address">
                                    Address
                                </label>
                                <input type="text" id="address" name="address" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="123 Main St, Dar es Salaam">
                            </div>
                        </div>
                        
                        <!-- Employment Details Column -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Employment Details</h4>
                            
                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="department">
                                    Department *
                                </label>
                                <select id="department" name="department" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->name }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-red-500 text-sm hidden" id="departmentError">Department is required</span>
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="position">
                                    Position *
                                </label>
                                <input type="text" id="position" name="position" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Software Developer" required>
                                <span class="text-red-500 text-sm hidden" id="positionError">Position is required</span>
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="role">
                                    Role *
                                </label>
                                <select id="role" name="role" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->slug }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-red-500 text-sm hidden" id="roleError">Role is required</span>
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="employment_type">
                                    Employment Type *
                                </label>
                                <select id="employment_type" name="employment_type" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="">Select Type</option>
                                    <option value="full-time">Full Time</option>
                                    <option value="part-time">Part Time</option>
                                    <option value="contract">Contract</option>
                                </select>
                                <span class="text-red-500 text-sm hidden" id="employmentTypeError">Employment Type is required</span>
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="hire_date">
                                    Hire Date *
                                </label>
                                <input type="date" id="hire_date" name="hire_date" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                <span class="text-red-500 text-sm hidden" id="hireDateError">Hire Date is required</span>
                            </div>

                            <div class="field-container hidden" id="contractEndDateContainer">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="contract_end_date">
                                    Contract End Date *
                                </label>
                                <input type="date" id="contract_end_date" name="contract_end_date" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="status">
                                    Status *
                                </label>
                                <select id="status" name="status" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="terminated">Terminated</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Compensation & Compliance Column -->
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Compensation & Compliance</h4>
                            
                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="base_salary">
                                    Base Salary (TZS) *
                                </label>
                                <input type="number" id="base_salary" name="base_salary" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="500000" required min="0" step="0.01">
                                <span class="text-red-500 text-sm hidden" id="baseSalaryError">Base Salary is required</span>
                            </div>
                            
                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="allowances">
                                    Allowances
                                </label>
                                <select name="allowances[]" multiple class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" style="min-height: 120px;">
                                    @foreach($allowances as $allowance)
                                        <option value="{{ $allowance->id }}">
                                            {{ $allowance->name }} (TZS {{ number_format($allowance->amount, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple allowances</p>
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="bank_name">
                                    Bank Name
                                </label>
                                <select id="bank_name" name="bank_name" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Bank</option>
                                    @foreach($banks as $bank)
                                        <option value="{{ $bank->name }}">{{ $bank->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="account_number">
                                    Account Number
                                </label>
                                <input type="text" id="account_number" name="account_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="1234567890">
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="nssf_number">
                                    NSSF Number
                                </label>
                                <input type="text" id="nssf_number" name="nssf_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="NSSF-123456">
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="nhif_number">
                                    NHIF Number
                                </label>
                                <input type="text" id="nhif_number" name="nhif_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="NHIF-123456">
                            </div>

                            <div class="field-container">
                                <label class="block text-gray-700 text-sm font-medium mb-2" for="tin_number">
                                    TIN Number
                                </label>
                                <input type="text" id="tin_number" name="tin_number" class="w-full min-w-[10rem] p-2.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="TIN-123456789">
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center" onclick="closeModal('addEmployeeModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center">
                            <i class="fas fa-check mr-2"></i> Add Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Run Payroll Modal -->
    <div id="runPayrollModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="runPayrollModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-purple-50 to-purple-100 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-purple-600 flex items-center" id="runPayrollModalTitle">
                        <i class="fas fa-calculator mr-2"></i> Run Payroll
                    </h3>
                    <button type="button" onclick="closeModal('runPayrollModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="runPayrollForm" action="{{ route('payroll.run') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="payroll_period" class="block text-gray-600 text-sm font-medium mb-1">Payroll Period</label>
                            <input type="text" id="payroll_period" name="payroll_period" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200" placeholder="Select month and year" required readonly>
                        </div>
                        <div>
                            <label for="employee_selection" class="block text-gray-600 text-sm font-medium mb-1">Employee Selection</label>
                            <select id="employee_selection" name="employee_selection" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200" required onchange="toggleEmployeeSelection()">
                                <option value="all">All Active Employees</option>
                                <option value="single">Single Employee</option>
                                <option value="multiple">Multiple Employees</option>
                            </select>
                        </div>
                        <div id="employee_id_single" class="hidden">
                            <label for="employee_id_select" class="block text-gray-600 text-sm font-medium mb-1">Select Employee</label>
                            <select id="employee_id_select" name="employee_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200">
                                <option value="">Select an employee</option>
                                @foreach($employees->where('status', 'active') as $employee)
                                    <option value="{{ $employee->employee_id }}">{{ $employee->name }} ({{ $employee->department ?? 'N/A' }} - {{ $employee->employee_id ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div id="employee_id_multiple" class="hidden">
                            <label for="employee_ids_select" class="block text-gray-600 text-sm font-medium mb-1">Select Employees</label>
                            <select id="employee_ids_select" name="employee_ids[]" multiple class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200" size="6">
                                @foreach($employees->where('status', 'active') as $employee)
                                    <option value="{{ $employee->employee_id }}">{{ $employee->name }} ({{ $employee->department ?? 'N/A' }} - {{ $employee->employee_id ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple employees</p>
                        </div>
                        <div>
                            <label for="nssf_rate" class="block text-gray-600 text-sm font-medium mb-1">NSSF Rate (%)</label>
                            <input type="number" id="nssf_rate" name="nssf_rate" step="0.1" value="{{ $settings['nssf_employee_rate'] ?? 10.0 }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200" required>
                        </div>
                        <div>
                            <label for="nhif_rate" class="block text-gray-600 text-sm font-medium mb-1">NHIF Rate (%)</label>
                            <input type="number" id="nhif_rate" name="nhif_rate" step="0.1" value="{{ $settings['nhif_employee_rate'] ?? 3.0 }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200" required>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('runPayrollModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 focus:ring-4 focus:ring-purple-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-calculator mr-2"></i> Run Payroll
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Generate Report Modal -->
    <div id="generateReportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="generateReportModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-green-100 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="generateReportModalTitle">
                        <i class="fas fa-file-pdf mr-2"></i> Generate Report
                    </h3>
                    <button type="button" onclick="closeModal('generateReportModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="generateReportForm" action="{{ route('reports.generate') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="report_type" class="block text-gray-600 text-sm font-medium mb-1">Report Type</label>
                            <select name="report_type" id="report_type" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200 text-gray-900">
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
                        </div>
                        <div>
                            <label for="report_period" class="block text-gray-600 text-sm font-medium mb-1">Report Period</label>
                            <input type="text" name="report_period" id="report_period" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200 text-gray-900" placeholder="Select period">
                        </div>
                        <div>
                            <label for="employee_id" class="block text-gray-600 text-sm font-medium mb-1">Specific Employee (Optional)</label>
                            <select name="employee_id" id="employee_id" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200 text-gray-900">
                                <option value="">All Employees</option>
                                @foreach($employees ?? [] as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="export_format" class="block text-gray-600 text-sm font-medium mb-1">Export Format</label>
                            <select name="export_format" id="export_format" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200 text-gray-900">
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('generateReportModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-download mr-2"></i> Generate
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Compliance Task Modal -->
    <div id="addComplianceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="addComplianceModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-yellow-600 flex items-center" id="addComplianceModalTitle">
                        <i class="fas fa-shield-alt mr-2"></i> Add Compliance Task
                    </h3>
                    <button type="button" onclick="closeModal('addComplianceModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="addComplianceForm" action="{{ route('compliance.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="compliance_type" class="block text-gray-600 text-sm font-medium mb-1">Compliance Type</label>
                            <select name="compliance_type" id="compliance_type" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200 text-gray-900">
                                <option value="">Select a compliance type</option>
                                <option value="tax">Tax Filing</option>
                                <option value="nssf">NSSF Submission</option>
                                <option value="nhif">NHIF Submission</option>
                                <option value="wcf">WCF Submission</option>
                                <option value="sdl">SDL Submission</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label for="compliance_employee_id" class="block text-gray-600 text-sm font-medium mb-1">Employee (Optional)</label>
                            <select name="employee_id" id="compliance_employee_id" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200 text-gray-900">
                                <option value="">All Employees</option>
                                @foreach($employees ?? [] as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="due_date" class="block text-gray-600 text-sm font-medium mb-1">Due Date</label>
                            <input type="date" name="due_date" id="due_date" required class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200 text-gray-900">
                        </div>
                        <div>
                            <label for="amount" class="block text-gray-600 text-sm font-medium mb-1">Amount (Optional)</label>
                            <input type="number" name="amount" id="amount" step="0.01" min="0" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200 text-gray-900" placeholder="0.00">
                        </div>
                        <div>
                            <label for="details" class="block text-gray-600 text-sm font-medium mb-1">Details</label>
                            <textarea name="details" id="details" rows="3" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200 text-gray-900" placeholder="Enter compliance task details..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center" onclick="closeModal('addComplianceModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 focus:ring-4 focus:ring-yellow-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Add Task
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Success/Error Modals -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
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

    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
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

    <!-- Spinner -->
    <div id="spinner" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-60 hidden">
        <div class="w-16 h-16 border-4 border-t-green-600 border-gray-200 rounded-full animate-spin"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        // Quick Actions Handler
        document.addEventListener('DOMContentLoaded', function() {
            // Quick Action Buttons
            const quickActionButtons = document.querySelectorAll('.quick-action-btn');
            quickActionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.dataset.action;
                    handleQuickAction(action);
                });
            });

            // Form submission handlers
            const addEmployeeForm = document.getElementById('addEmployeeForm');
            if (addEmployeeForm) {
                addEmployeeForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitForm(this, 'Employee added successfully!');
                });
            }

            const runPayrollForm = document.getElementById('runPayrollForm');
            if (runPayrollForm) {
                runPayrollForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitForm(this, 'Payroll processed successfully!');
                });
            }

            const generateReportForm = document.getElementById('generateReportForm');
            if (generateReportForm) {
                generateReportForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitForm(this, 'Report generated successfully!');
                });
            }

            const addComplianceForm = document.getElementById('addComplianceForm');
            if (addComplianceForm) {
                addComplianceForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitForm(this, 'Compliance task added successfully!');
                });
            }

            // Initialize date pickers
            initDatePickers();
        });

        // Handle Quick Actions
        function handleQuickAction(action) {
            switch(action) {
                case 'add_employee':
                    openModal('addEmployeeModal');
                    break;
                case 'run_payroll':
                    openModal('runPayrollModal');
                    break;
                case 'generate_reports':
                    openModal('generateReportModal');
                    break;
                case 'add_compliance':
                    openModal('addComplianceModal');
                    break;
                default:
                    console.log('Unknown action:', action);
            }
        }

        // Improved Modal functions
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
                
                const modalContent = modal.querySelector('.modal-content');
                if (modalContent) {
                    setTimeout(() => {
                        modalContent.classList.remove('scale-95');
                        modalContent.classList.add('scale-100');
                    }, 10);
                }
                
                // Initialize specific modal features
                if (modalId === 'runPayrollModal') {
                    initPayrollDatePicker();
                } else if (modalId === 'generateReportModal') {
                    initReportDatePicker();
                } else if (modalId === 'addEmployeeModal') {
                    handleEmploymentTypeChange();
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
                }
                
                setTimeout(() => {
                    modal.classList.remove('flex');
                    modal.classList.add('hidden');
                    document.body.style.overflow = 'auto';
                }, 300);
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed') && event.target.id.includes('Modal')) {
                closeModal(event.target.id);
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const openModals = document.querySelectorAll('.fixed.flex');
                openModals.forEach(modal => {
                    if (modal.id.includes('Modal')) {
                        closeModal(modal.id);
                    }
                });
            }
        });

        // Date picker initialization
        function initDatePickers() {
            // Payroll period date picker
            flatpickr("#payroll_period", {
                mode: "single",
                dateFormat: "Y-m",
                defaultDate: "today",
                static: true
            });

            // Report period date picker
            flatpickr("#report_period", {
                mode: "single",
                dateFormat: "Y-m",
                defaultDate: "today",
                static: true
            });

            // Due date picker for compliance
            flatpickr("#due_date", {
                minDate: "today",
                dateFormat: "Y-m-d",
                defaultDate: "today"
            });
        }

        function initPayrollDatePicker() {
            flatpickr("#payroll_period", {
                mode: "single",
                dateFormat: "Y-m",
                defaultDate: "today",
                static: true
            });
        }

        function initReportDatePicker() {
            flatpickr("#report_period", {
                mode: "single",
                dateFormat: "Y-m",
                defaultDate: "today",
                static: true
            });
        }

        // Employee selection toggle for payroll
        function toggleEmployeeSelection() {
            const selection = document.getElementById('employee_selection').value;
            const singleDiv = document.getElementById('employee_id_single');
            const multipleDiv = document.getElementById('employee_id_multiple');
            
            singleDiv.classList.add('hidden');
            multipleDiv.classList.add('hidden');
            
            if (selection === 'single') {
                singleDiv.classList.remove('hidden');
            } else if (selection === 'multiple') {
                multipleDiv.classList.remove('hidden');
            }
        }

        // Employment type change handler for Add Employee Modal
        function handleEmploymentTypeChange() {
            const employmentType = document.getElementById('employment_type');
            const contractEndDateContainer = document.getElementById('contractEndDateContainer');

            if (employmentType && contractEndDateContainer) {
                employmentType.addEventListener('change', function() {
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
                
                // Trigger change event on load
                employmentType.dispatchEvent(new Event('change'));
            }
        }

        // Generic form submission function
        function submitForm(form, successMessage) {
            showSpinner();
            
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                hideSpinner();
                if (data.success) {
                    showSuccess(successMessage);
                    form.reset();
                    closeModal(form.id.replace('Form', 'Modal'));
                    
                    // Refresh page if needed
                    if (form.id === 'addEmployeeForm' || form.id === 'runPayrollForm') {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                } else {
                    showError(data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                hideSpinner();
                console.error('Error:', error);
                showError('Failed to process request. Please try again.');
            });
        }

        function showSpinner() {
            document.getElementById('spinner').classList.remove('hidden');
        }

        function hideSpinner() {
            document.getElementById('spinner').classList.add('hidden');
        }

        function showSuccess(message) {
            document.getElementById('successMessage').textContent = message;
            openModal('successModal');
        }

        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            openModal('errorModal');
        }

        // Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('payrollChart').getContext('2d');
            const payrollChart = new Chart(ctx, {
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
                .then(response => response.json())
                .then(data => {
                    payrollChart.data.labels = data.labels;
                    payrollChart.data.datasets[0].data = data.values;
                    payrollChart.update();
                    hideSpinner();
                })
                .catch(error => {
                    console.error('Error updating chart:', error);
                    hideSpinner();
                });
            });
        });
    </script>
@endsection