<?php

namespace App\Http\Controllers;

use App\Models\ComplianceTask;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ComplianceController extends Controller
{
    /**
     * Display the compliance dashboard with a list of tasks.
     */
   public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get employees with both id and employee_id
        $employees = Employee::select('id', 'employee_id', 'name', 'email')->get();

        $query = ComplianceTask::with('employee');

        if (strtolower($user->role) === 'employee') {
            $query->where('employee_id', $user->id); // Use id (bigint) for filtering
        }

        // Handle search
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('task_id', 'like', '%' . $search . '%')
                  ->orWhereHas('employee', function ($q) use ($search) {
                      $q->where('name', 'like', '%' . $search . '%')
                        ->orWhere('employee_id', 'like', '%' . $search . '%'); // Search by employee_id string
                  });
            });
        }

        // Handle status filter
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        // Handle type filter
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $complianceTasks = $query->orderBy('due_date', 'asc')->paginate(10); 
        return view('dashboard.compliance', compact('complianceTasks', 'employees'));
    }

    /**
     * Store a new compliance task (Admin/HR only).
     */
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
        'employee_id' => 'nullable|exists:employees,employee_id', // HII NI MUHIMU - exists:employees,employee_id
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
            return response()->json([
                'success' => false, 
                'message' => 'Validation failed', 
                'errors' => $validator->errors()
            ], 422);
        }
        return redirect()->back()->withErrors($validator)->withInput();
    }

    try {
        $complianceTask = ComplianceTask::create([
            'task_id' => 'CMP-' . strtoupper(uniqid()),
            'type' => $request->type,
            'employee_id' => $request->employee_id ?: null, // HAKIKISHA HII NI STRING
            'due_date' => $request->due_date,
            'amount' => $request->amount ?: null,
            'details' => $request->details,
            'status' => 'Pending',
        ]);

        // Log the creation for audit purposes
        Log::info('Compliance task created', [
            'task_id' => $complianceTask->task_id,
            'type' => $complianceTask->type,
            'created_by' => $user->employee_id,
            'employee_id' => $complianceTask->employee_id
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true, 
                'message' => 'Compliance task created successfully.',
                'task' => $complianceTask
            ]);
        }
        
        return redirect()->route('compliance.index')
            ->with('success', 'Compliance task created successfully.');
            
    } catch (\Exception $e) {
        Log::error('Compliance task creation failed: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'input' => $request->except('password')
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => false, 
                'message' => 'Failed to create compliance task: ' . $e->getMessage()
            ], 500);
        }
        
        return redirect()->back()
            ->with('error', 'Failed to create compliance task: ' . $e->getMessage())
            ->withInput();
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
            'employee_id' => $task->employee_id, // This is the id (bigint)
            'employee_name' => $task->employee ? $task->employee->name : null,
            'employee_code' => $task->employee ? $task->employee->employee_id : null, // The string employee_id
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
                return response()->json([
                    'success' => false, 
                    'message' => 'Unauthorized action.'
                ], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        $validator = Validator::make($request->all(), [
            'type' => ['required', 'string', Rule::in(['PAYE', 'NSSF', 'NHIF', 'WCF', 'SDL'])],
            'employee_id' => 'nullable|exists:employees,employee_id',
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
                return response()->json([
                    'success' => false, 
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $task = ComplianceTask::findOrFail($id);
            
            $originalData = $task->toArray();
            
            $task->update([
                'type' => $request->type,
                'employee_id' => $request->employee_id ?: null,
                'due_date' => $request->due_date,
                'amount' => $request->amount ?: null,
                'details' => $request->details,
            ]);

            // Log the update for audit purposes
            Log::info('Compliance task updated', [
                'task_id' => $task->task_id,
                'updated_by' => $user->employee_id,
                'original_data' => $originalData,
                'new_data' => $task->toArray()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true, 
                    'message' => 'Compliance task updated successfully.',
                    'task' => $task
                ]);
            }
            
            return redirect()->route('compliance.index')
                ->with('success', 'Compliance task updated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Compliance task update failed: ' . $e->getMessage(), [
                'task_id' => $id,
                'user_id' => $user->id,
                'input' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Failed to update compliance task: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to update compliance task: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a compliance task (Admin/HR only).
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $task = ComplianceTask::findOrFail($id);
            $taskData = $task->toArray();
            
            $task->delete();

            // Log the deletion for audit purposes
            Log::info('Compliance task deleted', [
                'task_id' => $taskData['task_id'],
                'deleted_by' => $user->employee_id,
                'task_data' => $taskData
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Compliance task deleted successfully.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Compliance task deletion failed: ' . $e->getMessage(), [
                'task_id' => $id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Failed to delete compliance task: ' . $e->getMessage()
            ], 500);
        }
    }

 /**
     * Submit a compliance task (Employee can submit only their own).
     */
    public function submit(Request $request, $id)
    {
        $user = Auth::user();
        $task = ComplianceTask::findOrFail($id);

        // Use id (bigint) for comparison
        if (strtolower($user->role) === 'employee' && ($task->employee_id != $user->id)) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized action.');
        }

        try {
            $task->update([
                'status' => 'Submitted',
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

    /**
     * Approve a compliance task (Admin/HR only).
     */
    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            $task = ComplianceTask::findOrFail($id);
            
            // Check if task is in submitted status
            if ($task->status !== 'Submitted') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Only submitted tasks can be approved.'
                ], 422);
            }

            $task->update([
                'status' => 'Approved',
            ]);

            // Log the approval for audit purposes
            Log::info('Compliance task approved', [
                'task_id' => $task->task_id,
                'approved_by' => $user->employee_id,
                'approved_at' => now()
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Compliance task approved successfully.',
                'task' => $task
            ]);
            
        } catch (\Exception $e) {
            Log::error('Compliance task approval failed: ' . $e->getMessage(), [
                'task_id' => $id,
                'user_id' => $user->id
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Failed to approve compliance task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a compliance task (Admin/HR only).
     */
    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'Please provide a reason for rejection.',
            'rejection_reason.max' => 'Rejection reason cannot exceed 500 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false, 
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $task = ComplianceTask::findOrFail($id);
            
            // Check if task is in submitted status
            if ($task->status !== 'Submitted') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Only submitted tasks can be rejected.'
                ], 422);
            }

            $task->update([
                'status' => 'Rejected',
                'details' => $task->details . "\n\n--- REJECTION ---\nReason: " . $request->rejection_reason . "\nRejected by: " . $user->name . "\nRejected at: " . now()->format('Y-m-d H:i:s') . "\n---",
            ]);

            // Log the rejection for audit purposes
            Log::info('Compliance task rejected', [
                'task_id' => $task->task_id,
                'rejected_by' => $user->employee_id,
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now()
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Compliance task rejected successfully.',
                'task' => $task
            ]);
            
        } catch (\Exception $e) {
            Log::error('Compliance task rejection failed: ' . $e->getMessage(), [
                'task_id' => $id,
                'user_id' => $user->id,
                'rejection_reason' => $request->rejection_reason
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Failed to reject compliance task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get compliance statistics for dashboard.
     */
    public function getStats()
    {
        $user = Auth::user();
        
        $query = ComplianceTask::query();
        
        if (strtolower($user->role) === 'employee') {
            $query->where('employee_id', $user->employee_id);
        }

        $stats = [
            'total' => $query->count(),
            'pending' => $query->where('status', 'Pending')->count(),
            'submitted' => $query->where('status', 'Submitted')->count(),
            'approved' => $query->where('status', 'Approved')->count(),
            'rejected' => $query->where('status', 'Rejected')->count(),
            'overdue' => $query->where('due_date', '<', now())->where('status', 'Pending')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}