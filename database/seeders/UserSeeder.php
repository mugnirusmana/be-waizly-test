<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = [
            "name" => "Ade Mugni Rusmana",
            "email" => "mugnirusmana95@gmail.com",
            "username" => "mugnirusmana95",
            "password" => Hash::make("password123"),
            "is_verified" => true
        ];

        $checkUser = User::where('email', $user['email'])->first();

        if (!$checkUser) User::create($user);
    }
}
