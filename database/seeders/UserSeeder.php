<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            "user_name" => 'admin_1234',
            "full_name" => 'الأدارة',
            "password" => bcrypt("admin_@1234#"),
            "user_type" => 0,
        ]);
        User::create([
            "user_name" => 'user_1',
            "full_name" => 'علي لؤي خلف',
            "password" => bcrypt("user_@1#"),
            "user_type" => 1,
            "salary" => 500000,
            "start_attendance" => "8:00 AM",
            "leave_attendance" => "3:00 PM",
            "reward" => "50"
        ]);
    }
}