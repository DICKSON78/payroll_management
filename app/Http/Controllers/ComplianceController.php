<?php

namespace App\Http\Controllers;

use App\Models\ComplianceTask;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ComplianceController extends Controller
{
    /**
     * Display the compliance dashboard with a list of tasks.
     * Admin/HR see all, Employee sees only their own.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $employees = Employee::select('id', 'name', 'email')->get();

        $query = ComplianceTask::with('employee');

        if (strtolower($user->role) === 'employee') {
            $query->where('employee_id', $user->employee->id ?? 0);
        }

        // Handle search
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('task_id', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $complianceTasks = $query->paginate(6); 
        return view('dashboard.compliance', compact('complianceTasks', 'employees'));
    }

    /**
     * Store a new compliance task (Admin/HR only).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', Rule::in(['PAYE', 'NSSF', 'NHIF', 'WCF', 'SDL'])],
            'employee_id' => 'nullable|exists:employees,id',
            'due_date' => 'required|date|after_or_equal:today',
            'amount' => 'nullable|numeric|min:0',
            'details' => 'nullable|string|max:1000',
        ], [
            'type.required' => 'Please select a compliance type.',
            'type.in' => 'Invalid compliance type selected.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'due_date.required' => 'Please provide a due date.',
            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after_or_equal' => 'The due date cannot be in the past.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount cannot be negative.',
            'details.max' => 'The details cannot exceed 1000 characters.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => implode(', ', $validator->errors()->all())], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            ComplianceTask::create([
                'task_id' => 'CMP-' . strtoupper(uniqid()),
                'type' => $request->type,
                'employee_id' => $request->employee_id ?: null,
                'due_date' => $request->due_date,
                'amount' => $request->amount ?: null,
                'details' => $request->details,
                'status' => 'Pending',
                'created_by' => $user->id,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Compliance task created successfully.']);
            }
            return redirect()->route('compliance.index')->with('success', 'Compliance task created successfully.');
        } catch (\Exception $e) {
            \Log::error('Compliance task creation failed: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create compliance task: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to create compliance task: ' . $e->getMessage());
        }
    }

    /**
     * Get a compliance task for editing (Admin/HR only).
     */
    public function edit($id)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        $task = ComplianceTask::with('employee')->find($id);

        if (!$task) {
            return response()->json(['error' => 'Compliance task not found.'], 404);
        }

        return response()->json([
            'id' => $task->id,
            'task_id' => $task->task_id,
            'type' => $task->type,
            'employee_id' => $task->employee_id,
            'employee_name' => $task->employee ? $task->employee->name : null,
            'due_date' => $task->due_date->format('Y-m-d'),
            'amount' => $task->amount,
            'details' => $task->details,
            'status' => $task->status,
        ]);
    }

    /**
     * Update an existing compliance task (Admin/HR only).
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', Rule::in(['PAYE', 'NSSF', 'NHIF', 'WCF', 'SDL'])],
            'employee_id' => 'nullable|exists:employees,id',
            'due_date' => 'required|date|after_or_equal:today',
            'amount' => 'nullable|numeric|min:0',
            'details' => 'nullable|string|max:1000',
        ], [
            'type.required' => 'Please select a compliance type.',
            'type.in' => 'Invalid compliance type selected.',
            'employee_id.exists' => 'The selected employee does not exist.',
            'due_date.required' => 'Please provide a due date.',
            'due_date.date' => 'The due date must be a valid date.',
            'due_date.after_or_equal' => 'The due date cannot be in the past.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount cannot be negative.',
            'details.max' => 'The details cannot exceed 1000 characters.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => implode(', ', $validator->errors()->all())], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $task = ComplianceTask::findOrFail($id);
            $task->update([
                'type' => $request->type,
                'employee_id' => $request->employee_id ?: null,
                'due_date' => $request->due_date,
                'amount' => $request->amount ?: null,
                'details' => $request->details,
                'status' => 'Pending',
                'updated_by' => $user->id,
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Compliance task updated successfully.']);
            }
            return redirect()->route('compliance.index')->with('success', 'Compliance task updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Compliance task update failed: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update compliance task: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to update compliance task: ' . $e->getMessage());
        }
    }

    /**
     * Submit a compliance task (Employee can submit only their own).
     */
    public function submit(Request $request, $id)
    {
        $user = Auth::user();
        $task = ComplianceTask::findOrFail($id);

        if (strtolower($user->role) === 'employee' && ($task->employee_id != ($user->employee->id ?? 0))) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $task->update([
                'status' => 'Submitted',
                'submitted_by' => $user->id,
                'submitted_at' => now(),
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Compliance task submitted successfully.']);
            }
            return redirect()->route('compliance.index')->with('success', 'Compliance task submitted successfully.');
        } catch (\Exception $e) {
            \Log::error('Compliance task submission failed: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to submit compliance task: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Failed to submit compliance task: ' . $e->getMessage());
        }
    }
}