<div class="grid grid-cols-2 gap-4">
    <p><strong>Employee ID:</strong> {{ $employee->employee_id }}</p>
    <p><strong>Name:</strong> {{ $employee->name }}</p>
    <p><strong>Email:</strong> {{ $employee->email }}</p>
    <p><strong>Phone:</strong> {{ $employee->phone ?? 'N/A' }}</p>
    <p><strong>Gender:</strong> {{ ucfirst($employee->gender ?? 'N/A') }}</p>
    <p><strong>Date of Birth:</strong> {{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('M d, Y') : 'N/A' }}</p>
    <p><strong>Department:</strong> {{ $employee->department ?? 'N/A' }}</p>
    <p><strong>Position:</strong> {{ $employee->position ?? 'N/A' }}</p>
    <p><strong>Hire Date:</strong> {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') : 'N/A' }}</p>
    <p><strong>Base Salary:</strong> TZS {{ number_format($employee->base_salary, 2) ?? 'N/A' }}</p>
    <p><strong>Status:</strong> <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $employee->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ ucfirst($employee->status) }}</span></p>
</div>