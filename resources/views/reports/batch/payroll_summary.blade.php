<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Summary - {{ $period }}</title>
    <style>
        @page { 
            size: landscape; 
            margin: 0.3cm; 
        }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 5px; 
            transform: rotate(0deg);
        }
        .header { 
            text-align: center; 
            margin-bottom: 8px; 
            padding-bottom: 5px; 
            border-bottom: 1px solid #333; 
        }
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 5px;
        }
        .company-logo {
            max-height: 40px;
            max-width: 150px;
        }
        .report-title { 
            font-size: 14px; 
            font-weight: bold;
            margin: 5px 0; 
            color: #2d3748;
        }
        .report-info { 
            font-size: 10px; 
            margin-bottom: 8px; 
            color: #718096;
        }
        .table-container { 
            margin-top: 5px; 
            width: 100%;
            overflow: hidden;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5px; 
            font-size: 7px; 
            table-layout: fixed;
        }
        th, td { 
            border: 1px solid #cbd5e0; 
            padding: 3px; 
            text-align: left; 
            word-wrap: break-word;
            overflow: hidden;
        }
        th { 
            background-color: #f7fafc; 
            font-weight: bold; 
            color: #2d3748;
            font-size: 6px;
            padding: 2px;
        }
        .text-right { 
            text-align: right; 
        }
        .text-center { 
            text-align: center; 
        }
        .footer { 
            margin-top: 8px; 
            text-align: right; 
            font-size: 7px; 
            padding-top: 5px; 
            border-top: 1px solid #ddd; 
            color: #718096;
        }
        .summary-section { 
            background-color: #f9f9f9; 
            padding: 6px; 
            border-radius: 3px; 
            margin-bottom: 8px; 
            border: 1px solid #e2e8f0;
        }
        .summary-grid { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 5px; 
            margin-bottom: 5px; 
        }
        .summary-item { 
            background-color: #e8f4f8; 
            padding: 4px; 
            border-radius: 3px; 
            text-align: center; 
            border: 1px solid #bee3f8;
        }
        .summary-value { 
            font-size: 10px; 
            font-weight: bold; 
            color: #2b6cb0;
        }
        .summary-label { 
            font-size: 6px; 
            color: #4a5568;
        }
        .page-break { 
            page-break-after: always; 
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .currency {
            font-size: 5px;
            color: #718096;
        }
        .compact {
            letter-spacing: -0.2px;
        }
        
        /* Column Widths - Optimized for landscape */
        .col-no { width: 2.5%; }
        .col-name { width: 10%; }
        .col-position { width: 6%; }
        .col-basic { width: 6%; }
        .col-allowance { width: 5%; }
        .col-gross { width: 6%; }
        .col-start { width: 6%; }
        .col-end { width: 6%; }
        .col-basic-actual { width: 6%; }
        .col-nssf { width: 4%; }
        .col-payee { width: 4%; }
        .col-insurance { width: 4%; }
        .col-loan { width: 5%; }
        .col-tuico { width: 4%; }
        .col-debts { width: 6%; }
        .col-takehome { width: 6%; }
        .col-account { width: 8%; }
    </style>
</head>
<body>
    <div class="header">
        <!-- Logo Section -->
        <div class="logo-container">
            @if(isset($settings['company_logo']) && $settings['company_logo'])
                <img src="{{ storage_path('app/public/' . $settings['company_logo']) }}" class="company-logo" alt="Company Logo">
            @else
                @if(file_exists(storage_path('app/public/logo.png')))
                    <img src="{{ storage_path('app/public/logo.png') }}" class="company-logo" alt="Company Logo">
                @else
                    <div class="company-name" style="font-size: 16px; font-weight: bold;">
                        {{ $settings['company_name'] ?? 'Company Name' }}
                    </div>
                @endif
            @endif
        </div>
        
        <div class="report-title">PAYROLL SUMMARY REPORT</div>
        <div class="report-info">
            Period: {{ $period }} | Generated: {{ $generated_at->format('Y-m-d H:i') }} | Batch: {{ $batch_number ?? 'N/A' }}
        </div>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-value">{{ count($employees) }}</div>
                <div class="summary-label">TOTAL EMPLOYEES</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">
                    @php
                        $totalGross = array_sum(array_map(function($emp) {
                            return ($emp['basic_salary'] ?? 0) + ($emp['allowance'] ?? 0);
                        }, $employees));
                    @endphp
                    {{ number_format($totalGross, 0) }}
                </div>
                <div class="summary-label">TOTAL GROSS</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">
                    @php
                        $totalTakeHome = array_sum(array_column($employees, 'take_home'));
                    @endphp
                    {{ number_format($totalTakeHome, 0) }}
                </div>
                <div class="summary-label">TOTAL TAKE HOME</div>
            </div>
            <div class="summary-item">
                <div class="summary-value">
                    @php
                        $totalDeductions = array_sum(array_map(function($emp) {
                            return ($emp['nssf'] ?? 0) + ($emp['payee'] ?? 0) + ($emp['insurance'] ?? 0) + 
                                   ($emp['loan_board'] ?? 0) + ($emp['tuico'] ?? 0) + ($emp['personal_debts'] ?? 0);
                        }, $employees));
                    @endphp
                    {{ number_format($totalDeductions, 0) }}
                </div>
                <div class="summary-label">TOTAL DEDUCTIONS</div>
            </div>
        </div>
    </div>

    <!-- Payroll Details Table -->
    <div class="table-container">
        <table class="compact">
            <thead>
                <tr>
                    <th class="text-center col-no">No.</th>
                    <th class="col-name">JINA LA MFANYAKAZI</th>
                    <th class="col-position">CHEO</th>
                    <th class="text-right col-basic">BASIC SALARY</th>
                    <th class="text-right col-allowance">ALLOWANCE</th>
                    <th class="text-right col-gross">GROSS SALARY</th>
                    <th class="col-start">MWANZO</th>
                    <th class="col-end">MWISHO</th>
                    <th class="text-right col-basic-actual">BASIC/SALARY</th>
                    <th class="text-right col-nssf">NSSF</th>
                    <th class="text-right col-payee">PAYEE</th>
                    <th class="text-right col-insurance">BIMA</th>
                    <th class="text-right col-loan">BODI MIKOPO</th>
                    <th class="text-right col-tuico">TUICO</th>
                    <th class="text-right col-debts">MADENI NAFSIA</th>
                    <th class="text-right col-takehome">TAKE HOME</th>
                    <th class="col-account">AKAUNTI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($employees as $index => $employee)
                    @php
                        $grossSalary = ($employee['basic_salary'] ?? 0) + ($employee['allowance'] ?? 0);
                    @endphp
                    <tr>
                        <td class="text-center col-no">{{ $index + 1 }}</td>
                        <td class="col-name">{{ $employee['name'] ?? '' }}</td>
                        <td class="col-position">{{ $employee['position'] ?? '' }}</td>
                        <td class="text-right col-basic">{{ number_format($employee['basic_salary'] ?? 0, 0) }}<span class="currency"> TZS</span></td>
                        <td class="text-right col-allowance">{{ number_format($employee['allowance'] ?? 0, 0) }}<span class="currency"> TZS</span></td>
                        <td class="text-right col-gross">{{ number_format($grossSalary, 0) }}<span class="currency"> TZS</span></td>
                        <td class="col-start">{{ $employee['start_date'] ?? '' }}</td>
                        <td class="col-end">{{ $employee['end_date'] ?? '' }}</td>
                        <td class="text-right col-basic-actual">{{ number_format($employee['basic_salary_actual'] ?? ($employee['basic_salary'] ?? 0), 0) }}<span class="currency"> TZS</span></td>
                        <td class="text-right col-nssf">{{ number_format($employee['nssf'] ?? 0, 0) }}<span class="currency"> TZS</span></td>
                        <td class="text-right col-payee">{{ number_format($employee['payee'] ?? 0, 0) }}<span class="currency"> TZS</span></td>
                        <td class="text-right col-insurance">{{ number_format($employee['insurance'] ?? 0, 0) }}<span class="currency"> TZS</span></td>
                        <td class="text-right col-loan">{{ number_format($employee['loan_board'] ?? 0, 0) }}<span class="currency"> TZS</span></td>
                        <td class="text-right col-tuico">{{ number_format($employee['tuico'] ?? 0, 0) }}<span class="currency"> TZS</span></td>
                        <td class="text-right col-debts">{{ number_format($employee['personal_debts'] ?? 0, 0) }}<span class="currency"> TZS</span></td>
                        <td class="text-right col-takehome">{{ number_format($employee['take_home'] ?? 0, 0) }}<span class="currency"> TZS</span></td>
                        <td class="col-account">{{ $employee['account_number'] ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #e8f4f8; font-weight: bold;">
                    <td colspan="3" class="text-center">GRAND TOTALS</td>
                    <td class="text-right">{{ number_format(array_sum(array_column($employees, 'basic_salary')), 0) }}<span class="currency"> TZS</span></td>
                    <td class="text-right">{{ number_format(array_sum(array_column($employees, 'allowance')), 0) }}<span class="currency"> TZS</span></td>
                    <td class="text-right">{{ number_format($totalGross, 0) }}<span class="currency"> TZS</span></td>
                    <td colspan="2" class="text-center">{{ count($employees) }} Employees</td>
                    <td class="text-right">{{ number_format(array_sum(array_column($employees, 'basic_salary_actual')), 0) }}<span class="currency"> TZS</span></td>
                    <td class="text-right">{{ number_format(array_sum(array_column($employees, 'nssf')), 0) }}<span class="currency"> TZS</span></td>
                    <td class="text-right">{{ number_format(array_sum(array_column($employees, 'payee')), 0) }}<span class="currency"> TZS</span></td>
                    <td class="text-right">{{ number_format(array_sum(array_column($employees, 'insurance')), 0) }}<span class="currency"> TZS</span></td>
                    <td class="text-right">{{ number_format(array_sum(array_column($employees, 'loan_board')), 0) }}<span class="currency"> TZS</span></td>
                    <td class="text-right">{{ number_format(array_sum(array_column($employees, 'tuico')), 0) }}<span class="currency"> TZS</span></td>
                    <td class="text-right">{{ number_format(array_sum(array_column($employees, 'personal_debts')), 0) }}<span class="currency"> TZS</span></td>
                    <td class="text-right">{{ number_format($totalTakeHome, 0) }}<span class="currency"> TZS</span></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        Generated by: {{ $generated_by }} | Date: {{ now()->format('Y-m-d H:i:s') }} | Page 1 of 1
    </div>
</body>
</html>