<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $dateFrom;
    protected $dateTo;

    public function __construct($dateFrom = null, $dateTo = null)
    {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function query()
    {
        $query = Attendance::with('employee')->orderBy('date', 'desc');

        if ($this->dateFrom) {
            $query->where('date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->where('date', '<=', $this->dateTo);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Employee ID',
            'Date',
            'Check In',
            'Check Out',
            'Hours Worked',
            'Status',
            'Notes'
        ];
    }

    public function map($attendance): array
    {
        return [
            $attendance->employee->name,
            $attendance->employee->employee_id,
            $attendance->date,
            $attendance->check_in,
            $attendance->check_out,
            $attendance->hours_worked,
            $attendance->status,
            $attendance->notes
        ];
    }
}
