<!DOCTYPE html>
<html>
<head>
    <title>Payroll Report - {{ $period }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header img { max-width: 100px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        @if ($settings->company_logo)
            <img src="{{ public_path($settings->company_logo) }}" alt="Company Logo">
        @endif
        <h2>{{ $settings->company_name }}</h2>
        <h3>Payroll Report for {{ $period }}</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Base Salary ({{ $settings->currency }})</th>
                <th>Allowances</th>
                <th>Gross Salary</th>
                <th>PAYE</th>
                <th>NSSF</th>
                <th>NHIF</th>
                <th>WCF</th>
                <th>SDL</th>
                <th>TUICO</th>
                <th>HESLB</th>
                <th>Total Deductions</th>
                <th>Net Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payslips as $payslip)
                <tr>
                    <td>{{ $payslip->employee->employee_id ?? 'N/A' }}</td>
                    <td>{{ $payslip->employee->name ?? 'N/A' }}</td>
                    <td>{{ $payslip->employee->department ?? 'N/A' }}</td>
                    <td>{{ number_format($payslip->base_salary, 0) }}</td>
                    <td>{{ number_format($payslip->housing_allowance + $payslip->transport_allowance + $payslip->medical_allowance, 0) }}</td>
                    <td>{{ number_format($payslip->gross_salary, 0) }}</td>
                    <td>{{ number_format($payslip->paye, 0) }}</td>
                    <td>{{ number_format($payslip->nssf, 0) }}</td>
                    <td>{{ number_format($payslip->nhif, 0) }}</td>
                    <td>{{ number_format($payslip->wcf, 0) }}</td>
                    <td>{{ number_format($payslip->sdl, 0) }}</td>
                    <td>{{ number_format($payslip->tuico, 0) }}</td>
                    <td>{{ number_format($payslip->heslb, 0) }}</td>
                    <td>{{ number_format($payslip->total_deductions, 0) }}</td>
                    <td>{{ number_format($payslip->net_salary, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
