<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Employee extends Model implements AuthenticatableContract
{
    use Authenticatable, SoftDeletes, Notifiable;

    // ADD THESE 3 LINES - SET employee_id AS PRIMARY KEY
    protected $primaryKey = 'employee_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
        'hire_date' => 'date',
        'contract_end_date' => 'date',
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'last_login_at' => 'datetime',
        'last_logout_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'auto_logout' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'password',
        'remember_token',
        'email_verified_at',
        'department',
        'role',
        'position',
        'base_salary',
        'allowances',
        'deductions',
        'status',
        'gender',
        'dob',
        'nationality',
        'phone',
        'address',
        'hire_date',
        'contract_end_date',
        'bank_name',
        'account_number',
        'employment_type',
        'nssf_number',
        'nhif_number',
        'tin_number',
        'last_login_at',
        'last_login_ip',
        'last_logout_at',
        'auto_logout',
        'password_changed_at',
    ];

    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'employee_id';
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string|null
     */
    public function getRememberToken()
    {
        return $this->remember_token;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->remember_token = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    /**
     * Get the e-mail address where password reset links are sent.
     *
     * @return string
     */
    public function getEmailForPasswordReset()
    {
        return $this->email;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // You can implement email sending here if needed
        // For now, we'll just log it
        \Log::info("Password reset requested for {$this->email}. Token: {$token}");
    }

    // FIXED: Corrected allowances relationship
    public function allowances()
    {
        return $this->belongsToMany(
            Allowance::class,
            'employee_allowance',
            'employee_id',
            'allowance_id'
        )->withPivot('amount')
         ->withTimestamps();
    }

    public function departmentRel()
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_name', 'name');
    }

    // FIXED: Corrected deductions relationship
    public function deductions()
    {
        return $this->belongsToMany(
            Deduction::class,
            'employee_deduction',
            'employee_id',
            'deduction_id'
        )->withPivot('amount')
         ->wherePivot('active', 1) // Remove this if you don't have active column in pivot
         ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'employee_id');
    }

    public function complianceTasks()
    {
        return $this->hasMany(ComplianceTask::class, 'employee_id', 'employee_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'employee_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'employee_id', 'employee_id');
    }

    public function payrollAlerts()
    {
        return $this->hasMany(PayrollAlert::class, 'employee_id', 'employee_id');
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'employee_id', 'employee_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'employee_id', 'employee_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'generated_by', 'employee_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id', 'employee_id');
    }

    public function updatedSettings()
    {
        return $this->hasMany(Setting::class, 'updated_by', 'employee_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return strtolower($this->role) === strtolower($role);
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles)
    {
        return in_array(strtolower($this->role), array_map('strtolower', $roles));
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is HR
     */
    public function isHR()
    {
        return $this->hasRole('hr');
    }

    /**
     * Check if user is manager
     */
    public function isManager()
    {
        return $this->hasRole('manager');
    }

    /**
     * Check if user is employee
     */
    public function isEmployee()
    {
        return $this->hasRole('employee');
    }

    /**
     * Check if user account is active
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Update last login information
     */
    public function updateLastLogin($ipAddress = null)
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress
        ]);
    }

    /**
     * Update last logout information
     */
    public function updateLastLogout($autoLogout = false)
    {
        $this->update([
            'last_logout_at' => now(),
            'auto_logout' => $autoLogout
        ]);
    }

    /**
     * Update password change timestamp
     */
    public function updatePasswordChangedAt()
    {
        $this->update([
            'password_changed_at' => now()
        ]);
    }

    /**
     * Scope for active employees
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for employees by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope for employees by department
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Get formatted salary
     */
    public function getFormattedSalaryAttribute()
    {
        return 'TZS ' . number_format($this->base_salary, 2);
    }

    /**
     * Get formatted net salary
     */
    public function getFormattedNetSalaryAttribute()
    {
        $netSalary = $this->base_salary + ($this->allowances ?? 0) - ($this->deductions ?? 0);
        return 'TZS ' . number_format($netSalary, 2);
    }

    /**
     * Get employee's full profile
     */
    public function getProfileAttribute()
    {
        return [
            'employee_id' => $this->employee_id,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'position' => $this->position,
            'role' => $this->role,
            'status' => $this->status,
            'salary' => $this->formatted_salary,
            'net_salary' => $this->formatted_net_salary,
            'employment_type' => $this->employment_type,
            'hire_date' => $this->hire_date?->format('Y-m-d'),
            'last_login' => $this->last_login_at?->format('Y-m-d H:i:s'),
        ];
    }

    // In Employee model
public function updateSalaryData($adjustmentType, $amount)
{
    switch ($adjustmentType) {
        case 'salary_adjustment':
            $this->base_salary += $amount;
            break;
        case 'allowance':
            $this->allowances += $amount;
            break;
        case 'deduction':
            $this->deductions += $amount;
            break;
    }
    
    return $this->save();
}


}