@php
    $settings = $settings ?? ['company_name' => 'Default Company', 'currency' => 'TZS'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Paycheck</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header img { max-width: 100px; }
        .details { margin-bottom: 20px; }
        .details p { margin: 5px 0; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f4f4f4; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($settings['company_logo'])
                <img src="{{ asset('storage/' . $settings['company_logo']) }}" alt="Company Logo">
            @endif
            <h2>{{ $settings['company_name'] }}</h2>
            <p>Paycheck for {{ $employee->name }}</p>
            <p>Period: {{ $payslip->period }}</p>
        </div>
        <div class="details">
            <p><strong>Employee ID:</strong> {{ $employee->employee_id }}</p>
            <p><strong>Department:</strong> {{ $employee->department }}</p>
            <p><strong>Position:</strong> {{ $employee->position }}</p>
        </div>
        <table class="table">
            <tr>
                <th>Earnings</th>
                <th>Amount ({{ $settings['currency'] }})</th>
            </tr>
            <tr>
                <td>Base Salary</td>
                <td>{{ number_format($employee->base_salary, 2) }}</td>
            </tr>
            @if($payslip->housing_allowance)
                <tr>
                    <td>Housing Allowance</td>
                    <td>{{ number_format($payslip->housing_allowance, 2) }}</td>
                </tr>
            @endif
            @if($payslip->transport_allowance)
                <tr>
                    <td>Transport Allowance</td>
                    <td>{{ number_format($payslip->transport_allowance, 2) }}</td>
                </tr>
            @endif
            @if($payslip->medical_allowance)
                <tr>
                    <td>Medical Allowance</td>
                    <td>{{ number_format($payslip->medical_allowance, 2) }}</td>
                </tr>
            @endif
            @if($payslip->overtime_pay)
                <tr>
                    <td>Overtime Pay</td>
                    <td>{{ number_format($payslip->overtime_pay, 2) }}</td>
                </tr>
            @endif
            <tr>
                <th>Total Earnings</th>
                <td>{{ number_format($payslip->gross_salary, 2) }}</td>
            </tr>
        </table>
        <table class="table">
            <tr>
                <th>Deductions</th>
                <th>Amount ({{ $settings['currency'] }})</th>
            </tr>
            @if($payslip->nssf)
                <tr>
                    <td>NSSF</td>
                    <td>{{ number_format($payslip->nssf, 2) }}</td>
                </tr>
            @endif
            @if($payslip->paye)
                <tr>
                    <td>PAYE</td>
                    <td>{{ number_format($payslip->paye, 2) }}</td>
                </tr>
            @endif
            @if($payslip->nhif)
                <tr>
                    <td>NHIF</td>
                    <td>{{ number_format($payslip->nhif, 2) }}</td>
                </tr>
            @endif
            @if($payslip->wcf)
                <tr>
                    <td>WCF</td>
                    <td>{{ number_format($payslip->wcf, 2) }}</td>
                </tr>
            @endif
            @if($payslip->sdl)
                <tr>
                    <td>SDL</td>
                    <td>{{ number_format($payslip->sdl, 2) }}</td>
                </tr>
            @endif
            @if($payslip->other_deductions)
                <tr>
                    <td>Other Deductions</td>
                    <td>{{ number_format($payslip->other_deductions, 2) }}</td>
                </tr>
            @endif
            <tr>
                <th>Total Deductions</th>
                <td>{{ number_format($payslip->nssf + $payslip->paye + $payslip->nhif + $payslip->wcf + $payslip->sdl + $payslip->other_deductions, 2) }}</td>
            </tr>
        </table>
        <table class="table">
            <tr>
                <th>Net Pay</th>
                <td>{{ number_format($payslip->net_salary, 2) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
