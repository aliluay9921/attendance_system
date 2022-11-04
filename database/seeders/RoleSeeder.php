<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            "role" => "غائب",
            "value" => 20
        ]);
        // Role::create([
        //     "role" => "مكافئة",
        //     "value" => 20
        // ]);
        Role::create([
            "role" => "تأخير",
            "value" => 10
        ]);
    }
}