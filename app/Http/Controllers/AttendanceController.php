<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Employee;
use App\Models\LeaveType; // Ongeza mstari huu
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceExport;

class AttendanceController extends Controller
{
    /**
     * Helper to get current authenticated user
     */
    private function currentUser()
    {
        return Auth::user();
    }

    /**
     * Helper to check user role
     */
    private function hasRole($roles)
    {
        $user = $this->currentUser();
        if (!$user) return false;

        $roles = is_array($roles) ? $roles : [$roles];
        return in_array(strtolower($user->role), array_map('strtolower', $roles));
    }

    /**
     * Helper to authorize roles and abort if unauthorized
     */
    private function authorizeRole($roles)
    {
        if (!$this->hasRole($roles)) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Display the attendance and leave management dashboard.
     */
    public function index(Request $request)
    {
        $user = $this->currentUser();
        $employees = Employee::all();
        $leaveTypes = LeaveType::all(); // Ongeza mstari huu

        // Initialize attendance query
        $attendancesQuery = Attendance::with('employee')->orderBy('date', 'desc');

        // Apply search filter for attendance if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $attendancesQuery->whereHas('employee', function($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            });
        }

        // Filter attendance records based on user role
        if (strtolower($user->role) === 'employee') {
            $attendancesQuery->where('employee_id', $user->employee->id ?? 0);
        }

        $attendances = $attendancesQuery->paginate(6);
        $leaveRequests = LeaveRequest::with('employee')->latest()->paginate(6);

        return view('dashboard.attendance', compact('attendances', 'leaveRequests', 'employees', 'leaveTypes')); // Ongeza '$leaveTypes' hapa
    }

    /**
     * Store a new attendance record (Admin/HR only).
     */
    public function store(Request $request)
    {
        $this->authorizeRole(['admin', 'hr']);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'hours_worked' => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Attendance::create($request->all());

        return redirect()->route('dashboard.attendance')->with('success', 'Attendance record added successfully.');
    }

    /**
     * Show the form for editing attendance (Admin/HR only)
     */
    public function edit($id)
    {
        $this->authorizeRole(['admin', 'hr']);

        $attendance = Attendance::findOrFail($id);
        return response()->json($attendance);
    }

    /**
     * Update an attendance record (Admin/HR only)
     */
    public function update(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'hr']);

        $attendance = Attendance::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'hours_worked' => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attendance->update($request->all());

        return redirect()->route('dashboard.attendance')->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Handle leave request
     */
    public function requestLeave(Request $request)
    {
        $user = $this->currentUser();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->back()->with('error', 'No employee record found for this user.');
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|string|exists:leave_types,name', // Badilisha 'in' na 'exists'
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Authorization check: ensure employee only submits for themselves
        if (strtolower($user->role) === 'employee' && $request->employee_id != $employee->id) {
            return redirect()->back()->with('error', 'Unauthorized to submit leave request for this employee.');
        }

        LeaveRequest::create([
            'employee_id' => $request->employee_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        return redirect()->back()->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Review leave request
     */
    public function reviewLeaveRequest($id)
    {
        $this->authorizeRole(['admin', 'hr']);

        $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);
        return response()->json([
            'employee_name' => $leaveRequest->employee->name,
            'leave_type' => $leaveRequest->leave_type,
            'start_date' => $leaveRequest->start_date->format('Y-m-d'),
            'end_date' => $leaveRequest->end_date->format('Y-m-d'),
            'reason' => $leaveRequest->reason,
            'status' => $leaveRequest->status,
            'feedback' => $leaveRequest->feedback,
        ]);
    }

    /**
     * Update a leave request status (Admin/HR only)
     */
    public function updateLeaveRequest(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'hr']);

        $leaveRequest = LeaveRequest::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Approved,Rejected',
            'feedback' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $leaveRequest->update([
            'status' => $request->status,
            'feedback' => $request->feedback,
        ]);

        return redirect()->route('dashboard.attendance')->with('success', 'Leave request updated successfully.');
    }

    /**
     * Export attendance data
     */
    public function export(Request $request)
    {
        $this->authorizeRole(['admin', 'hr']);

        $validator = Validator::make($request->all(), [
            'format' => 'required|in:csv,xlsx',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $format = $request->format;
        return Excel::download(new AttendanceExport, 'attendance.' . $format);
    }
}
