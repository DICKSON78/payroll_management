<form action="" method="POST" class="space-y-6" id="editEmployeeForm">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Personal Information -->
        <div class="space-y-6">
            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Personal Information</h4>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_name">Full Name</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-user text-gray-400 text-base"></i>
                </div>
                <input type="text" name="name" id="edit_name" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="John Doe" aria-describedby="edit_nameError">
                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="edit_nameError">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <span class="error-message"></span>
                </span>
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_email">Email</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-envelope text-gray-400 text-base"></i>
                </div>
                <input type="email" name="email" id="edit_email" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="john.doe@example.com" aria-describedby="edit_emailError">
                <span class="text-red-500 text-sm flex items-center mt-1 hidden" id="edit_emailError">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    <span class="error-message"></span>
                </span>
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_phone">Phone</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-phone text-gray-400 text-base"></i>
                </div>
                <input type="tel" name="phone" id="edit_phone" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="+255 123 456 789">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_gender">Gender</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-venus-mars text-gray-400 text-base"></i>
                </div>
                <select name="gender" id="edit_gender" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                    <option value="" disabled selected>Select gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_dob">Date of Birth</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                </div>
                <input type="date" name="dob" id="edit_dob" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_nationality">Nationality</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-flag text-gray-400 text-base"></i>
                </div>
                <input type="text" name="nationality" id="edit_nationality" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="e.g., Tanzanian">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_address">Address</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-map-marker-alt text-gray-400 text-base"></i>
                </div>
                <input type="text" name="address" id="edit_address" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="e.g., 123 Main St, Dar es Salaam">
            </div>
        </div>

        <!-- Employment Details -->
        <div class="space-y-6">
            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Employment Details</h4>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_department">Department</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-building text-gray-400 text-base"></i>
                </div>
                <select name="department" id="edit_department" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                    <option value="" disabled>Select department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_position">Position</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-briefcase text-gray-400 text-base"></i>
                </div>
                <input type="text" name="position" id="edit_position" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="e.g., Software Developer">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_role">Role</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-user-tag text-gray-400 text-base"></i>
                </div>
                <select name="role" id="edit_role" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                    <option value="" disabled>Select role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_employment_type">Employment Type</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-user-circle text-gray-400 text-base"></i>
                </div>
                <select name="employment_type" id="edit_employment_type" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                    <option value="" disabled>Select type</option>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="contract">Contract</option>
                </select>
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_hire_date">Hire Date</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-calendar-alt text-gray-400 text-base"></i>
                </div>
                <input type="date" name="hire_date" id="edit_hire_date" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_contract_end_date">Contract End Date</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-calendar-times text-gray-400 text-base"></i>
                </div>
                <input type="date" name="contract_end_date" id="edit_contract_end_date" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
            </div>
        </div>
        
        <!-- Salary & Banking -->
        <div class="space-y-6">
            <h4 class="text-lg font-medium text-gray-700 border-b border-gray-200 pb-2">Salary & Banking</h4>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_base_salary">Base Salary (TZS)</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-money-bill-wave text-gray-400 text-base"></i>
                </div>
                <input type="number" name="base_salary" id="edit_base_salary" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="e.g., 5000000">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_allowances">Allowances</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-hand-holding-usd text-gray-400 text-base"></i>
                </div>
                <select name="allowances[]" id="edit_allowances" multiple class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                    @foreach($allowances as $allowance)
                        <option value="{{ $allowance->id }}">{{ $allowance->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_status">Status</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-check-circle text-gray-400 text-base"></i>
                </div>
                <select name="status" id="edit_status" required class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="terminated">Terminated</option>
                </select>
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_bank_name">Bank Name</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-university text-gray-400 text-base"></i>
                </div>
                <input type="text" name="bank_name" id="edit_bank_name" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="e.g., CRDB Bank">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_account_number">Account Number</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-credit-card text-gray-400 text-base"></i>
                </div>
                <input type="text" name="account_number" id="edit_account_number" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="0123456789">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_nssf_number">NSSF Number</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-id-card text-gray-400 text-base"></i>
                </div>
                <input type="text" name="nssf_number" id="edit_nssf_number" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="NSSF-123456789">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_tin_number">TIN Number</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-id-card text-gray-400 text-base"></i>
                </div>
                <input type="text" name="tin_number" id="edit_tin_number" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="TIN-123456">
            </div>
            <div class="relative">
                <label class="block text-gray-600 text-sm font-medium mb-2" for="edit_nhif_number">NHIF Number</label>
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-id-card text-gray-400 text-base"></i>
                </div>
                <input type="text" name="nhif_number" id="edit_nhif_number" class="pl-10 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 block w-full py-2.5 px-3 leading-6 transition-all duration-200" placeholder="NHIF-123456789">
            </div>
        </div>
    </div>
    <div class="flex justify-end space-x-3">
        <button type="button" onclick="closeModal('editEmployeeModal')" class="text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
            <i class="fas fa-times mr-2"></i> Ghairi
        </button>
        <button type="submit" class="text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-4 focus:ring-green-200 font-medium rounded-lg text-sm px-4 py-2 text-center transition-all duration-200 flex items-center shadow-sm hover:shadow-md">
            <i class="fas fa-save mr-2"></i> Hifadhi Mabadiliko
        </button>
    </div>
</form>
