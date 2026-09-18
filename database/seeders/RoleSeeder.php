<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
        [
            'name' => 'Admin',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'Owner',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'Manager',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'name' => 'Seller',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
    }
}
