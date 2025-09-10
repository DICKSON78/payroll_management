<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Create Roles =====
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'HR Manager', 'slug' => 'hr'],
            ['name' => 'Employee', 'slug' => 'employee'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        $adminRole = Role::where('slug', 'admin')->first();

        // ===== Create Admin User =====
        $adminUser = User::where('email', 'admin@payroll.com')->first();
        if (!$adminUser) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@payroll.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
            ]);
        }

        // ===== Seed Employees =====
        $faker = \Faker\Factory::create();

        for ($i = 1; $i <= 10; $i++) {
            Employee::create([
                'employee_id' => 'SN' . $faker->unique()->numberBetween(1000, 9999),
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'department' => $faker->randomElement(['HR', 'Finance', 'IT', 'Operations']),
                'position' => $faker->jobTitle,
                'status' => $faker->randomElement(['Active', 'Inactive']),
                'gender' => $faker->randomElement(['Male', 'Female']),
                'dob' => $faker->date('Y-m-d', '2000-01-01'),
                'nationality' => $faker->country,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'hire_date' => $faker->date('Y-m-d', 'now'),
                'base_salary' => $faker->numberBetween(2000, 7000),
                'allowances' => $faker->numberBetween(100, 1000),
                'deductions' => $faker->numberBetween(0, 500),
                'bank_name' => $faker->company,
                'account_number' => $faker->bankAccountNumber,
                'employment_type' => $faker->randomElement(['Full-Time', 'Part-Time']),
            ]);
        }
    }
}
