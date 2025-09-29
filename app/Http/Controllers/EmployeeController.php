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
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
        $user = Auth::user();
        if (!$user) {
            Log::warning('User not authenticated.');
            return redirect('/login')->with('error', 'Please log in.');
        }

        // Determine user role for access control
        $userRole = strtolower($user->role ?? 'employee');

        // Handle search and filtering parameters
        $search = $request->input('search', '');
        $department = $request->input('department', '');
        $status = $request->input('status', '');
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');

        // Validate sort column to prevent SQL injection
        $validSortColumns = ['name', 'position', 'department', 'base_salary'];
        $sort = in_array($sort, $validSortColumns) ? $sort : 'name';
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        // Fetch paginated employee records with eager loading and filters
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

        // Data for Dashboard/Widgets
        $totalEmployees = Employee::count();
        $activeEmployeeCount = Employee::where('status', 'active')->count();
        $employeeGrowth = $this->calculateGrowth(Employee::class, 'hire_date');
        $complianceTasksDue = ComplianceTask::whereDate('due_date', '<=', Carbon::now()->addDays(7))->count();
        $currentPeriod = Carbon::now()->format('F Y');

        // Fetch supporting data for frontend dropdowns/forms
        $departments = Department::all();
        $banks = Bank::all();
        $allowances = Allowance::where('active', 1)->get();
        $roles = Role::all();

        if ($request->ajax()) {
            // Return table HTML directly for AJAX requests
            ob_start();
            ?>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-users text-green-500 mr-2"></i> Employee List
                    <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $employees->total() }} employees</span>
                </h3>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700 text-sm">
                                <th class="py-3.5 px-6 text-left font-semibold cursor-pointer" onclick="sortTable('name')" data-sort-column="name" data-sort-direction="{{ $request->input('direction', 'asc') }}">
                                    <div class="flex items-center space-x-1">
                                        <span>Employee</span>
                                        <i class="fas fa-sort text-gray-400"></i>
                                    </div>
                                </th>
                                <th class="py-3.5 px-6 text-left font-semibold cursor-pointer" onclick="sortTable('position')" data-sort-column="position" data-sort-direction="{{ $request->input('direction', 'asc') }}">
                                    <div class="flex items-center space-x-1">
                                        <span>Position</span>
                                        <i class="fas fa-sort text-gray-400"></i>
                                    </div>
                                </th>
                                <th class="py-3.5 px-6 text-left font-semibold cursor-pointer" onclick="sortTable('department')" data-sort-column="department" data-sort-direction="{{ $request->input('direction', 'asc') }}">
                                    <div class="flex items-center space-x-1">
                                        <span>Department</span>
                                        <i class="fas fa-sort text-gray-400"></i>
                                    </div>
                                </th>
                                <th class="py-3.5 px-6 text-left font-semibold cursor-pointer" onclick="sortTable('base_salary')" data-sort-column="base_salary" data-sort-direction="{{ $request->input('direction', 'asc') }}">
                                    <div class="flex items-center space-x-1">
                                        <span>Salary</span>
                                        <i class="fas fa-sort text-gray-400"></i>
                                    </div>
                                </th>
                                <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                                <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="employeesTableBody" class="divide-y divide-gray-100">
                            <?php foreach ($employees as $employee): ?>
                                <?php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-red-100 text-red-800',
                                        'terminated' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $statusColor = $statusColors[$employee->status ?? 'active'] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <tr class="bg-white hover:bg-gray-50 transition-all duration-200 employee-row"
                                    data-name="<?php echo strtolower($employee->name); ?>"
                                    data-email="<?php echo strtolower($employee->email); ?>"
                                    data-employee-id="<?php echo strtolower($employee->employee_id); ?>"
                                    data-department="<?php echo strtolower($employee->department); ?>"
                                    data-status="<?php echo strtolower($employee->status); ?>"
                                    data-position="<?php echo strtolower($employee->position); ?>">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                                <span class="font-medium text-green-800"><?php echo substr($employee->name, 0, 1); ?></span>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900"><?php echo $employee->name; ?></div>
                                                <div class="text-sm text-gray-500"><?php echo $employee->email; ?></div>
                                                <div class="text-xs text-gray-400"><?php echo $employee->employee_id; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-900"><?php echo $employee->position ?? 'N/A'; ?></td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php echo $employee->departmentRel->name ?? $employee->department ?? 'N/A'; ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm font-medium text-gray-900">TZS <?php echo number_format($employee->base_salary, 0); ?></td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $statusColor; ?>">
                                            <i class="fas fa-circle mr-1" style="font-size: 6px;"></i>
                                            <?php echo ucfirst($employee->status); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex space-x-2">
                                            <button onclick="viewEmployeeDetails('<?php echo $employee->employee_id; ?>')" class="text-blue-600 hover:text-blue-800" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button onclick="editEmployee('<?php echo $employee->employee_id; ?>')" class="text-green-600 hover:text-green-800" title="Edit Employee">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="toggleStatus('<?php echo $employee->employee_id; ?>', '<?php echo $employee->status; ?>')" class="text-gray-600 hover:text-gray-800" title="<?php echo $employee->status === 'active' ? 'Deactivate' : 'Activate'; ?> Employee">
                                                <i class="fas <?php echo $employee->status === 'active' ? 'fa-power-off' : 'fa-play'; ?>"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-4" id="paginationContainer">
                    <?php echo $employees->links(); ?>
                </div>
            </div>
            <?php
            return ob_get_clean();
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

public function store(Request $request)
{
    \Log::info('=== EMPLOYEE STORE START ===');
    \Log::info('Request Data:', $request->all());

    // Check what data exists in database
    $departments = Department::pluck('id', 'name')->toArray();
    $roles = Role::pluck('slug', 'name')->toArray();
    
    \Log::info('Available Departments:', $departments);
    \Log::info('Available Roles:', $roles);

    // Validation rahisi sana - ondoa validation ngumu
    $rules = [
        'name' => 'required',
        'email' => 'required|email',
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

    // Check if email already exists
    if (Employee::where('email', $request->email)->exists()) {
        \Log::error('Email already exists: ' . $request->email);
        return redirect()->back()
            ->with('error', 'Email already registered: ' . $request->email)
            ->withInput();
    }

    try {
        DB::beginTransaction();

        // Generate Employee ID
        $employeeId = "EMP-" . strtoupper(Str::random(8));
        
        // Generate Password
        $nameParts = explode(' ', trim($request->name));
        $lastName = end($nameParts);
        $initialPassword = strtolower($lastName ?: 'employee123');
        $password = Hash::make($initialPassword);

        // Create employee
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
            'allowances' => 0.00,
            'deductions' => 0.00,
        ];

        // Add optional fields kama zipo
        $optionalFields = ['phone', 'gender', 'dob', 'nationality', 'address', 'contract_end_date', 
                          'bank_name', 'account_number', 'nssf_number', 'tin_number', 'nhif_number'];
        
        foreach ($optionalFields as $field) {
            if ($request->has($field) && !empty($request->$field)) {
                $employeeData[$field] = $request->$field;
            }
        }

        \Log::info('Final Employee Data:', $employeeData);

        $employee = Employee::create($employeeData);
        \Log::info('Employee created with ID: ' . $employee->id);

        // Handle allowances baadaye - skip kwanza
        if ($request->has('allowances') && is_array($request->allowances)) {
            \Log::info('Allowances found, will sync later: ', $request->allowances);
        }

        DB::commit();

        \Log::info('=== EMPLOYEE STORE SUCCESS ===');

        return redirect()->route('employees.index')
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
        // Tengeneza mchanganyiko wa herufi na namba (8 characters)
        $randomPart = strtoupper(Str::random(8));
        $newId = $prefix . '-' . $randomPart;
        
    } while (Employee::where('employee_id', $newId)->exists());

    return $newId;
}

/**
 * Helper function to extract the last word from a full name (LastName)
 */
private function getLastName($fullName)
{
    $parts = array_filter(explode(' ', trim($fullName)));
    
    if (count($parts) > 0) {
        return end($parts);
    }
    return 'employee'; // Default password kama hakuna jina la mwisho
}
    /**
     * Display the specified employee's data as HTML for modals.
     */
    public function show($employeeId, Request $request)
    {
        Log::info('Attempting to fetch employee with ID: ' . $employeeId);
        try {
            $employee = Employee::with(['departmentRel', 'allowances' => function($query) {
                $query->where('active', 1); // Only load active allowances
            }])
                ->withTrashed()
                ->whereRaw('LOWER(employee_id) = ?', [strtolower($employeeId)])
                ->firstOrFail();

            $departments = Department::all();
            $banks = Bank::all();
            $allowances = Allowance::where('active', 1)->get();
            $roles = Role::all();

            $mode = $request->query('mode', 'view');

            if ($mode === 'edit') {
                // Inline edit form HTML
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
                                        <option value="<?php echo $dept->id; ?>" <?php echo $employee->department == $dept->id ? 'selected' : ''; ?>><?php echo $dept->name; ?></option>
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
                                                // Fix: Check if allowances is a collection and contains the allowance
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
                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" onclick="closeModal('editEmployeeModal')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>
                </form>
                <?php
                return ob_get_clean();
            } else {
                // Inline view HTML
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
                            // Fix: Check if allowances is a collection and not empty
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
        $employee = Employee::withTrashed()
            ->whereRaw('LOWER(employee_id) = ?', [strtolower($employeeId)])
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employee->id)],
            'position' => 'required|string|max:255',
            'department' => 'required|exists:departments,id',
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
            'allowance' => 'nullable|array', // ensure allowances array
            'allowance.*' => 'exists:allowances,id',
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

            // Calculate total allowances
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
                'deductions' => 0.00, // Placeholder; update if deductions table exists
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
            $employee = Employee::withTrashed()
                ->whereRaw('LOWER(employee_id) = ?', [strtolower($employeeId)])
                ->firstOrFail();
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
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Define headers matching the import template instructions
            $headers = [
                'name', 'email', 'phone', 'gender', 'dob', 'nationality', 'address',
                'department', 'position', 'employment_type', 'hire_date', 'contract_end_date',
                'base_salary', 'bank_name', 'account_number', 'nssf_number', 'tin_number',
                'nhif_number', 'role'
            ];

            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle('A1:S1')->getFont()->setBold(true);

            // Add example data row for clarity
            $exampleData = [
                'John Doe', 'john.doe@example.com', '+255123456789', 'male', '1990-01-01',
                'Tanzanian', '123 Street, Dar es Salaam', '1', 'Software Engineer',
                'full-time', '2023-01-01', '', '1000000', 'CRDB', '1234567890',
                'NSSF123456', 'TIN123456', 'NHIF123456', 'employee'
            ];
            $sheet->fromArray([$exampleData], null, 'A2');

            // Auto-size columns for better readability
            foreach (range('A', 'S') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'employee_import_template_' . date('Ymd_His') . '.xlsx';

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
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('error', 'Invalid file format. Please upload an XLSX, XLS, or CSV file.')
                ->withInput();
        }

        try {
            Excel::import(new EmployeesImport, $request->file('file'));
            return redirect()->route('employees.index')
                ->with('success', 'Employees imported successfully.');
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
            return Excel::download(new EmployeesExport, 'employees_' . date('Ymd_His') . '.xlsx');
        } catch (Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export employees: ' . $e->getMessage());
        }
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
}
