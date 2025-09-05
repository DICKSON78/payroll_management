@extends('layout.global')

@section('title', 'Settings')

@section('header-title')
    {{ $settings['company_name'] }} Settings
    <span class="payroll-badge text-xs font-semibold px-2 py-1 rounded-full ml-3 bg-green-100 text-green-800">
        <i class="fas fa-bolt mr-1"></i> Premium Plan
    </span>
@endsection

@section('header-subtitle')
    Configure system and payroll settings for {{ $settings['company_name'] }}.
@endsection

@section('content')
    <!-- Quick Actions -->
    <div>
        <h3 class="text-lg font-medium text-gray-700 mb-4 flex items-center">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i> Quick Actions
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <a href="#" class="card hover:shadow-lg transition-all flex flex-col items-center text-center bg-white rounded-xl p-6 border border-gray-200 hover:border-green-300" onclick="openModal('updateSettingsModal')">
                <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center mb-4">
                    <i class="fas fa-cog text-gray-600 text-xl"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Update Settings</h4>
                <p class="text-sm text-gray-500 mt-1">Modify system configurations</p>
            </a>
        </div>
    </div>

    <!-- Settings Overview -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-700 flex items-center">
                <i class="fas fa-cog text-blue-500 mr-2"></i> Current Settings
            </h3>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">Company Name</p>
                        <p class="text-sm text-gray-500">{{ $settings['company_name'] }}</p>
                    </div>
                    <button class="text-blue-600 text-sm font-medium hover:underline" onclick="openModal('updateSettingsModal')">Edit</button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">Tax Rate (%)</p>
                        <p class="text-sm text-gray-500">{{ number_format($settings['tax_rate'], 2) }}%</p>
                    </div>
                    <button class="text-blue-600 text-sm font-medium hover:underline" onclick="openModal('updateSettingsModal')">Edit</button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">Payroll Cycle</p>
                        <p class="text-sm text-gray-500">{{ $settings['payroll_cycle'] }}</p>
                    </div>
                    <button class="text-blue-600 text-sm font-medium hover:underline" onclick="openModal('updateSettingsModal')">Edit</button>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">Currency</p>
                        <p class="text-sm text-gray-500">{{ $settings['currency'] }}</p>
                    </div>
                    <button class="text-blue-600 text-sm font-medium hover:underline" onclick="openModal('updateSettingsModal')">Edit</button>
                </div>
                @if($settings['company_logo'])
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">Company Logo</p>
                            <img src="{{ storage_path('app/public/' . $settings['company_logo']) }}" alt="Company Logo" class="h-12 mt-2">
                        </div>
                        <button class="text-blue-600 text-sm font-medium hover:underline" onclick="openModal('updateSettingsModal')">Edit</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Update Settings Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden" id="updateSettingsModal">
        <div class="bg-white rounded-xl w-full max-w-2xl transform transition-all duration-300 scale-95 modal-content">
            <div class="p-6 bg-gradient-to-r from-green-50 to-blue-50 border-b">
                <h3 class="text-xl font-semibold text-green-600 flex items-center">
                    <i class="fas fa-cog mr-2"></i> Update Settings
                </h3>
            </div>
            <div class="p-6">
                <form id="updateSettingsForm" action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="company_name">Company Name</label>
                            <input type="text" id="company_name" name="company_name" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="{{ $settings['company_name'] }}" required>
                            <span class="text-red-500 text-sm mt-1 hidden" id="companyNameError">Company Name is required</span>
                            @error('company_name')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="tax_rate">Tax Rate (%)</label>
                            <input type="number" id="tax_rate" name="tax_rate" step="0.01" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="{{ $settings['tax_rate'] }}" required>
                            <span class="text-red-500 text-sm mt-1 hidden" id="taxRateError">Tax Rate is required</span>
                            @error('tax_rate')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="payroll_cycle">Payroll Cycle</label>
                            <select id="payroll_cycle" name="payroll_cycle" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                                <option value="Monthly" {{ $settings['payroll_cycle'] == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="Bi-Weekly" {{ $settings['payroll_cycle'] == 'Bi-Weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                                <option value="Weekly" {{ $settings['payroll_cycle'] == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                            </select>
                            <span class="text-red-500 text-sm mt-1 hidden" id="payrollCycleError">Payroll Cycle is required</span>
                            @error('payroll_cycle')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="currency">Currency</label>
                            <input type="text" id="currency" name="currency" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" value="{{ $settings['currency'] }}" required>
                            <span class="text-red-500 text-sm mt-1 hidden" id="currencyError">Currency is required</span>
                            @error('currency')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="mb-4 col-span-2">
                            <label class="block text-gray-600 text-sm font-medium mb-2" for="company_logo">Company Logo</label>
                            <input type="file" id="company_logo" name="company_logo" accept="image/*" class="bg-gray-50 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            @if($settings['company_logo'])
                                <p class="text-sm text-gray-500 mt-1">Current logo: <a href="{{ storage_path('app/public/' . $settings['company_logo']) }}" target="_blank">View</a></p>
                            @endif
                            @error('company_logo')
                                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" class="text-white bg-gradient-to-r from-gray-500 to-gray-700 hover:from-gray-600 hover:to-gray-800 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200" onclick="closeModal('updateSettingsModal')">
                            <i class="fas fa-times mr-2"></i> Cancel
                        </button>
                        <button type="submit" class="text-white bg-gradient-to-r from-green-600 to-green-800 hover:from-green-700 hover:to-green-900 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition-all duration-200 flex items-center">
                            <i class="fas fa-save mr-2"></i> Save Changes
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('updateSettingsForm');
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
        });
    </script>
@endsection
