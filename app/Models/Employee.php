<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'job_title',
        'salary',
        'department',
        'join_date'
    ];

    public function sales() {
        return $this->hasMany('App\Models\Sales', 'employee_id', 'id');
    }
}
