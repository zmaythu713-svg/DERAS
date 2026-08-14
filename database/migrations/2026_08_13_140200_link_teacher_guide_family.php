<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_guide_issues', function (Blueprint $table) {
            $table->foreignId('teacher_guide_id')
                ->nullable()
                ->after('id')
                ->constrained('teacher_guides')
                ->nullOnDelete();
        });

        Schema::table('teacher_guide_summaries', function (Blueprint $table) {
            $table->foreignId('teacher_guide_id')
                ->nullable()
                ->after('id')
                ->constrained('teacher_guides')
                ->nullOnDelete();
        });

        $guides = DB::table('teacher_guides')->get();
        foreach ($guides as $guide) {
            $match = [
                'academic_year_id' => $guide->academic_year_id,
                'grade_id' => $guide->grade_id,
                'book_name_id' => $guide->book_name_id,
                'group_no' => $guide->group_no,
                'guide_type' => $guide->guide_type,
                'sequence_no' => $guide->sequence_no,
            ];

            DB::table('teacher_guide_issues')->where($match)->update([
                'teacher_guide_id' => $guide->id,
            ]);
            DB::table('teacher_guide_summaries')->where($match)->update([
                'teacher_guide_id' => $guide->id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('teacher_guide_issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_guide_id');
        });
        Schema::table('teacher_guide_summaries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('teacher_guide_id');
        });
    }
};
