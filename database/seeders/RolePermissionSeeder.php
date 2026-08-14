<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Migration already seeds roles/permissions; this seeder is idempotent for fresh runs.
        $super = Role::firstOrCreate(
            ['slug' => 'super'],
            ['name' => 'Super Admin', 'description' => 'စနစ်ပိုင်ရှင် — user / delete / rollover']
        );
        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin', 'description' => 'ခရိုင်စာရေး — နေ့စဉ် create / edit']
        );

        $defs = [
            ['name' => 'ကြည့်ရှုရန်', 'slug' => 'records.view', 'group' => 'records'],
            ['name' => 'ဖန်တီး / ပြင်ဆင်ရန်', 'slug' => 'records.manage', 'group' => 'records'],
            ['name' => 'ဖျက်ရန်', 'slug' => 'records.delete', 'group' => 'records'],
            ['name' => 'အသုံးပြုသူ စီမံရန်', 'slug' => 'users.manage', 'group' => 'users'],
            ['name' => 'ပညာသင်နှစ် Rollover', 'slug' => 'academic_years.rollover', 'group' => 'academic_years'],
        ];

        $ids = [];
        foreach ($defs as $def) {
            $ids[$def['slug']] = Permission::firstOrCreate(
                ['slug' => $def['slug']],
                $def
            )->id;
        }

        $super->permissions()->sync(array_values($ids));
        $admin->permissions()->sync([
            $ids['records.view'],
            $ids['records.manage'],
        ]);
    }
}
