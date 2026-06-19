<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // is_admin は $fillable 外のため forceFill で明示的に付与する。
        $admin->forceFill(['is_admin' => true])->save();
    }
}
