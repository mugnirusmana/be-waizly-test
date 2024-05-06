<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'amount'
    ];

    public function employee() {
        return $this->hasOne('App\Models\Employee', 'employee_id', 'id');
    }
}
