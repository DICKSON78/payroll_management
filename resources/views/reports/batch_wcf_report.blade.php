<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WCF Report - {{ $settings['company_name'] ?? 'Company' }}</title>
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
        .wcf-info { background-color: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings['company_name'] ?? 'Company' }}</div>
        <div class="report-title">WORKERS COMPENSATION FUND (WCF) REPORT</div>
        <div class="batch-info">
            Period: {{ $period }} | Batch: #{{ $batch_number }} | Generated: {{ $generated_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- WCF Information -->
    <div class="wcf-info">
        <h4 style="margin-top: 0;">Workers Compensation Fund Details</h4>
        <p><strong>Contribution Rate:</strong> 0.5% of total gross payroll</p>
        <p><strong>Calculation Basis:</strong> Total gross salaries of all employees</p>
        <p><strong>Purpose:</strong> Provides compensation to employees for work-related injuries and diseases</p>
        <p><strong>Legal Requirement:</strong> Mandatory for all employers in Tanzania</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; text-align: center;">WCF CONTRIBUTION SUMMARY</h3>
        
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $employeeCount }}</div>
                <div class="summary-label">COVERED EMPLOYEES</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalWCF, 2) }}</div>
                <div class="summary-label">TOTAL WCF ({{ $settings['currency'] ?? 'TZS' }})</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($wcfRate * 100, 1) }}%</div>
                <div class="summary-label">CONTRIBUTION RATE</div>
            </div>
        </div>
    </div>

    <!-- Employee WCF Contributions -->
    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Employment Type</th>
                <th>Gross Salary</th>
                <th>WCF Rate</th>
                <th>WCF Contribution</th>
                <th>Coverage Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($wcfData as $data)
                @php
                    $employee = $data['employee'];
                    $payslips = $employee->payslips->where('period', $period);
                    $hasPayslip = $payslips->count() > 0;
                    $grossSalary = $hasPayslip ? $payslips->sum('gross_salary') : $employee->base_salary + $employee->allowances;
                    $isCovered = in_array(strtolower($employee->employment_type), ['full-time', 'contract', 'permanent']);
                @endphp
                
                <tr>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->department }}</td>
                    <td>{{ $employee->employment_type }}</td>
                    <td class="amount">{{ number_format($grossSalary, 2) }}</td>
                    <td class="amount">{{ number_format($wcfRate * 100, 1) }}%</td>
                    <td class="amount">{{ number_format($data['wcf_amount'], 2) }}</td>
                    <td>
                        @if($isCovered)
                            <span style="color: green;">● Covered</span>
                        @else
                            <span style="color: orange;">● Limited</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #d1ecf1; font-weight: bold;">
                <td colspan="6">TOTALS</td>
                <td class="amount">{{ number_format($totalWCF, 2) }}</td>
                <td>{{ $employeeCount }} Employees</td>
            </tr>
        </tfoot>
    </table>

    <!-- WCF Summary by Department -->
    <h4>WCF Summary by Department</h4>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Employees</th>
                <th>Total Gross Salary</th>
                <th>Total WCF</th>
                <th>Average WCF</th>
                <th>Risk Level</th>
            </tr>
        </thead>
        <tbody>
            @php
                $deptWCFSummary = [];
                $riskLevels = [
                    'Operations' => 'High',
                    'IT' => 'Low', 
                    'Finance' => 'Low',
                    'HR' => 'Low',
                    'Marketing' => 'Medium'
                ];
                
                foreach($wcfData as $data) {
                    $employee = $data['employee'];
                    $dept = $employee->department ?: 'Unassigned';
                    if (!isset($deptWCFSummary[$dept])) {
                        $deptWCFSummary[$dept] = ['count' => 0, 'total_wcf' => 0, 'total_gross' => 0];
                    }
                    $deptWCFSummary[$dept]['count']++;
                    $deptWCFSummary[$dept]['total_wcf'] += $data['wcf_amount'];
                    
                    $payslips = $employee->payslips->where('period', $period);
                    $deptWCFSummary[$dept]['total_gross'] += $payslips->count() > 0 ? 
                        $payslips->sum('gross_salary') : ($employee->base_salary + $employee->allowances);
                }
            @endphp
            
            @foreach($deptWCFSummary as $dept => $data)
                @php
                    $avgWCF = $data['count'] > 0 ? $data['total_wcf'] / $data['count'] : 0;
                    $riskLevel = $riskLevels[$dept] ?? 'Medium';
                @endphp
                <tr>
                    <td>{{ $dept }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td class="amount">{{ number_format($data['total_gross'], 2) }}</td>
                    <td class="amount">{{ number_format($data['total_wcf'], 2) }}</td>
                    <td class="amount">{{ number_format($avgWCF, 2) }}</td>
                    <td>
                        @if($riskLevel == 'High')
                            <span style="color: red; font-weight: bold;">High</span>
                        @elseif($riskLevel == 'Medium')
                            <span style="color: orange; font-weight: bold;">Medium</span>
                        @else
                            <span style="color: green; font-weight: bold;">Low</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- WCF Compliance Information -->
    <div style="background-color: #f8d7da; padding: 10px; border-radius: 5px; margin-top: 15px;">
        <h4 style="margin-top: 0; color: #721c24;">WCF Compliance Requirements</h4>
        <p><strong>Submission Deadline:</strong> 15th of each month</p>
        <p><strong>Payment Method:</strong> Bank transfer to WCF account</p>
        <p><strong>Required Documents:</strong> WCF return form, Payment receipt</p>
        <p><strong>Penalties:</strong> Late submission attracts penalties and interest</p>
    </div>

    <div class="footer">
        Generated on: {{ now()->format('Y-m-d H:i:s') }} by {{ $generated_by }} | 
        This report is for WCF compliance and submission purposes
    </div>
</body>
</html>