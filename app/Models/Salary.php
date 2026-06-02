<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Salary extends Model
{
    protected $table = 'PAYROLL.EMP_SAL_BREAKUP';
    protected $primaryKey = 'EMPCODE';
    public $incrementing = false;
    public $timestamps = false;

    public static function grossSalaryFor($empCode): ?float
    {
        $row = DB::selectOne(
            'SELECT GROSS_SAL FROM PAYROLL.EMP_SAL_BREAKUP WHERE EMPCODE = ?',
            [$empCode]
        );

        if (! $row) {
            return null;
        }

        $row = array_change_key_case((array) $row, CASE_LOWER);

        return isset($row['gross_sal']) ? (float) $row['gross_sal'] : null;
    }
}
