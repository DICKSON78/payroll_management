<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Year End Summary Report - {{ $period_display }}</title>
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
        
        .summary-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
            font-size: 7px;
        }
        .summary-table th, .summary-table td { 
            padding: 3px; 
            border: 1px solid #333; 
            text-align: left; 
        }
        .summary-table th { 
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
        
        .section-header {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }
        
        .footer { 
            margin-top: 20px; 
            text-align: center; 
            font-size: 7px; 
            border-top: 1px solid #333; 
            padding-top: 8px; 
        }
        
        .summary-info {
            margin: 10px 0;
            padding: 8px;
            background-color: #f5f5f5;
            border-radius: 4px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $settings->company_name ?? 'Summit Financial Advisory' }}</div>
        <div class="report-title">RIPOTI YA MWISHO WA MWAKA</div>
        <div class="period">{{ $period_display }}</div>
        <div>Imetengenezwa: {{ $generated_at->format('M d, Y H:i') }}</div>
    </div>

    <div class="summary-info">
        <strong>Taarifa za Jumla:</strong><br>
        Jumla ya Wafanyakazi: {{ $totals['employee_count'] }}<br>
        Jumla ya Miezi Iliyofanya Kazi: {{ $totals['total_months_worked'] }}<br>
        Jumla ya Mshahara wa Msingi: TZS {{ number_format($totals['total_basic_salary'], 0) }}<br>
        Jumla ya Mshahara Jumla: TZS {{ number_format($totals['total_gross_salary'], 0) }}
    </div>

    <!-- Employee Contributions Section -->
    <table class="summary-table">
        <thead>
            <tr class="section-header">
                <th colspan="15">MICHANGO YA WAFANYAKAZI - MWAKA WA {{ $period }}</th>
            </tr>
            <tr>
                <th>NA.</th>
                <th>JINA LA MFANYAKAZI</th>
                <th>CHEO</th>
                <th>NSSF NO.</th>
                <th>TIN NO.</th>
                <th>MIEZI</th>
                <th>MSHAHARA MSINGI</th>
                <th>ALLOWANCE</th>
                <th>MSHAHARA JUMLA</th>
                <th>NSSF</th>
                <th>NHIF</th>
                <th>PAYEE</th>
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
                <td class="text-center">{{ $employee['nssf_number'] }}</td>
                <td class="text-center">{{ $employee['tin_number'] }}</td>
                <td class="text-center">{{ $employee['months_worked'] }}</td>
                <td class="text-right">{{ number_format($employee['total_basic_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_allowances'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_gross_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_nssf'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_nhif'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_payee'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_other_deductions'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_net_salary'], 0) }}</td>
                <td class="text-center">{{ $employee['account'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">JUMLA:</td>
                <td class="text-center">{{ $totals['total_months_worked'] }}</td>
                <td class="text-right">{{ number_format($totals['total_basic_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_allowances'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_gross_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_nssf'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_nhif'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_payee'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_other_deductions'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_net_salary'], 0) }}</td>
                <td class="text-center">{{ $totals['employee_count'] }} Wafanyakazi</td>
            </tr>
        </tfoot>
    </table>

    <!-- Employer Contributions Section -->
    <table class="summary-table">
        <thead>
            <tr class="section-header">
                <th colspan="10">MICHANGO YA MWAJIRI - MWAKA WA {{ $period }}</th>
            </tr>
            <tr>
                <th>NA.</th>
                <th>JINA LA MFANYAKAZI</th>
                <th>NSSF NO.</th>
                <th>MSHAHARA JUMLA</th>
                <th>NSSF (MWAJIRI)</th>
                <th>WCF (MWAJIRI)</th>
                <th>SDL (MWAJIRI)</th>
                <th>JUMLA MWAJIRI</th>
                <th>JUMLA WOTE</th>
                <th>AKAUNTI</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
            <tr>
                <td class="text-center">{{ $employee['na'] }}</td>
                <td>{{ $employee['name'] }}</td>
                <td class="text-center">{{ $employee['nssf_number'] }}</td>
                <td class="text-right">{{ number_format($employee['total_gross_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_employer_nssf'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_employer_wcf'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_employer_sdl'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_employer_contributions'], 0) }}</td>
                <td class="text-right">{{ number_format($employee['total_employer_contributions'] + $employee['total_nssf'] + $employee['total_nhif'] + $employee['total_payee'], 0) }}</td>
                <td class="text-center">{{ $employee['account'] }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right">JUMLA MWAJIRI:</td>
                <td class="text-right">{{ number_format($totals['total_gross_salary'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_employer_nssf'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_employer_wcf'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_employer_sdl'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_employer_contributions'], 0) }}</td>
                <td class="text-right">{{ number_format($totals['total_employer_contributions'] + $totals['total_nssf'] + $totals['total_nhif'] + $totals['total_payee'], 0) }}</td>
                <td class="text-center">{{ $totals['employee_count'] }} Wafanyakazi</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ $settings->company_name ?? 'Kampuni' }} - Mfumo wa Usimamizi wa Malipo | 
        Ilitengenezwa tarehe {{ $generated_at->format('Y-m-d H:i:s') }} | 
        Ukurasa 1 wa 1
    </div>
</body>
</html>