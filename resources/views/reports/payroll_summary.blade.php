<!DOCTYPE html>
<html>
<head>
    <title>Payroll Summary</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { color: #2d3748; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e2e8f0; padding: 8px; text-align: left; }
        th { background-color: #f7fafc; }
    </style>
</head>
<body>
    <h1>Payroll Summary for {{ $period }}</h1>
    <p>Company: {{ config('app.company_name', 'Your Company') }}</p>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Employee ID</th>
                <th>Gross Salary</th>
                <th>Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report->employee ? [$report->employee] : \App\Models\Employee::all() as $employee)
                @php
                    $payslip = \App\Models\Payslip::where('employee_id', $employee->id)->where('period', $report->period)->first();
                @endphp
                <tr>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $payslip->gross_salary ?? 0 }}</td>
                    <td>{{ $payslip->net_salary ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>