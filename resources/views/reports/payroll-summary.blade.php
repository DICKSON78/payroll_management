<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payroll Report - {{ $period_display }}</title>
    <style>
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            margin: 10px; 
            font-size: 8px; 
        }
        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            border-bottom: 1px solid #333; 
            padding-bottom: 8px; 
        }
        .company-name { 
            font-size: 14px; 
            font-weight: bold; 
        }
        .report-title { 
            font-size: 12px; 
            margin: 3px 0; 
        }
        .period { 
            font-size: 10px; 
            margin-bottom: 3px; 
        }
        
        .payroll-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
            font-size: 7px;
        }
        .payroll-table th, .payroll-table td { 
            padding: 3px; 
            border: 1px solid #333; 
            text-align: left; 
        }
        .payroll-table th { 
            background-color: #f0f0f0; 
            font-weight: bold; 
            text-align: center; 
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        
        .total-row { 
            font-weight: bold; 
            background-color: #f9f9f9; 
        }
        
        .footer { 
            margin-top: 20px; 
            text-align: center; 
            font-size: 7px; 
            border-top: 1px solid #333; 
            padding-top: 8px; 
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings->company_name ?? 'Summit Financial Adversory' }}</div>
        <div class="report-title">PAYROLL REPORT</div>
        <div class="period">{{ $period_display }}</div>
        <div>Generated on: {{ $generated_at->format('M d, Y H:i') }}</div>
    </div>

    <table class="payroll-table">
        <thead>
            <tr>
                <th>NA.</th>
                <th>JINA LA MFANYAKAZI</th>
                <th>CHEO</th>
                <th>BASIC SALARY</th>
                <th>ALLOWANCE</th>
                <th>GROSS SALARY</th>
                <th>NSSF</th>
                <th>PAYEE</th>
                <th>BIMA</th>
                <th>BODI MIKOPO</th>
                <th>TUICO</th>
                <th>MADENI NAFSIA</th>
                <th>TAKE HOME</th>
                <th>AKAUNTI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
            <tr>
                <td class="text-center">{{ $employee['na'] }}</td>
                <td>{{ $employee['name'] }}</td>
                <td>{{ $employee['position'] }}</td>
                <td class="text-right">{{ number_format($employee['basic_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['allowance'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['gross_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['nssf'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['payee'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['bima'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['bodi_mikopo'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['tuico'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['madeni_nafsia'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['take_home'], 0) }}</td>
                <td class="text-center">{{ $employee['account'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">TOTALS:</td>
                <td class="text-right">{{ number_format($totals['total_basic_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_allowance'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_gross_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_nssf'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_payee'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_bima'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_bodi_mikopo'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_tuico'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_madeni_nafsia'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_take_home'], 0) }}</td>
                <td class="text-center">{{ $totals['employee_count'] }} Wafanyakazi</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ $settings->company_name ?? 'Kampuni' }} - Mfumo wa Usimamizi wa Malipo | 
        Ilitengenezwa tarehe {{ $generated_at->format('Y-m-d H:i:s') }}
    </div>
</body>
</html>