<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDL Report - {{ $settings['company_name'] ?? 'Company' }}</title>
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
        .sdl-info { background-color: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings['company_name'] ?? 'Company' }}</div>
        <div class="report-title">SKILLS DEVELOPMENT LEVY (SDL) REPORT</div>
        <div class="batch-info">
            Period: {{ $period }} | Batch: #{{ $batch_number }} | Generated: {{ $generated_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- SDL Information -->
    <div class="sdl-info">
        <h4 style="margin-top: 0;">Skills Development Levy Details</h4>
        <p><strong>Contribution Rate:</strong> 3.5% of total gross payroll</p>
        <p><strong>Calculation Basis:</strong> Total gross salaries of all employees</p>
        <p><strong>Purpose:</strong> Funds skills development and vocational training programs</p>
        <p><strong>Administration:</strong> Vocational Education and Training Authority (VETA)</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; text-align: center;">SDL CONTRIBUTION SUMMARY</h3>
        
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $employeeCount }}</div>
                <div class="summary-label">COVERED EMPLOYEES</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($totalSDL, 2) }}</div>
                <div class="summary-label">TOTAL SDL ({{ $settings['currency'] ?? 'TZS' }})</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($sdlRate * 100, 1) }}%</div>
                <div class="summary-label">CONTRIBUTION RATE</div>
            </div>
        </div>
    </div>

    <!-- Employee SDL Contributions -->
    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Gross Salary</th>
                <th>SDL Rate</th>
                <th>SDL Contribution</th>
                <th>Training Eligibility</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sdlData as $data)
                @php
                    $employee = $data['employee'];
                    $payslips = $employee->payslips->where('period', $period);
                    $hasPayslip = $payslips->count() > 0;
                    $grossSalary = $hasPayslip ? $payslips->sum('gross_salary') : $employee->base_salary + $employee->allowances;
                    $isEligible = $grossSalary >= 100000; // Example eligibility criteria
                @endphp
                
                <tr>
                    <td>{{ $employee->employee_id }}</td>
                    <td>{{ $employee->name }}</td>
                    <td>{{ $employee->department }}</td>
                    <td>{{ $employee->position }}</td>
                    <td class="amount">{{ number_format($grossSalary, 2) }}</td>
                    <td class="amount">{{ number_format($sdlRate * 100, 1) }}%</td>
                    <td class="amount">{{ number_format($data['sdl_amount'], 2) }}</td>
                    <td>
                        @if($isEligible)
                            <span style="color: green;">● Eligible</span>
                        @else
                            <span style="color: orange;">● Review</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #d1ecf1; font-weight: bold;">
                <td colspan="6">TOTALS</td>
                <td class="amount">{{ number_format($totalSDL, 2) }}</td>
                <td>{{ $employeeCount }} Employees</td>
            </tr>
        </tfoot>
    </table>

    <!-- SDL Summary by Department -->
    <h4>SDL Summary by Department</h4>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Employees</th>
                <th>Total Gross Salary</th>
                <th>Total SDL</th>
                <th>Average SDL</th>
                <th>Training Priority</th>
            </tr>
        </thead>
        <tbody>
            @php
                $deptSDLSummary = [];
                $trainingPriority = [
                    'IT' => 'High',
                    'Operations' => 'High',
                    'Finance' => 'Medium',
                    'HR' => 'Medium', 
                    'Marketing' => 'Low'
                ];
                
                foreach($sdlData as $data) {
                    $employee = $data['employee'];
                    $dept = $employee->department ?: 'Unassigned';
                    if (!isset($deptSDLSummary[$dept])) {
                        $deptSDLSummary[$dept] = ['count' => 0, 'total_sdl' => 0, 'total_gross' => 0];
                    }
                    $deptSDLSummary[$dept]['count']++;
                    $deptSDLSummary[$dept]['total_sdl'] += $data['sdl_amount'];
                    
                    $payslips = $employee->payslips->where('period', $period);
                    $deptSDLSummary[$dept]['total_gross'] += $payslips->count() > 0 ? 
                        $payslips->sum('gross_salary') : ($employee->base_salary + $employee->allowances);
                }
            @endphp
            
            @foreach($deptSDLSummary as $dept => $data)
                @php
                    $avgSDL = $data['count'] > 0 ? $data['total_sdl'] / $data['count'] : 0;
                    $priority = $trainingPriority[$dept] ?? 'Medium';
                @endphp
                <tr>
                    <td>{{ $dept }}</td>
                    <td>{{ $data['count'] }}</td>
                    <td class="amount">{{ number_format($data['total_gross'], 2) }}</td>
                    <td class="amount">{{ number_format($data['total_sdl'], 2) }}</td>
                    <td class="amount">{{ number_format($avgSDL, 2) }}</td>
                    <td>
                        @if($priority == 'High')
                            <span style="color: red; font-weight: bold;">High Priority</span>
                        @elseif($priority == 'Medium')
                            <span style="color: orange; font-weight: bold;">Medium Priority</span>
                        @else
                            <span style="color: green; font-weight: bold;">Low Priority</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- SDL Utilization Plan -->
    <div style="background-color: #e8f4f8; padding: 10px; border-radius: 5px; margin-top: 15px;">
        <h4 style="margin-top: 0;">Recommended Skills Development Plan</h4>
        <p><strong>Total SDL Funds Available:</strong> {{ number_format($totalSDL, 2) }} {{ $settings['currency'] ?? 'TZS' }}</p>
        <p><strong>Recommended Allocation:</strong></p>
        <ul>
            <li>IT Department: Technical skills upgrade (40%)</li>
            <li>Operations: Process improvement training (30%)</li>
            <li>Finance & HR: Professional certification (20%)</li>
            <li>Marketing: Digital marketing courses (10%)</li>
        </ul>
    </div>

    <div class="footer">
        Generated on: {{ now()->format('Y-m-d H:i:s') }} by {{ $generated_by }} | 
        This report is for SDL compliance and skills development planning
    </div>
</body>
</html>