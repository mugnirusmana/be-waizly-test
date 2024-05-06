<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use Carbon\Carbon;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => 'John Smith',
                'job_title' => 'Manager',
                'salary' => 60000,
                'department' => 'Sales',
                'join_date' => Carbon::parse('2022-01-15'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'John Doe',
                'job_title' => 'Analyst',
                'salary' => 45000,
                'department' => 'Marketing',
                'join_date' => Carbon::parse('2022-02-01'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Mike Brown',
                'job_title' => 'Developer',
                'salary' => 55000,
                'department' => 'IT',
                'join_date' => Carbon::parse('2022-03-10'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Anna Lee',
                'job_title' => 'Manager',
                'salary' => 65000,
                'department' => 'Sales',
                'join_date' => Carbon::parse('2022-12-05'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Mark Wong',
                'job_title' => 'Developer',
                'salary' => 50000,
                'department' => 'IT',
                'join_date' => Carbon::parse('2023-05-20'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ],
            [
                'name' => 'Emely Chen',
                'job_title' => 'Analyst',
                'salary' => 48000,
                'department' => 'Marketing',
                'join_date' => Carbon::parse('2023-06-02'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]
        ];
        Employee::truncate();
        Employee::insert($data);
    }
}
