<?php

namespace App\Http\Controllers;

use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class SettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            Log::warning('No authenticated user found');
            return redirect('/login')->with('error', 'Please log in.');
        }

        if (!in_array(strtolower($user->role), ['admin', 'hr manager'])) {
            Log::warning('Unauthorized access attempt', ['user_id' => $user->id, 'role' => $user->role]);
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $settings = $this->getSettings();
        $allowances = Allowance::all();
        $deductions = Deduction::all();

        return view('dashboard.setting', compact(
            'user',
            'settings',
            'allowances',
            'deductions'
        ));
    }

    private function getSettings()
    {
        $settings = [];
        
        $settingRecords = Setting::all();
        foreach ($settingRecords as $setting) {
            $settings[$setting->key] = $this->castSettingValue($setting->value, $setting->type);
        }

        // Map schema keys to view expectations
        $mappings = [
            'pay_schedule' => 'payroll_frequency',
            'default_currency' => 'currency',
            'tax_rate' => 'tax_rate',
        ];

        foreach ($mappings as $schemaKey => $viewKey) {
            if (isset($settings[$schemaKey])) {
                $settings[$viewKey] = $settings[$schemaKey];
            }
        }

        // Set defaults if not exists
        $defaults = [
            'payroll_frequency' => 'monthly',
            'tax_rate' => 0.0,
            'currency' => 'TZS',
            'processing_day' => 25,
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!isset($settings[$key])) {
                $settings[$key] = $defaultValue;
            }
        }

        return (object) $settings;
    }

    private function castSettingValue($value, $type)
    {
        switch ($type) {
            case 'integer':
                return (int) $value;
            case 'decimal':
                return (float) $value;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'array':
            case 'json':
                if (is_array($value)) {
                    return $value;
                }
                if (is_null($value) || empty($value)) {
                    return [];
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    return json_last_error() === JSON_ERROR_NONE ? $decoded : [];
                }
                return [];
            default:
                return $value;
        }
    }

    public function updatePayroll(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'payroll_frequency' => 'required|in:monthly,biweekly,weekly',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'processing_day' => 'required|integer|min:1|max:31',
            'currency' => 'required|string|size:3',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $settingsData = [
                'pay_schedule' => $request->payroll_frequency,
                'tax_rate' => $request->tax_rate,
                'processing_day' => $request->processing_day,
                'default_currency' => $request->currency,
            ];

            foreach ($settingsData as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'type' => $this->getSettingType($value),
                        'category' => 'payroll',
                        'description' => $this->getSettingDescription($key),
                        'updated_by' => Auth::id(),
                    ]
                );
            }

            Log::info('Payroll settings updated', ['user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Payroll configuration updated successfully.');
        } catch (\Exception $e) {
            Log::error('Payroll settings update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update payroll settings: ' . $e->getMessage());
        }
    }

    private function getSettingType($value)
    {
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'decimal';
        if (is_bool($value)) return 'boolean';
        if (is_array($value)) return 'array';
        return 'string';
    }

    private function getSettingDescription($key)
    {
        $descriptions = [
            'pay_schedule' => 'Payroll processing schedule',
            'tax_rate' => 'Default tax rate (%)',
            'processing_day' => 'Day of month for payroll processing',
            'default_currency' => 'Default currency for payroll',
        ];

        return $descriptions[$key] ?? 'System setting';
    }

    public function storeAllowance(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:allowance,name',
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

    public function updateAllowance(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $allowance = Allowance::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:allowance,name,' . $allowance->id,
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0' . ($request->type === 'percentage' ? '|max:100' : ''),
            'taxable' => 'boolean',
            'active' => 'boolean',
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
                'active' => $request->boolean('active', true),
            ]);

            Log::info('Allowance updated', ['id' => $allowance->id, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Allowance updated successfully.');
        } catch (\Exception $e) {
            Log::error('Allowance update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update allowance: ' . $e->getMessage());
        }
    }

    public function destroyAllowance($id)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        try {
            $allowance = Allowance::findOrFail($id);
            
            // Check if allowance is used by any employee before deleting
            $isUsed = DB::table('employee_allowance')->where('allowance_id', $id)->exists();
            
            if ($isUsed) {
                return redirect()->back()->with('error', 'Cannot delete allowance. It is currently assigned to employees.');
            }
            
            $allowance->delete();

            Log::info('Allowance deleted', ['id' => $allowance->id, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Allowance deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Allowance deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete allowance: ' . $e->getMessage());
        }
    }

    public function storeDeduction(Request $request)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:deductions,name',
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

    public function updateDeduction(Request $request, $id)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        $deduction = Deduction::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:deductions,name,' . $deduction->id,
            'category' => 'required|in:statutory,voluntary',
            'type' => 'required|in:fixed,percentage',
            'amount' => 'required|numeric|min:0' . ($request->type === 'percentage' ? '|max:100' : ''),
            'active' => 'boolean',
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
                'active' => $request->boolean('active', true),
            ]);

            Log::info('Deduction updated', ['id' => $deduction->id, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Deduction updated successfully.');
        } catch (\Exception $e) {
            Log::error('Deduction update failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update deduction: ' . $e->getMessage());
        }
    }

    public function destroyDeduction($id)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        try {
            $deduction = Deduction::findOrFail($id);
            
            // Check if deduction is used by any employee before deleting
            $isUsed = DB::table('employee_deduction')->where('deduction_id', $id)->exists();
            
            if ($isUsed) {
                return redirect()->back()->with('error', 'Cannot delete deduction. It is currently assigned to employees.');
            }
            
            $deduction->delete();

            Log::info('Deduction deleted', ['id' => $deduction->id, 'user_id' => Auth::id()]);
            return redirect()->route('settings.index')->with('success', 'Deduction deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Deduction deletion failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete deduction: ' . $e->getMessage());
        }
    }

    public function toggleAllowance($id)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        try {
            $allowance = Allowance::findOrFail($id);
            $allowance->update([
                'active' => !$allowance->active
            ]);

            $status = $allowance->active ? 'activated' : 'deactivated';
            Log::info('Allowance status toggled', ['id' => $allowance->id, 'status' => $status, 'user_id' => Auth::id()]);
            
            return redirect()->route('settings.index')->with('success', "Allowance {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Allowance toggle failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update allowance status.');
        }
    }

    public function toggleDeduction($id)
    {
        $this->authorizeRole(['admin', 'hr manager']);

        try {
            $deduction = Deduction::findOrFail($id);
            $deduction->update([
                'active' => !$deduction->active
            ]);

            $status = $deduction->active ? 'activated' : 'deactivated';
            Log::info('Deduction status toggled', ['id' => $deduction->id, 'status' => $status, 'user_id' => Auth::id()]);
            
            return redirect()->route('settings.index')->with('success', "Deduction {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Deduction toggle failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update deduction status.');
        }
    }

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