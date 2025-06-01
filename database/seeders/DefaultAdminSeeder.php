<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Juma Kassim',
            'email' => 'jumakassim89@gmail.com',
            'email_verified_at' => Carbon::now(),
            'phone' => '255714257454',
            'sex' => 'male',
            'role' => 'super_admin',
            'password' => Hash::make('Sup3rP4ssw0rd')
        ]);
    }
}
