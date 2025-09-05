@extends('layout.global')

@section('title', 'Attendance')

@section('header-title')
    Attendance Management
    <span class="payroll-badge text-xs font-semibold px-2 py-1 rounded-full ml-3 bg-green-100 text-green-800">
        <i class="fas fa-bolt mr-1"></i> Track Time
    </span>
@endsection

@section('header-subtitle')
    Log and manage employee attendance and leave requests.
@endsection

@section('content')
    <div class="mb-8 flex justify-between items-center">
        <h3 class="text-xl font-semibold text-gray-800">Attendance Records</h3>
        <div class="space-x-3">
            <button class="btn-primary" onclick="openModal('addAttendanceModal')">
                <i class="fas fa-plus mr-2"></i> Log Attendance
            </button>
            <button class="btn-primary" onclick="openModal('requestLeaveModal')">
                <i class="fas fa-calendar-plus mr-2"></i> Request Leave
            </button>
            <form action="{{ route('attendance.export') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn-primary">
                    <i class="fas fa-download mr-2"></i> Export CSV
                </button>
            </form>
        </div>
    </div>

    <div class="card">
        <table class="w-full text-left">
            <thead>
                <tr>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Employee</th>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Date</th>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Hours Worked</th>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Overtime Hours</th>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Status</th>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                    <tr>
                        <td class="py-2 px-4 text-sm text-gray-600">{{ $attendance->employee->name }}</td>
                        <td class="py-2 px-4 text-sm text-gray-600">{{ $attendance->date->format('d/m/Y') }}</td>
                        <td class="py-2 px-4 text-sm text-gray-600">{{ $attendance->hours_worked }}</td>
                        <td class="py-2 px-4 text-sm text-gray-600">{{ $attendance->overtime_hours }}</td>
                        <td class="py-2 px-4 text-sm">
                            <span class="status-badge {{ $attendance->status == 'Processed' ? 'status-paid' : 'status-pending' }}">
                                {{ $attendance->status }}
                            </span>
                        </td>
                        <td class="py-2 px-4 text-sm">
                            <button onclick="editAttendance({{ $attendance->id }})" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Add Attendance Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="addAttendanceModal" role="dialog" aria-labelledby="addAttendanceModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center" id="addAttendanceModalTitle">
                    <i class="fas fa-plus mr-2"></i> Log Attendance
                </h3>
            </div>
            <div class="p-6">
                <form id="addAttendanceForm" action="{{ route('attendance.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                            <select name="employee_id" id="employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" id="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="hours_worked" class="block text-sm font-medium text-gray-700">Hours Worked</label>
                            <input type="number" name="hours_worked" id="hours_worked" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('hours_worked')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="overtime_hours" class="block text-sm font-medium text-gray-700">Overtime Hours</label>
                            <input type="number" name="overtime_hours" id="overtime_hours" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('overtime_hours')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" class="btn-primary bg-gray-500 hover:bg-gray-600" onclick="closeModal('addAttendanceModal')">Cancel</button>
                        <button type="submit" class="btn-primary">Log Attendance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="editAttendanceModal" role="dialog" aria-labelledby="editAttendanceModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center" id="editAttendanceModalTitle">
                    <i class="fas fa-edit mr-2"></i> Edit Attendance
                </h3>
            </div>
            <div class="p-6">
                <form id="editAttendanceForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_employee_id" class="block text-sm font-medium text-gray-700">Employee</label>
                            <select name="employee_id" id="edit_employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="edit_date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input type="date" name="date" id="edit_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="edit_hours_worked" class="block text-sm font-medium text-gray-700">Hours Worked</label>
                            <input type="number" name="hours_worked" id="edit_hours_worked" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('hours_worked')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="edit_overtime_hours" class="block text-sm font-medium text-gray-700">Overtime Hours</label>
                            <input type="number" name="overtime_hours" id="edit_overtime_hours" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            @error('overtime_hours')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" class="btn-primary bg-gray-500 hover:bg-gray-600" onclick="closeModal('editAttendanceModal')">Cancel</button>
                        <button type="submit" class="btn-primary">Update Attendance</button>
                    </div>
                </form>
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

    @section('modals')
        @parent
    @endsection

    <script>
        function editAttendance(id) {
            fetch(`/dashboard/attendance/${id}/edit`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_employee_id').value = data.employee_id;
                    document.getElementById('edit_date').value = data.date;
                    document.getElementById('edit_hours_worked').value = data.hours_worked;
                    document.getElementById('edit_overtime_hours').value = data.overtime_hours;
                    document.getElementById('editAttendanceForm').action = `/dashboard/attendance/${id}`;
                    openModal('editAttendanceModal');
                })
                .catch(error => console.error('Error fetching attendance:', error));
        }
    </script>
@endsection