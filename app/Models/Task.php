<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Mavinoo\Batch\Traits\HasBatch;

class Task extends Model
{
    use HasFactory, HasBatch;

    public static $todo = '1';
    public static $complete = '2';

    protected $fillable = [
        'name',
        'status',
        'sort_number',
        'user_id',
        'desc'
    ];
}
