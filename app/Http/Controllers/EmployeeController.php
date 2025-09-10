<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\ComplianceTask;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 6;
        $search = $request->input('search', '');

        // Query for employees with search filter
        $query = Employee::query();
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                  ->orWhere('employee_id', 'like', '%' . $search . '%');
        }

        $employees = $query->paginate($perPage);
        $totalEmployees = Employee::count(); // Total count for all employees
        $monthlyPayroll = Employee::sum('base_salary'); // Sum for all employees
        $employeeGrowth = $this->calculateGrowth(Employee::class, 'created_at');
        $payrollGrowth = 3; // Example value, adjust as needed
        $payslipsGenerated = Payslip::count(); // Example, adjust to actual logic
        $pendingTasks = ComplianceTask::where('status', 'pending')->count(); // Example
        $settings = ['currency' => 'TZS'];
        $currentPeriod = now()->format('F Y');

        return view('dashboard.employee', compact(
            'employees',
            'totalEmployees',
            'monthlyPayroll',
            'employeeGrowth',
            'payrollGrowth',
            'payslipsGenerated',
            'pendingTasks',
            'settings',
            'currentPeriod',
            'search' // Pass search term to view for persistence
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can add employees.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|max:50|unique:employees',
            'email' => 'required|email|max:255|unique:employees|unique:users',
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'role' => 'required|string|in:admin,hr,manager,employee',
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'hire_date' => 'required|date',
            'employment_type' => 'required|in:full-time,part-time,contract',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'nssf_number' => 'nullable|string|max:50',
            'nhif_number' => 'nullable|string|max:50',
            'tin_number' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated, &$defaultPassword) {
            $defaultPassword = 'TZ' . date('Y') . rand(1000, 9999);
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($defaultPassword),
                'role' => $validated['role'],
                'must_change_password' => true,
            ]);
            $employeeData = array_merge($validated, [
                'user_id' => $user->id,
                'default_password' => $defaultPassword,
                'sick_leave_balance' => 14,
                'vacation_leave_balance' => 28,
                'maternity_leave_balance' => 84,
            ]);
            Employee::create($employeeData);
        });

        return redirect()->route('employees')->with('success', 'Employee added successfully. Default password: ' . $defaultPassword);
    }

    public function import(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can import employees.');
        }
        $request->validate(['csv_file' => 'required|mimes:csv,txt|max:2048']);
        try {
            Excel::import(new EmployeesImport, $request->file('csv_file'));
            return redirect()->back()->with('success', 'Employees imported successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to import employees. Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = Auth::user();
        $employee = Employee::with(['user', 'payslips', 'attendances', 'leaveRequests'])->findOrFail($id);
        if ($user->role === 'employee' && $user->employee->id !== $employee->id) {
            return redirect()->back()->with('error', 'Unauthorized. You can only view your own data.');
        }
        $totalEarnings = $employee->payslips->sum('gross_salary');
        $totalDeductions = $employee->payslips->sum('total_deductions');
        $averageSalary = $employee->payslips->avg('net_salary');
        return response()->json([
            'employee' => $employee,
            'statistics' => [
                'total_earnings' => $totalEarnings,
                'total_deductions' => $totalDeductions,
                'average_salary' => $averageSalary,
                'years_of_service' => Carbon::parse($employee->hire_date)->diffInYears(Carbon::now()),
            ]
        ]);
    }

    public function edit($id)
    {
        $user = Auth::user();
        if ($user->role === 'employee') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }
        $employee = Employee::with('user')->findOrFail($id);
        return response()->json($employee);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'employee_id' => 'required|string|max:50|unique:employees,employee_id,' . $id,
            'email' => 'required|email|max:255|unique:employees,email,' . $id,
            'department' => 'required|string|max:100',
            'position' => 'required|string|max:100',
            'role' => 'required|string|in:admin,hr,manager,employee',
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'hire_date' => 'required|date',
            'employment_type' => 'required|in:full-time,part-time,contract',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'nssf_number' => 'nullable|string|max:50',
            'nhif_number' => 'nullable|string|max:50',
            'tin_number' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive,terminated',
        ]);

        DB::transaction(function () use ($validated, $id) {
            $employee = Employee::findOrFail($id);
            $employee->update($validated);
            if ($employee->user) {
                $employee->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'role' => $validated['role'],
                ]);
            }
        });

        return redirect()->route('employees')->with('success', 'Employee updated successfully.');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return redirect()->back()->with('error', 'Unauthorized. Only Admin can delete employees.');
        }
        DB::transaction(function () use ($id) {
            $employee = Employee::findOrFail($id);
            $employee->update(['status' => 'terminated']);
            if ($employee->user) {
                $employee->user->update(['status' => 'inactive']);
            }
        });
        return redirect()->route('employees')->with('success', 'Employee deactivated successfully.');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }
        $employee = Employee::findOrFail($id);
        $newPassword = 'TZ' . date('Y') . rand(1000, 9999);
        if ($employee->user) {
            $employee->user->update([
                'password' => Hash::make($newPassword),
                'must_change_password' => true,
            ]);
        }
        return redirect()->back()->with('success', 'Password reset successfully. New password: ' . $newPassword);
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }
        $format = $request->get('format', 'xlsx');
        return Excel::download(new \App\Exports\EmployeesExport, 'employees_' . date('Y_m_d') . '.' . $format);
    }

    private function calculateGrowth($model, $dateColumn)
    {
        $currentPeriod = $model::whereMonth($dateColumn, Carbon::now()->month)
            ->whereYear($dateColumn, Carbon::now()->year)
            ->count();
        $previousPeriod = $model::whereMonth($dateColumn, Carbon::now()->subMonth()->month)
            ->whereYear($dateColumn, Carbon::now()->subMonth()->year)
            ->count();
        return $previousPeriod == 0 ? ($currentPeriod > 0 ? 100 : 0) :
            round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 2);
    }
}
?>