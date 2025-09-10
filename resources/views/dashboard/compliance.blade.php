@extends('layout.global')

@section('title', 'Compliance Management')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">Compliance Management</span>
        <span class="payroll-badge inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-shield-alt mr-1.5"></i> Regulatory Tasks
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Manage compliance tasks for PAYE, NSSF, NHIF, WCF, and SDL.</span>
@endsection

@section('content')
    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Search Input -->
    <div class="mb-6 relative">
        <div class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.65a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <input id="searchCompliance" type="text" placeholder="Search by task ID or employee..." class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-150 bg-white shadow-sm text-gray-900 placeholder-gray-500">
        </div>
    </div>

    <!-- Compliance Table Header -->
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-700 flex items-center">
            <i class="fas fa-shield-alt text-green-500 mr-2"></i> Compliance Tasks
            <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $complianceTasks->total() }} tasks</span>
        </h3>
        <button class="text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-4 focus:ring-green-200 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md" onclick="openModal('addComplianceModal')">
            <i class="fas fa-plus mr-2"></i> Add Compliance Task
        </button>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-gray-600 text-sm">
                        <th class="py-3.5 px-6 text-left font-semibold">Task ID</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Type</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Employee</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Due Date</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Amount</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                        <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody id="complianceTable" class="divide-y divide-gray-100">
                    @foreach($complianceTasks as $task)
                    @php
                        $statusColors = [
                            'Submitted' => 'bg-green-100 text-green-800',
                            'Pending' => 'bg-yellow-100 text-yellow-800',
                            'Processing' => 'bg-blue-100 text-blue-800'
                        ];
                        $statusColor = $statusColors[$task->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <tr id="task-{{ $task->id }}" class="bg-white hover:bg-gray-50/50 transition duration-150 task-row" data-task-id="{{ strtolower($task->task_id) }}" data-employee="{{ strtolower($task->employee ? $task->employee->name : '') }}">
                        <td class="py-4 px-6 text-sm text-gray-900 font-mono">{{ $task->task_id }}</td>
                        <td class="py-4 px-6 text-sm text-gray-700">{{ $task->type }}</td>
                        <td class="py-4 px-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="font-medium text-green-800">{{ $task->employee ? substr($task->employee->name, 0, 1) : 'N/A' }}</span>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $task->employee ? $task->employee->name : 'N/A' }}</div>
                                    <div class="text-sm text-gray-500">{{ $task->employee ? $task->employee->email : '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-sm text-gray-900">{{ $task->due_date->format('d/m/Y') }}</td>
                        <td class="py-4 px-6 text-sm text-gray-900 font-medium">TZS {{ number_format($task->amount, 2) }}</td>
                        <td class="py-4 px-6">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                <span class="w-2 h-2 bg-{{ $task->status == 'Submitted' ? 'green' : ($task->status == 'Pending' ? 'yellow' : 'blue') }}-500 rounded-full mr-1.5"></span>
                                {{ $task->status }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center space-x-2">
                                <button onclick="openApproveModal('edit', {{ $task->id }})" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-md hover:bg-blue-50 transition duration-150" title="Edit">
                                    <i class="fas fa-edit text-sm"></i>
                                </button>
                                @if($task->status == 'Pending')
                                <form id="submit-form-{{ $task->id }}" action="{{ route('compliance.submit', $task->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="button" onclick="openApproveModal('submit', {{ $task->id }})" class="text-green-600 hover:text-green-800 p-1.5 rounded-md hover:bg-green-50 transition duration-150" title="Submit">
                                        <i class="fas fa-paper-plane text-sm"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        @if($complianceTasks->count() == 0)
        <div class="text-center py-12">
            <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="fas fa-shield-alt text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No compliance tasks found</h3>
            <p class="text-gray-500 mb-6">Get started by adding your first compliance task.</p>
            <button class="text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-4 focus:ring-green-200 font-medium rounded-md text-sm px-4 py-2 text-center transition-all duration-200 inline-flex items-center shadow-sm hover:shadow-md" onclick="openModal('addComplianceModal')">
                <i class="fas fa-plus mr-2"></i> Add Compliance Task
            </button>
        </div>
        @endif
    </div>

    <!-- Pagination -->
@if($complianceTasks->hasPages())
<div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-5">
    <div class="text-sm text-gray-700">
        Showing {{ $complianceTasks->firstItem() }} to {{ $complianceTasks->lastItem() }} of {{ $complianceTasks->total() }} results
    </div>
    <div class="flex space-x-2">
        @if($complianceTasks->onFirstPage())
        <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Previous</span>
        @else
        <a href="{{ $complianceTasks->previousPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-gray-700 text-sm hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Previous</a>
        @endif
        
        @if($complianceTasks->hasMorePages())
        <a href="{{ $complianceTasks->nextPageUrl() }}" class="px-3 py-1.5 rounded-md bg-white border border-gray-300 text-gray-700 text-sm hover:bg-green-600 hover:text-white hover:border-green-600 transition-all duration-200">Next</a>
        @else
        <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-400 text-sm">Next</span>
        @endif
    </div>
</div>
@endif

    <!-- Add Compliance Task Modal -->
    <div id="addComplianceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="addComplianceModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="addComplianceModalTitle">
                        <i class="fas fa-plus mr-2"></i> Add Compliance Task
                    </h3>
                    <button type="button" onclick="closeModal('addComplianceModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="addComplianceForm" action="{{ route('compliance.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="type" class="block text-gray-600 text-sm font-medium mb-2">Compliance Type</label>
                            <select name="type" id="type" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="">Select Type</option>
                                <option value="PAYE">PAYE</option>
                                <option value="NSSF">NSSF</option>
                                <option value="NHIF">NHIF</option>
                                <option value="WCF">WCF</option>
                                <option value="SDL">SDL</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="typeError">Compliance Type is required</span>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="employee_id" class="block text-gray-600 text-sm font-medium mb-2">Employee (Optional)</label>
                            <select name="employee_id" id="employee_id" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">None</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="due_date" class="block text-gray-600 text-sm font-medium mb-2">Due Date</label>
                            <input type="date" name="due_date" id="due_date" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="dueDateError">Due Date is required</span>
                            @error('due_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="amount" class="block text-gray-600 text-sm font-medium mb-2">Amount (Optional)</label>
                            <input type="number" name="amount" id="amount" step="0.01" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-span-2">
                            <label for="details" class="block text-gray-600 text-sm font-medium mb-2">Details (Optional)</label>
                            <textarea name="details" id="details" rows="4" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></textarea>
                            @error('details')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-4">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('addComplianceModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Create Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Compliance Task Modal -->
    <div id="editComplianceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="editComplianceModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="editComplianceModalTitle">
                        <i class="fas fa-edit mr-2"></i> Edit Compliance Task
                    </h3>
                    <button type="button" onclick="closeModal('editComplianceModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form id="editComplianceForm" action="" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_type" class="block text-gray-600 text-sm font-medium mb-2">Compliance Type</label>
                            <select name="type" id="edit_type" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="">Select Type</option>
                                <option value="PAYE">PAYE</option>
                                <option value="NSSF">NSSF</option>
                                <option value="NHIF">NHIF</option>
                                <option value="WCF">WCF</option>
                                <option value="SDL">SDL</option>
                            </select>
                            <span class="text-red-500 text-sm hidden" id="editTypeError">Compliance Type is required</span>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="edit_employee_id" class="block text-gray-600 text-sm font-medium mb-2">Employee (Optional)</label>
                            <select name="employee_id" id="edit_employee_id" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">None</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="edit_due_date" class="block text-gray-600 text-sm font-medium mb-2">Due Date</label>
                            <input type="date" name="due_date" id="edit_due_date" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm hidden" id="editDueDateError">Due Date is required</span>
                            @error('due_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="edit_amount" class="block text-gray-600 text-sm font-medium mb-2">Amount (Optional)</label>
                            <input type="number" name="amount" id="edit_amount" step="0.01" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            @error('amount')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="col-span-2">
                            <label for="edit_details" class="block text-gray-600 text-sm font-medium mb-2">Details (Optional)</label>
                            <textarea name="details" id="edit_details" rows="4" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></textarea>
                            @error('details')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-4">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('editComplianceModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                            <i class="fas fa-save mr-2"></i> Update Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approve Action Modal -->
    <div id="approveActionModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50" role="dialog" aria-labelledby="approveActionModalTitle" aria-modal="true">
        <div class="bg-white rounded-xl w-full max-w-md transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center" id="approveActionModalTitle">
                        <i class="fas fa-check-circle mr-2"></i> Confirm Action
                    </h3>
                    <button type="button" onclick="closeModal('approveActionModal')" class="text-gray-400 hover:text-gray-500 rounded-md p-1.5 hover:bg-gray-100 transition duration-150">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <p class="text-gray-700 mb-4" id="approveActionMessage">Are you sure you want to <span id="actionType"></span> this compliance task?</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('approveActionModal')">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="button" id="confirmActionButton" class="text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200">
                        <i class="fas fa-check mr-2"></i> Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                modal.querySelector('.modal-content').classList.add('scale-100');
                modal.querySelector('.modal-content').classList.remove('scale-95');
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.querySelector('.modal-content').classList.add('scale-95');
                modal.querySelector('.modal-content').classList.remove('scale-100');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }

        function openApproveModal(action, taskId) {
            const modal = document.getElementById('approveActionModal');
            const actionTypeSpan = document.getElementById('actionType');
            const confirmButton = document.getElementById('confirmActionButton');
            const message = document.getElementById('approveActionMessage');

            actionTypeSpan.textContent = action;
            message.innerHTML = `Are you sure you want to <span id="actionType">${action}</span> this compliance task?`;

            if (action === 'edit') {
                confirmButton.onclick = () => {
                    editCompliance(taskId);
                    closeModal('approveActionModal');
                };
            } else if (action === 'submit') {
                confirmButton.onclick = () => {
                    document.getElementById(`submit-form-${taskId}`).submit();
                    closeModal('approveActionModal');
                };
            }

            openModal('approveActionModal');
        }

        function editCompliance(id) {
            fetch(`/dashboard/compliance/${id}/edit`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to fetch compliance task');
                    return response.json();
                })
                .then(data => {
                    document.getElementById('edit_type').value = data.type || '';
                    document.getElementById('edit_employee_id').value = data.employee_id || '';
                    document.getElementById('edit_due_date').value = data.due_date || '';
                    document.getElementById('edit_amount').value = data.amount || '';
                    document.getElementById('edit_details').value = data.details || '';
                    document.getElementById('editComplianceForm').action = `/dashboard/compliance/${id}`;
                    openModal('editComplianceModal');
                })
                .catch(error => {
                    console.error('Error fetching compliance task:', error);
                    alert('Failed to load compliance task data. Please try again.');
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            ['addComplianceForm', 'editComplianceForm'].forEach(formId => {
                const form = document.getElementById(formId);
                if (form) {
                    form.addEventListener('submit', function(e) {
                        let valid = true;
                        form.querySelectorAll('[required]').forEach(input => {
                            const errorElement = document.getElementById(`${input.id}Error`);
                            if (!input.value.trim()) {
                                valid = false;
                                if (errorElement) errorElement.classList.remove('hidden');
                            } else {
                                if (errorElement) errorElement.classList.add('hidden');
                            }
                        });
                        if (!valid) {
                            e.preventDefault();
                            return;
                        }

                        // For addComplianceForm, show loading state and handle submission
                        if (formId === 'addComplianceForm') {
                            const submitButton = form.querySelector('button[type="submit"]');
                            submitButton.disabled = true;
                            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Creating...';
                        }
                    });
                }
            });

            const searchInput = document.getElementById('searchCompliance');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchValue = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.task-row');
                    rows.forEach(row => {
                        const taskId = row.dataset.taskId || '';
                        const employee = row.dataset.employee || '';
                        const matches = taskId.includes(searchValue) || employee.includes(searchValue);
                        row.style.display = matches ? '' : 'none';
                    });
                });
            }
        });
    </script>
@endsection