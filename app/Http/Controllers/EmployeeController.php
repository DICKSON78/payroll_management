<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Bank;
use App\Models\Role;
use App\Models\Allowance;
use App\Models\ComplianceTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// Excel Imports/Exports
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;

// PhpSpreadsheet for Template Download
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Exception;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource (READ ALL).
     */
    public function index(Request $request)
    {
        $userRole = Auth::user();
        if (!$userRole) {
            Log::warning('User not authenticated.');
            return redirect('/')->with('error', 'Please log in.');
        }

        // Handle search and filtering parameters
        $search = $request->input('search', '');
        $department = $request->input('department', '');
        $status = $request->input('status', '');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Validate sort column
        $validSortColumns = ['name', 'position', 'department', 'base_salary', 'created_at', 'employee_id'];
        $sort = in_array($sort, $validSortColumns) ? $sort : 'created_at';
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';

        // Fetch paginated employee records
        $query = Employee::with('departmentRel', 'allowances');

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('employee_id', 'like', '%' . $search . '%')
                  ->orWhere('position', 'like', '%' . $search . '%');
            });
        }

        // Apply department filter
        if ($department) {
            $query->where('department', $department);
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        $employees = $query->orderBy($sort, $direction)->paginate(15);

        $totalEmployees = Employee::count();
        $activeEmployeeCount = Employee::where('status', 'active')->count();
        $employeeGrowth = $this->calculateGrowth(Employee::class, 'hire_date');
        $complianceTasksDue = ComplianceTask::whereDate('due_date', '<=', Carbon::now()->addDays(7))->count();
        $currentPeriod = Carbon::now()->format('F Y');

        $departments = Department::all();
        $banks = Bank::all();
        $allowances = Allowance::where('active', 1)->get();
        $roles = Role::all();

        // AJAX response - PASS ALL VARIABLES
        if ($request->ajax()) {
            return $this->getEmployeesTableHtml($employees, $request, [
                'totalEmployees' => $totalEmployees,
                'activeEmployeeCount' => $activeEmployeeCount,
                'employeeGrowth' => $employeeGrowth,
                'complianceTasksDue' => $complianceTasksDue,
                'currentPeriod' => $currentPeriod,
                'departments' => $departments,
                'banks' => $banks,
                'allowances' => $allowances,
                'roles' => $roles,
                'userRole' => $userRole,
                'search' => $search,
                'department' => $department,
                'status' => $status
            ]);
        }

        return view('dashboard.employee', compact(
            'employees',
            'totalEmployees',
            'activeEmployeeCount',
            'employeeGrowth',
            'complianceTasksDue',
            'currentPeriod',
            'departments',
            'banks',
            'allowances',
            'roles',
            'userRole',
            'request',
            'search',
            'department',
            'status'
        ));
    }

    /**
     * Display the specified employee's data as HTML for modals.
     */
    public function show($employeeId, Request $request)
    {
        Log::info('Attempting to fetch employee with ID: ' . $employeeId);
        try {
            // FIXED: Use correct field name for employee_id
            $employee = Employee::with(['departmentRel', 'allowances' => function($query) {
                $query->where('active', 1);
            }])
                ->where('employee_id', $employeeId)
                ->first();

            if (!$employee) {
                Log::error('Employee not found with ID: ' . $employeeId);
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.'
                ], 404);
            }

            $departments = Department::all();
            $banks = Bank::all();
            $allowances = Allowance::where('active', 1)->get();
            $roles = Role::all();

            $mode = $request->query('mode', 'view');

            if ($mode === 'edit') {
                ob_start();
                ?>
                <form id="editEmployeeForm" action="<?php echo route('employees.update', $employee->employee_id); ?>" method="POST">
                    <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="_method" value="PUT">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-700 border-b pb-2">Personal Information</h4>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Full Name *</label>
                                <input type="text" name="name" value="<?php echo $employee->name; ?>" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Email Address *</label>
                                <input type="email" name="email" value="<?php echo $employee->email; ?>" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Phone Number</label>
                                <input type="text" name="phone" value="<?php echo $employee->phone; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Gender</label>
                                <select name="gender" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                    <option value="">Select Gender</option>
                                    <option value="male" <?php echo $employee->gender == 'male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo $employee->gender == 'female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="other" <?php echo $employee->gender == 'other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Date of Birth</label>
                                <input type="date" name="dob" value="<?php echo $employee->dob ? $employee->dob->format('Y-m-d') : ''; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Nationality</label>
                                <input type="text" name="nationality" value="<?php echo $employee->nationality; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Address</label>
                                <input type="text" name="address" value="<?php echo $employee->address; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                        </div>
                        <div class="space-y-4">
                            <h4 class="text-lg font-medium text-gray-700 border-b pb-2">Employment Information</h4>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Department *</label>
                                <select name="department" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept->name; ?>" <?php echo $employee->department == $dept->name ? 'selected' : ''; ?>><?php echo $dept->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Position *</label>
                                <input type="text" name="position" value="<?php echo $employee->position; ?>" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Role *</label>
                                <select name="role" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                    <option value="">Select Role</option>
                                    <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role->slug; ?>" <?php echo $employee->role == $role->slug ? 'selected' : ''; ?>><?php echo $role->name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Employment Type *</label>
                                <select name="employment_type" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                    <option value="">Select Type</option>
                                    <option value="full-time" <?php echo $employee->employment_type == 'full-time' ? 'selected' : ''; ?>>Full Time</option>
                                    <option value="part-time" <?php echo $employee->employment_type == 'part-time' ? 'selected' : ''; ?>>Part Time</option>
                                    <option value="contract" <?php echo $employee->employment_type == 'contract' ? 'selected' : ''; ?>>Contract</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Hire Date *</label>
                                <input type="date" name="hire_date" value="<?php echo $employee->hire_date->format('Y-m-d'); ?>" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div id="contractEndDateContainer" class="<?php echo $employee->employment_type == 'contract' ? '' : 'hidden'; ?>">
                                <label class="block text-gray-600 text-sm font-medium mb-2">Contract End Date *</label>
                                <input type="date" name="contract_end_date" value="<?php echo $employee->contract_end_date ? $employee->contract_end_date->format('Y-m-d') : ''; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Status *</label>
                                <select name="status" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                    <option value="active" <?php echo $employee->status == 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo $employee->status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="terminated" <?php echo $employee->status == 'terminated' ? 'selected' : ''; ?>>Terminated</option>
                                </select>
                            </div>
                        </div>
                        <div class="space-y-4 col-span-1 md:col-span-2">
                            <h4 class="text-lg font-medium text-gray-700 border-b pb-2">Salary Information</h4>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Base Salary (TZS) *</label>
                                <input type="number" name="base_salary" value="<?php echo $employee->base_salary; ?>" step="0.01" min="0" required class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                            </div>
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2">Allowances</label>
                                <div class="space-y-2">
                                    <?php foreach ($allowances as $allowance): ?>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="allowances[]" value="<?php echo $allowance->id; ?>"
                                                <?php
                                                if ($employee->allowances && is_object($employee->allowances) && method_exists($employee->allowances, 'contains')) {
                                                    echo $employee->allowances->contains($allowance->id) ? 'checked' : '';
                                                }
                                                ?>
                                                class="mr-2">
                                            <span><?php echo $allowance->name; ?> (TZS <?php echo number_format($allowance->amount, 0); ?>)</span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4 col-span-1 md:col-span-2">
                            <h4 class="text-lg font-medium text-gray-700 border-b pb-2">Additional Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-600 text-sm font-medium mb-2">Bank Name</label>
                                    <select name="bank_name" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                        <option value="">Select Bank</option>
                                        <?php foreach ($banks as $bank): ?>
                                            <option value="<?php echo $bank->name; ?>" <?php echo $employee->bank_name == $bank->name ? 'selected' : ''; ?>><?php echo $bank->name; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-gray-600 text-sm font-medium mb-2">Account Number</label>
                                    <input type="text" name="account_number" value="<?php echo $employee->account_number; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                </div>
                                <div>
                                    <label class="block text-gray-600 text-sm font-medium mb-2">NSSF Number</label>
                                    <input type="text" name="nssf_number" value="<?php echo $employee->nssf_number; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                </div>
                                <div>
                                    <label class="block text-gray-600 text-sm font-medium mb-2">TIN Number</label>
                                    <input type="text" name="tin_number" value="<?php echo $employee->tin_number; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                </div>
                                <div>
                                    <label class="block text-gray-600 text-sm font-medium mb-2">NHIF Number</label>
                                    <input type="text" name="nhif_number" value="<?php echo $employee->nhif_number; ?>" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <?php
                return ob_get_clean();
            } else {
                ob_start();
                ?>
                <div>
                    <h4 class="text-lg font-medium text-gray-700 border-b pb-2">Personal Information</h4>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><strong>Name:</strong> <?php echo $employee->name; ?></div>
                        <div><strong>Email:</strong> <?php echo $employee->email; ?></div>
                        <div><strong>Phone:</strong> <?php echo $employee->phone ?? 'N/A'; ?></div>
                        <div><strong>Gender:</strong> <?php echo ucfirst($employee->gender ?? 'N/A'); ?></div>
                        <div><strong>Date of Birth:</strong> <?php echo $employee->dob ? $employee->dob->format('Y-m-d') : 'N/A'; ?></div>
                        <div><strong>Nationality:</strong> <?php echo $employee->nationality ?? 'N/A'; ?></div>
                        <div><strong>Address:</strong> <?php echo $employee->address ?? 'N/A'; ?></div>
                    </div>
                    <h4 class="text-lg font-medium text-gray-700 border-b pb-2 mt-6">Employment Information</h4>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><strong>Employee ID:</strong> <?php echo $employee->employee_id; ?></div>
                        <div><strong>Department:</strong> <?php echo $employee->departmentRel->name ?? $employee->department ?? 'N/A'; ?></div>
                        <div><strong>Position:</strong> <?php echo $employee->position ?? 'N/A'; ?></div>
                        <div><strong>Role:</strong> <?php echo $employee->role ? (Role::where('slug', $employee->role)->first()->name ?? 'N/A') : 'N/A'; ?></div>
                        <div><strong>Employment Type:</strong> <?php echo ucfirst($employee->employment_type ?? 'N/A'); ?></div>
                        <div><strong>Hire Date:</strong> <?php echo $employee->hire_date->format('Y-m-d'); ?></div>
                        <div><strong>Contract End Date:</strong> <?php echo $employee->contract_end_date ? $employee->contract_end_date->format('Y-m-d') : 'N/A'; ?></div>
                        <div><strong>Status:</strong> <?php echo ucfirst($employee->status); ?></div>
                    </div>
                    <h4 class="text-lg font-medium text-gray-700 border-b pb-2 mt-6">Salary Information</h4>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><strong>Base Salary:</strong> TZS <?php echo number_format($employee->base_salary, 0); ?></div>
                        <div><strong>Allowances:</strong>
                            <?php
                            if ($employee->allowances && is_object($employee->allowances) && method_exists($employee->allowances, 'isEmpty') && !$employee->allowances->isEmpty()) {
                                echo $employee->allowances->pluck('name')->implode(', ');
                            } else {
                                echo 'None';
                            }
                            ?>
                        </div>
                    </div>
                    <h4 class="text-lg font-medium text-gray-700 border-b pb-2 mt-6">Additional Information</h4>
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div><strong>Bank Name:</strong> <?php echo $employee->bank_name ?? 'N/A'; ?></div>
                        <div><strong>Account Number:</strong> <?php echo $employee->account_number ?? 'N/A'; ?></div>
                        <div><strong>NSSF Number:</strong> <?php echo $employee->nssf_number ?? 'N/A'; ?></div>
                        <div><strong>TIN Number:</strong> <?php echo $employee->tin_number ?? 'N/A'; ?></div>
                        <div><strong>NHIF Number:</strong> <?php echo $employee->nhif_number ?? 'N/A'; ?></div>
                    </div>
                </div>
                <?php
                return ob_get_clean();
            }

        } catch (Exception $e) {
            Log::error('Error fetching employee details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Employee not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified employee in storage (UPDATE).
     */
    public function update(Request $request, $employeeId)
    {
        $employee = Employee::where('employee_id', $employeeId)->first();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee->id)],
            'position' => 'required|string|max:255',
            'department' => 'required|exists:departments,name',
            'base_salary' => 'required|numeric|min:0',
            'hire_date' => 'required|date',
            'employment_type' => 'required|in:full-time,part-time,contract',
            'role' => 'required|exists:roles,slug',
            'phone' => 'nullable|string|max:20',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'bank_name' => 'nullable|exists:banks,name',
            'account_number' => 'nullable|string|max:50',
            'nssf_number' => 'nullable|string|max:50',
            'tin_number' => 'nullable|string|max:50',
            'nhif_number' => 'nullable|string|max:50',
            'allowances' => 'nullable|array',
            'allowances.*' => 'exists:allowances,id',
            'contract_end_date' => 'nullable|date|required_if:employment_type,contract',
            'status' => 'required|in:active,inactive,terminated',
        ], [
            'contract_end_date.required_if' => 'Contract end date is required for contract employees.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $totalAllowances = $request->has('allowances') && is_array($request->allowances)
                ? array_sum(Allowance::whereIn('id', $request->allowances)->pluck('amount')->toArray())
                : 0.00;

            $employee->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'dob' => $request->dob ? Carbon::parse($request->dob) : null,
                'nationality' => $request->nationality,
                'address' => $request->address,
                'department' => $request->department,
                'position' => $request->position,
                'role' => $request->role,
                'employment_type' => $request->employment_type,
                'hire_date' => Carbon::parse($request->hire_date),
                'contract_end_date' => $request->contract_end_date ? Carbon::parse($request->contract_end_date) : null,
                'base_salary' => $request->base_salary,
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'nssf_number' => $request->nssf_number,
                'tin_number' => $request->tin_number,
                'nhif_number' => $request->nhif_number,
                'status' => $request->status,
                'allowances' => $totalAllowances,
                'deductions' => 0.00,
            ]);

            if ($request->has('allowances') && is_array($request->allowances)) {
                $employee->allowances()->sync($request->allowances);
            } else {
                $employee->allowances()->sync([]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully.'
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Employee update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle employee status (active/inactive).
     */
    public function toggleStatus(Request $request, $employeeId)
    {
        try {
            $employee = Employee::where('employee_id', $employeeId)->first();

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.'
                ], 404);
            }

            $newStatus = $employee->status === 'active' ? 'inactive' : 'active';

            $employee->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'Employee status updated to ' . $newStatus . '.'
            ]);

        } catch (Exception $e) {
            Log::error('Status toggle failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download import template.
     */
    public function downloadTemplate()
    {
        try {
            // Create new Spreadsheet - HAIHITAJI kuangalia employees!
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Define headers with required indicators
            $headers = [
                'name*', 'email*', 'phone', 'gender', 'dob (YYYY-MM-DD)', 'nationality', 'address',
                'department*', 'position*', 'employment_type*', 'hire_date* (YYYY-MM-DD)', 'contract_end_date (YYYY-MM-DD)',
                'base_salary*', 'bank_name', 'account_number', 'nssf_number', 'tin_number',
                'nhif_number', 'role*'
            ];

            // Set headers
            $sheet->fromArray([$headers], null, 'A1');

            // Add sample data
            $sampleData = [
                'John Doe',
                'john.doe@company.com',
                '+255123456789',
                'male',
                '1990-05-15',
                'Tanzanian',
                '123 Main Street, Dar es Salaam',
                'IT',  // Must match existing department name
                'Software Developer',
                'full-time', // full-time, part-time, or contract
                '2024-01-15',
                '', // Leave empty for non-contract employees
                '1500000',
                'CRDB Bank',
                '0123456789',
                'NSSF123456789',
                'TIN123456789',
                'NHIF123456789',
                'employee' // Must match role slug
            ];
            $sheet->fromArray([$sampleData], null, 'A2');

            // Style headers
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E7D32']
                ]
            ];
            $sheet->getStyle('A1:S1')->applyFromArray($headerStyle);

            // Style sample data
            $sampleStyle = [
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E8']
                ]
            ];
            $sheet->getStyle('A2:S2')->applyFromArray($sampleStyle);

            // Add instructions
            $instructions = [
                'REQUIRED FIELDS marked with *',
                'Email must be unique',
                'Phone format: +255XXX',
                'male/female/other',
                'Date format: YYYY-MM-DD',
                'Country name',
                'Full address',
                'Must exist in departments',
                'Job title',
                'full-time/part-time/contract',
                'Date format: YYYY-MM-DD',
                'Required for contract employees',
                'Numeric value only',
                'Bank name',
                'Account number',
                'NSSF number',
                'TIN number',
                'NHIF number',
                'Must exist in roles (employee,manager,admin)'
            ];
            $sheet->fromArray([$instructions], null, 'A3');

            // Style instructions
            $instructionStyle = [
                'font' => [
                    'italic' => true,
                    'color' => ['rgb' => '666666']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF9C4']
                ]
            ];
            $sheet->getStyle('A3:S3')->applyFromArray($instructionStyle);

            // Auto-size columns
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Freeze panes for better navigation
            $sheet->freezePane('A4');

            $writer = new Xlsx($spreadsheet);
            $filename = 'employee_import_template.xlsx';

            $tempFile = tempnam(sys_get_temp_dir(), $filename);
            $writer->save($tempFile);

            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            Log::error('Template download failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to download template: ' . $e->getMessage());
        }
    }

    /**
     * Handle bulk import of employees.
     */
    public function bulkImport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Invalid file format. Please upload an XLSX, XLS, or CSV file (max 10MB).')
                ->withInput();
        }

        try {
            if (!class_exists('Maatwebsite\Excel\Excel')) {
                throw new Exception('Excel package not installed. Run: composer require maatwebsite/excel');
            }

            Excel::import(new EmployeesImport, $request->file('file'));

            return redirect()->route('employees.index')
                ->with('success', 'Employees imported successfully!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];

            foreach ($failures as $failure) {
                $errorMessages[] = "Row {$failure->row()}: {$failure->errors()[0]}";
            }

            return redirect()->back()
                ->with('error', 'Import validation failed: ' . implode('; ', $errorMessages));

        } catch (Exception $e) {
            Log::error('Bulk import failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Bulk import failed: ' . $e->getMessage());
        }
    }

    /**
     * Export employees to Excel.
     */
    public function export()
    {
        try {
            $employeeCount = Employee::count();

            if ($employeeCount === 0) {
                return redirect()->back()->with('error', 'No employees found to export.');
            }

            $employees = Employee::with(['departmentRel', 'allowances'])->get();
            
            $exportData = [];
            foreach ($employees as $employee) {
                $exportData[] = [
                    $employee->employee_id,
                    $employee->name,
                    $employee->email,
                    $employee->phone ?? 'N/A',
                    $employee->gender ?? 'N/A',
                    $employee->dob ? $employee->dob->format('Y-m-d') : 'N/A',
                    $employee->nationality ?? 'N/A',
                    $employee->address ?? 'N/A',
                    $employee->departmentRel->name ?? $employee->department ?? 'N/A',
                    $employee->position ?? 'N/A',
                    $employee->role ?? 'N/A',
                    $employee->employment_type ?? 'N/A',
                    $employee->hire_date->format('Y-m-d'),
                    $employee->contract_end_date ? $employee->contract_end_date->format('Y-m-d') : 'N/A',
                    $employee->base_salary,
                    $employee->allowances ?? 0.00,
                    $employee->bank_name ?? 'N/A',
                    $employee->account_number ?? 'N/A',
                    $employee->nssf_number ?? 'N/A',
                    $employee->tin_number ?? 'N/A',
                    $employee->nhif_number ?? 'N/A',
                    $employee->status,
                    $employee->created_at->format('Y-m-d H:i:s')
                ];
            }

            return Excel::download(new EmployeesExport($exportData), 'employees_' . date('Ymd_His') . '.xlsx');

        } catch (Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export employees: ' . $e->getMessage());
        }
    }

    /**
     * Store new employee
     */
    public function store(Request $request)
    {
        \Log::info('=== EMPLOYEE STORE START ===');
        \Log::info('Request Data:', $request->all());

        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:employees,email',
            'position' => 'required',
            'department' => 'required',
            'base_salary' => 'required|numeric',
            'hire_date' => 'required|date',
            'employment_type' => 'required',
            'role' => 'required',
            'status' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            \Log::error('VALIDATION ERRORS:', $errors);

            return redirect()->back()
                ->withErrors($validator)
                ->with('error', 'Validation errors: ' . implode(', ', $errors))
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $employeeId = $this->generateUniqueEmployeeId();

            $nameParts = explode(' ', trim($request->name));
            $lastName = end($nameParts);
            $initialPassword = strtolower($lastName ?: 'employee123');
            $password = Hash::make($initialPassword);

            $totalAllowances = 0.00;
            if ($request->has('allowances') && is_array($request->allowances)) {
                $allowanceIds = $request->allowances;
                $totalAllowances = Allowance::whereIn('id', $allowanceIds)->sum('amount');
            }

            $employeeData = [
                'employee_id' => $employeeId,
                'name' => $request->name,
                'email' => $request->email,
                'password' => $password,
                'department' => $request->department,
                'position' => $request->position,
                'role' => $request->role,
                'base_salary' => $request->base_salary,
                'employment_type' => $request->employment_type,
                'hire_date' => $request->hire_date,
                'status' => $request->status,
                'allowances' => $totalAllowances,
                'deductions' => 0.00,
            ];

            $optionalFields = ['phone', 'gender', 'dob', 'nationality', 'address', 'contract_end_date',
                              'bank_name', 'account_number', 'nssf_number', 'tin_number', 'nhif_number'];

            foreach ($optionalFields as $field) {
                if ($request->has($field) && !empty($request->$field)) {
                    $employeeData[$field] = $request->$field;
                }
            }

            \Log::info('Final Employee Data:', $employeeData);

            $employee = Employee::create($employeeData);

            if ($request->has('allowances') && is_array($request->allowances)) {
                $employee->allowances()->sync($request->allowances);
            }

            DB::commit();

            \Log::info('=== EMPLOYEE STORE SUCCESS ===');

            return redirect()->route('employees.index', ['page' => 1, 'sort' => 'created_at', 'direction' => 'desc'])
                ->with('success', 'Employee registered successfully! ID: ' . $employeeId . ', Password: ' . $initialPassword)
                ->with('new_employee_id', $employeeId);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('EMPLOYEE STORE ERROR: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()->back()
                ->with('error', 'Registration failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Helper function to generate a unique Employee ID
     */
    private function generateUniqueEmployeeId()
    {
        $prefix = "EMP";

        do {
            $randomPart = strtoupper(Str::random(8));
            $newId = $prefix . '-' . $randomPart;
        } while (Employee::where('employee_id', $newId)->exists());

        return $newId;
    }

    /**
     * Calculate growth percentage for a model based on a date field.
     */
    private function calculateGrowth($model, $dateField)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();

        $currentCount = $model::where($dateField, '>=', $currentMonth)->count();
        $previousCount = $model::where($dateField, '>=', $previousMonth)
            ->where($dateField, '<', $currentMonth)
            ->count();

        if ($previousCount == 0) {
            return $currentCount > 0 ? 100 : 0;
        }

        return round((($currentCount - $previousCount) / $previousCount) * 100, 2);
    }

    /**
     * Get employees table HTML for AJAX requests - FIXED VERSION
     */
    private function getEmployeesTableHtml($employees, $request, $additionalData = [])
    {
        // Pass all required variables to the view
        return view('dashboard.employee', array_merge([
            'employees' => $employees,
            'request' => $request,
            'totalEmployees' => $additionalData['totalEmployees'] ?? 0,
            'activeEmployeeCount' => $additionalData['activeEmployeeCount'] ?? 0,
            'employeeGrowth' => $additionalData['employeeGrowth'] ?? 0,
            'complianceTasksDue' => $additionalData['complianceTasksDue'] ?? 0,
            'currentPeriod' => $additionalData['currentPeriod'] ?? Carbon::now()->format('F Y'),
            'departments' => $additionalData['departments'] ?? [],
            'banks' => $additionalData['banks'] ?? [],
            'allowances' => $additionalData['allowances'] ?? [],
            'roles' => $additionalData['roles'] ?? [],
            'userRole' => $additionalData['userRole'] ?? null,
            'search' => $additionalData['search'] ?? '',
            'department' => $additionalData['department'] ?? '',
            'status' => $additionalData['status'] ?? ''
        ], $additionalData))->fragment('employeesTable');
    }
}