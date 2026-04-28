<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => config('services.admin.email')],
            [
                'name' => 'PESO Admin',
                'password' => Hash::make('P3$0!'),
                'is_admin' => true,
            ]
        );
    }
}
