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
use MehediJaman\ZKTeco\ZKTeco;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    private function currentUser()
    {
        return Auth::user();
    }

    private function hasRole($roles)
    {
        $user = $this->currentUser();
        if (!$user) return false;
        $roles = is_array($roles) ? $roles : [$roles];
        return in_array(strtolower($user->role), array_map('strtolower', $roles));
    }

    private function authorizeRole($roles)
    {
        if (!$this->hasRole($roles)) {
            abort(403, 'Unauthorized action.');
        }
    }

    /**
     * Display attendance dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get current week dates (Monday to Sunday)
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $endOfWeek = Carbon::now()->endOfWeek();     // Sunday

        // Query for current week attendance (for all users - data will be cleaned weekly)
        $currentWeekQuery = Attendance::query()->with('employee')
            ->whereBetween('date', [$startOfWeek, $endOfWeek]);

        if (strtolower($user->role) === 'employee') {
            $currentWeekQuery->where('employee_id', $user->id);
        }

        $currentWeekAttendances = $currentWeekQuery->orderBy('date', 'desc')->paginate(10);

        // Convert attendance date to Carbon
        $currentWeekAttendances->getCollection()->transform(function ($attendance) {
            $attendance->date = $attendance->date ? Carbon::parse($attendance->date) : null;
            return $attendance;
        });

        $employees = strtolower($user->role) === 'employee' ? collect([]) : Employee::all();

        $leaveRequests = LeaveRequest::with('employee')
            ->where(function ($q) use ($user) {
                if (strtolower($user->role) === 'employee') {
                    $q->where('employee_id', $user->id);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Convert leave request dates to Carbon
        $leaveRequests->getCollection()->transform(function ($leaveRequest) {
            $leaveRequest->start_date = $leaveRequest->start_date ? Carbon::parse($leaveRequest->start_date) : null;
            $leaveRequest->end_date = $leaveRequest->end_date ? Carbon::parse($leaveRequest->end_date) : null;
            return $leaveRequest;
        });

        $leaveTypes = ['Sick Leave', 'Casual Leave', 'Paid Leave', 'Maternity Leave', 'Paternity Leave'];
        $pendingLeaveCount = strtolower($user->role) === 'employee' ? 0 : LeaveRequest::where('status', 'Pending')->count();

        // Week information
        $weekInfo = [
            'start' => $startOfWeek->format('d/m/Y'),
            'end' => $endOfWeek->format('d/m/Y'),
            'current' => Carbon::now()->format('d/m/Y')
        ];

        return view('dashboard.attendance', compact(
            'currentWeekAttendances',
            'employees',
            'leaveRequests',
            'leaveTypes',
            'pendingLeaveCount',
            'weekInfo'
        ));
    }

    /**
     * Clean up old attendance records (run weekly for ALL users)
     */
public function cleanupOldRecords()
{
    $startOfWeek = Carbon::now()->startOfWeek();
    $deletedCount = Attendance::where('date', '<', $startOfWeek)->delete();
    
    // NOTIFICATION: Cleanup imekamilika
    return redirect()->back()->with('success', "Cleaned up {$deletedCount} old attendance records.");
}

    /**
     * Store a new attendance record
     */
    public function store(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i:s',
            'check_out' => 'nullable|date_format:H:i:s',
            'hours_worked' => 'required|numeric|min:0',
            'status' => 'required|in:Present,Absent,Leave,Holiday',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $employee = Employee::findOrFail($request->employee_id);

        Attendance::create([
            'employee_id' => $request->employee_id,
            'employee_name' => $employee->name,
            'date' => $request->date,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'hours_worked' => $request->hours_worked,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('dashboard.attendance')->with('success', 'Attendance record added successfully.');
    }

    /**
     * Edit an attendance record
     */
    public function edit($id)
    {
        $this->authorizeRole(['admin', 'hr manager']);
        $attendance = Attendance::findOrFail($id);
        return response()->json($attendance);
    }

    /**
     * Update an attendance record
     */
    public function update(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'hr manager']);
        $attendance = Attendance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'check_in' => 'nullable|date_format:H:i:s',
            'check_out' => 'nullable|date_format:H:i:s',
            'hours_worked' => 'required|numeric|min:0',
            'status' => 'required|in:Present,Absent,Leave,Holiday',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $employee = Employee::findOrFail($request->employee_id);

        $attendance->update([
            'employee_id' => $request->employee_id,
            'employee_name' => $employee->name,
            'date' => $request->date,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'hours_worked' => $request->hours_worked,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        return redirect()->route('dashboard.attendance')->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Request a leave
     */
public function requestLeave(Request $request)
{
    $user = $this->currentUser();
    $employee = $user;

    // Validate request
    $validator = Validator::make($request->all(), [
        'employee_id' => 'required|exists:employees,id',
        'leave_type' => 'required|in:Sick Leave,Casual Leave,Paid Leave,Maternity Leave,Paternity Leave',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'reason' => 'nullable|string|max:500',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    // Check if user is employee and trying to request leave for someone else
    if (strtolower($user->role) === 'employee' && $request->employee_id != $employee->id) {
        return redirect()->back()->with('error', 'You can only request leave for yourself.');
    }

    // Generate unique request_id
    $requestId = 'LRQ-' . Str::upper(Str::random(5));
    while (LeaveRequest::where('request_id', $requestId)->exists()) {
        $requestId = 'LRQ-' . Str::upper(Str::random(5));
    }

    // Calculate leave days
    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    $days = $startDate->diffInDays($endDate) + 1;

    // Get employee name
    $employee = Employee::find($request->employee_id);

    LeaveRequest::create([
        'request_id' => $requestId,
        'employee_id' => $request->employee_id,
        'employee_name' => $employee->name,
        'leave_type' => $request->leave_type,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'days' => $days,
        'reason' => $request->reason,
        'status' => $request->status ?? 'Pending',
    ]);

    // NOTIFICATION: Ombi la likizo limewasilishwa
    return redirect()->back()->with('success', 'Leave request submitted.');
}

    /**
     * Review a leave request
     */
    public function reviewLeaveRequest($id)
    {
        $this->authorizeRole(['admin', 'hr manager']);
        $leaveRequest = LeaveRequest::with('employee')->findOrFail($id);
        return response()->json([
            'employee_name' => $leaveRequest->employee_name,
            'leave_type' => $leaveRequest->leave_type,
            'start_date' => $leaveRequest->start_date->format('Y-m-d'),
            'end_date' => $leaveRequest->end_date->format('Y-m-d'),
            'reason' => $leaveRequest->reason,
        ]);
    }

    /**
     * Update a leave request
     */
public function updateLeaveRequest(Request $request, $id)
{
    $this->authorizeRole(['admin', 'hr manager']);
    $leaveRequest = LeaveRequest::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'status' => 'required|in:Pending,Approved,Rejected',
        'approved_by' => 'nullable|exists:employees,id',
    ]);

    if ($validator->fails()) {
        return redirect()->back()->withErrors($validator)->withInput();
    }

    $leaveRequest->update([
        'status' => $request->status,
        'approved_by' => $request->approved_by ?? Auth::id(),
    ]);

    // NOTIFICATION: Ombi la likizo limehakikiwa
    return redirect()->route('dashboard.attendance')->with('success', 'Leave request updated.');
}

    /**
     * Export attendance records with date range
     */
    public function export(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'format' => 'required|in:csv,xlsx',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        return Excel::download(new AttendanceExport($dateFrom, $dateTo), 'attendance.' . $request->format);
    }

    /**
     * Sync biometric attendance data
     */
    public function syncBiometric()
    {
        $this->authorizeRole(['admin', 'hr manager']);

        try {
            $zk = new ZKTeco(config('zkteco.ip'), config('zkteco.port'));
            if (!$zk->connect()) {
                return redirect()->back()->with('error', 'Failed to connect to device.');
            }

            $deviceLogs = $zk->getAttendance();
            $groupedLogs = [];

            foreach ($deviceLogs as $log) {
                $biometricId = $log['id'];
                $timestamp = strtotime($log['timestamp']);
                $date = date('Y-m-d', $timestamp);
                $time = date('H:i:s', $timestamp);

                if (!isset($groupedLogs[$biometricId][$date])) {
                    $groupedLogs[$biometricId][$date] = ['check_in' => $time, 'check_out' => $time];
                } else {
                    if ($time < $groupedLogs[$biometricId][$date]['check_in']) {
                        $groupedLogs[$biometricId][$date]['check_in'] = $time;
                    }
                    if ($time > $groupedLogs[$biometricId][$date]['check_out']) {
                        $groupedLogs[$biometricId][$date]['check_out'] = $time;
                    }
                }
            }

            $syncedRecords = 0;
            foreach ($groupedLogs as $biometricId => $dates) {
                $employee = Employee::find($biometricId);
                if (!$employee) {
                    continue;
                }

                foreach ($dates as $date => $times) {
                    $checkIn = Carbon::parse($date . ' ' . $times['check_in']);
                    $checkOut = Carbon::parse($date . ' ' . $times['check_out']);
                    $hoursWorked = $checkOut->diffInHours($checkIn);

                    Attendance::updateOrCreate(
                        ['employee_id' => $employee->id, 'date' => $date],
                        [
                            'employee_name' => $employee->name,
                            'check_in' => $times['check_in'],
                            'check_out' => $times['check_out'],
                            'hours_worked' => $hoursWorked,
                            'status' => 'Present',
                            'notes' => 'Synced from biometric device',
                        ]
                    );
                    $syncedRecords++;
                }
            }

            return redirect()->route('dashboard.attendance')->with('success', "Synced {$syncedRecords} attendance records successfully.");
        } catch (\Exception $e) {
            \Log::error('Biometric sync error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to sync: ' . $e->getMessage());
        }
    }
    protected function schedule(Schedule $schedule)
{
    // Clean up old attendance records every Sunday at 23:59 for ALL users
    $schedule->call(function () {
        $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
        $deletedCount = \App\Models\Attendance::where('date', '<', $startOfWeek)->delete();
        \Log::info("Weekly attendance cleanup: {$deletedCount} records deleted.");
    })->weeklyOn(0, '23:59'); // Sunday at 23:59
}
}
