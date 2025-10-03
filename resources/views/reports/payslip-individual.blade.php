<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $employee ? $employee->name : 'Unknown Employee' }} - {{ $period_display }}</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; margin: 15px; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #333; padding-bottom: 10px; }
        .company-name { font-size: 18px; font-weight: bold; }
        .document-title { font-size: 16px; margin: 5px 0; }
        .period { font-size: 14px; margin-bottom: 5px; }
        .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .info-table th, .info-table td { padding: 6px; border: 1px solid #333; text-align: left; }
        .info-table th { background-color: #f5f5f5; font-weight: bold; }
        .salary-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .salary-table th, .salary-table td { padding: 8px; border: 1px solid #333; }
        .salary-table th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .currency { font-family: 'DejaVu Sans', monospace; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .breakdown { margin: 15px 0; padding: 10px; border: 1px solid #333; }
        .breakdown-title { font-weight: bold; margin-bottom: 8px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; border-top: 1px solid #333; padding-top: 10px; }
        .signature-section { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-line { border-top: 1px solid #333; width: 200px; margin-top: 40px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings->company_name ?? 'Summit Financial Adversory' }}</div>
        <div class="document-title">EMPLOYEE PAYSLIP</div>
        <div class="period">{{ $period_display }}</div>
    </div>

    <!-- Employee Information Table -->
    <table class="info-table">
        <thead>
            <tr>
                <th colspan="4">EMPLOYEE INFORMATION</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="25%"><strong>Employee Name:</strong></td>
                <td width="25%">{{ $employee ? $employee->name : 'N/A' }}</td>
                <td width="25%"><strong>Employee ID:</strong></td>
                <td width="25%">{{ $employee ? $employee->employee_id : ($payroll->employee_id ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td><strong>Position:</strong></td>
                <td>{{ $employee ? $employee->position : 'N/A' }}</td>
                <td><strong>Department:</strong></td>
                <td>{{ $employee ? $employee->department : 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Pay Period:</strong></td>
                <td>{{ $period_display }}</td>
                <td><strong>Payment Date:</strong></td>
                <td>{{ isset($payroll->payment_date) ? \Carbon\Carbon::parse($payroll->payment_date)->format('Y-m-d') : \Carbon\Carbon::now('Africa/Nairobi')->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td><strong>Payment Method:</strong></td>
                <td>{{ $payroll->payment_method ?? 'Bank Transfer' }}</td>
                <td><strong>Bank Account:</strong></td>
                <td>{{ $employee ? $employee->account_number : 'N/A' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Salary Details Table -->
    <table class="salary-table">
        <thead>
            <tr>
                <th colspan="2">EARNINGS</th>
                <th colspan="2">DEDUCTIONS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Earnings Column -->
                <td width="25%">Basic Salary</td>
                <td width="25%" class="text-right currency">{{ number_format($payroll->base_salary ?? 0, 0) }}</td>
                
                <!-- Deductions Column -->
                <td width="25%">NSSF</td>
                <td width="25%" class="text-right currency">{{ number_format($deduction_breakdown['nssf'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td>Allowances</td>
                <td class="text-right currency">{{ number_format($payroll->allowances ?? 0, 0) }}</td>
                
                <td>NHIF</td>
                <td class="text-right currency">{{ number_format($deduction_breakdown['nhif'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td>Other Earnings</td>
                <td class="text-right currency">{{ number_format($payroll->other_earnings ?? 0, 0) }}</td>
                
                <td>PAYE Tax</td>
                <td class="text-right currency">{{ number_format($deduction_breakdown['paye'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                
                <td>Other Deductions</td>
                <td class="text-right currency">{{ number_format($deduction_breakdown['other_deductions'] ?? 0, 0) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Gross Salary</strong></td>
                <td class="text-right currency"><strong>{{ number_format($payroll->total_amount ?? 0, 0) }}</strong></td>
                
                <td><strong>Total Deductions</strong></td>
                <td class="text-right currency"><strong>{{ number_format($payroll->deductions ?? 0, 0) }}</strong></td>
            </tr>
            <tr class="total-row">
                <td colspan="3" class="text-right"><strong>NET SALARY</strong></td>
                <td class="text-right currency"><strong>{{ number_format($payroll->net_salary ?? 0, 0) }}</strong></td>
            </tr>
        </tbody>
    </table>

    @if(isset($deduction_breakdown))
    <div class="breakdown">
        <div class="breakdown-title">DEDUCTION BREAKDOWN</div>
        <table style="width: 100%;">
            <tr>
                <td width="25%"><strong>NSSF:</strong></td>
                <td width="25%" class="currency">TZS {{ number_format($deduction_breakdown['nssf'] ?? 0, 0) }}</td>
                <td width="25%"><strong>NHIF:</strong></td>
                <td width="25%" class="currency">TZS {{ number_format($deduction_breakdown['nhif'] ?? 0, 0) }}</td>
            </tr>
            <tr>
                <td><strong>PAYE Tax:</strong></td>
                <td class="currency">TZS {{ number_format($deduction_breakdown['paye'] ?? 0, 0) }}</td>
                <td><strong>Other Deductions:</strong></td>
                <td class="currency">TZS {{ number_format($deduction_breakdown['other_deductions'] ?? 0, 0) }}</td>
            </tr>
        </table>
    </div>
    @endif

    <div class="signature-section">
        <div>
            <div class="signature-line"></div>
            <div>Employee Signature</div>
        </div>
        <div>
            <div class="signature-line"></div>
            <div>Authorized Signature</div>
        </div>
    </div>

    <div class="footer">
        {{ $settings->company_name ?? 'Company' }} - Payslip |
        Generated on {{ isset($generated_at) ? $generated_at->format('Y-m-d H:i:s') : \Carbon\Carbon::now('Africa/Nairobi')->format('Y-m-d H:i:s') }} |
        This is a computer generated document
    </div>
</body>
</html>