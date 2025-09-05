@extends('layout.global')

@section('title', 'Compliance')

@section('header-title')
    Compliance
    <span class="payroll-badge text-xs font-semibold px-2 py-1 rounded-full ml-3 bg-green-100 text-green-800">
        <i class="fas fa-bolt mr-1"></i> Premium Plan
    </span>
@endsection

@section('header-subtitle')
    Manage tax and statutory compliance tasks for {{ $settings['company_name'] }}.
@endsection

@section('content')
    <!-- Quick Actions -->
    <div>
        <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="#" class="card hover:shadow-lg transition-all flex flex-col items-center text-center bg-white rounded-xl p-6 border border-gray-200 hover:border-green-300" onclick="openModal('submitComplianceModal')">
                <div class="w-12 h-12 rounded-lg bg-yellow-100 flex items-center justify-center mb-4">
                    <i class="fas fa-shield-alt text-yellow-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Submit Compliance</h4>
                <p class="text-sm text-gray-500 mt-1">File tax & statutory reports</p>
            </a>
        </div>
    </div>

    <!-- Compliance Tasks -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4 flex-col sm:flex-row gap-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-shield-alt text-blue-500 mr-2"></i> Compliance Tasks
            </h3>
            <div class="flex items-center space-x-4 w-full sm:w-auto">
                <input type="text" id="searchInput" class="bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full sm:w-64 p-2.5 text-sm" placeholder="Search by Task ID, Type, or Employee">
                <select id="statusFilter" class="bg-white border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500 block w-full sm:w-48 p-2.5 text-sm">
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="Submitted">Submitted</option>
                    <option value="Approved">Approved</option>
                </select>
                <button id="exportCsvButton" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                    <i class="fas fa-download mr-2"></i> Export to CSV
                    <svg class="hidden w-4 h-4 ml-2 animate-spin text-white" id="exportSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3.5-3.5L12 8v4a8 8 0 01-8-8z"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="overflow-x-auto">
                <table class="w-full" id="complianceTable">
                    <thead>
                        <tr class="text-left text-gray-500 text-sm">
                            <th class="pb-3 font-medium cursor-pointer" data-sort="task_id">Task ID <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="type">Type <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="employee">Employee <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="due_date">Due Date <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="amount">Amount ({{ $settings['currency'] }}) <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium cursor-pointer" data-sort="status">Status <i class="fas fa-sort ml-1"></i></th>
                            <th class="pb-3 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($complianceTasks as $task)
                            <tr class="task-row" data-status="{{ $task->status }}">
                                <td class="py-4">{{ $task->task_id }}</td>
                                <td class="py-4">{{ $task->type }}</td>
                                <td class="py-4">{{ $task->employee ? $task->employee->name . ' (' . $task->employee->employee_id . ')' : 'N/A' }}</td>
                                <td class="py-4">{{ $task->due_date->format('d/m/Y') }}</td>
                                <td class="py-4">{{ number_format($task->amount, 2) }}</td>
                                <td class="py-4">
                                    <span class="status-badge {{ $task->status == 'Pending' ? 'status-pending' : ($task->status == 'Submitted' ? 'status-submitted' : 'status-approved') }}">
                                        {{ $task->status }}
                                    </span>
                                </td>
                                <td class="py-4">
                                    <button class="text-blue-600 text-sm font-medium hover:underline" onclick="editCompliance('{{ $task->id }}')" title="Edit Task">Edit</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-4 text-center text-gray-500">No compliance tasks available</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @section('modals')
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="submitComplianceModal">
            <div class="bg-white rounded-xl w-full max-w-md">
                <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                    <h3 class="text-xl font-semibold text-green-600 flex items-center">
                        <i class="fas fa-shield-alt mr-2"></i> Submit Compliance
                    </h3>
                </div>
                <div class="p-6">
                    <form id="submitComplianceForm" action="{{ route('compliance.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="compliance_type">Compliance Type</label>
                            <select id="compliance_type" name="type" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="">Select Type</option>
                                <option value="NSSF">NSSF</option>
                                <option value="PAYE">PAYE</option>
                                <option value="NHIF">NHIF</option>
                            </select>
                            <span class="text-red-500 text-sm mt-1 hidden" id="complianceTypeError">Compliance Type is required</span>
                            @error('type')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="employee_id">Employee</label>
                            <select id="employee_id" name="employee_id" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="due_date">Due Date</label>
                            <input type="date" id="due_date" name="due_date" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm mt-1 hidden" id="dueDateError">Due Date is required</span>
                            @error('due_date')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="amount">Amount ({{ $settings['currency'] }})</label>
                            <input type="number" id="amount" name="amount" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <span class="text-red-500 text-sm mt-1 hidden" id="amountError">Amount is required</span>
                            @error('amount')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="details">Details</label>
                            <textarea id="details" name="details" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"></textarea>
                            @error('details')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('submitComplianceModal')">
                                <i class="fas fa-times mr-2"></i> Cancel
                            </button>
                            <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                                <i class="fas fa-save mr-2"></i> Submit
                                <svg class="hidden w-4 h-4 ml-2 animate-spin text-white" id="formSpinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3.5-3.5L12 8v4a8 8 0 01-8-8z"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

    <style>
        .status-badge { @apply px-2 py-1 rounded-full text-xs font-medium; }
        .status-pending { @apply bg-yellow-100 text-yellow-800; }
        .status-submitted { @apply bg-blue-100 text-blue-800; }
        .status-approved { @apply bg-green-100 text-green-800; }
        th[data-sort] { @apply hover:text-green-600; }
        th.sorted-asc i.fa-sort::before { content: "\f0dd"; }
        th.sorted-desc i.fa-sort::before { content: "\f0de"; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal functions
            function openModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        const modalContent = modal.querySelector('.modal-content') || modal;
                        modalContent.classList.remove('scale-95');
                        modalContent.classList.add('scale-100');
                    }, 10);
                }
            }

            function closeModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    const modalContent = modal.querySelector('.modal-content') || modal;
                    modalContent.classList.remove('scale-100');
                    modalContent.classList.add('scale-95');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                }
            }

            // Search and Filter with Debounce
            let searchTimeout;
            const searchInput = document.getElementById('searchInput');
            const statusFilter = document.getElementById('statusFilter');
            if (searchInput && statusFilter) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        const searchValue = this.value.toLowerCase();
                        const statusValue = statusFilter.value;
                        filterComplianceTable(searchValue, statusValue);
                    }, 300);
                });

                statusFilter.addEventListener('change', function() {
                    const searchValue = searchInput.value.toLowerCase();
                    const statusValue = this.value;
                    filterComplianceTable(searchValue, statusValue);
                });
            }

            function filterComplianceTable(searchValue, statusFilter) {
                const rows = document.querySelectorAll('#complianceTable .task-row');
                rows.forEach(row => {
                    const id = row.querySelector('td:first-child')?.textContent.toLowerCase() || '';
                    const type = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
                    const employee = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                    const status = row.getAttribute('data-status') || '';
                    const matchesSearch = id.includes(searchValue) || type.includes(searchValue) || employee.includes(searchValue);
                    const matchesStatus = !statusFilter || status === statusFilter;
                    row.style.display = matchesSearch && matchesStatus ? '' : 'none';
                });
            }

            // Table Sorting
            let sortDirection = {};
            document.querySelectorAll('th[data-sort]').forEach(header => {
                header.addEventListener('click', function() {
                    const sortKey = this.getAttribute('data-sort');
                    sortDirection[sortKey] = !sortDirection[sortKey] ? 'asc' : sortDirection[sortKey] === 'asc' ? 'desc' : 'asc';
                    document.querySelectorAll('th[data-sort]').forEach(th => {
                        th.classList.remove('sorted-asc', 'sorted-desc');
                        th.querySelector('i.fa-sort').className = 'fas fa-sort ml-1';
                    });
                    this.classList.add(`sorted-${sortDirection[sortKey]}`);
                    sortTable(sortKey, sortDirection[sortKey]);
                });
            });

            function sortTable(key, direction) {
                const tbody = document.querySelector('#complianceTable tbody');
                const rows = Array.from(tbody.querySelectorAll('.task-row'));
                rows.sort((a, b) => {
                    let aValue = a.querySelector(`td:nth-child(${key === 'task_id' ? 1 : key === 'type' ? 2 : key === 'employee' ? 3 : key === 'due_date' ? 4 : key === 'amount' ? 5 : 6})`)?.textContent || '';
                    let bValue = b.querySelector(`td:nth-child(${key === 'task_id' ? 1 : key === 'type' ? 2 : key === 'employee' ? 3 : key === 'due_date' ? 4 : key === 'amount' ? 5 : 6})`)?.textContent || '';
                    if (key === 'due_date') {
                        aValue = new Date(aValue.split('/').reverse().join('-')).getTime();
                        bValue = new Date(bValue.split('/').reverse().join('-')).getTime();
                    } else if (key === 'amount') {
                        aValue = parseFloat(aValue.replace(/[^0-9.-]+/g, '')) || 0;
                        bValue = parseFloat(bValue.replace(/[^0-9.-]+/g, '')) || 0;
                    }
                    return direction === 'asc' ? aValue.localeCompare(bValue, undefined, {numeric: true}) : bValue.localeCompare(aValue, undefined, {numeric: true});
                });
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
            }

            // Export to CSV
            document.getElementById('exportCsvButton').addEventListener('click', function() {
                const button = this;
                const spinner = document.getElementById('exportSpinner');
                button.disabled = true;
                spinner.classList.remove('hidden');
                setTimeout(() => {
                    try {
                        const tasks = JSON.parse('{{ $complianceTasks->map(function($task) {
                            return [
                                "task_id" => $task->task_id,
                                "type" => $task->type,
                                "employee" => $task->employee ? $task->employee->name . " (" . $task->employee->employee_id . ")" : "N/A",
                                "due_date" => $task->due_date->format("d/m/Y"),
                                "amount" => number_format($task->amount, 2),
                                "status" => $task->status
                            ];
                        })->toJson() }}');
                        const headers = ['Task ID', 'Type', 'Employee', 'Due Date', 'Amount ({{ $settings['currency'] }})', 'Status'];
                        const csvRows = [headers.join(',')];
                        tasks.forEach(task => {
                            const row = [
                                task.task_id || '',
                                `"${(task.type || '').replace(/"/g, '""')}"`,
                                `"${(task.employee || '').replace(/"/g, '""')}"`,
                                task.due_date || '',
                                task.amount || 0,
                                task.status || ''
                            ];
                            csvRows.push(row.join(','));
                        });
                        const csvContent = csvRows.join('\n');
                        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = 'compliance_tasks.csv';
                        link.click();
                        URL.revokeObjectURL(link.href);
                    } catch (e) {
                        console.error('Error exporting CSV:', e);
                        alert('Failed to export CSV. Please try again.');
                    } finally {
                        button.disabled = false;
                        spinner.classList.add('hidden');
                    }
                }, 500);
            });

            // Form Validation
            const form = document.getElementById('submitComplianceForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    let valid = true;
                    const spinner = document.getElementById('formSpinner');
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
                    } else {
                        e.preventDefault();
                        const submitButton = form.querySelector('button[type="submit"]');
                        submitButton.disabled = true;
                        spinner.classList.remove('hidden');
                        setTimeout(() => {
                            form.submit();
                        }, 500);
                    }
                });
            }

            window.editCompliance = function(id) {
                fetch(`/compliance/${id}/edit`)
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to fetch compliance task data');
                        return response.json();
                    })
                    .then(data => {
                        const form = document.getElementById('submitComplianceForm');
                        form.action = `/compliance/${id}`;
                        form.querySelectorAll('input[name="_method"]').forEach(el => el.remove());
                        form.innerHTML += `<input type="hidden" name="_method" value="PUT">`;
                        document.getElementById('compliance_type').value = data.type || '';
                        document.getElementById('employee_id').value = data.employee_id || '';
                        document.getElementById('due_date').value = data.due_date || '';
                        document.getElementById('amount').value = data.amount || '';
                        document.getElementById('details').value = data.details || '';
                        openModal('submitComplianceModal');
                    })
                    .catch(error => {
                        console.error('Error fetching compliance task:', error);
                        alert('Failed to load compliance task data. Please try again.');
                    });
            };
        });
    </script>
@endsection
