<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NHIF Report - {{ $settings['company_name'] ?? 'Company' }}</title>
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
        .nhif-rates { background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings['company_name'] ?? 'Company' }}</div>
        <div class="report-title">NATIONAL HEALTH INSURANCE FUND (NHIF) REPORT</div>
        <div class="batch-info">
            Period: {{ $period }} | Batch: #{{ $batch_number }} | Generated: {{ $generated_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- NHIF Rates Information -->
    <div class="nhif-rates">
        <h4 style="margin-top: 0;">NHIF Contribution Rates (Tiered System)</h4>
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px;">
            <div><strong>Salary Range</strong></div>
            <div><strong>Contribution</strong></div>
            <div><strong>Salary Range</strong></div>
            <div>Up to 5,000</div><div>150</div>
            <div>35,001 - 40,000</div><div>1,100</div>
            <div>5,001 - 6,000</div><div>300</div>
            <div>40,001 - 45,000</div><div>1,200</div>
            <div>6,001 - 8,000</div><div>400</div>
            <div>45,001 - 50,000</div><div>1,300</div>
            <div>8,001 - 10,000</div><div>500</div>
            <div>50,001 - 60,000</div><div>1,400</div>
            <div>10,001 - 12,000</div><div>600</div>
            <div>60,001 - 70,000</div><div>1,500</div>
            <div>12,001 - 15,000</div><div>750</div>
            <div>70,001 - 80,000</div><div>1,600</div>
            <div>15,001 - 20,000</div><div>850</div>
            <div>80,001 - 90,000</div><div>1,700</div>
            <div>20,001 - 25,000</div><div>900</div>
            <div>90,001 - 100,000</div><div>1,800</div>
            <div>25,001 - 30,000</div><div>950</div>
            <div>Above 100,000</div><div>2,000</div>
            <div>30,001 - 35,000</div><div>1,000</div>
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; text-align: center;">NHIF CONTRIBUTION SUMMARY</h3>
        
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $employeeCount }}</div>
                <div class="summary-label">EMPLOYEES</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalNHIF, 2) }}</div>
                <div class="summary-label">TOTAL NHIF ({{ $settings['currency'] ?? 'TZS' }})</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">Tiered</div>
                <div class="summary-label">CALCULATION METHOD</div>
            </div>
        </div>
    </div>

    <!-- Employee NHIF Contributions -->
    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Name</th>
                <th>NHIF Number</th>
                <th>Department</th>
                <th>Gross Salary</th>
                <th>Salary Tier</th>
                <th>NHIF Contribution</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nhifData as $data)
                @php
                    $employee = $data['employee'];
                    $payslips = $employee->payslips->where('period', $period);
                    $hasPayslip = $payslips->count() > 0;
                    $grossSalary = $hasPayslip ? $payslips->sum('gross_salary') : $employee->base_salary + $employee->allowances;
                    $salaryTier = $this->getNHIFTier($grossSalary);
                @endphp
                
                <tr>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->nhif_number ?? 'N/A' }}</td>
                    <td>{{ $employee->department }}</td>
                    <td class="amount">{{ number_format($grossSalary, 2) }}</td>
                    <td>{{ $salaryTier }}</td>
                    <td class="amount">{{ number_format($data['nhif_amount'], 2) }}</td>
                    <td>{{ $hasPayslip ? 'Verified' : 'Estimated' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #d1ecf1; font-weight: bold;">
                <td colspan="6">TOTALS</td>
                <td class="amount">{{ number_format($totalNHIF, 2) }}</td>
                <td>{{ $employeeCount }} Employees</td>
            </tr>
        </tfoot>
    </table>

    <!-- NHIF Summary by Department -->
    <h4>NHIF Summary by Department</h4>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Employees</th>
                <th>Total Gross Salary</th>
                <th>Total NHIF</th>
                <th>Average NHIF</th>
                <th>% of Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $deptNHIFSummary = [];
                foreach($nhifData as $data) {
                    $employee = $data['employee'];
                    $dept = $employee->department ?: 'Unassigned';
                    if (!isset($deptNHIFSummary[$dept])) {
                        $deptNHIFSummary[$dept] = ['count' => 0, 'total_nhif' => 0, 'total_gross' => 0];
                    }
                    $deptNHIFSummary[$dept]['count']++;
                    $deptNHIFSummary[$dept]['total_nhif'] += $data['nhif_amount'];
                    
                    $payslips = $employee->payslips->where('period', $period);
                    $deptNHIFSummary[$dept]['total_gross'] += $payslips->count() > 0 ? 
                        $payslips->sum('gross_salary') : ($employee->base_salary + $employee->allowances);
                }
            @endphp
            
            @foreach($deptNHIFSummary as $dept => $data)
                @php
                    $avgNHIF = $data['count'] > 0 ? $data['total_nhif'] / $data['count'] : 0;
                    $percentage = $totalNHIF > 0 ? ($data['total_nhif'] / $totalNHIF) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $dept }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td class="amount">{{ number_format($data['total_gross'], 2) }}</td>
                    <td class="amount">{{ number_format($data['total_nhif'], 2) }}</td>
                    <td class="amount">{{ number_format($avgNHIF, 2) }}</td>
                    <td class="amount">{{ number_format($percentage, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- NHIF Distribution by Tier -->
    <h4>NHIF Distribution by Salary Tier</h4>
    <table>
        <thead>
            <tr>
                <th>Salary Tier</th>
                <th>Employees</th>
                <th>Total Contribution</th>
                <th>Average Contribution</th>
                <th>% of Employees</th>
            </tr>
        </thead>
        <tbody>
            @php
                $tierSummary = [];
                foreach($nhifData as $data) {
                    $employee = $data['employee'];
                    $payslips = $employee->payslips->where('period', $period);
                    $grossSalary = $payslips->count() > 0 ? 
                        $payslips->sum('gross_salary') : ($employee->base_salary + $employee->allowances);
                    $tier = $this->getNHIFTier($grossSalary);
                    
                    if (!isset($tierSummary[$tier])) {
                        $tierSummary[$tier] = ['count' => 0, 'total_contribution' => 0];
                    }
                    $tierSummary[$tier]['count']++;
                    $tierSummary[$tier]['total_contribution'] += $data['nhif_amount'];
                }
            @endphp
            
            @foreach($tierSummary as $tier => $data)
                @php
                    $avgContribution = $data['count'] > 0 ? $data['total_contribution'] / $data['count'] : 0;
                    $percentage = $employeeCount > 0 ? ($data['count'] / $employeeCount) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $tier }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td class="amount">{{ number_format($data['total_contribution'], 2) }}</td>
                    <td class="amount">{{ number_format($avgContribution, 2) }}</td>
                    <td class="amount">{{ number_format($percentage, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on: {{ now()->format('Y-m-d H:i:s') }} by {{ $generated_by }} | 
        This report is for NHIF compliance and submission purposes
    </div>
</body>
</html>