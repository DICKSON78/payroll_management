<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Payslip Report - {{ $settings['company_name'] ?? 'Company' }}</title>
    <style>
        @page { size: landscape; margin: 1cm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .company-name { font-size: 20px; font-weight: bold; }
        .report-title { font-size: 16px; margin: 8px 0; }
        .batch-info { font-size: 12px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .footer { margin-top: 20px; text-align: right; font-size: 10px; padding-top: 10px; border-top: 1px solid #ddd; }
        .summary-section { background-color: #f9f9f9; padding: 12px; border-radius: 5px; margin-bottom: 15px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 10px; }
        .summary-item { background-color: #e8f4f8; padding: 8px; border-radius: 4px; text-align: center; }
        .summary-value { font-size: 14px; font-weight: bold; }
        .summary-label { font-size: 10px; }
        .amount { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings['company_name'] ?? 'Company' }}</div>
        <div class="report-title">BATCH PAYSLIP REPORT - ALL EMPLOYEES</div>
        <div class="batch-info">
            Period: {{ $period }} | Batch: #{{ $batch_number }} | Generated: {{ $generated_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; text-align: center;">PAYSLIP SUMMARY - BATCH #{{ $batch_number }}</h3>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $employeeCount }}</div>
                <div class="summary-label">TOTAL EMPLOYEES</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $employeesWithPayslips }}</div>
                <div class="summary-label">WITH PAYSLIPS</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalGross, 2) }} {{ $settings['currency'] ?? 'TZS' }}</div>
                <div class="summary-label">TOTAL GROSS</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalNet, 2) }} {{ $settings['currency'] ?? 'TZS' }}</div>
                <div class="summary-label">TOTAL NET</div>
            </div>
        </div>
    </div>

    <!-- Employee Payslip Summary Table -->
    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Base Salary</th>
                <th>Allowances</th>
                <th>Gross Salary</th>
                <th>Deductions</th>
                <th>Net Salary</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
                @php
                    $payslips = $employee->payslips->where('period', $period);
                    $hasPayslip = $payslips->count() > 0;
                    $payslip = $hasPayslip ? $payslips->first() : null;
                @endphp

                <tr>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->department }}</td>
                    <td>{{ $employee->position }}</td>
                    <td class="amount">{{ number_format($employee->base_salary, 2) }}</td>
                    <td class="amount">{{ number_format($employee->allowances, 2) }}</td>
                    <td class="amount">
                        {{ $hasPayslip ? number_format($payslip->gross_salary, 2) : 'N/A' }}
                    </td>
                    <td class="amount">
                        {{ $hasPayslip ? number_format($payslip->deductions, 2) : 'N/A' }}
                    </td>
                    <td class="amount">
                        {{ $hasPayslip ? number_format($payslip->net_salary, 2) : 'N/A' }}
                    </td>
                    <td>{{ $hasPayslip ? 'Processed' : 'No Payslip' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #d1ecf1; font-weight: bold;">
                <td colspan="6">TOTALS</td>
                <td class="amount">{{ number_format($totalGross, 2) }}</td>
                <td class="amount">{{ number_format($totalDeductions, 2) }}</td>
                <td class="amount">{{ number_format($totalNet, 2) }}</td>
                <td>{{ $employeesWithPayslips }}/{{ $employeeCount }} Employees</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on: {{ now()->format('Y-m-d H:i:s') }} by {{ $generated_by }} |
        Coverage: {{ number_format($coveragePercentage, 1) }}%
    </div>
</body>
</html>
