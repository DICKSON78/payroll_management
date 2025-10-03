<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>NHIF Report - {{ $period_display }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .company-name { font-size: 20px; font-weight: bold; color: #2c3e50; }
        .report-title { font-size: 16px; margin: 8px 0; color: #34495e; }
        .period { font-size: 14px; color: #7f8c8d; margin-bottom: 5px; }
        .summary-box { background: #d4edda; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #28a745; }
        .summary-item { display: inline-block; margin-right: 30px; }
        .summary-label { font-weight: bold; color: #155724; }
        .summary-value { color: #155724; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10px; }
        th { background-color: #28a745; color: white; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 8px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #7f8c8d; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
        .totals-row { background-color: #d4edda; font-weight: bold; }
        .currency { font-family: 'DejaVu Sans', monospace; }
        .tier-badge { background: #6c757d; color: white; padding: 2px 6px; border-radius: 3px; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings->company_name ?? 'COMPANY NAME' }}</div>
        <div class="report-title">NHIF CONTRIBUTION REPORT</div>
        <div class="period">Period: {{ $period_display }}</div>
        <div>Generated on: {{ $generated_at->format('M d, Y H:i') }} by {{ $generated_by }}</div>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <span class="summary-label">Total Employees:</span>
            <span class="summary-value">{{ number_format($employeeCount ?? 0) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total NHIF Contributions:</span>
            <span class="summary-value currency">TZS {{ number_format($totalNHIF ?? 0, 0) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Reporting Period:</span>
            <span class="summary-value">{{ $period_display }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee Name</th>
                <th>Employee ID</th>
                <th>NHIF Number</th>
                <th>Gross Salary</th>
                <th>NHIF Tier</th>
                <th>NHIF Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalGrossSalary = 0;
                
                // NHIF Tiers function ndani ya template
                function getNHIFTier($salary) {
                    $tiers = [
                        5000 => 'Up to 5,000',
                        10000 => '5,001 - 10,000',
                        15000 => '10,001 - 15,000',
                        20000 => '15,001 - 20,000',
                        25000 => '20,001 - 25,000',
                        30000 => '25,001 - 30,000',
                        35000 => '30,001 - 35,000',
                        40000 => '35,001 - 40,000',
                        45000 => '40,001 - 45,000',
                        50000 => '45,001 - 50,000',
                        60000 => '50,001 - 60,000',
                        70000 => '60,001 - 70,000',
                        80000 => '70,001 - 80,000',
                        90000 => '80,001 - 90,000',
                        100000 => '90,001 - 100,000',
                        PHP_INT_MAX => 'Above 100,000'
                    ];

                    foreach ($tiers as $limit => $tier) {
                        if ($salary <= $limit) {
                            return $tier;
                        }
                    }
                    return 'Above 100,000';
                }
            @endphp
            
            @foreach(($nhifData ?? []) as $index => $nhif)
            @php
                $totalGrossSalary += $nhif['gross_salary'] ?? 0;
                $nhifTier = getNHIFTier($nhif['gross_salary'] ?? 0);
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $nhif['employee_name'] ?? 'N/A' }}</td>
                <td>{{ $nhif['employee_id'] ?? 'N/A' }}</td>
                <td>{{ $nhif['nhif_number'] ?? 'N/A' }}</td>
                <td class="text-right currency">{{ number_format($nhif['gross_salary'] ?? 0, 0) }}</td>
                <td class="text-center"><span class="tier-badge">{{ $nhifTier }}</span></td>
                <td class="text-right currency">{{ number_format($nhif['nhif_amount'] ?? 0, 0) }}</td>
            </tr>
            @endforeach
            
            @if(empty($nhifData))
            <tr>
                <td colspan="7" class="text-center">No NHIF data available for this period</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="4" class="text-right"><strong>TOTALS:</strong></td>
                <td class="text-right currency"><strong>{{ number_format($totalGrossSalary, 0) }}</strong></td>
                <td class="text-center"><strong>{{ $employeeCount ?? 0 }} Employees</strong></td>
                <td class="text-right currency"><strong>{{ number_format($totalNHIF ?? 0, 0) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ $settings->company_name ?? 'Company' }} - NHIF Contribution Report | 
        Generated on {{ $generated_at->format('Y-m-d H:i:s') }} | 
        Page 1 of 1
    </div>
</body>
</html>