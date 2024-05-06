<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $procedure = "
            CREATE PROCEDURE GetEmployeeByDepartment(IN _dep_name TEXT)
            BEGIN
                SELECT name, salary FROM employees WHERE department = _dep_name COLLATE utf8mb4_unicode_ci;
            END;
        ";
        DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS GetEmployeeByDepartment;');
    }
};
