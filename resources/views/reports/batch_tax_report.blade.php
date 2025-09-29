<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Report - {{ $settings['company_name'] ?? 'Company' }}</title>
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
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 10px; }
        .summary-item { background-color: #e8f4f8; padding: 8px; border-radius: 4px; text-align: center; }
        .summary-value { font-size: 14px; font-weight: bold; }
        .summary-label { font-size: 10px; }
        .amount { text-align: right; }
        .tax-breakdown { background-color: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings['company_name'] ?? 'Company' }}</div>
        <div class="report-title">TAX DEDUCTION REPORT (PAYE)</div>
        <div class="batch-info">
            Period: {{ $period }} | Batch: #{{ $batch_number }} | Generated: {{ $generated_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; text-align: center;">TAX SUMMARY</h3>
        
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $employeeCount }}</div>
                <div class="summary-label">EMPLOYEES</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalTax, 2) }}</div>
                <div class="summary-label">TOTAL TAX ({{ $settings['currency'] ?? 'TZS' }})</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ $settings['tax_rate'] ?? 15 }}%</div>
                <div class="summary-label">TAX RATE</div>
            </div>
        </div>
    </div>

    <!-- Tax Breakdown -->
    <div class="tax-breakdown">
        <h4>Tax Calculation Basis</h4>
        <p><strong>Tax-Free Threshold:</strong> 270,000 {{ $settings['currency'] ?? 'TZS' }}</p>
        <p><strong>Tax Brackets:</strong></p>
        <ul style="font-size: 10px; margin: 0; padding-left: 15px;">
            <li>0 - 270,000: 0%</li>
            <li>270,001 - 520,000: 8%</li>
            <li>520,001 - 760,000: 20%</li>
            <li>760,001 - 1,000,000: 25%</li>
            <li>Above 1,000,000: 30%</li>
        </ul>
    </div>

    <!-- Employee Tax Details -->
    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Name</th>
                <th>TIN Number</th>
                <th>Department</th>
                <th>Gross Salary</th>
                <th>NSSF Deduction</th>
                <th>Taxable Income</th>
                <th>PAYE Tax</th>
                <th>Net After Tax</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($taxData as $data)
                @php
                    $employee = $data['employee'];
                    $payslips = $employee->payslips->where('period', $period);
                    $hasPayslip = $payslips->count() > 0;
                    $grossSalary = $hasPayslip ? $payslips->sum('gross_salary') : $employee->base_salary + $employee->allowances;
                    $nssfDeduction = min($grossSalary * 0.10, 20000); // NSSF calculation
                    $taxableIncome = $grossSalary - $nssfDeduction;
                @endphp
                
                <tr>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->tin_number ?? 'N/A' }}</td>
                    <td>{{ $employee->department }}</td>
                    <td class="amount">{{ number_format($grossSalary, 2) }}</td>
                    <td class="amount">{{ number_format($nssfDeduction, 2) }}</td>
                    <td class="amount">{{ number_format($taxableIncome, 2) }}</td>
                    <td class="amount">{{ number_format($data['tax_amount'], 2) }}</td>
                    <td class="amount">{{ number_format($grossSalary - $data['tax_amount'] - $nssfDeduction, 2) }}</td>
                    <td>{{ $hasPayslip ? 'Verified' : 'Estimated' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #d1ecf1; font-weight: bold;">
                <td colspan="7">TOTALS</td>
                <td class="amount">{{ number_format($totalTax, 2) }}</td>
                <td colspan="2">{{ $employeeCount }} Employees</td>
            </tr>
        </tfoot>
    </table>

    <!-- Tax Summary by Department -->
    <h4>Tax Summary by Department</h4>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Employees</th>
                <th>Total Gross</th>
                <th>Total Tax</th>
                <th>Average Tax</th>
                <th>% of Total Tax</th>
            </tr>
        </thead>
        <tbody>
            @php
                $deptTaxSummary = [];
                foreach($taxData as $data) {
                    $employee = $data['employee'];
                    $dept = $employee->department ?: 'Unassigned';
                    if (!isset($deptTaxSummary[$dept])) {
                        $deptTaxSummary[$dept] = ['count' => 0, 'total_tax' => 0, 'total_gross' => 0];
                    }
                    $deptTaxSummary[$dept]['count']++;
                    $deptTaxSummary[$dept]['total_tax'] += $data['tax_amount'];
                    
                    $payslips = $employee->payslips->where('period', $period);
                    $deptTaxSummary[$dept]['total_gross'] += $payslips->count() > 0 ? 
                        $payslips->sum('gross_salary') : ($employee->base_salary + $employee->allowances);
                }
            @endphp
            
            @foreach($deptTaxSummary as $dept => $data)
                @php
                    $avgTax = $data['count'] > 0 ? $data['total_tax'] / $data['count'] : 0;
                    $percentage = $totalTax > 0 ? ($data['total_tax'] / $totalTax) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $dept }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td class="amount">{{ number_format($data['total_gross'], 2) }}</td>
                    <td class="amount">{{ number_format($data['total_tax'], 2) }}</td>
                    <td class="amount">{{ number_format($avgTax, 2) }}</td>
                    <td class="amount">{{ number_format($percentage, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on: {{ now()->format('Y-m-d H:i:s') }} by {{ $generated_by }} | 
        This report is for PAYE tax compliance purposes
    </div>
</body>
</html>