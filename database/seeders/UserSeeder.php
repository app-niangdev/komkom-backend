<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        User::create([
            'first_name' => 'Ibrahima',
            'last_name' => 'NIANG',
            'email' => 'niangdev031299@gmail.com',
            'status' => true,
            'role_id' => $adminRole->id,
            'email_verified_at'=> Carbon::now(),
            'type'=>'admin',
            'password' => Hash::make('password'),
            'phone_number_one'=>'771234567',
            'phone_number_two'=>'771234568',
            'address'=>'Bargny',
            'gender'=>'male',
            'active'=>true,
        ]);
    }
}
