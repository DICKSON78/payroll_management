<div id="{{ $containerId }}" class="hidden">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <h3 class="text-xl font-semibold text-green-600 flex items-center mb-6">
            <i class="fas {{ $titleIcon }} mr-2"></i> {{ $title }}
        </h3>
        <form action="{{ $action }}" method="{{ $method }}" class="space-y-6" id="{{ $formId }}" @if($formType === 'edit') data-employee-id="{{ $employee->employee_id }}" @endif>
            @csrf
            @if($formType === 'edit')
                @method('PUT')
            @endif
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Personal Information -->
                <div class="space-y-6">
                    <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Personal Information</h4>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_name">Full Name</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-user text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="name" id="{{ $formType }}_name" value="{{ $formType === 'edit' ? ($employee->name ?? '') : '' }}" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="John Doe" aria-describedby="{{ $formType }}_nameError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_nameError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Full Name is required
                        </span>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_email">Email Address</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-envelope text-gray-400 text-base"></i>
                        </div>
                        <input type="email" name="email" id="{{ $formType }}_email" value="{{ $formType === 'edit' ? ($employee->email ?? '') : '' }}" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="john@company.com" aria-describedby="{{ $formType }}_emailError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_emailError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Valid email is required
                        </span>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_phone">Phone Number</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-phone text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="phone" id="{{ $formType }}_phone" value="{{ $formType === 'edit' ? ($employee->phone ?? '') : '' }}" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="+255 123 456 789">
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_gender">Gender</label>
                        <select name="gender" id="{{ $formType }}_gender" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" aria-describedby="{{ $formType }}_genderError">
                            <option value="">Select Gender</option>
                            <option value="male" {{ $formType === 'edit' && $employee->gender === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ $formType === 'edit' && $employee->gender === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ $formType === 'edit' && $employee->gender === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_genderError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Gender is required
                        </span>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_dob">Date of Birth</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="dob" id="{{ $formType }}_dob" value="{{ $formType === 'edit' ? ($employee->dob ?? '') : '' }}" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr" placeholder="Select date" aria-describedby="{{ $formType }}_dobError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_dobError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Invalid date format
                        </span>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_nationality">Nationality</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-globe text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="nationality" id="{{ $formType }}_nationality" value="{{ $formType === 'edit' ? ($employee->nationality ?? '') : '' }}" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Tanzanian">
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_address">Address</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-map-marker-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="address" id="{{ $formType }}_address" value="{{ $formType === 'edit' ? ($employee->address ?? '') : '' }}" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="123 Main St, Dar es Salaam">
                    </div>
                </div>
                <!-- Employment Details -->
                <div class="space-y-6">
                    <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Employment Details</h4>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_department">Department</label>
                        <select name="department" id="{{ $formType }}_department" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" required aria-describedby="{{ $formType }}_departmentError">
                            <option value="">Select Department</option>
                            @if($departments->isNotEmpty())
                                @foreach($departments as $department)
                                    <option value="{{ $department->name }}" {{ $formType === 'edit' && $employee->department === $department->name ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>No departments available</option>
                            @endif
                        </select>
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_departmentError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Department is required
                        </span>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_position">Position</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-briefcase text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="position" id="{{ $formType }}_position" value="{{ $formType === 'edit' ? ($employee->position ?? '') : '' }}" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="Software Developer" aria-describedby="{{ $formType }}_positionError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_positionError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Position is required
                        </span>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_role">Role</label>
                        <select name="role" id="{{ $formType }}_role" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" required aria-describedby="{{ $formType }}_roleError">
                            <option value="">Select Role</option>
                            @if($roles->isNotEmpty())
                                @foreach($roles as $role)
                                    <option value="{{ $role->slug }}" {{ $formType === 'edit' && $employee->role === $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>No roles available</option>
                            @endif
                        </select>
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_roleError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Role is required
                        </span>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_employment_type">Employment Type</label>
                        <select name="employment_type" id="{{ $formType }}_employment_type" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" required aria-describedby="{{ $formType }}_employmentTypeError">
                            <option value="">Select Type</option>
                            <option value="full-time" {{ $formType === 'edit' && $employee->employment_type === 'full-time' ? 'selected' : '' }}>Full-Time</option>
                            <option value="part-time" {{ $formType === 'edit' && $employee->employment_type === 'part-time' ? 'selected' : '' }}>Part-Time</option>
                            <option value="contract" {{ $formType === 'edit' && $employee->employment_type === 'contract' ? 'selected' : '' }}>Contract</option>
                        </select>
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_employmentTypeError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Employment Type is required
                        </span>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_hire_date">Hire Date</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="hire_date" id="{{ $formType }}_hire_date" value="{{ $formType === 'edit' ? ($employee->hire_date ?? '') : '' }}" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr" placeholder="Select date" aria-describedby="{{ $formType }}_hireDateError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_hireDateError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Hire Date is required
                        </span>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_contract_end_date">Contract End Date</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="contract_end_date" id="{{ $formType }}_contract_end_date" value="{{ $formType === 'edit' ? ($employee->contract_end_date ?? '') : '' }}" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200 flatpickr" placeholder="Select date" aria-describedby="{{ $formType }}_contractEndDateError">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_contractEndDateError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Invalid date format
                        </span>
                    </div>
                </div>
                <!-- Banking & Compliance -->
                <div class="space-y-6">
                    <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Banking & Compliance</h4>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_base_salary">Base Salary (TZS)</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-money-bill text-gray-400 text-base"></i>
                        </div>
                        <input type="number" name="base_salary" id="{{ $formType }}_base_salary" value="{{ $formType === 'edit' ? ($employee->base_salary ?? '') : '' }}" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="5000000" aria-describedby="{{ $formType }}_baseSalaryError" oninput="calculateTotalSalary('{{ $formType }}')">
                        <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="{{ $formType }}_baseSalaryError">
                            <i class="fas fa-exclamation-circle mr-1"></i> Base Salary is required
                        </span>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_allowance_id">Allowances (TZS)</label>
                        <select name="allowance_id" id="{{ $formType }}_allowance_id" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" onchange="calculateTotalSalary('{{ $formType }}')">
                            <option value="" data-amount="0" {{ $formType === 'edit' && !$employee->allowances ? 'selected' : '' }}>No Allowance (0 TZS)</option>
                            @if($allowances->isNotEmpty())
                                @foreach($allowances as $allowance)
                                    <option value="{{ $allowance->id }}" data-amount="{{ $allowance->amount }}" {{ $formType === 'edit' && $employee->allowances == $allowance->amount ? 'selected' : '' }}>{{ $allowance->name }} (TZS {{ number_format($allowance->amount, 0) }})</option>
                                @endforeach
                            @else
                                <option value="" disabled>No allowances available</option>
                            @endif
                        </select>
                        <input type="hidden" name="allowances" id="{{ $formType }}_allowances" value="{{ $formType === 'edit' ? ($employee->allowances ?? 0) : 0 }}">
                        <span class="text-gray-500 text-sm mt-1 block">Total Salary: <span id="totalSalary{{ ucfirst($formType) }}">TZS {{ $formType === 'edit' ? number_format(($employee->base_salary + ($employee->allowances ?? 0)), 0) : 0 }}</span></span>
                    </div>
                    <div>
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_bank_name">Bank Name</label>
                        <select name="bank_name" id="{{ $formType }}_bank_name" class="bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                            <option value="">Select Bank</option>
                            @if($banks->isNotEmpty())
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->name }}" {{ $formType === 'edit' && $employee->bank_name === $bank->name ? 'selected' : '' }}>{{ $bank->name }}</option>
                                @endforeach
                            @else
                                <option value="" disabled>No banks available</option>
                            @endif
                        </select>
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_account_number">Account Number</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-credit-card text-gray-400 text-base"></i>
                        </div>
                        <input type="text" name="account_number" id="{{ $formType }}_account_number" value="{{ $formType === 'edit' ? ($employee->account_number ?? '') : '' }}" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="1234567890">
                    </div>
                    <div class="relative">
                        <label class="block text-gray-600 text-sm font-medium mb-2" for="{{ $formType }}_nssf_number">NSSF Number</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-id-card text-gray-400 text-base"></i>
                       