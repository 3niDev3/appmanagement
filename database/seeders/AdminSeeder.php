<?php 
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run()
    {
        Admin::updateOrCreate(
            ['email' => 'appmanagement@yopmail.com'], 
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'is_active' => 1,
                'email_verified_at' => now(),
            ]
        );
    }
}

?>