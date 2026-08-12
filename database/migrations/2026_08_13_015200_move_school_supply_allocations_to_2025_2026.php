<?php

use App\Models\AcademicYear;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $currentId = AcademicYear::where('name', '2025-2026')->value('id');
        $oldId = AcademicYear::where('name', '2024-2025')->value('id');

        if (!$currentId || !$oldId || (int) $currentId === (int) $oldId) {
            return;
        }

        DB::table('school_supply_allocations')
            ->where('academic_year_id', $oldId)
            ->update(['academic_year_id' => $currentId]);
    }

    public function down(): void
    {
        $currentId = AcademicYear::where('name', '2025-2026')->value('id');
        $oldId = AcademicYear::where('name', '2024-2025')->value('id');

        if (!$currentId || !$oldId || (int) $currentId === (int) $oldId) {
            return;
        }

        DB::table('school_supply_allocations')
            ->where('academic_year_id', $currentId)
            ->update(['academic_year_id' => $oldId]);
    }
};
