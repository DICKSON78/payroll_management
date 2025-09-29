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
            @foreach($data as $payslip)
                <tr>
                    <td>{{ $payslip->employee->name ?? 'N/A' }}</td>
                    <td>{{ $payslip->employee->employee_id ?? 'N/A' }}</td>
                    <td>{{ number_format($payslip->gross_salary, 2) }}</td>
                    <td>{{ number_format($payslip->net_salary, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>