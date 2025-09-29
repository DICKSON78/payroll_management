<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Summary - {{ $settings['company_name'] ?? 'Company' }}</title>
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
        .department-section { margin-bottom: 20px; }
        .department-header { background-color: #e9ecef; padding: 8px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings['company_name'] ?? 'Company' }}</div>
        <div class="report-title">PAYROLL SUMMARY REPORT</div>
        <div class="batch-info">
            Period: {{ $period }} | Batch: #{{ $batch_number }} | Generated: {{ $generated_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; text-align: center;">PAYROLL OVERVIEW</h3>
        
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $employeeCount }}</div>
                <div class="summary-label">TOTAL EMPLOYEES</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $employeesWithPayslips }}</div>
                <div class="summary-label">PROCESSED</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalGross, 2) }}</div>
                <div class="summary-label">TOTAL GROSS ({{ $settings['currency'] ?? 'TZS' }})</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalNet, 2) }}</div>
                <div class="summary-label">TOTAL NET ({{ $settings['currency'] ?? 'TZS' }})</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
            <div style="background-color: #fff3cd; padding: 8px; border-radius: 4px;">
                <strong>Total Deductions:</strong> {{ number_format($totalDeductions, 2) }} {{ $settings['currency'] ?? 'TZS' }}
            </div>
            <div style="background-color: #d4edda; padding: 8px; border-radius: 4px;">
                <strong>Average Net:</strong> {{ number_format($averageNet, 2) }} {{ $settings['currency'] ?? 'TZS' }}
            </div>
            <div style="background-color: #f8d7da; padding: 8px; border-radius: 4px;">
                <strong>Coverage:</strong> {{ number_format($coveragePercentage, 1) }}%
            </div>
        </div>
    </div>

    <!-- Department-wise Breakdown -->
    <div class="department-section">
        <h4>Department Summary</h4>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Employees</th>
                    <th>Total Gross</th>
                    <th>Total Net</th>
                    <th>Average Salary</th>
                    <th>% of Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $deptSummary = [];
                    foreach($employees as $employee) {
                        $dept = $employee->department ?: 'Unassigned';
                        if (!isset($deptSummary[$dept])) {
                            $deptSummary[$dept] = ['count' => 0, 'total_gross' => 0, 'total_net' => 0];
                        }
                        $deptSummary[$dept]['count']++;
                        
                        $payslips = $employee->payslips->where('period', $period);
                        if ($payslips->count() > 0) {
                            $deptSummary[$dept]['total_gross'] += $payslips->sum('gross_salary');
                            $deptSummary[$dept]['total_net'] += $payslips->sum('net_salary');
                        }
                    }
                @endphp
                
                @foreach($deptSummary as $dept => $data)
                    @php
                        $avgSalary = $data['count'] > 0 ? $data['total_net'] / $data['count'] : 0;
                        $percentage = $totalNet > 0 ? ($data['total_net'] / $totalNet) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $dept }}</td>
                        <td>{{ $data['count'] }}</td>
                        <td class="amount">{{ number_format($data['total_gross'], 2) }}</td>
                        <td class="amount">{{ number_format($data['total_net'], 2) }}</td>
                        <td class="amount">{{ number_format($avgSalary, 2) }}</td>
                        <td class="amount">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Detailed Employee List -->
    <h4>Employee Payroll Details</h4>
    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Name</th>
                <th>Department</th>
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
    </table>

    <div class="footer">
        Generated on: {{ now()->format('Y-m-d H:i:s') }} by {{ $generated_by }} | 
        {{ $settings['company_name'] ?? 'Company' }} Payroll System
    </div>
</body>
</html>