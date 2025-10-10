<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; 
use Carbon\Carbon; 

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'admin', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'user-ubate', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'user-silvania', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'user-cota', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
