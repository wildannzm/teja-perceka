<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = Role::firstOrCreate(['name' => 'super_admin']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@tejaperceka.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );

        if (! $admin->hasRole('super_admin')) {
            $admin->assignRole($role);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
