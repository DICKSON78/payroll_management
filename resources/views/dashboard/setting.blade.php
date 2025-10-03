@extends('layout.global')

@section('title', 'System Settings')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">System Settings</span>
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-cog mr-1.5"></i> Admin/HR Access
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Manage payroll settings, allowances, and deductions.</span>
@endsection

@section('content')
    <!-- Success/Error Message -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 rounded-lg mb-6 shadow-sm" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="flex space-x-4 border-b border-gray-200" role="tablist">
            <button id="payrollTab" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-t-md focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="true" aria-controls="payrollContainer">
                Payroll Settings
            </button>
            <button id="allowancesTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="allowancesContainer">
                Allowances
            </button>
            <button id="deductionsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="deductionsContainer">
                Deductions
            </button>
        </div>
    </div>

    <!-- Payroll Settings Container -->
    <div id="payrollContainer" class="block">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-xl font-semibold text-green-600 flex items-center mb-6">
                <i class="fas fa-money-check-alt mr-2"></i> Payroll Settings
            </h3>
            <form action="{{ route('settings.payroll.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="payroll_frequency" class="block text-gray-600 text-sm font-medium mb-2">Payroll Frequency</label>
                        <select name="payroll_frequency" id="payroll_frequency" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                            <option value="monthly" {{ old('payroll_frequency', $settings->payroll_frequency ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="biweekly" {{ old('payroll_frequency', $settings->payroll_frequency ?? '') == 'biweekly' ? 'selected' : '' }}>Biweekly</option>
                            <option value="weekly" {{ old('payroll_frequency', $settings->payroll_frequency ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        </select>
                        @error('payroll_frequency')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="tax_rate" class="block text-gray-600 text-sm font-medium mb-2">Default Tax Rate (%)</label>
                        <input type="number" name="tax_rate" id="tax_rate" value="{{ old('tax_rate', $settings->tax_rate ?? '') }}" step="0.01" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Enter tax rate">
                        @error('tax_rate')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center" onclick="resetPayrollForm()">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Allowances Container -->
    <div id="allowancesContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-xl font-semibold text-green-600 flex items-center mb-6">
                <i class="fas fa-hand-holding-usd mr-2"></i> Manage Allowances
            </h3>
            <!-- Add Allowance Form -->
            <form action="{{ route('settings.allowances.store') }}" method="POST" class="space-y-6 mb-8">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="allowance_name" class="block text-gray-600 text-sm font-medium mb-2">Allowance Name</label>
                        <input type="text" name="name" id="allowance_name" value="{{ old('name') }}" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Enter allowance name" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="allowance_amount" class="block text-gray-600 text-sm font-medium mb-2">Amount</label>
                        <input type="number" name="amount" id="allowance_amount" value="{{ old('amount') }}" step="0.01" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Enter amount" required>
                        @error('amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Allowance
                    </button>
                </div>
            </form>

            <!-- Allowances Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Name</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Amount</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($allowances as $allowance)
                            <tr class="bg-white hover:bg-gray-50 transition-all duration-200">
                                <td class="py-4 px-6 text-sm text-gray-600">{{ $allowance->name }}</td>
                                <td class="py-4 px-6 text-sm text-gray-600">{{ number_format($allowance->amount, 2) }}</td>
                                <td class="py-4 px-6 text-sm">
                                    <button onclick="editAllowance({{ $allowance->id }}, '{{ $allowance->name }}', {{ $allowance->amount }})" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-md hover:bg-blue-50 transition-all duration-200" title="Edit allowance">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                    <form action="{{ route('settings.allowances.destroy', $allowance->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this allowance?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 p-1.5 rounded-md hover:bg-red-50 transition-all duration-200" title="Delete allowance">
                                            <i class="fas fa-trash mr-1"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Empty State -->
            @if($allowances->count() == 0)
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-hand-holding-usd text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No allowances found</h3>
                    <p class="text-gray-500 mb-6">Add allowances to display them here.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Deductions Container -->
    <div id="deductionsContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-xl font-semibold text-green-600 flex items-center mb-6">
                <i class="fas fa-minus-circle mr-2"></i> Manage Deductions
            </h3>
            <!-- Add Deduction Form -->
            <form action="{{ route('settings.deductions.store') }}" method="POST" class="space-y-6 mb-8">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="deduction_name" class="block text-gray-600 text-sm font-medium mb-2">Deduction Name</label>
                        <input type="text" name="name" id="deduction_name" value="{{ old('name') }}" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Enter deduction name" required>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="deduction_amount" class="block text-gray-600 text-sm font-medium mb-2">Amount</label>
                        <input type="number" name="amount" id="deduction_amount" value="{{ old('amount') }}" step="0.01" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Enter amount" required>
                        @error('amount')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center">
                        <i class="fas fa-plus mr-2"></i> Add Deduction
                    </button>
                </div>
            </form>

            <!-- Deductions Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Name</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Amount</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($deductions as $deduction)
                            <tr class="bg-white hover:bg-gray-50 transition-all duration-200">
                                <td class="py-4 px-6 text-sm text-gray-600">{{ $deduction->name }}</td>
                                <td class="py-4 px-6 text-sm text-gray-600">{{ number_format($deduction->amount, 2) }}</td>
                                <td class="py-4 px-6 text-sm">
                                    <button onclick="editDeduction({{ $deduction->id }}, '{{ $deduction->name }}', {{ $deduction->amount }})" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-md hover:bg-blue-50 transition-all duration-200" title="Edit deduction">
                                        <i class="fas fa-edit mr-1"></i> Edit
                                    </button>
                                    <form action="{{ route('settings.deductions.destroy', $deduction->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this deduction?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 p-1.5 rounded-md hover:bg-red-50 transition-all duration-200" title="Delete deduction">
                                            <i class="fas fa-trash mr-1"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Empty State -->
            @if($deductions->count() == 0)
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-minus-circle text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No deductions found</h3>
                    <p class="text-gray-500 mb-6">Add deductions to display them here.</p>
                </div>
            @endif
        </div>
    </div>

@endsection

@section('modals')
    @parent
    <!-- Edit Allowance Modal -->
    <div id="editAllowanceModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Allowance</h3>
            <form id="editAllowanceForm" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_allowance_name" class="block text-gray-600 text-sm font-medium mb-2">Allowance Name</label>
                    <input type="text" name="name" id="edit_allowance_name" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="edit_allowance_amount" class="block text-gray-600 text-sm font-medium mb-2">Amount</label>
                    <input type="number" name="amount" id="edit_allowance_amount" step="0.01" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" required>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditAllowanceModal()" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200">Cancel</button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Edit Deduction Modal -->
    <div id="editDeductionModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Edit Deduction</h3>
            <form id="editDeductionForm" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="edit_deduction_name" class="block text-gray-600 text-sm font-medium mb-2">Deduction Name</label>
                    <input type="text" name="name" id="edit_deduction_name" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" required>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="edit_deduction_amount" class="block text-gray-600 text-sm font-medium mb-2">Amount</label>
                    <input type="number" name="amount" id="edit_deduction_amount" step="0.01" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" required>
                    @error('amount')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeEditDeductionModal()" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200">Cancel</button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection

<script>
    // Initialize Tab Navigation
    document.addEventListener('DOMContentLoaded', function() {
        // Tab click event listeners
        const tabs = ['payrollTab', 'allowancesTab', 'deductionsTab'];
        tabs.forEach(tabId => {
            const tab = document.getElementById(tabId);
            if (tab) {
                tab.addEventListener('click', () => toggleTab(tabId));
            }
        });

        // Add loading states to forms
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
                }
            });
        });
    });

    // Tab navigation functionality
    function toggleTab(tabId) {
        const tabs = ['payrollTab', 'allowancesTab', 'deductionsTab'];
        const containers = ['payrollContainer', 'allowancesContainer', 'deductionsContainer'];

        tabs.forEach(id => {
            const tab = document.getElementById(id);
            if (tab) {
                tab.classList.remove('bg-green-600', 'text-white');
                tab.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                tab.setAttribute('aria-selected', 'false');
            }
        });

        containers.forEach(id => {
            const container = document.getElementById(id);
            if (container) container.classList.add('hidden');
        });

        const activeTab = document.getElementById(tabId);
        if (activeTab) {
            activeTab.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            activeTab.classList.add('bg-green-600', 'text-white');
            activeTab.setAttribute('aria-selected', 'true');
        }

        const containerId = tabId.replace('Tab', 'Container');
        const container = document.getElementById(containerId);
        if (container) container.classList.remove('hidden');

        // Reset forms when switching tabs
        if (tabId === 'payrollTab') {
            resetPayrollForm();
        }
    }

    // Form reset functions
    function resetPayrollForm() {
        document.querySelector('#payrollContainer form').reset();
    }

    // Edit Allowance Modal
    function editAllowance(id, name, amount) {
        const form = document.getElementById('editAllowanceForm');
        form.action = `{{ url('admin/settings/allowances') }}/${id}`;
        document.getElementById('edit_allowance_name').value = name;
        document.getElementById('edit_allowance_amount').value = amount;
        document.getElementById('editAllowanceModal').classList.remove('hidden');
    }

    function closeEditAllowanceModal() {
        document.getElementById('editAllowanceModal').classList.add('hidden');
        document.getElementById('editAllowanceForm').reset();
    }

    // Edit Deduction Modal
    function editDeduction(id, name, amount) {
        const form = document.getElementById('editDeductionForm');
        form.action = `{{ url('admin/settings/deductions') }}/${id}`;
        document.getElementById('edit_deduction_name').value = name;
        document.getElementById('edit_deduction_amount').value = amount;
        document.getElementById('editDeductionModal').classList.remove('hidden');
    }

    function closeEditDeductionModal() {
        document.getElementById('editDeductionModal').classList.add('hidden');
        document.getElementById('editDeductionForm').reset();
    }
</script>