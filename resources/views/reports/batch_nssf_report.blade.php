<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSSF Report - {{ $settings['company_name'] ?? 'Company' }}</title>
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
        .nssf-info { background-color: #d1ecf1; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings['company_name'] ?? 'Company' }}</div>
        <div class="report-title">NATIONAL SOCIAL SECURITY FUND (NSSF) REPORT</div>
        <div class="batch-info">
            Period: {{ $period }} | Batch: #{{ $batch_number }} | Generated: {{ $generated_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- NSSF Information -->
    <div class="nssf-info">
        <h4 style="margin-top: 0;">NSSF Contribution Details</h4>
        <p><strong>Contribution Rate:</strong> 10% of basic salary (Employee: 5%, Employer: 5%)</p>
        <p><strong>Maximum Contribution:</strong> 20,000 {{ $settings['currency'] ?? 'TZS' }} per month (10,000 each)</p>
        <p><strong>Calculation Basis:</strong> Basic salary up to 200,000 {{ $settings['currency'] ?? 'TZS' }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; text-align: center;">NSSF CONTRIBUTION SUMMARY</h3>
        
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $employeeCount }}</div>
                <div class="summary-label">EMPLOYEES</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalNSSF, 2) }}</div>
                <div class="summary-label">TOTAL NSSF ({{ $settings['currency'] ?? 'TZS' }})</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalNSSF / 2, 2) }}</div>
                <div class="summary-label">EMPLOYEE SHARE</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalNSSF / 2, 2) }}</div>
                <div class="summary-label">EMPLOYER SHARE</div>
            </div>
        </div>
    </div>

    <!-- Employee NSSF Contributions -->
    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Name</th>
                <th>NSSF Number</th>
                <th>Department</th>
                <th>Basic Salary</th>
                <th>Gross Salary</th>
                <th>NSSF Basis</th>
                <th>Employee Share (5%)</th>
                <th>Employer Share (5%)</th>
                <th>Total NSSF</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($nssfData as $data)
                @php
                    $employee = $data['employee'];
                    $payslips = $employee->payslips->where('period', $period);
                    $hasPayslip = $payslips->count() > 0;
                    $grossSalary = $hasPayslip ? $payslips->sum('gross_salary') : $employee->base_salary + $employee->allowances;
                    $nssfBasis = min($employee->base_salary, 200000);
                    $employeeShare = $data['nssf_amount'] / 2;
                    $employerShare = $data['nssf_amount'] / 2;
                @endphp
                
                <tr>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->nssf_number ?? 'N/A' }}</td>
                    <td>{{ $employee->department }}</td>
                    <td class="amount">{{ number_format($employee->base_salary, 2) }}</td>
                    <td class="amount">{{ number_format($grossSalary, 2) }}</td>
                    <td class="amount">{{ number_format($nssfBasis, 2) }}</td>
                    <td class="amount">{{ number_format($employeeShare, 2) }}</td>
                    <td class="amount">{{ number_format($employerShare, 2) }}</td>
                    <td class="amount">{{ number_format($data['nssf_amount'], 2) }}</td>
                    <td>{{ $hasPayslip ? 'Verified' : 'Estimated' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #d1ecf1; font-weight: bold;">
                <td colspan="7">TOTALS</td>
                <td class="amount">{{ number_format($totalNSSF / 2, 2) }}</td>
                <td class="amount">{{ number_format($totalNSSF / 2, 2) }}</td>
                <td class="amount">{{ number_format($totalNSSF, 2) }}</td>
                <td>{{ $employeeCount }} Employees</td>
            </tr>
        </tfoot>
    </table>

    <!-- NSSF Summary by Department -->
    <h4>NSSF Summary by Department</h4>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Employees</th>
                <th>Total Basic Salary</th>
                <th>Total NSSF</th>
                <th>Average NSSF</th>
                <th>% of Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $deptNSSFSummary = [];
                foreach($nssfData as $data) {
                    $employee = $data['employee'];
                    $dept = $employee->department ?: 'Unassigned';
                    if (!isset($deptNSSFSummary[$dept])) {
                        $deptNSSFSummary[$dept] = ['count' => 0, 'total_nssf' => 0, 'total_basic' => 0];
                    }
                    $deptNSSFSummary[$dept]['count']++;
                    $deptNSSFSummary[$dept]['total_nssf'] += $data['nssf_amount'];
                    $deptNSSFSummary[$dept]['total_basic'] += $employee->base_salary;
                }
            @endphp
            
            @foreach($deptNSSFSummary as $dept => $data)
                @php
                    $avgNSSF = $data['count'] > 0 ? $data['total_nssf'] / $data['count'] : 0;
                    $percentage = $totalNSSF > 0 ? ($data['total_nssf'] / $totalNSSF) * 100 : 0;
                @endphp
                <tr>
                    <td>{{ $dept }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td class="amount">{{ number_format($data['total_basic'], 2) }}</td>
                    <td class="amount">{{ number_format($data['total_nssf'], 2) }}</td>
                    <td class="amount">{{ number_format($avgNSSF, 2) }}</td>
                    <td class="amount">{{ number_format($percentage, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on: {{ now()->format('Y-m-d H:i:s') }} by {{ $generated_by }} | 
        This report is for NSSF compliance and submission purposes
    </div>
</body>
</html>