<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $employee->name }} - {{ $settings['company_name'] }}</title>
    <style>
        @page { size: portrait; margin: 1cm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 15px; }
        .header { text-align: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #333; }
        .company-name { font-size: 20px; font-weight: bold; }
        .report-title { font-size: 16px; margin: 8px 0; }
        .period-info { font-size: 12px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .footer { margin-top: 20px; text-align: right; font-size: 10px; padding-top: 10px; border-top: 1px solid #ddd; }
        .employee-info { margin-bottom: 15px; padding: 10px; background-color: #f9f9f9; border-radius: 5px; }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 10px; }
        .info-item { display: flex; flex-direction: column; }
        .info-label { font-size: 10px; color: #666; margin-bottom: 2px; }
        .info-value { font-size: 12px; font-weight: 500; }
        .amount { text-align: right; font-family: 'Courier New', monospace; }
        .total-row { background-color: #d1ecf1; font-weight: bold; }
        .section-title { background-color: #e9ecef; padding: 8px; font-weight: bold; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings['company_name'] }}</div>
        <div class="report-title">EMPLOYEE PAYSLIP</div>
        <div class="period-info">
            Period: {{ $period }} | Employee: {{ $employee->name }} | Generated: {{ $generated_at->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- Employee Information -->
    <div class="employee-info">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">EMPLOYEE ID</span>
                <span class="info-value">{{ $employee->employee_id }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">DEPARTMENT</span>
                <span class="info-value">{{ $employee->department }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">POSITION</span>
                <span class="info-value">{{ $employee->position }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">EMPLOYMENT TYPE</span>
                <span class="info-value">{{ $employee->employment_type }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">BANK</span>
                <span class="info-value">{{ $employee->bank_name ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">ACCOUNT NUMBER</span>
                <span class="info-value">{{ $employee->account_number ?? 'N/A' }}</span>
            </div>
        </div>
    </div>

    @if($payslip)
        <!-- Earnings Section -->
        <div class="section-title">EARNINGS</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount ({{ $settings['currency'] }})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Basic Salary</td>
                    <td class="amount">{{ number_format($employee->base_salary, 2) }}</td>
                </tr>
                <tr>
                    <td>Allowances</td>
                    <td class="amount">{{ number_format($employee->allowances, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>GROSS SALARY</strong></td>
                    <td class="amount"><strong>{{ number_format($payslip->gross_salary, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Deductions Section -->
        <div class="section-title">DEDUCTIONS</div>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Amount ({{ $settings['currency'] }})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>NSSF Contribution</td>
                    <td class="amount">{{ number_format($payslip->deductions * 0.4, 2) }}</td>
                </tr>
                <tr>
                    <td>PAYE Tax</td>
                    <td class="amount">{{ number_format($payslip->deductions * 0.4, 2) }}</td>
                </tr>
                <tr>
                    <td>NHIF Contribution</td>
                    <td class="amount">{{ number_format($payslip->deductions * 0.2, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>TOTAL DEDUCTIONS</strong></td>
                    <td class="amount"><strong>{{ number_format($payslip->deductions, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Net Salary Section -->
        <table>
            <tbody>
                <tr class="total-row">
                    <td><strong>NET SALARY PAYABLE</strong></td>
                    <td class="amount"><strong>{{ number_format($payslip->net_salary, 2) }} {{ $settings['currency'] }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Payment Information -->
        <div class="section-title">PAYMENT INFORMATION</div>
        <div class="employee-info">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">PAYMENT DATE</span>
                    <span class="info-value">{{ now()->format('M d, Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">PAYMENT METHOD</span>
                    <span class="info-value">Bank Transfer</span>
                </div>
                <div class="info-item">
                    <span class="info-label">BANK NAME</span>
                    <span class="info-value">{{ $employee->bank_name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">ACCOUNT NUMBER</span>
                    <span class="info-value">{{ $employee->account_number ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

    @else
        <div style="text-align: center; padding: 20px; background-color: #f8d7da; border-radius: 5px;">
            <h3>No Payslip Available</h3>
            <p>No payslip found for {{ $employee->name }} for period {{ $period }}</p>
        </div>
    @endif

    <div class="footer">
        Generated on: {{ now()->format('Y-m-d H:i:s') }} | {{ $settings['company_name'] }} Payslip System<br>
        This is a computer-generated document and does not require a signature.
    </div>
</body>
</html>