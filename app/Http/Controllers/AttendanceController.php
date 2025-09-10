<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Employee;
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
    public function index()
    {
        $user = $this->currentUser();

        if ($this->hasRole(['admin', 'hr'])) {
            $attendances = Attendance::with('employee')->get();
            $leaveRequests = LeaveRequest::with('employee')->get();
            $employees = Employee::all();
        } elseif ($this->hasRole('employee')) {
            $attendances = Attendance::where('employee_id', $user->employee->id)
                ->with('employee')
                ->get();
            $leaveRequests = LeaveRequest::where('employee_id', $user->employee->id)
                ->with('employee')
                ->get();
            $employees = null;
        } else {
            abort(403, 'Unauthorized action.');
        }

        return view('dashboard.attendance', compact('attendances', 'leaveRequests', 'employees'));
    }

    /**
     * Store a new attendance record
     */
    public function store(Request $request)
    {
        $this->authorizeRole(['admin', 'hr']); // Only admin/HR can add attendance

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Attendance::create($request->all());

        return redirect()->route('attendance')->with('success', 'Attendance record added successfully.');
    }

    /**
     * Show a single attendance record for editing
     */
    public function edit($id)
    {
        $attendance = Attendance::findOrFail($id);

        if ($this->hasRole(['admin', 'hr']) || ($this->hasRole('employee') && $attendance->employee_id == $this->currentUser()->employee->id)) {
            return response()->json($attendance);
        }

        abort(403, 'Unauthorized action.');
    }

    /**
     * Update an attendance record
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        if (!($this->hasRole(['admin', 'hr']) || ($this->hasRole('employee') && $attendance->employee_id == $this->currentUser()->employee->id))) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'required|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $attendance->update($request->all());

        return redirect()->route('attendance')->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Handle a new leave request from an employee
     */
    public function requestLeave(Request $request)
    {
        $user = $this->currentUser();

        // Only employee can submit leave for themselves, admin/HR can submit for anyone
        if ($this->hasRole('employee') && $request->employee_id != $user->employee->id) {
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|in:sick,vacation,maternity',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        LeaveRequest::create([
            'employee_id' => $request->employee_id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        return redirect()->route('attendance')->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Export attendance data
     */
    public function export(Request $request)
    {
        $this->authorizeRole(['admin', 'hr']); // Only admin/HR can export

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
