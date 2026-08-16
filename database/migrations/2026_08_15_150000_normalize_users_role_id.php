<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')
                    ->nullable()
                    ->after('email')
                    ->constrained('roles')
                    ->nullOnDelete();
            }
        });

        $roleIds = DB::table('roles')->pluck('id', 'slug');

        foreach (DB::table('users')->select('id', 'role')->get() as $user) {
            $slug = $user->role ?: 'admin';
            $roleId = $roleIds[$slug] ?? $roleIds['admin'] ?? null;
            if ($roleId) {
                DB::table('users')->where('id', $user->id)->update(['role_id' => $roleId]);
            }
        }

        // Keep legacy `role` slug in sync for existing auth checks; role_id is the normalized FK.
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role_id')) {
                $table->dropConstrainedForeignId('role_id');
            }
        });
    }
};
