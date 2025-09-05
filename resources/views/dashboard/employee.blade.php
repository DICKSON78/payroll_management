@extends('layout.global')

@section('title', 'Employee Portal')

@section('header-title')
    Employee Portal
    <span class="payroll-badge text-xs font-semibold px-2 py-1 rounded-full ml-3 bg-green-100 text-green-800">
        <i class="fas fa-bolt mr-1"></i> Employee Access
    </span>
@endsection

@section('header-subtitle')
    Manage your personal details, payslips, and leave balances.
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <a href="#" class="card hover:shadow-md transition-all flex flex-col items-center text-center" onclick="openModal('updateDetailsModal')">
            <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                <i class="fas fa-user-edit text-blue-600 text-xl"></i>
            </div>
            <h4 class="font-medium text-gray-900">Update Details</h4>
            <p class="text-sm text-gray-500 mt-1">Edit personal information</p>
        </a>
        <a href="#" class="card hover:shadow-md transition-all flex flex-col items-center text-center" onclick="openModal('viewPayslipsModal')">
            <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center mb-4">
                <i class="fas fa-file-invoice text-purple-600 text-xl"></i>
            </div>
            <h4 class="font-medium text-gray-900">View Payslips</h4>
            <p class="text-sm text-gray-500 mt-1">Access payslips and tax forms</p>
        </a>
        <a href="#" class="card hover:shadow-md transition-all flex flex-col items-center text-center" onclick="openModal('viewLeaveModal')">
            <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mb-4">
                <i class="fas fa-calendar-alt text-yellow-600 text-xl"></i>
            </div>
            <h4 class="font-medium text-gray-900">Leave Balances</h4>
            <p class="text-sm text-gray-500 mt-1">Check leave status</p>
        </a>
        <a href="#" class="card hover:shadow-md transition-all flex flex-col items-center text-center" onclick="openModal('requestLeaveModal')">
            <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center mb-4">
                <i class="fas fa-calendar-plus text-green-600 text-xl"></i>
            </div>
            <h4 class="font-medium text-gray-900">Request Leave</h4>
            <p class="text-sm text-gray-500 mt-1">Submit a leave request</p>
        </a>
    </div>

    <!-- Update Details Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="updateDetailsModal" role="dialog" aria-labelledby="updateModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center" id="updateModalTitle">
                    <i class="fas fa-user-edit mr-2"></i> Update Personal Details
                </h3>
            </div>
            <div class="p-6">
                <form id="updateDetailsForm" action="{{ route('employee.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" id="phone" value="{{ $employee->phone }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('phone')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" name="address" id="address" value="{{ $employee->address }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700">Bank Name</label>
                            <input type="text" name="bank_name" id="bank_name" value="{{ $employee->bank_name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('bank_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="account_number" class="block text-sm font-medium text-gray-700">Account Number</label>
                            <input type="text" name="account_number" id="account_number" value="{{ $employee->account_number }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('account_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" class="btn-primary bg-gray-500 hover:bg-gray-600" onclick="closeModal('updateDetailsModal')">Cancel</button>
                        <button type="submit" class="btn-primary">Update Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Payslips Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="viewPayslipsModal" role="dialog" aria-labelledby="payslipsModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-4xl modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center" id="payslipsModalTitle">
                    <i class="fas fa-file-invoice mr-2"></i> Your Payslips
                </h3>
            </div>
            <div class="p-6">
                <table class="w-full text-left">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 text-sm font-medium text-gray-700">Period</th>
                            <th class="py-2 px-4 text-sm font-medium text-gray-700">Gross Salary</th>
                            <th class="py-2 px-4 text-sm font-medium text-gray-700">Net Salary</th>
                            <th class="py-2 px-4 text-sm font-medium text-gray-700">Status</th>
                            <th class="py-2 px-4 text-sm font-medium text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payslips as $payslip)
                            <tr>
                                <td class="py-2 px-4 text-sm text-gray-600">{{ $payslip->period }}</td>
                                <td class="py-2 px-4 text-sm text-gray-600">{{ number_format($payslip->gross_salary, 2) }}</td>
                                <td class="py-2 px-4 text-sm text-gray-600">{{ number_format($payslip->net_salary, 2) }}</td>
                                <td class="py-2 px-4 text-sm">
                                    <span class="status-badge {{ $payslip->status == 'Processed' ? 'status-paid' : 'status-pending' }}">
                                        {{ $payslip->status }}
                                    </span>
                                </td>
                                <td class="py-2 px-4 text-sm">
                                    <a href="{{ route('payslip.download', $payslip->id) }}" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-6 flex justify-end">
                    <button type="button" class="btn-primary bg-gray-500 hover:bg-gray-600" onclick="closeModal('viewPayslipsModal')">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Leave Balances Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="viewLeaveModal" role="dialog" aria-labelledby="leaveModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center" id="leaveModalTitle">
                    <i class="fas fa-calendar-alt mr-2"></i> Leave Balances
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="card">
                        <h4 class="font-medium text-gray-900">Sick Leave</h4>
                        <p class="text-sm text-gray-500 mt-1">{{ $leaveBalances['sick_leave_balance'] }} days remaining</p>
                    </div>
                    <div class="card">
                        <h4 class="font-medium text-gray-900">Vacation Leave</h4>
                        <p class="text-sm text-gray-500 mt-1">{{ $leaveBalances['vacation_leave_balance'] }} days remaining</p>
                    </div>
                    <div class="card">
                        <h4 class="font-medium text-gray-900">Maternity Leave</h4>
                        <p class="text-sm text-gray-500 mt-1">{{ $leaveBalances['maternity_leave_balance'] }} days remaining</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="button" class="btn-primary bg-gray-500 hover:bg-gray-600" onclick="closeModal('viewLeaveModal')">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Leave Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="requestLeaveModal" role="dialog" aria-labelledby="requestLeaveModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center" id="requestLeaveModalTitle">
                    <i class="fas fa-calendar-plus mr-2"></i> Request Leave
                </h3>
            </div>
            <div class="p-6">
                <form id="requestLeaveForm" action="{{ route('leave.request') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="leave_type" class="block text-sm font-medium text-gray-700">Leave Type</label>
                            <select name="leave_type" id="leave_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                <option value="Sick">Sick Leave</option>
                                <option value="Vacation">Vacation Leave</option>
                                <option value="Maternity">Maternity Leave</option>
                            </select>
                            @error('leave_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('start_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('end_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" class="btn-primary bg-gray-500 hover:bg-gray-600" onclick="closeModal('requestLeaveModal')">Cancel</button>
                        <button type="submit" class="btn-primary">Submit Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection