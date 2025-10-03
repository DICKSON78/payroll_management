<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tax Report - {{ $period_display }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 20px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .company-name { font-size: 20px; font-weight: bold; color: #2c3e50; }
        .report-title { font-size: 16px; margin: 8px 0; color: #34495e; }
        .period { font-size: 14px; color: #7f8c8d; margin-bottom: 5px; }
        .summary-box { background: #fff3cd; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #ffc107; }
        .summary-item { display: inline-block; margin-right: 30px; }
        .summary-label { font-weight: bold; color: #856404; }
        .summary-value { color: #155724; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 10px; }
        th { background-color: #dc3545; color: white; padding: 8px; text-align: left; border: 1px solid #ddd; }
        td { padding: 8px; border: 1px solid #ddd; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { margin-top: 30px; text-align: center; color: #7f8c8d; font-size: 10px; border-top: 1px solid #ddd; padding-top: 10px; }
        .totals-row { background-color: #f8d7da; font-weight: bold; }
        .currency { font-family: 'DejaVu Sans', monospace; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings->company_name ?? 'COMPANY NAME' }}</div>
        <div class="report-title">PAYE TAX REPORT</div>
        <div class="period">Period: {{ $period_display }}</div>
        <div>Generated on: {{ $generated_at->format('M d, Y H:i') }} by {{ $generated_by }}</div>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <span class="summary-label">Total Employees:</span>
            <span class="summary-value">{{ number_format($employeeCount ?? 0) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total PAYE Tax:</span>
            <span class="summary-value currency">TZS {{ number_format($totalTax ?? 0, 0) }}</span>
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
                <th>Taxable Income</th>
                <th>PAYE Tax</th>
                <th>Tax Period</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTaxableIncome = 0;
            @endphp
            
            @foreach(($taxData ?? []) as $index => $tax)
            @php
                $totalTaxableIncome += $tax['taxable_income'] ?? 0;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $tax['employee_name'] ?? 'N/A' }}</td>
                <td>{{ $tax['employee_id'] ?? 'N/A' }}</td>
                <td class="text-right currency">{{ number_format($tax['taxable_income'] ?? 0, 0) }}</td>
                <td class="text-right currency">{{ number_format($tax['tax_amount'] ?? 0, 0) }}</td>
                <td class="text-center">{{ $period_display }}</td> {{-- Use period_display instead of payroll_period --}}
            </tr>
            @endforeach
            
            @if(empty($taxData))
            <tr>
                <td colspan="6" class="text-center">No tax data available for this period</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td colspan="3" class="text-right"><strong>TOTALS:</strong></td>
                <td class="text-right currency"><strong>{{ number_format($totalTaxableIncome, 0) }}</strong></td>
                <td class="text-right currency"><strong>{{ number_format($totalTax ?? 0, 0) }}</strong></td>
                <td class="text-center"><strong>{{ $employeeCount ?? 0 }} Employees</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ $settings->company_name ?? 'Company' }} - PAYE Tax Report | 
        Generated on {{ $generated_at->format('Y-m-d H:i:s') }} | 
        Page 1 of 1
    </div>
</body>
</html>