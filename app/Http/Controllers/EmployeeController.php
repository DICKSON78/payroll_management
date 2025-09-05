<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::all();
        return view('dashboard.employee', compact('employees'));
    }

    public function store(Request $request)
    {
        Log::info('Form data: ' . json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees',
            'department' => 'required|string',
            'position' => 'required|string',
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'hire_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after_or_equal:hire_date',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'employment_type' => 'required|in:Full-Time,Part-Time,Contract'
        ]);

        if ($validator->fails()) {
            Log::error('Validation errors: ' . json_encode($validator->errors()));
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Generate unique employee_id (e.g., EMP-001)
        $lastEmployee = Employee::orderBy('id', 'desc')->first();
        $nextId = $lastEmployee ? $lastEmployee->id + 1 : 1;
        $employeeId = 'EMP-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        Employee::create(array_merge($request->all(), [
            'employee_id' => $employeeId,
            'status' => 'Active'
        ]));

        return redirect()->route('dashboard.employee')->with('success', 'Employee added successfully');
    }

    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return response()->json([
            'employee_id' => $employee->employee_id ?? '',
            'name' => $employee->name ?? '',
            'email' => $employee->email ?? '',
            'department' => $employee->department ?? '',
            'position' => $employee->position ?? '',
            'base_salary' => $employee->base_salary ?? 0,
            'allowances' => $employee->allowances ?? 0,
            'employment_type' => $employee->employment_type ?? '',
            'bank_name' => $employee->bank_name ?? '',
            'account_number' => $employee->account_number ?? '',
            'hire_date' => $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '',
            'contract_end_date' => $employee->contract_end_date ? $employee->contract_end_date->format('Y-m-d') : '',
            'status' => $employee->status ?? 'Active'
        ]);
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return response()->json([
            'name' => $employee->name ?? '',
            'email' => $employee->email ?? '',
            'department' => $employee->department ?? '',
            'position' => $employee->position ?? '',
            'base_salary' => $employee->base_salary ?? 0,
            'allowances' => $employee->allowances ?? 0,
            'employment_type' => $employee->employment_type ?? '',
            'bank_name' => $employee->bank_name ?? '',
            'account_number' => $employee->account_number ?? '',
            'hire_date' => $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '',
            'contract_end_date' => $employee->contract_end_date ? $employee->contract_end_date->format('Y-m-d') : ''
        ]);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees,email,' . $employee->id,
            'department' => 'required|string',
            'position' => 'required|string',
            'base_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'hire_date' => 'required|date',
            'contract_end_date' => 'nullable|date|after_or_equal:hire_date',
            'bank_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'employment_type' => 'required|in:Full-Time,Part-Time,Contract'
        ]);

        if ($validator->fails()) {
            Log::error('Validation errors: ' . json_encode($validator->errors()));
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $employee->update($request->all());

        return redirect()->route('dashboard.employee')->with('success', 'Employee updated successfully');
    }
}