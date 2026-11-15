<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('role', 'admin')->exists()) {
            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(14, symbols: false);

        User::create([
            'name' => env('ADMIN_NAME', 'مدير الموقع'),
            'email' => env('ADMIN_EMAIL', 'admin@berrybear.sa'),
            'password' => $password,
            'role' => 'admin',
            'is_active' => true,
        ]);

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn('Admin created: '.env('ADMIN_EMAIL', 'admin@berrybear.sa').' / '.$password.'  (change it after first login)');
        }
    }
}
