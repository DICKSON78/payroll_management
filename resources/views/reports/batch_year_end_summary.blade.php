<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Year-End Summary - {{ $settings['company_name'] ?? 'Company' }}</title>
    <style>
        /* Keep the same styles as before */
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
        <div class="report-title">YEAR-END PAYROLL SUMMARY REPORT</div>
        <div class="batch-info">
            Year: {{ $period }} | Batch: #{{ $batch_number }} | Generated: {{ $generated_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- Safe Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; text-align: center;">ANNUAL PAYROLL OVERVIEW - {{ $period }}</h3>
        
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ $employeeCount ?? 0 }}</div>
                <div class="summary-label">TOTAL EMPLOYEES</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($annualTotals['gross'] ?? 0, 2) }}</div>
                <div class="summary-label">ANNUAL GROSS ({{ $settings['currency'] ?? 'TZS' }})</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($annualTotals['net'] ?? 0, 2) }}</div>
                <div class="summary-label">ANNUAL NET ({{ $settings['currency'] ?? 'TZS' }})</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">{{ number_format($annualTotals['tax'] ?? 0, 2) }}</div>
                <div class="summary-label">TOTAL TAX PAID</div>
            </div>
        </div>
    </div>

    <!-- Simple Employee Listing -->
    <h4>Employee Annual Summary</h4>
    <table>
        <thead>
            <tr>
                <th>Emp ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Annual Gross</th>
                <th>Annual Net</th>
                <th>Payroll Months</th>
            </tr>
        </thead>
        <tbody>
            @forelse($yearlyData ?? [] as $data)
                <tr>
                    <td>{{ $data['employee']->employee_id ?? 'N/A' }}</td>
                    <td>{{ $data['employee']->name ?? 'N/A' }}</td>
                    <td>{{ $data['employee']->department ?? 'N/A' }}</td>
                    <td class="amount">{{ number_format($data['gross_salary'] ?? 0, 2) }}</td>
                    <td class="amount">{{ number_format($data['net_salary'] ?? 0, 2) }}</td>
                    <td>{{ $data['payslip_count'] ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No payroll data available for the selected year</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on: {{ now()->format('Y-m-d H:i:s') }} | 
        {{ $settings['company_name'] ?? 'Company' }} Annual Payroll Review
    </div>
</body>
</html>