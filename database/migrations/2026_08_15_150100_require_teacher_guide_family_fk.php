<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Ensure teacher_guide_issues / summaries reference teacher_guides (normalized identity via FK).
 *
 * Must replace ON DELETE SET NULL before making teacher_guide_id NOT NULL (MySQL error 1830).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->linkOrphans('teacher_guide_issues');
        $this->linkOrphans('teacher_guide_summaries');

        DB::table('teacher_guide_issues')->whereNull('teacher_guide_id')->delete();
        DB::table('teacher_guide_summaries')->whereNull('teacher_guide_id')->delete();

        $this->requireTeacherGuideFk(
            'teacher_guide_issues',
            'teacher_guide_issues_teacher_guide_id_foreign'
        );
        $this->requireTeacherGuideFk(
            'teacher_guide_summaries',
            'teacher_guide_summaries_teacher_guide_id_foreign'
        );
    }

    public function down(): void
    {
        $this->restoreNullableSetNullFk(
            'teacher_guide_issues',
            'teacher_guide_issues_teacher_guide_id_foreign'
        );
        $this->restoreNullableSetNullFk(
            'teacher_guide_summaries',
            'teacher_guide_summaries_teacher_guide_id_foreign'
        );
    }

    private function requireTeacherGuideFk(string $table, string $fkName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $this->dropForeignIfExists($table, $fkName);

        DB::statement("ALTER TABLE `{$table}` MODIFY `teacher_guide_id` BIGINT UNSIGNED NOT NULL");

        DB::statement(
            "ALTER TABLE `{$table}`
             ADD CONSTRAINT `{$fkName}`
             FOREIGN KEY (`teacher_guide_id`) REFERENCES `teacher_guides` (`id`)
             ON DELETE CASCADE"
        );
    }

    private function restoreNullableSetNullFk(string $table, string $fkName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        $this->dropForeignIfExists($table, $fkName);

        DB::statement("ALTER TABLE `{$table}` MODIFY `teacher_guide_id` BIGINT UNSIGNED NULL");

        DB::statement(
            "ALTER TABLE `{$table}`
             ADD CONSTRAINT `{$fkName}`
             FOREIGN KEY (`teacher_guide_id`) REFERENCES `teacher_guides` (`id`)
             ON DELETE SET NULL"
        );
    }

    private function dropForeignIfExists(string $table, string $fkName): void
    {
        $exists = DB::selectOne(
            'SELECT CONSTRAINT_NAME
             FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND CONSTRAINT_TYPE = ?',
            [$table, $fkName, 'FOREIGN KEY']
        );

        if ($exists) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fkName}`");
        }
    }

    private function linkOrphans(string $table): void
    {
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

            DB::table($table)
                ->whereNull('teacher_guide_id')
                ->where($match)
                ->update(['teacher_guide_id' => $guide->id]);
        }
    }
};
