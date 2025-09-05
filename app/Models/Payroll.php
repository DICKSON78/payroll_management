<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payroll_id',
        'employee_id',
        'period',
        'base_salary',
        'allowances',
        'deductions',
        'tax_amount',
        'total_amount',
        'status',
        'payment_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'period' => 'date:Y-m',
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    /**
     * Get the employee associated with the payroll.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Generate a unique payroll ID.
     *
     * @return string
     */
    public static function generatePayrollId()
    {
        $latestPayroll = self::latest('id')->first();
        $number = $latestPayroll ? (int) str_replace('PAY', '', $latestPayroll->payroll_id) + 1 : 1;
        return 'PAY' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate tax amount based on settings.
     *
     * @param float $baseSalary
     * @param float $allowances
     * @param float|null $taxRate
     * @return float
     */
    public static function calculateTax($baseSalary, $allowances, $taxRate = null)
    {
        $settings = Setting::first() ?? (object) ['tax_rate' => 30.00];
        $taxRate = $taxRate ?? $settings->tax_rate;
        $taxableIncome = $baseSalary + $allowances;
        return round($taxableIncome * ($taxRate / 100), 2);
    }


    public static function calculateTotalAmount($baseSalary, $allowances, $deductions, $taxAmount)
    {
        return round($baseSalary + $allowances - $deductions - $taxAmount, 2);
    }

    /**
     * Process payroll for an employee.
     *
     * @param array $data
     * @return Payroll
     */
    public static function processPayroll($data)
    {
        $taxAmount = self::calculateTax($data['base_salary'], $data['allowances']);
        $totalAmount = self::calculateTotalAmount(
            $data['base_salary'],
            $data['allowances'],
            $data['deductions'] ?? 0,
            $taxAmount
        );

        return self::create([
            'payroll_id' => self::generatePayrollId(),
            'employee_id' => $data['employee_id'],
            'period' => $data['period'],
            'base_salary' => $data['base_salary'],
            'allowances' => $data['allowances'] ?? 0,
            'deductions' => $data['deductions'] ?? 0,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'status' => $data['status'] ?? 'Pending',
            'payment_date' => $data['payment_date'] ?? null,
        ]);
    }
}