<?php

namespace App\Http\Controllers;

use App\Models\ComplianceTask;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComplianceController extends Controller
{
    public function index()
    {
        $complianceTasks = ComplianceTask::with('employee')->get();
        $employees = Employee::all();
        return view('dashboard.compliance', compact('complianceTasks', 'employees'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'compliance_type' => 'required|in:PAYE,NSSF,NHIF',
            'employee_id' => 'nullable|exists:employees,id',
            'due_date' => 'required|date',
            'amount' => 'nullable|numeric|min:0',
            'details' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        ComplianceTask::create([
            'task_id' => 'CMP-' . uniqid(),
            'type' => $request->compliance_type,
            'employee_id' => $request->employee_id,
            'due_date' => $request->due_date,
            'amount' => $request->amount,
            'details' => $request->details,
            'status' => 'Pending'
        ]);

        return redirect()->route('compliance.index')->with('success', 'Compliance task submitted successfully');
    }

    public function edit($id)
    {
        $task = ComplianceTask::findOrFail($id);
        return response()->json([
            'type' => $task->type,
            'employee_id' => $task->employee_id,
            'due_date' => $task->due_date->format('Y-m-d'),
            'amount' => $task->amount,
            'details' => $task->details
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'compliance_type' => 'required|in:PAYE,NSSF,NHIF',
            'employee_id' => 'nullable|exists:employees,id',
            'due_date' => 'required|date',
            'amount' => 'nullable|numeric|min:0',
            'details' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $task = ComplianceTask::findOrFail($id);
        $task->update([
            'type' => $request->compliance_type,
            'employee_id' => $request->employee_id,
            'due_date' => $request->due_date,
            'amount' => $request->amount,
            'details' => $request->details,
            'status' => 'Pending'
        ]);

        return redirect()->route('dashboard.compliance')->with('success', 'Compliance task updated successfully');
    }
}
