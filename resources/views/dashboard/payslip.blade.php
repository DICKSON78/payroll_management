<!DOCTYPE html>
<html>
<head>
    <title>Payslip Report - {{ $period }} - {{ $settings['company_name'] }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 100px; }
    </style>
</head>
<body>
    <div class="header">
        @if($settings['company_logo'])
            <img src="{{ storage_path('app/public/' . $settings['company_logo']) }}" class="logo" alt="Company Logo">
        @endif
        <h1>{{ $settings['company_name'] }} - Payslip Report for {{ $period }}</h1>
    </div>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Employee ID</th>
                <th>Department</th>
                <th>Gross Salary ({{ $settings['currency'] }})</th>
                <th>NSSF</th>
                <th>PAYE</th>
                <th>NHIF</th>
                <th>Other Deductions</th>
                <th>Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $payslip)
                <tr>
                    <td>{{ $payslip->employee->name ?? 'N/A' }}</td>
                    <td>{{ $payslip->employee->employee_id ?? 'N/A' }}</td>
                    <td>{{ $payslip->employee->department ?? 'N/A' }}</td>
                    <td>{{ number_format($payslip->gross_salary, 2) }}</td>
                    <td>{{ number_format($payslip->nssf, 2) }}</td>
                    <td>{{ number_format($payslip->paye, 2) }}</td>
                    <td>{{ number_format($payslip->nhif, 2) }}</td>
                    <td>{{ number_format($payslip->other_deductions ?? 0, 2) }}</td>
                    <td>{{ number_format($payslip->net_salary, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>