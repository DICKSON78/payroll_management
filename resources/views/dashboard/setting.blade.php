@extends('layout.global')

@section('title', 'System Settings')

@section('header-title')
    <div class="flex items-center space-x-3">
        <span class="text-2xl font-bold text-gray-900">System Settings</span>
        <span class="inline-flex items-center px-3 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
            <i class="fas fa-cogs mr-1.5"></i> Admin Access
        </span>
    </div>
@endsection

@section('header-subtitle')
    <span class="text-gray-600">Configure payroll, allowances, deductions, notifications, and integrations.</span>
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
            <button id="personalAccountTab" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-t-md focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="true" aria-controls="personalAccountContainer" onclick="toggleTab('personalAccountTab')">
                Personal Account
            </button>
            <button id="payrollConfigTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="payrollConfigContainer" onclick="toggleTab('payrollConfigTab')">
                Payroll Configuration
            </button>
            <button id="allowancesTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="allowancesContainer" onclick="toggleTab('allowancesTab')">
                Allowance Types
            </button>
            <button id="deductionsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="deductionsContainer" onclick="toggleTab('deductionsTab')">
                Deduction Types
            </button>
            <button id="notificationsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="notificationsContainer" onclick="toggleTab('notificationsTab')">
                Notifications
            </button>
            <button id="integrationsTab" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-t-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-green-300 transition-all duration-200" role="tab" aria-selected="false" aria-controls="integrationsContainer" onclick="toggleTab('integrationsTab')">
                Integrations
            </button>
        </div>
    </div>

    <!-- Personal Account Container -->
    <div id="personalAccountContainer" class="block">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-lg font-medium text-gray-700 flex items-center mb-4">
                <i class="fas fa-user-cog text-green-500 mr-2"></i> Personal Account Settings
            </h3>
            <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="name">Full Name</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-user text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Your full name" required>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="email">Email Address</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-envelope text-gray-400 text-base"></i>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="your@email.com" required>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="password">New Password</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-lock text-gray-400 text-base"></i>
                        </div>
                        <input type="password" name="password" id="password" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Leave blank to keep current">
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="password_confirmation">Confirm Password</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-lock text-gray-400 text-base"></i>
                        </div>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="Confirm new password">
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="resetPersonalForm()" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payroll Configuration Container -->
    <div id="payrollConfigContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-lg font-medium text-gray-700 flex items-center mb-4">
                <i class="fas fa-money-bill-wave text-green-500 mr-2"></i> Payroll Configuration
            </h3>

            <form action="{{ route('settings.payroll.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="pay_schedule">Pay Schedule</label>
                        <select name="pay_schedule" id="pay_schedule" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                            <option value="monthly" {{ ($settings['pay_schedule'] ?? 'monthly') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            <option value="bi-weekly" {{ ($settings['pay_schedule'] ?? 'monthly') == 'bi-weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                            <option value="weekly" {{ ($settings['pay_schedule'] ?? 'monthly') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        </select>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="processing_day">Processing Day</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-day text-gray-400 text-base"></i>
                        </div>
                        <input type="number" name="processing_day" id="processing_day" min="1" max="31" value="{{ $settings['processing_day'] ?? 25 }}" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900 placeholder-gray-500" placeholder="25">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="default_currency">Default Currency</label>
                        <select name="default_currency" id="default_currency" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                            <option value="TZS" {{ ($settings['default_currency'] ?? 'TZS') == 'TZS' ? 'selected' : '' }}>TZS - Tanzanian Shilling</option>
                            <option value="USD" {{ ($settings['default_currency'] ?? 'TZS') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="overtime_calculation">Overtime Calculation</label>
                        <select name="overtime_calculation" id="overtime_calculation" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                            <option value="1.5x" {{ ($settings['overtime_calculation'] ?? '1.5x') == '1.5x' ? 'selected' : '' }}>1.5x Regular Rate</option>
                            <option value="2x" {{ ($settings['overtime_calculation'] ?? '1.5x') == '2x' ? 'selected' : '' }}>2x Regular Rate</option>
                            <option value="custom" {{ ($settings['overtime_calculation'] ?? '1.5x') == 'custom' ? 'selected' : '' }}>Custom Rate</option>
                        </select>
                    </div>
                </div>
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h4 class="text-lg font-medium text-gray-700 mb-4">Tanzanian Statutory Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <h5 class="font-medium text-gray-900 mb-3">NSSF Contributions</h5>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-gray-600 text-xs font-medium mb-1" for="nssf_employer_rate">Employer Rate (%)</label>
                                    <input type="number" name="nssf_employer_rate" id="nssf_employer_rate" step="0.01" value="{{ $settings['nssf_employer_rate'] ?? 10 }}" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                                </div>
                                <div>
                                    <label class="block text-gray-600 text-xs font-medium mb-1" for="nssf_employee_rate">Employee Rate (%)</label>
                                    <input type="number" name="nssf_employee_rate" id="nssf_employee_rate" step="0.01" value="{{ $settings['nssf_employee_rate'] ?? 10 }}" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <h5 class="font-medium text-gray-900 mb-3">NHIF Contributions</h5>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-gray-600 text-xs font-medium mb-1" for="nhif_calculation_method">Calculation Method</label>
                                    <select name="nhif_calculation_method" id="nhif_calculation_method" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                                        <option value="tiered" {{ ($settings['nhif_calculation_method'] ?? 'tiered') == 'tiered' ? 'selected' : '' }}>Tiered</option>
                                        <option value="fixed" {{ ($settings['nhif_calculation_method'] ?? 'tiered') == 'fixed' ? 'selected' : '' }}>Fixed Rate</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                            <h5 class="font-medium text-gray-900 mb-3">PAYE Tax Brackets</h5>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-gray-600 text-xs font-medium mb-1" for="paye_tax_free">Tax-Free Threshold (TZS)</label>
                                    <input type="number" name="paye_tax_free" id="paye_tax_free" value="{{ $settings['paye_tax_free'] ?? 270000 }}" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="resetPayrollForm()" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-undo mr-2"></i> Reset to Defaults
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i> Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Allowance Types Container -->
    <div id="allowancesContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-hand-holding-usd text-green-500 mr-2"></i> Allowance Types Configuration
                    <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $allowances->count() }} allowances</span>
                </h3>
                <button onclick="openModal('addAllowanceModal')" class="text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 focus:ring-4 focus:ring-green-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i> Add Allowance
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Allowance Name</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Type</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Amount/Rate</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Taxable</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($allowances as $allowance)
                            <tr class="hover:bg-gray-50 transition-all duration-200">
                                <td class="py-3.5 px-6 text-sm text-gray-900 font-medium">{{ $allowance->name }}</td>
                                <td class="py-3.5 px-6 text-sm text-gray-600">{{ ucfirst($allowance->type) }}</td>
                                <td class="py-3.5 px-6 text-sm text-gray-900">
                                    {{ $allowance->type == 'fixed' ? 'TZS ' . number_format($allowance->amount, 0) : $allowance->amount . '%' }}
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $allowance->taxable ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $allowance->taxable ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $allowance->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $allowance->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-sm">
                                    <div class="flex space-x-2">
                                        <button onclick="editAllowance({{ $allowance->id }})" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-md hover:bg-blue-50 transition-all duration-200" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteAllowance({{ $allowance->id }})" class="text-red-600 hover:text-red-800 p-1.5 rounded-md hover:bg-red-50 transition-all duration-200" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deduction Types Container -->
    <div id="deductionsContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-700 flex items-center">
                    <i class="fas fa-minus-circle text-green-500 mr-2"></i> Deduction Types Configuration
                    <span class="ml-2 text-sm text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $deductions->count() }} deductions</span>
                </h3>
                <button onclick="openModal('addDeductionModal')" class="text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 focus:ring-4 focus:ring-green-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                    <i class="fas fa-plus mr-2"></i> Add Deduction
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-700 text-sm">
                            <th class="py-3.5 px-6 text-left font-semibold">Deduction Name</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Type</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Category</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Amount/Rate</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Status</th>
                            <th class="py-3.5 px-6 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($deductions as $deduction)
                            <tr class="hover:bg-gray-50 transition-all duration-200">
                                <td class="py-3.5 px-6 text-sm text-gray-900 font-medium">{{ $deduction->name }}</td>
                                <td class="py-3.5 px-6 text-sm text-gray-600">{{ ucfirst($deduction->type) }}</td>
                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $deduction->category == 'statutory' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ ucfirst($deduction->category) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-sm text-gray-900">
                                    {{ $deduction->type == 'fixed' ? 'TZS ' . number_format($deduction->amount, 0) : $deduction->amount . '%' }}
                                </td>
                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $deduction->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $deduction->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-6 text-sm">
                                    <div class="flex space-x-2">
                                        <button onclick="editDeduction({{ $deduction->id }})" class="text-blue-600 hover:text-blue-800 p-1.5 rounded-md hover:bg-blue-50 transition-all duration-200" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteDeduction({{ $deduction->id }})" class="text-red-600 hover:text-red-800 p-1.5 rounded-md hover:bg-red-50 transition-all duration-200" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Notifications Container -->
    <div id="notificationsContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-lg font-medium text-gray-700 flex items-center mb-4">
                <i class="fas fa-bell text-green-500 mr-2"></i> Notification Settings
            </h3>
            <form action="{{ route('settings.notifications.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <h4 class="text-base font-medium text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-envelope text-blue-500 mr-2"></i> Email Notifications
                        </h4>
                        <div class="space-y-4">
                            @foreach(['payroll_processing', 'payment_confirmation', 'contract_expiry', 'tax_filing'] as $type)
                                <div class="flex items-center justify-between">
                                    <div>
                                        <label class="text-gray-700 font-medium" for="email_{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }} Notifications</label>
                                        <p class="text-gray-500 text-sm">Receive email alerts for {{ str_replace('_', ' ', $type) }}</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="email_notifications[]" value="{{ $type }}" id="email_{{ $type }}" {{ in_array($type, $settings['email_notifications'] ?? []) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <h4 class="text-base font-medium text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-sms text-green-500 mr-2"></i> SMS Notifications
                        </h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-gray-700 font-medium">SMS Gateway Enabled</label>
                                    <p class="text-gray-500 text-sm">Enable SMS notifications for employees</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="sms_enabled" value="1" {{ ($settings['sms_enabled'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-600 text-sm font-medium mb-2" for="sms_gateway">SMS Gateway</label>
                                    <select name="sms_gateway" id="sms_gateway" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                                        <option value="twilio" {{ ($settings['sms_gateway'] ?? '') == 'twilio' ? 'selected' : '' }}>Twilio</option>
                                        <option value="africas_talking" {{ ($settings['sms_gateway'] ?? '') == 'africas_talking' ? 'selected' : '' }}>Africa's Talking</option>
                                        <option value="custom" {{ ($settings['sms_gateway'] ?? '') == 'custom' ? 'selected' : '' }}>Custom Gateway</option>
                                    </select>
                                </div>
                                <div class="relative">
                                    <label class="block text-gray-600 text-sm font-medium mb-2" for="sms_balance_alert">Balance Alert Threshold</label>
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-money-bill text-gray-400 text-base"></i>
                                    </div>
                                    <input type="number" name="sms_balance_alert" id="sms_balance_alert" value="{{ $settings['sms_balance_alert'] ?? 100 }}" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="resetNotificationForm()" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i> Save Notification Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Integrations Container -->
    <div id="integrationsContainer" class="hidden">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
            <h3 class="text-lg font-medium text-gray-700 flex items-center mb-4">
                <i class="fas fa-plug text-green-500 mr-2"></i> Integration Settings
            </h3>
            <form action="{{ route('settings.integrations.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="space-y-6">
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <h4 class="text-base font-medium text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-calculator text-purple-500 mr-2"></i> Accounting Software
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="accounting_software">Software</label>
                                <select name="accounting_software" id="accounting_software" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                                    <option value="">None</option>
                                    <option value="quickbooks" {{ ($settings['accounting_software'] ?? '') == 'quickbooks' ? 'selected' : '' }}>QuickBooks</option>
                                    <option value="xero" {{ ($settings['accounting_software'] ?? '') == 'xero' ? 'selected' : '' }}>Xero</option>
                                    <option value="sage" {{ ($settings['accounting_software'] ?? '') == 'sage' ? 'selected' : '' }}>Sage</option>
                                </select>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="api_key">API Key</label>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-key text-gray-400 text-base"></i>
                                </div>
                                <input type="password" name="api_key" id="api_key" value="{{ $settings['api_key'] ?? '' }}" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" placeholder="Enter API key">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <h4 class="text-base font-medium text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-university text-blue-500 mr-2"></i> Banking Integration
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="bank_api">Bank API</label>
                                <select name="bank_api" id="bank_api" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                                    <option value="">None</option>
                                    <option value="swift" {{ ($settings['bank_api'] ?? '') == 'swift' ? 'selected' : '' }}>SWIFT</option>
                                    <option value="custom" {{ ($settings['bank_api'] ?? '') == 'custom' ? 'selected' : '' }}>Custom API</option>
                                </select>
                            </div>
                            <div class="relative">
                                <label class="block text-gray-600 text-sm font-medium mb-2" for="bank_endpoint">API Endpoint</label>
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <i class="fas fa-link text-gray-400 text-base"></i>
                                </div>
                                <input type="url" name="bank_endpoint" id="bank_endpoint" value="{{ $settings['bank_endpoint'] ?? '' }}" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" placeholder="https://api.bank.com">
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-6 rounded-lg shadow-sm">
                        <h4 class="text-base font-medium text-gray-700 mb-4 flex items-center">
                            <i class="fas fa-clock text-orange-500 mr-2"></i> Time Tracking Integration
                        </h4>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <label class="text-gray-700 font-medium">Sync Attendance Data</label>
                                    <p class="text-gray-500 text-sm">Automatically sync with time tracking system</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="attendance_sync" value="1" {{ ($settings['attendance_sync'] ?? false) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                </label>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-600 text-sm font-medium mb-2" for="sync_frequency">Sync Frequency</label>
                                    <select name="sync_frequency" id="sync_frequency" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                                        <option value="daily" {{ ($settings['sync_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ ($settings['sync_frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ ($settings['sync_frequency'] ?? 'daily') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>
                                <div class="relative">
                                    <label class="block text-gray-600 text-sm font-medium mb-2" for="last_sync">Last Sync</label>
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="fas fa-calendar text-gray-400 text-base"></i>
                                    </div>
                                    <input type="text" name="last_sync" id="last_sync" value="{{ $settings['last_sync'] ?? 'Never' }}" class="pl-10 w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-100 text-gray-900" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="testIntegrations()" class="text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 focus:ring-4 focus:ring-blue-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-plug mr-2"></i> Test Connections
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i> Save Integration Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Allowance Modal -->
    <div id="addAllowanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="modal-content bg-white rounded-lg shadow-lg p-6 w-full max-w-md transform scale-95 transition-transform duration-300">
            <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                <i class="fas fa-hand-holding-usd text-green-500 mr-2"></i> Add New Allowance
            </h3>
            <form id="addAllowanceForm" action="{{ route('settings.allowances.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="allowance_name">Allowance Name</label>
                        <input type="text" name="name" id="allowance_name" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" required>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="allowance_type">Type</label>
                        <select name="type" id="allowance_type" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="allowance_amount">Amount</label>
                        <input type="number" name="amount" id="allowance_amount" step="0.01" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" required>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="taxable" id="allowance_taxable" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="allowance_taxable" class="ml-2 text-gray-600 text-sm">Taxable Allowance</label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addAllowanceModal')" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i> Add Allowance
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Deduction Modal -->
    <div id="addDeductionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="modal-content bg-white rounded-lg shadow-lg p-6 w-full max-w-md transform scale-95 transition-transform duration-300">
            <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                <i class="fas fa-minus-circle text-green-500 mr-2"></i> Add New Deduction
            </h3>
            <form id="addDeductionForm" action="{{ route('settings.deductions.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="deduction_name">Deduction Name</label>
                        <input type="text" name="name" id="deduction_name" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" required>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="deduction_category">Category</label>
                        <select name="category" id="deduction_category" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                            <option value="statutory">Statutory</option>
                            <option value="voluntary">Voluntary</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="deduction_type">Type</label>
                        <select name="type" id="deduction_type" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="deduction_amount">Amount</label>
                        <input type="number" name="amount" id="deduction_amount" step="0.01" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" required>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addDeductionModal')" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i> Add Deduction
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Allowance Modal -->
    <div id="editAllowanceModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="modal-content bg-white rounded-lg shadow-lg p-6 w-full max-w-md transform scale-95 transition-transform duration-300">
            <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                <i class="fas fa-edit text-green-500 mr-2"></i> Edit Allowance
            </h3>
            <form id="editAllowanceForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="allowance_id" id="edit_allowance_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_allowance_name">Allowance Name</label>
                        <input type="text" name="name" id="edit_allowance_name" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" required>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_allowance_type">Type</label>
                        <select name="type" id="edit_allowance_type" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_allowance_amount">Amount</label>
                        <input type="number" name="amount" id="edit_allowance_amount" step="0.01" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" required>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="taxable" id="edit_allowance_taxable" class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <label for="edit_allowance_taxable" class="ml-2 text-gray-600 text-sm">Taxable Allowance</label>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('editAllowanceModal')" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i> Update Allowance
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Deduction Modal -->
    <div id="editDeductionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="modal-content bg-white rounded-lg shadow-lg p-6 w-full max-w-md transform scale-95 transition-transform duration-300">
            <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                <i class="fas fa-edit text-green-500 mr-2"></i> Edit Deduction
            </h3>
            <form id="editDeductionForm" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="deduction_id" id="edit_deduction_id">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_deduction_name">Deduction Name</label>
                        <input type="text" name="name" id="edit_deduction_name" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" required>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_deduction_category">Category</label>
                        <select name="category" id="edit_deduction_category" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                            <option value="statutory">Statutory</option>
                            <option value="voluntary">Voluntary</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_deduction_type">Type</label>
                        <select name="type" id="edit_deduction_type" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900">
                            <option value="fixed">Fixed Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_deduction_amount">Amount</label>
                        <input type="number" name="amount" id="edit_deduction_amount" step="0.01" class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-white shadow-sm text-gray-900" required>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('editDeductionModal')" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                        <i class="fas fa-save mr-2"></i> Update Deduction
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="modal-content bg-white rounded-lg shadow-lg p-6 w-full max-w-md transform scale-95 transition-transform duration-300">
            <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i> Confirm Deletion
            </h3>
            <p class="text-gray-600 mb-6" id="deleteConfirmationText">Are you sure you want to delete this item?</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeModal('deleteConfirmationModal')" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                    <i class="fas fa-times mr-2"></i> Cancel
                </button>
                <button type="button" id="confirmDeleteButton" class="text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
                    <i class="fas fa-trash mr-2"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        // Global variables for modal management
        let currentDeleteId = null;
        let currentDeleteType = null;

        // Tab navigation functionality
        function toggleTab(tabId) {
            const tabs = ['personalAccountTab', 'payrollConfigTab', 'allowancesTab', 'deductionsTab', 'notificationsTab', 'integrationsTab'];
            const containers = ['personalAccountContainer', 'payrollConfigContainer', 'allowancesContainer', 'deductionsContainer', 'notificationsContainer', 'integrationsContainer'];
            
            tabs.forEach(tab => {
                const tabElement = document.getElementById(tab);
                if (tabElement) {
                    tabElement.classList.remove('bg-green-600', 'text-white');
                    tabElement.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                }
            });
            
            containers.forEach(container => {
                const containerElement = document.getElementById(container);
                if (containerElement) {
                    containerElement.classList.add('hidden');
                }
            });
            
            const activeTab = document.getElementById(tabId);
            if (activeTab) {
                activeTab.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                activeTab.classList.add('bg-green-600', 'text-white');
                
                const targetContainer = tabId.replace('Tab', 'Container');
                const containerElement = document.getElementById(targetContainer);
                if (containerElement) {
                    containerElement.classList.remove('hidden');
                }
            }
        }

        // Modal functionality
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.querySelector('.modal-content').classList.remove('scale-95');
                    modal.querySelector('.modal-content').classList.add('scale-100');
                }, 10);
            }
        }

        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.querySelector('.modal-content').classList.remove('scale-100');
                modal.querySelector('.modal-content').classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }

        // Form reset functions
        function resetPersonalForm() {
            document.querySelector('#personalAccountContainer form').reset();
        }

        function resetPayrollForm() {
            if (confirm('Reset all payroll settings to defaults?')) {
                document.querySelector('#payrollConfigContainer form').reset();
            }
        }

        function resetNotificationForm() {
            document.querySelector('#notificationsContainer form').reset();
        }

        // Integration test function
        function testIntegrations() {
            alert('Testing integration connections... This would typically make API calls to verify connections.');
        }

        // Allowance management functions
        async function editAllowance(id) {
            try {
                const response = await fetch(`/settings/allowances/${id}/edit`);
                if (!response.ok) throw new Error('Failed to fetch allowance data');
                
                const allowance = await response.json();
                
                document.getElementById('edit_allowance_id').value = allowance.id;
                document.getElementById('edit_allowance_name').value = allowance.name;
                document.getElementById('edit_allowance_type').value = allowance.type;
                document.getElementById('edit_allowance_amount').value = allowance.amount;
                document.getElementById('edit_allowance_taxable').checked = allowance.taxable;
                
                document.getElementById('editAllowanceForm').action = `/settings/allowances/${id}`;
                openModal('editAllowanceModal');
            } catch (error) {
                console.error('Error fetching allowance:', error);
                alert('Failed to load allowance data');
            }
        }

        async function deleteAllowance(id) {
            currentDeleteId = id;
            currentDeleteType = 'allowance';
            document.getElementById('deleteConfirmationText').textContent = 'Are you sure you want to delete this allowance? This action cannot be undone.';
            openModal('deleteConfirmationModal');
        }

        // Deduction management functions
        async function editDeduction(id) {
            try {
                const response = await fetch(`/settings/deductions/${id}/edit`);
                if (!response.ok) throw new Error('Failed to fetch deduction data');
                
                const deduction = await response.json();
                
                document.getElementById('edit_deduction_id').value = deduction.id;
                document.getElementById('edit_deduction_name').value = deduction.name;
                document.getElementById('edit_deduction_category').value = deduction.category;
                document.getElementById('edit_deduction_type').value = deduction.type;
                document.getElementById('edit_deduction_amount').value = deduction.amount;
                
                document.getElementById('editDeductionForm').action = `/settings/deductions/${id}`;
                openModal('editDeductionModal');
            } catch (error) {
                console.error('Error fetching deduction:', error);
                alert('Failed to load deduction data');
            }
        }

        async function deleteDeduction(id) {
            currentDeleteId = id;
            currentDeleteType = 'deduction';
            document.getElementById('deleteConfirmationText').textContent = 'Are you sure you want to delete this deduction? This action cannot be undone.';
            openModal('deleteConfirmationModal');
        }

        // Delete confirmation handler
        document.getElementById('confirmDeleteButton').addEventListener('click', async function() {
            if (!currentDeleteId || !currentDeleteType) return;
            
            try {
                const url = `/settings/${currentDeleteType}s/${currentDeleteId}`;
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                if (response.ok) {
                    location.reload();
                } else {
                    throw new Error('Delete failed');
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('Failed to delete item');
            } finally {
                closeModal('deleteConfirmationModal');
                currentDeleteId = null;
                currentDeleteType = null;
            }
        });

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('fixed')) {
                const modals = document.querySelectorAll('.fixed.inset-0');
                modals.forEach(modal => {
                    if (!modal.classList.contains('hidden')) {
                        closeModal(modal.id);
                    }
                });
            }
        });

        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const allowanceForm = document.getElementById('addAllowanceForm');
            if (allowanceForm) {
                allowanceForm.addEventListener('submit', function(e) {
                    const amount = parseFloat(document.getElementById('allowance_amount').value);
                    const type = document.getElementById('allowance_type').value;
                    
                    if (type === 'percentage' && (amount < 0 || amount > 100)) {
                        e.preventDefault();
                        alert('Percentage must be between 0 and 100');
                        return false;
                    }
                });
            }
            
            const deductionForm = document.getElementById('addDeductionForm');
            if (deductionForm) {
                deductionForm.addEventListener('submit', function(e) {
                    const amount = parseFloat(document.getElementById('deduction_amount').value);
                    const type = document.getElementById('deduction_type').value;
                    
                    if (type === 'percentage' && (amount < 0 || amount > 100)) {
                        e.preventDefault();
                        alert('Percentage must be between 0 and 100');
                        return false;
                    }
                });
            }

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
    </script>
    @endsection