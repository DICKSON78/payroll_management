<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NSSF Report - {{ $period_display }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .company-name { font-size: 20px; font-weight: bold; color: #2c3e50; }
        .report-title { font-size: 16px; margin: 8px 0; color: #34495e; }
        .period { font-size: 14px; color: #7f8c8d; margin-bottom: 5px; }
        .summary-box { background: #d1ecf1; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #17a2b8; }
        .summary-item { display: inline-block; margin-right: 30px; }
        .summary-label { font-weight: bold; color: #0c5460; }
        .summary-value { color: #155724; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10px; }
        th { background-color: #17a2b8; color: white; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 8px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #7f8c8d; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
        .totals-row { background-color: #d1ecf1; font-weight: bold; }
        .currency { font-family: 'DejaVu Sans', monospace; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings->company_name ?? 'COMPANY NAME' }}</div>
        <div class="report-title">NSSF CONTRIBUTION REPORT</div>
        <div class="period">Period: {{ $period_display }}</div>
        <div>Generated on: {{ $generated_at->format('M d, Y H:i') }} by {{ $generated_by }}</div>
    </div>

    @php
        // Calculate totals from the data
        $totalEmployeeNSSF = 0;
        $totalEmployerNSSF = 0;
        $totalGrossSalary = 0;
        
        foreach (($nssfData ?? []) as $nssf) {
            $totalEmployeeNSSF += $nssf['nssf_amount'] ?? 0;
            $totalEmployerNSSF += $nssf['employer_contribution'] ?? 0;
            $totalGrossSalary += $nssf['gross_salary'] ?? 0;
        }
        
        $totalNSSF = $totalEmployeeNSSF + $totalEmployerNSSF;
    @endphp

    <div class="summary-box">
        <div class="summary-item">
            <span class="summary-label">Total Employees:</span>
            <span class="summary-value">{{ number_format($employeeCount ?? 0) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Employee NSSF:</span>
            <span class="summary-value currency">TZS {{ number_format($totalEmployeeNSSF, 0) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Employer NSSF:</span>
            <span class="summary-value currency">TZS {{ number_format($totalEmployerNSSF, 0) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total NSSF:</span>
            <span class="summary-value currency">TZS {{ number_format($totalNSSF, 0) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee Name</th>
                <th>Employee ID</th>
                <th>NSSF Number</th>
                <th>Gross Salary</th>
                <th>Employee NSSF</th>
                <th>Employer NSSF</th>
                <th>Total NSSF</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($nssfData ?? []) as $index => $nssf)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $nssf['employee_name'] ?? 'N/A' }}</td>
                <td>{{ $nssf['employee_id'] ?? 'N/A' }}</td>
                <td>{{ $nssf['nssf_number'] ?? 'N/A' }}</td>
                <td class="text-right currency">{{ number_format($nssf['gross_salary'] ?? 0, 0) }}</td>
                <td class="text-right currency">{{ number_format($nssf['nssf_amount'] ?? 0, 0) }}</td>
                <td class="text-right currency">{{ number_format($nssf['employer_contribution'] ?? 0, 0) }}</td>
                <td class="text-right currency">{{ number_format(($nssf['nssf_amount'] ?? 0) + ($nssf['employer_contribution'] ?? 0), 0) }}</td>
            </tr>
            @endforeach
            
            @if(empty($nssfData))
            <tr>
                <td colspan="8" class="text-center">No NSSF data available for this period</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="4" class="text-right"><strong>TOTALS:</strong></td>
                <td class="text-right currency"><strong>{{ number_format($totalGrossSalary, 0) }}</strong></td>
                <td class="text-right currency"><strong>{{ number_format($totalEmployeeNSSF, 0) }}</strong></td>
                <td class="text-right currency"><strong>{{ number_format($totalEmployerNSSF, 0) }}</strong></td>
                <td class="text-right currency"><strong>{{ number_format($totalNSSF, 0) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ $settings->company_name ?? 'Company' }} - NSSF Contribution Report | 
        Generated on {{ $generated_at->format('Y-m-d H:i:s') }} | 
        Page 1 of 1
    </div>
</body>
</html>