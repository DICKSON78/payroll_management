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
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer quick-action-btn" data-action="quick_add_employee">
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
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer quick-action-btn" data-action="quick_run_payroll">
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
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer quick-action-btn" data-action="quick_generate_payslip">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                            <i class="fas fa-file-pdf text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Generate Payslip</h4>
                            <p class="text-sm text-gray-500">Individual payslip</p>
                        </div>
                    </div>
                </div>

                <!-- Add Compliance Task -->
                <div class="card bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 p-6 cursor-pointer quick-action-btn" data-action="quick_add_compliance">
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

    <!-- Quick Add Employee Modal -->
    <div id="quickAddEmployeeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-blue-50 to-blue-100 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-blue-600 flex items-center">
                        <i class="fas fa-user-plus mr-2"></i> Quick Add Employee
                    </h3>
                    <button type="button" onclick="closeModal('quickAddEmployeeModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            <form id="quickAddEmployeeForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="quick_add_employee">
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="quick_name" class="block text-gray-600 text-sm font-medium mb-1">Full Name *</label>
                            <input type="text" id="quick_name" name="name" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-all duration-200" placeholder="John Doe" required>
                        </div>
                        <div>
                            <label for="quick_email" class="block text-gray-600 text-sm font-medium mb-1">Email *</label>
                            <input type="email" id="quick_email" name="email" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-all duration-200" placeholder="john@company.com" required>
                        </div>
                        <div>
                            <label for="quick_position" class="block text-gray-600 text-sm font-medium mb-1">Position *</label>
                            <input type="text" id="quick_position" name="position" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-all duration-200" placeholder="Software Developer" required>
                        </div>
                        <div>
                            <label for="quick_department" class="block text-gray-600 text-sm font-medium mb-1">Department *</label>
                            <select id="quick_department" name="department" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-all duration-200" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="quick_base_salary" class="block text-gray-600 text-sm font-medium mb-1">Base Salary (TZS) *</label>
                            <input type="number" id="quick_base_salary" name="base_salary" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 transition-all duration-200" placeholder="500000" required min="0">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center" onclick="closeModal('quickAddEmployeeModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center">
                            <i class="fas fa-check mr-2"></i> Add Employee
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Run Payroll Modal -->
    <div id="quickRunPayrollModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-purple-50 to-purple-100 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-purple-600 flex items-center">
                        <i class="fas fa-calculator mr-2"></i> Quick Run Payroll
                    </h3>
                    <button type="button" onclick="closeModal('quickRunPayrollModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            <form id="quickRunPayrollForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="quick_run_payroll">
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="quick_period" class="block text-gray-600 text-sm font-medium mb-1">Payroll Period</label>
                            <input type="text" id="quick_period" name="period" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200" placeholder="Select month and year" required readonly>
                        </div>
                        <div>
                            <label for="quick_employee_selection" class="block text-gray-600 text-sm font-medium mb-1">Employee Selection</label>
                            <select id="quick_employee_selection" name="employee_selection" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200" required>
                                <option value="all">All Active Employees</option>
                                <option value="single">Single Employee</option>
                            </select>
                        </div>
                        <div id="quick_employee_id_container" class="hidden">
                            <label for="quick_employee_id" class="block text-gray-600 text-sm font-medium mb-1">Select Employee</label>
                            <select id="quick_employee_id" name="employee_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200">
                                <option value="">Select an employee</option>
                                @foreach($employees->where('status', 'active') as $employee)
                                    <option value="{{ $employee->employee_id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="quick_nssf_rate" class="block text-gray-600 text-sm font-medium mb-1">NSSF Rate (%)</label>
                            <input type="number" id="quick_nssf_rate" name="nssf_rate" step="0.1" value="{{ $settings['nssf_employee_rate'] ?? 10.0 }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200" required>
                        </div>
                        <div>
                            <label for="quick_nhif_rate" class="block text-gray-600 text-sm font-medium mb-1">NHIF Rate (%)</label>
                            <input type="number" id="quick_nhif_rate" name="nhif_rate" step="0.1" value="{{ $settings['nhif_employee_rate'] ?? 3.0 }}" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5 transition-all duration-200" required>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center" onclick="closeModal('quickRunPayrollModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 flex items-center">
                            <i class="fas fa-calculator mr-2"></i> Run Payroll
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Generate Payslip Modal -->
    <div id="quickGeneratePayslipModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-green-100 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-file-invoice mr-2"></i> Quick Generate Payslip
                    </h3>
                    <button type="button" onclick="closeModal('quickGeneratePayslipModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            <form id="quickGeneratePayslipForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="quick_generate_payslip">
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="quick_payslip_employee_id" class="block text-gray-600 text-sm font-medium mb-1">Employee *</label>
                            <select id="quick_payslip_employee_id" name="employee_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" required>
                                <option value="">Select an employee</option>
                                @foreach($employees->where('status', 'active') as $employee)
                                    <option value="{{ $employee->employee_id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="quick_payslip_period" class="block text-gray-600 text-sm font-medium mb-1">Period *</label>
                            <input type="text" id="quick_payslip_period" name="period" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full p-2.5 transition-all duration-200" placeholder="Select month and year" required readonly>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center" onclick="closeModal('quickGeneratePayslipModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center">
                            <i class="fas fa-file-invoice mr-2"></i> Generate Payslip
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Add Compliance Modal -->
    <div id="quickAddComplianceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-yellow-50 to-yellow-100 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-yellow-600 flex items-center">
                        <i class="fas fa-shield-alt mr-2"></i> Quick Add Compliance Task
                    </h3>
                    <button type="button" onclick="closeModal('quickAddComplianceModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition-all duration-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
            </div>
            <form id="quickAddComplianceForm" method="POST">
                @csrf
                <input type="hidden" name="action" value="quick_add_compliance">
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label for="quick_compliance_type" class="block text-gray-600 text-sm font-medium mb-1">Compliance Type *</label>
                            <select id="quick_compliance_type" name="type" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200" required>
                                <option value="">Select a compliance type</option>
                                <option value="PAYE">PAYE</option>
                                <option value="NSSF">NSSF</option>
                                <option value="NHIF">NHIF</option>
                                <option value="WCF">WCF</option>
                                <option value="SDL">SDL</option>
                            </select>
                        </div>
                        <div>
                            <label for="quick_compliance_employee_id" class="block text-gray-600 text-sm font-medium mb-1">Employee (Optional)</label>
                            <select id="quick_compliance_employee_id" name="employee_id" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200">
                                <option value="">Select an employee (optional)</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->employee_id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="quick_due_date" class="block text-gray-600 text-sm font-medium mb-1">Due Date *</label>
                            <input type="date" id="quick_due_date" name="due_date" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200" required>
                        </div>
                        <div>
                            <label for="quick_amount" class="block text-gray-600 text-sm font-medium mb-1">Amount (Optional)</label>
                            <input type="number" id="quick_amount" name="amount" step="0.01" min="0" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200" placeholder="0.00">
                        </div>
                        <div>
                            <label for="quick_details" class="block text-gray-600 text-sm font-medium mb-1">Details</label>
                            <textarea id="quick_details" name="details" rows="3" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 block w-full p-2.5 transition-all duration-200" placeholder="Enter compliance task details..."></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 flex items-center" onclick="closeModal('quickAddComplianceModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 flex items-center">
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
    // Global variables
    let payrollChart = null;
    let isRefreshing = false;

    // Quick Actions Handler
    document.addEventListener('DOMContentLoaded', function() {
        initializeDashboard();
    });

    function initializeDashboard() {
        console.log('🚀 Initializing Dashboard...');
        
        // Quick Action Buttons
        const quickActionButtons = document.querySelectorAll('.quick-action-btn');
        quickActionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const action = this.dataset.action;
                console.log('Quick action clicked:', action);
                handleQuickAction(action);
            });
        });

        // Form submission handlers
        setupFormHandlers();

        // Initialize date pickers
        initDatePickers();
        
        // Initialize chart
        initChart();

        console.log('✅ Dashboard initialized successfully');
    }

    function setupFormHandlers() {
        // Quick Add Employee Form
        const quickAddEmployeeForm = document.getElementById('quickAddEmployeeForm');
        if (quickAddEmployeeForm) {
            quickAddEmployeeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitQuickActionForm(this);
            });
        }

        // Quick Run Payroll Form
        const quickRunPayrollForm = document.getElementById('quickRunPayrollForm');
        if (quickRunPayrollForm) {
            quickRunPayrollForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitQuickActionForm(this);
            });
        }

        // Quick Generate Payslip Form
        const quickGeneratePayslipForm = document.getElementById('quickGeneratePayslipForm');
        if (quickGeneratePayslipForm) {
            quickGeneratePayslipForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitQuickActionForm(this);
            });
        }

        // Quick Add Compliance Form
        const quickAddComplianceForm = document.getElementById('quickAddComplianceForm');
        if (quickAddComplianceForm) {
            quickAddComplianceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitQuickActionForm(this);
            });
        }

        // Employee selection toggle for quick payroll
        const quickEmployeeSelection = document.getElementById('quick_employee_selection');
        if (quickEmployeeSelection) {
            quickEmployeeSelection.addEventListener('change', function() {
                const employeeContainer = document.getElementById('quick_employee_id_container');
                if (this.value === 'single') {
                    employeeContainer.classList.remove('hidden');
                } else {
                    employeeContainer.classList.add('hidden');
                }
            });
        }
    }

    // Handle Quick Actions
    function handleQuickAction(action) {
        switch(action) {
            case 'quick_add_employee':
                openModal('quickAddEmployeeModal');
                break;
            case 'quick_run_payroll':
                openModal('quickRunPayrollModal');
                break;
            case 'quick_generate_payslip':
                openModal('quickGeneratePayslipModal');
                break;
            case 'quick_add_compliance':
                openModal('quickAddComplianceModal');
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
            if (modalId === 'quickRunPayrollModal') {
                initQuickPayrollDatePicker();
                // Reset employee selection
                setTimeout(() => {
                    const selection = document.getElementById('quick_employee_selection');
                    if (selection) {
                        selection.value = 'all';
                        const employeeContainer = document.getElementById('quick_employee_id_container');
                        employeeContainer.classList.add('hidden');
                    }
                }, 100);
            } else if (modalId === 'quickGeneratePayslipModal') {
                initQuickPayslipDatePicker();
            } else if (modalId === 'quickAddComplianceModal') {
                // Set default due date to today
                const dueDate = document.getElementById('quick_due_date');
                if (dueDate) {
                    dueDate.value = new Date().toISOString().split('T')[0];
                }
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

    // Date picker initialization
    function initDatePickers() {
        // Quick payroll period date picker
        if (document.getElementById('quick_period')) {
            flatpickr("#quick_period", {
                mode: "single",
                dateFormat: "Y-m",
                defaultDate: "today",
                static: true
            });
        }

        // Quick payslip period date picker
        if (document.getElementById('quick_payslip_period')) {
            flatpickr("#quick_payslip_period", {
                mode: "single",
                dateFormat: "Y-m",
                defaultDate: "today",
                static: true
            });
        }
    }

    function initQuickPayrollDatePicker() {
        if (document.getElementById('quick_period')) {
            flatpickr("#quick_period", {
                mode: "single",
                dateFormat: "Y-m",
                defaultDate: "today",
                static: true
            });
        }
    }

    function initQuickPayslipDatePicker() {
        if (document.getElementById('quick_payslip_period')) {
            flatpickr("#quick_payslip_period", {
                mode: "single",
                dateFormat: "Y-m",
                defaultDate: "today",
                static: true
            });
        }
    }

    // Quick Action Form Submission
    function submitQuickActionForm(form) {
        if (isRefreshing) {
            console.log('⚠️ Already refreshing, skipping duplicate request');
            return;
        }

        console.log('📤 Submitting quick action form...');
        showSpinner();
        
        const formData = new FormData(form);
        
        fetch('{{ route("dashboard.quick-actions") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            console.log('📥 Response status:', response.status);
            
            if (!response.ok) {
                return response.text().then(text => {
                    let errorMessage = 'Network response was not ok: ' + response.status;
                    try {
                        const errorData = JSON.parse(text);
                        errorMessage = errorData.message || errorMessage;
                    } catch (e) {
                        if (text.includes('CSRF token mismatch')) {
                            errorMessage = 'Session expired. Please refresh the page.';
                        }
                    }
                    throw new Error(errorMessage);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('✅ Success response:', data);
            hideSpinner();
            isRefreshing = false;
            
            if (data.success) {
                showSuccess(data.message);
                form.reset();
                closeModal(form.id.replace('Form', 'Modal'));
                
                // 🔄 AUTOMATIC REAL-TIME REFRESH - IMMEDIATE UPDATE
                refreshDashboardData();
                
            } else {
                showError(data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            console.error('❌ Fetch error:', error);
            hideSpinner();
            isRefreshing = false;
            showError(error.message || 'Failed to process request. Please try again.');
        });
    }

    // Function to refresh dashboard data
    function refreshDashboardData() {
        if (isRefreshing) {
            console.log('⚠️ Refresh already in progress');
            return;
        }

        isRefreshing = true;
        console.log('🔄 Refreshing dashboard data...');
        
        // Show refreshing indicator
        showRefreshingIndicator();

        fetch('{{ route("dashboard.refresh-data") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('📊 Dashboard data received:', data);
            
            if (data.success) {
                // Update dashboard statistics with animation
                updateDashboardStats(data.data);
                
                // Update recent payslips
                updateRecentPayslips(data.data.recentPayslips);
                
                // Refresh chart data as well
                refreshChartData();
                
                console.log('✅ Dashboard data refreshed successfully');
            } else {
                console.error('❌ Failed to refresh dashboard data:', data.message);
            }
            hideRefreshingIndicator();
            isRefreshing = false;
        })
        .catch(error => {
            console.error('❌ Error refreshing dashboard data:', error);
            hideRefreshingIndicator();
            isRefreshing = false;
        });
    }

    // Function to update dashboard statistics with smooth animations
    function updateDashboardStats(data) {
        console.log('🎯 Updating dashboard stats:', data);
        
        // Update total employees with counting animation
        animateCounter('.card:nth-child(1) .text-3xl', data.totalEmployees);
        updateCardGrowth(1, data.employeeGrowth);
        
        // Update monthly payroll with counting animation
        animateCounter('.card:nth-child(2) .text-3xl', data.monthlyPayroll, true);
        updateCardGrowth(2, data.payrollGrowth);
        
        // Update payslips generated with counting animation
        animateCounter('.card:nth-child(3) .text-3xl', data.payslipsGenerated);
        
        // Update pending tasks with counting animation
        animateCounter('.card:nth-child(4) .text-3xl', data.pendingTasks);
    }

    // Animated counter function
    function animateCounter(selector, targetValue, isCurrency = false) {
        const element = document.querySelector(selector);
        if (!element) return;

        const currentValue = parseInt(element.textContent.replace(/[^0-9]/g, '')) || 0;
        const duration = 1000;
        const steps = 60;
        const stepValue = (targetValue - currentValue) / steps;
        let currentStep = 0;

        const timer = setInterval(() => {
            currentStep++;
            const newValue = currentValue + (stepValue * currentStep);
            
            if (currentStep >= steps) {
                if (isCurrency) {
                    element.textContent = `TZS ${formatNumber(targetValue)}`;
                } else {
                    element.textContent = Math.round(targetValue);
                }
                clearInterval(timer);
            } else {
                if (isCurrency) {
                    element.textContent = `TZS ${formatNumber(Math.round(newValue))}`;
                } else {
                    element.textContent = Math.round(newValue);
                }
            }
        }, duration / steps);
    }

    function updateCardGrowth(cardIndex, growth) {
        const card = document.querySelector(`.card:nth-child(${cardIndex})`);
        if (card) {
            const growthElement = card.querySelector('.text-green-600 + span');
            if (growthElement) {
                growthElement.innerHTML = `${growth}% from last month`;
                
                // Add visual feedback for growth changes
                if (growth > 0) {
                    growthElement.classList.remove('text-red-600', 'text-gray-600');
                    growthElement.classList.add('text-green-600');
                } else if (growth < 0) {
                    growthElement.classList.remove('text-green-600', 'text-gray-600');
                    growthElement.classList.add('text-red-600');
                } else {
                    growthElement.classList.remove('text-green-600', 'text-red-600');
                    growthElement.classList.add('text-gray-600');
                }
            }
        }
    }

    // Function to update recent payslips with smooth transition
    function updateRecentPayslips(payslips) {
        const payslipsContainer = document.querySelector('.space-y-4');
        if (!payslipsContainer) return;
        
        // Add fade out effect
        payslipsContainer.style.opacity = '0.5';
        payslipsContainer.style.transition = 'opacity 0.3s ease';
        
        setTimeout(() => {
            if (payslips.length === 0) {
                payslipsContainer.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-file-invoice text-gray-300 text-4xl mb-3"></i>
                        <p class="text-gray-500">No payslips generated yet</p>
                    </div>
                `;
            } else {
                let payslipsHTML = '';
                payslips.forEach(payslip => {
                    const statusClass = payslip.status == 'Generated' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800';
                    payslipsHTML += `
                        <div class="flex items-start animate-fade-in">
                            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-file-invoice"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-medium text-gray-900">${payslip.employee_name}</p>
                                <p class="text-sm text-gray-500">${payslip.department} • ${payslip.position}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-green-600">TZS ${formatNumber(payslip.net_salary)}</p>
                                <span class="px-2 py-1 rounded-full text-xs font-medium ${statusClass}">${payslip.status}</span>
                            </div>
                        </div>
                    `;
                });
                
                payslipsContainer.innerHTML = payslipsHTML;
            }
            
            // Fade back in
            setTimeout(() => {
                payslipsContainer.style.opacity = '1';
            }, 50);
        }, 300);
    }

    // Function to refresh chart data
    function refreshChartData() {
        const periodSelect = document.getElementById('chartPeriod');
        if (!periodSelect || !payrollChart) return;
        
        const period = periodSelect.value;
        fetch(`{{ route("payroll.data") }}?period=${period}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.labels && data.values) {
                // Smooth chart update
                payrollChart.data.labels = data.labels;
                payrollChart.data.datasets[0].data = data.values;
                payrollChart.update('active');
                console.log('📈 Chart updated successfully');
            }
        })
        .catch(error => {
            console.error('Error updating chart:', error);
        });
    }

    // Chart initialization
    function initChart() {
        const ctx = document.getElementById('payrollChart');
        if (!ctx) return;
        
        payrollChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Payroll (TZS Millions)',
                    data: @json($chartData),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
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
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Period'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#374151',
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                animation: {
                    duration: 1000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Chart period change handler
        const chartPeriodSelect = document.getElementById('chartPeriod');
        if (chartPeriodSelect) {
            chartPeriodSelect.addEventListener('change', function() {
                refreshChartData();
            });
        }
    }

    // Visual feedback functions
    function showRefreshingIndicator() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.classList.add('opacity-75');
            card.style.transition = 'opacity 0.3s ease';
        });
    }

    function hideRefreshingIndicator() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.classList.remove('opacity-75');
        });
    }

    // Helper function to format numbers
    function formatNumber(number) {
        return new Intl.NumberFormat().format(Math.round(number));
    }

    function showSpinner() {
        const spinner = document.getElementById('spinner');
        if (spinner) {
            spinner.classList.remove('hidden');
            spinner.classList.add('flex');
        }
    }

    function hideSpinner() {
        const spinner = document.getElementById('spinner');
        if (spinner) {
            spinner.classList.remove('flex');
            spinner.classList.add('hidden');
        }
    }

    function showSuccess(message) {
        const successMessage = document.getElementById('successMessage');
        const successModal = document.getElementById('successModal');
        if (successMessage && successModal) {
            successMessage.textContent = message;
            openModal('successModal');
        }
    }

    function showError(message) {
        const errorMessage = document.getElementById('errorMessage');
        const errorModal = document.getElementById('errorModal');
        if (errorMessage && errorModal) {
            errorMessage.textContent = message;
            openModal('errorModal');
        }
    }

    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    `;
    document.head.appendChild(style);

    console.log('🎉 Dashboard JavaScript loaded successfully!');
</script>
@endsection