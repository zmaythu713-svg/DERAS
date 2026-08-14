<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('group')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
        });

        $now = now();

        $roleIds = [];
        foreach ([
            ['name' => 'Super Admin', 'slug' => 'super', 'description' => 'စနစ်ပိုင်ရှင် — user / delete / rollover'],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'ခရိုင်စာရေး — နေ့စဉ် create / edit'],
        ] as $role) {
            $roleIds[$role['slug']] = DB::table('roles')->insertGetId($role + [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissionDefs = [
            ['name' => 'ကြည့်ရှုရန်', 'slug' => 'records.view', 'group' => 'records'],
            ['name' => 'ဖန်တီး / ပြင်ဆင်ရန်', 'slug' => 'records.manage', 'group' => 'records'],
            ['name' => 'ဖျက်ရန်', 'slug' => 'records.delete', 'group' => 'records'],
            ['name' => 'အသုံးပြုသူ စီမံရန်', 'slug' => 'users.manage', 'group' => 'users'],
            ['name' => 'ပညာသင်နှစ် Rollover', 'slug' => 'academic_years.rollover', 'group' => 'academic_years'],
        ];

        $permissionIds = [];
        foreach ($permissionDefs as $perm) {
            $permissionIds[$perm['slug']] = DB::table('permissions')->insertGetId($perm + [
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Super: all permissions
        foreach ($permissionIds as $permId) {
            DB::table('role_permission')->insert([
                'role_id' => $roleIds['super'],
                'permission_id' => $permId,
            ]);
        }

        // Admin: view + manage only
        foreach (['records.view', 'records.manage'] as $slug) {
            DB::table('role_permission')->insert([
                'role_id' => $roleIds['admin'],
                'permission_id' => $permissionIds[$slug],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
