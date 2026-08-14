<?php

use App\Support\TownshipKeys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $townshipSlugByName = [];

    public function up(): void
    {
        Schema::dropIfExists('teacher_guide_township_allocations');
        Schema::dropIfExists('allocation_plan_townships');

        Schema::create('teacher_guide_township_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_guide_id')
                ->constrained('teacher_guides')
                ->cascadeOnDelete();
            $table->foreignId('township_id')
                ->constrained('townships')
                ->cascadeOnDelete();
            $table->unsignedInteger('kg_g12_qty')->default(0);
            $table->unsignedInteger('g1_g5_qty')->default(0);
            $table->timestamps();

            $table->unique(['teacher_guide_id', 'township_id'], 'tg_town_alloc_unique');
        });

        Schema::create('allocation_plan_townships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allocation_plan_id')
                ->constrained('allocation_plans')
                ->cascadeOnDelete();
            $table->foreignId('township_id')
                ->constrained('townships')
                ->cascadeOnDelete();
            $table->integer('previous')->default(0);
            $table->integer('total_students')->default(0);
            $table->integer('transferable')->default(0);
            $table->timestamps();

            $table->unique(['allocation_plan_id', 'township_id'], 'ap_town_unique');
        });

        $this->loadTownshipIds();
        $this->copyTeacherGuideTownshipData();
        $this->copyAllocationPlanTownshipData();

        Schema::table('teacher_guides', function (Blueprint $table) {
            $table->dropColumn([
                'kg_g12_myanaung_qty',
                'kg_g12_kyankhin_qty',
                'kg_g12_ingapu_qty',
                'g1_g5_myanaung_qty',
                'g1_g5_kyankhin_qty',
                'g1_g5_ingapu_qty',
            ]);
        });

        Schema::dropIfExists('allocation_plan_details');
    }

    public function down(): void
    {
        Schema::create('allocation_plan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('allocation_plan_id')
                ->constrained('allocation_plans')
                ->cascadeOnDelete();
            $table->integer('myanaung_students')->default(0);
            $table->integer('kyankhin_students')->default(0);
            $table->integer('ingapu_students')->default(0);
            $table->integer('myanaung_allocation')->default(0);
            $table->integer('kyankhin_allocation')->default(0);
            $table->integer('ingapu_allocation')->default(0);
            $table->integer('myanaung_package')->default(0);
            $table->integer('myanaung_loose')->default(0);
            $table->integer('kyankhin_package')->default(0);
            $table->integer('kyankhin_loose')->default(0);
            $table->integer('ingapu_package')->default(0);
            $table->integer('ingapu_loose')->default(0);
            $table->integer('myanaung_previous')->default(0);
            $table->integer('kyankhin_previous')->default(0);
            $table->integer('ingapu_previous')->default(0);
            $table->integer('myanaung_total_students')->default(0);
            $table->integer('kyankhin_total_students')->default(0);
            $table->integer('ingapu_total_students')->default(0);
            $table->integer('myanaung_transferable')->default(0);
            $table->integer('kyankhin_transferable')->default(0);
            $table->integer('ingapu_transferable')->default(0);
            $table->integer('myanaung_final')->default(0);
            $table->integer('kyankhin_final')->default(0);
            $table->integer('ingapu_final')->default(0);
            $table->integer('myanaung_difference')->default(0);
            $table->integer('kyankhin_difference')->default(0);
            $table->integer('ingapu_difference')->default(0);
            $table->integer('total_difference')->default(0);
            $table->timestamps();
        });

        Schema::table('teacher_guides', function (Blueprint $table) {
            $table->unsignedInteger('kg_g12_myanaung_qty')->nullable();
            $table->unsignedInteger('kg_g12_kyankhin_qty')->nullable();
            $table->unsignedInteger('kg_g12_ingapu_qty')->nullable();
            $table->unsignedInteger('g1_g5_myanaung_qty')->nullable();
            $table->unsignedInteger('g1_g5_kyankhin_qty')->nullable();
            $table->unsignedInteger('g1_g5_ingapu_qty')->nullable();
        });

        $this->loadTownshipIds();

        foreach (DB::table('allocation_plan_townships')
            ->select('allocation_plan_id')
            ->distinct()
            ->pluck('allocation_plan_id') as $planId) {
            DB::table('allocation_plan_details')->insert([
                'allocation_plan_id' => $planId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (DB::table('teacher_guide_township_allocations')->get() as $row) {
            $slug = $this->slugForTownshipId((int) $row->township_id);
            if (!$slug) {
                continue;
            }

            $guide = DB::table('teacher_guides')->where('id', $row->teacher_guide_id)->first();
            if (!$guide) {
                continue;
            }

            DB::table('teacher_guides')->where('id', $guide->id)->update([
                "kg_g12_{$slug}_qty" => (int) $row->kg_g12_qty,
                "g1_g5_{$slug}_qty" => (int) $row->g1_g5_qty,
            ]);
        }

        foreach (DB::table('allocation_plan_townships')->get() as $row) {
            $slug = $this->slugForTownshipId((int) $row->township_id);
            if (!$slug) {
                continue;
            }

            DB::table('allocation_plan_details')
                ->where('allocation_plan_id', $row->allocation_plan_id)
                ->update([
                    "{$slug}_previous" => (int) $row->previous,
                    "{$slug}_total_students" => (int) $row->total_students,
                    "{$slug}_transferable" => (int) $row->transferable,
                ]);
        }

        Schema::dropIfExists('allocation_plan_townships');
        Schema::dropIfExists('teacher_guide_township_allocations');
    }

    private function loadTownshipIds(): void
    {
        $this->townshipSlugByName = [];

        $rows = DB::table('townships')
            ->whereIn('name', TownshipKeys::names())
            ->get(['id', 'name']);

        foreach ($rows as $row) {
            $slug = TownshipKeys::nameToSlug($row->name);
            if ($slug) {
                $this->townshipSlugByName[$row->name] = $slug;
            }
        }
    }

    private function copyTeacherGuideTownshipData(): void
    {
        $townshipIds = DB::table('townships')
            ->whereIn('name', TownshipKeys::names())
            ->pluck('id', 'name');

        $now = now();

        foreach (DB::table('teacher_guides')->get() as $guide) {
            foreach (TownshipKeys::nameToSlugMap() as $name => $slug) {
                $townshipId = $townshipIds[$name] ?? null;
                if (!$townshipId) {
                    continue;
                }

                DB::table('teacher_guide_township_allocations')->insert([
                    'teacher_guide_id' => $guide->id,
                    'township_id' => $townshipId,
                    'kg_g12_qty' => (int) ($guide->{"kg_g12_{$slug}_qty"} ?? 0),
                    'g1_g5_qty' => (int) ($guide->{"g1_g5_{$slug}_qty"} ?? 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function copyAllocationPlanTownshipData(): void
    {
        $townshipIds = DB::table('townships')
            ->whereIn('name', TownshipKeys::names())
            ->pluck('id', 'name');

        $now = now();

        foreach (DB::table('allocation_plan_details')->get() as $detail) {
            foreach (TownshipKeys::nameToSlugMap() as $name => $slug) {
                $townshipId = $townshipIds[$name] ?? null;
                if (!$townshipId) {
                    continue;
                }

                DB::table('allocation_plan_townships')->insert([
                    'allocation_plan_id' => $detail->allocation_plan_id,
                    'township_id' => $townshipId,
                    'previous' => (int) ($detail->{"{$slug}_previous"} ?? 0),
                    'total_students' => (int) ($detail->{"{$slug}_total_students"} ?? 0),
                    'transferable' => (int) ($detail->{"{$slug}_transferable"} ?? 0),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    private function slugForTownshipId(int $townshipId): ?string
    {
        $name = DB::table('townships')->where('id', $townshipId)->value('name');

        return $name ? TownshipKeys::nameToSlug($name) : null;
    }
};
