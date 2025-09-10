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
            @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                <button class="btn-primary" onclick="openModal('addAttendanceModal')">
                    <i class="fas fa-plus mr-2"></i> Log Attendance
                </button>
                <form action="{{ route('attendance.export') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-download mr-2"></i> Export CSV
                    </button>
                </form>
            @endif
            <button class="btn-primary" onclick="openModal('requestLeaveModal')">
                <i class="fas fa-calendar-plus mr-2"></i> Request Leave
            </button>
        </div>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Employee</th>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Date</th>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Hours Worked</th>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Overtime Hours</th>
                    <th class="py-2 px-4 text-sm font-medium text-gray-700">Status</th>
                    @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                        <th class="py-2 px-4 text-sm font-medium text-gray-700">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($attendances as $attendance)
                    @if(Auth::user()->role !== 'Employee' || Auth::user()->id === $attendance->employee->user_id)
                        <tr>
                            <td class="py-2 px-4 text-sm text-gray-600">{{ $attendance->employee->name }}</td>
                            <td class="py-2 px-4 text-sm text-gray-600">{{ $attendance->date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-2 px-4 text-sm text-gray-600">{{ number_format($attendance->hours_worked, 2) }}</td>
                            <td class="py-2 px-4 text-sm text-gray-600">{{ number_format($attendance->overtime_hours, 2) }}</td>
                            <td class="py-2 px-4 text-sm">
                                <span class="status-badge {{ $attendance->status == 'Processed' ? 'status-paid' : 'status-pending' }}">
                                    {{ $attendance->status }}
                                </span>
                            </td>
                            @if(Auth::user()->role === 'Admin' || Auth::user()->role === 'HR')
                                <td class="py-2 px-4 text-sm">
                                    <button onclick="editAttendance({{ $attendance->id }})" class="text-blue-600 hover:text-blue-800">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modals (Add, Edit, Request Leave) remain the same as before, no changes to design -->

@endsection

@section('modals')
    @parent
@endsection

<script>
    function editAttendance(id) {
        fetch(`/dashboard/attendance/${id}/edit`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                document.getElementById('edit_employee_id').value = data.employee_id;
                document.getElementById('edit_date').value = data.date;
                document.getElementById('edit_hours_worked').value = parseFloat(data.hours_worked).toFixed(2);
                document.getElementById('edit_overtime_hours').value = parseFloat(data.overtime_hours).toFixed(2);
                document.getElementById('editAttendanceForm').action = `/dashboard/attendance/${id}`;
                openModal('editAttendanceModal');
            })
            .catch(error => {
                console.error('Error fetching attendance:', error);
                alert('Failed to fetch attendance data. Please try again.');
            });
    }
</script>
