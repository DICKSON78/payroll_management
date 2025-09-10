@php
    $settings = $settings ?? ['company_name' => 'Default Company', 'currency' => 'TZS'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Year-End Tax Form</title>
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
            <p>Year-End Tax Form</p>
            <p>Year: {{ $year }}</p>
        </div>
        @foreach($data->groupBy('employee_id') as $employeeId => $payslips)
            @php
                $employee = $payslips->first()->employee;
            @endphp
            <div class="details">
                <p><strong>Employee:</strong> {{ $employee->name }}</p>
                <p><strong>Employee ID:</strong> {{ $employee->employee_id }}</p>
            </div>
            <table class="table">
                <tr>
                    <th>Period</th>
                    <th>Gross Salary ({{ $settings['currency'] }})</th>
                    <th>PAYE ({{ $settings['currency'] }})</th>
                    <th>NSSF ({{ $settings['currency'] }})</th>
                    <th>NHIF ({{ $settings['currency'] }})</th>
                    <th>WCF ({{ $settings['currency'] }})</th>
                    <th>SDL ({{ $settings['currency'] }})</th>
                </tr>
                @foreach($payslips as $payslip)
                    <tr>
                        <td>{{ $payslip->period }}</td>
                        <td>{{ number_format($payslip->gross_salary, 2) }}</td>
                        <td>{{ number_format($payslip->paye, 2) }}</td>
                        <td>{{ number_format($payslip->nssf, 2) }}</td>
                        <td>{{ number_format($payslip->nhif, 2) }}</td>
                        <td>{{ number_format($payslip->wcf, 2) }}</td>
                        <td>{{ number_format($payslip->sdl, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th>Total</th>
                    <td>{{ number_format($payslips->sum('gross_salary'), 2) }}</td>
                    <td>{{ number_format($payslips->sum('paye'), 2) }}</td>
                    <td>{{ number_format($payslips->sum('nssf'), 2) }}</td>
                    <td>{{ number_format($payslips->sum('nhif'), 2) }}</td>
                    <td>{{ number_format($payslips->sum('wcf'), 2) }}</td>
                    <td>{{ number_format($payslips->sum('sdl'), 2) }}</td>
                </tr>
            </table>
        @endforeach
    </div>
</body>
</html>
