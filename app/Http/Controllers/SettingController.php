<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\ComplianceTask;
use App\Models\Allowance;
use App\Models\Deduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controller;
use Carbon\Carbon;

class SettingController extends Controller
{
    /**
     * Constructor to apply middleware
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the settings dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            Log::warning('No authenticated user found');
            return redirect('/login')->with('error', 'Please log in.');
        }

        // Restrict access to admin and hr manager roles
        if (!in_array(strtolower($user->role), ['admin', 'hr manager'])) {
            Log::warning('Unauthorized access attempt', ['user_id' => $user->id, 'role' => $user->role]);
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        // Default settings (aligned with schema and setting.blade.php)
        $settings = [
            'pay_schedule' => 'monthly',
            'processing_day' => 25,
            'default_currency' => 'TZS',
            'overtime_calculation' => '1.5x',
            'nssf_employer_rate' => 10.00,
            'nssf_employee_rate' => 10.00,
            'nhif_calculation_method' => 'tiered',
            'paye_tax_free' => 270000,
            'email_notifications' => ['payroll_processing', 'payment_confirmation'],
            'sms_enabled' => false,
            'sms_gateway' => 'twilio',
            'sms_balance_alert' => 100,
            'accounting_software' => '',
            'api_key' => '',
            'bank_api' => '',
            'bank_endpoint' => '',
            'attendance_sync' => false,
            'sync_frequency' => 'daily',
            'last_sync' => 'Never',
        ];

        // Fetch counts for dashboard
        $totalEmployees = Employee::count();
        $activePayrolls = Payroll::where('status', 'Processed')->count();
        $payslipsThisMonth = Payslip::where('period', Carbon::now()->format('Y-m'))->count();
        $pendingComplianceTasks = ComplianceTask::where('status', 'Pending')->count();

        // Fetch allowances and deductions
        $allowances = Allowance::all();
        $deductions = Deduction::all();

        // Fetch employees for dropdowns
        $employees = Employee::select('id', 'name')->orderBy('name')->get();

        return view('dashboard.setting', compact(
            'user',
            'settings',
            'totalEmployees',
            'activePayrolls',
            'payslipsThisMonth',
            'pendingComplianceTasks',
            'allowances',
            'deductions',
            'employees'
        ));
    }

    /**
     * Update personal account settings
     */
    public function updatePersonal(Request $request)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr manager'])) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password ? Hash::make($request->password) : $user->password,
            ]);

            Log::info('Personal account updated', ['user_id' => $user->id]);
            return redirect()->route('settings.index')->with('success', 'Personal account updated successfully.');
        } catch (\Exception $e) {
            Log::error('Personal account update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update personal account: ' . $e->getMessage());
        }
    }

    /**
     * Update payroll configuration
     */
    public function updatePayroll(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'pay_schedule' => 'required|in:monthly,bi-weekly,weekly',
            'processing_day' => 'required|integer|min:1|max:31',
            'default_currency' => 'required|in:TZS,USD',
            'overtime_calculation' => 'required|in:1.5x,2x,custom',
            'nssf_employer_rate' => 'required|numeric|min:0|max:100',
            'nssf_employee_rate' => 'required|numeric|min:0|max:100',
            'nhif_calculation_method' => 'required|in:tiered,fixed',
            'paye_tax_free' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $settings = [
                'pay_schedule' => $request->pay_schedule,
                'processing_day' => $request->processing_day,
                'default_currency' => $request->default_currency,
                'overtime_calculation' => $request->overtime_calculation,
                'nssf_employer_rate' => $request->nssf_employer_rate,
                'nssf_employee_rate' => $request->nssf_employee_rate,
                'nhif_calculation_method' => $request->nhif_calculation_method,
                'paye_tax_free' => $request->paye_tax_free,
            ];

            cache()->put('payroll_settings', $settings, now()->addDays(30));

            Log::info('Payroll settings updated', ['user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Payroll configuration updated successfully.');
        } catch (\Exception $e) {
            Log::error('Payroll settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update payroll settings: ' . $e->getMessage());
        }
    }

    /**
     * Store a new allowance
     */
    public function storeAllowance(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0' . ($request->type === 'percentage' ? '|max:100' : ''),
            'taxable' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Allowance::create([
                'name' => $request->name,
                'type' => $request->type,
                'amount' => $request->amount,
                'taxable' => $request->boolean('taxable', false),
                'active' => true,
            ]);

            Log::info('Allowance created', ['name' => $request->name, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Allowance added successfully.');
        } catch (\Exception $e) {
            Log::error('Allowance creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add allowance: ' . $e->getMessage());
        }
    }

    /**
     * Update an allowance
     */
    public function updateAllowance(Request $request, Allowance $allowance)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0' . ($request->type === 'percentage' ? '|max:100' : ''),
            'taxable' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $allowance->update([
                'name' => $request->name,
                'type' => $request->type,
                'amount' => $request->amount,
                'taxable' => $request->boolean('taxable', false),
            ]);

            Log::info('Allowance updated', ['id' => $allowance->id, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Allowance updated successfully.');
        } catch (\Exception $e) {
            Log::error('Allowance update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update allowance: ' . $e->getMessage());
        }
    }

    /**
     * Delete an allowance
     */
    public function destroyAllowance(Allowance $allowance)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        try {
            $allowance->delete();

            Log::info('Allowance deleted', ['id' => $allowance->id, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Allowance deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Allowance deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete allowance: ' . $e->getMessage());
        }
    }

    /**
     * Store a new deduction
     */
    public function storeDeduction(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|in:statutory,voluntary',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0' . ($request->type === 'percentage' ? '|max:100' : ''),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            Deduction::create([
                'name' => $request->name,
                'category' => $request->category,
                'type' => $request->type,
                'amount' => $request->amount,
                'active' => true,
            ]);

            Log::info('Deduction created', ['name' => $request->name, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Deduction added successfully.');
        } catch (\Exception $e) {
            Log::error('Deduction creation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add deduction: ' . $e->getMessage());
        }
    }

    /**
     * Update a deduction
     */
    public function updateDeduction(Request $request, Deduction $deduction)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|in:statutory,voluntary',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0' . ($request->type === 'percentage' ? '|max:100' : ''),
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $deduction->update([
                'name' => $request->name,
                'category' => $request->category,
                'type' => $request->type,
                'amount' => $request->amount,
            ]);

            Log::info('Deduction updated', ['id' => $deduction->id, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Deduction updated successfully.');
        } catch (\Exception $e) {
            Log::error('Deduction update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update deduction: ' . $e->getMessage());
        }
    }

    /**
     * Delete a deduction
     */
    public function destroyDeduction(Deduction $deduction)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        try {
            $deduction->delete();

            Log::info('Deduction deleted', ['id' => $deduction->id, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Deduction deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Deduction deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete deduction: ' . $e->getMessage());
        }
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'email_notifications' => 'array',
            'email_notifications.*' => 'in:payroll_processing,payment_confirmation,contract_expiry,tax_filing',
            'sms_enabled' => 'boolean',
            'sms_gateway' => 'required_if:sms_enabled,1|in:twilio,africas_talking,custom',
            'sms_balance_alert' => 'required_if:sms_enabled,1|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $settings = [
                'email_notifications' => $request->email_notifications ?? [],
                'sms_enabled' => $request->boolean('sms_enabled', false),
                'sms_gateway' => $request->sms_enabled ? $request->sms_gateway : null,
                'sms_balance_alert' => $request->sms_enabled ? $request->sms_balance_alert : null,
            ];

            cache()->put('notification_settings', $settings, now()->addDays(30));

            Log::info('Notification settings updated', ['user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Notification settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Notification settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update notification settings: ' . $e->getMessage());
        }
    }

    /**
     * Update integration settings
     */
    public function updateIntegrations(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'accounting_software' => 'nullable|in:quickbooks,xero,sage',
            'api_key' => 'nullable|string|max:255',
            'bank_api' => 'nullable|in:swift,custom',
            'bank_endpoint' => 'nullable|url',
            'attendance_sync' => 'boolean',
            'sync_frequency' => 'required_if:attendance_sync,1|in:daily,weekly,monthly',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $settings = [
                'accounting_software' => $request->accounting_software,
                'api_key' => $request->api_key,
                'bank_api' => $request->bank_api,
                'bank_endpoint' => $request->bank_endpoint,
                'attendance_sync' => $request->boolean('attendance_sync', false),
                'sync_frequency' => $request->attendance_sync ? $request->sync_frequency : null,
                'last_sync' => $request->attendance_sync ? now()->toDateTimeString() : 'Never',
            ];

            cache()->put('integration_settings', $settings, now()->addDays(30));

            Log::info('Integration settings updated', ['user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Integration settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Integration settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update integration settings: ' . $e->getMessage());
        }
    }

    /**
     * Authorize role check
     */
    private function authorizeRole($roles)
    {
        $user = Auth::user();
        $roles = is_array($roles) ? $roles : [$roles];
        if (!$user || !in_array(strtolower($user->role), array_map('strtolower', $roles))) {
            Log::warning('Unauthorized action', ['user_id' => $user->id ?? null, 'role' => $user->role ?? 'none']);
            abort(403, 'Unauthorized action.');
        }
    }
}