<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quota_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quota_id')->constrained('quotas')->cascadeOnDelete();
            $table->string('school_level', 20); // primary|middle|high|agriculture
            $table->string('ownership', 20)->default(''); // public|monk|private|'' for agriculture
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();

            $table->unique(['quota_id', 'school_level', 'ownership'], 'quota_lines_unique');
        });

        $quotas = DB::table('quotas')->get();
        $now = now();
        $rows = [];

        $map = [
            ['primary', 'public', 'primary_public'],
            ['primary', 'monk', 'primary_monk'],
            ['primary', 'private', 'primary_private'],
            ['middle', 'public', 'middle_public'],
            ['middle', 'monk', 'middle_monk'],
            ['middle', 'private', 'middle_private'],
            ['high', 'public', 'high_public'],
            ['high', 'monk', 'high_monk'],
            ['high', 'private', 'high_private'],
        ];

        foreach ($quotas as $quota) {
            foreach ($map as [$level, $ownership, $col]) {
                $rows[] = [
                    'quota_id' => $quota->id,
                    'school_level' => $level,
                    'ownership' => $ownership,
                    'quantity' => (int) ($quota->{$col} ?? 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $rows[] = [
                'quota_id' => $quota->id,
                'school_level' => 'agriculture',
                'ownership' => '',
                'quantity' => (int) ($quota->agriculture ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('quota_lines')->insert($chunk);
        }

        Schema::table('quotas', function (Blueprint $table) {
            $cols = [
                'primary_public', 'primary_monk', 'primary_private',
                'middle_public', 'middle_monk', 'middle_private',
                'high_public', 'high_monk', 'high_private',
                'agriculture',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('quotas', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            $table->integer('primary_public')->default(0);
            $table->integer('primary_monk')->default(0);
            $table->integer('primary_private')->default(0);
            $table->integer('middle_public')->default(0);
            $table->integer('middle_monk')->default(0);
            $table->integer('middle_private')->default(0);
            $table->integer('high_public')->default(0);
            $table->integer('high_monk')->default(0);
            $table->integer('high_private')->default(0);
            $table->integer('agriculture')->default(0);
        });

        foreach (DB::table('quotas')->pluck('id') as $quotaId) {
            $lines = DB::table('quota_lines')->where('quota_id', $quotaId)->get();
            $data = [
                'primary_public' => 0, 'primary_monk' => 0, 'primary_private' => 0,
                'middle_public' => 0, 'middle_monk' => 0, 'middle_private' => 0,
                'high_public' => 0, 'high_monk' => 0, 'high_private' => 0,
                'agriculture' => 0,
            ];
            foreach ($lines as $line) {
                if ($line->school_level === 'agriculture') {
                    $data['agriculture'] = (int) $line->quantity;
                    continue;
                }
                $key = $line->school_level . '_' . $line->ownership;
                if (array_key_exists($key, $data)) {
                    $data[$key] = (int) $line->quantity;
                }
            }
            DB::table('quotas')->where('id', $quotaId)->update($data);
        }

        Schema::dropIfExists('quota_lines');
    }
};
