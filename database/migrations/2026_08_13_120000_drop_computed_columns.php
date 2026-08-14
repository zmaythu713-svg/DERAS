<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            $table->dropColumn([
                'primary_total',
                'middle_total',
                'high_total',
                'grand_public',
                'grand_monk',
                'grand_private',
                'grand_total',
                'total_with_agriculture',
                'distribution_total',
            ]);
        });

        Schema::table('teacher_guides', function (Blueprint $table) {
            $table->dropColumn([
                'total_quota',
                'total_myanaung_qty',
                'total_kyankhin_qty',
                'total_ingapu_qty',
                'distributed_total',
                'remaining_total',
            ]);
        });

        Schema::table('allocation_plans', function (Blueprint $table) {
            $table->dropColumn([
                'ratio',
                'eligible_students_total',
                'allocated_books_total',
                'student_count_total',
                'transferable_books_total',
                'available_total',
                'surplus_shortage_total',
            ]);
        });

        Schema::table('allocation_plan_details', function (Blueprint $table) {
            $table->dropColumn([
                'myanaung_students',
                'kyankhin_students',
                'ingapu_students',
                'myanaung_allocation',
                'kyankhin_allocation',
                'ingapu_allocation',
                'myanaung_package',
                'myanaung_loose',
                'kyankhin_package',
                'kyankhin_loose',
                'ingapu_package',
                'ingapu_loose',
                'myanaung_final',
                'kyankhin_final',
                'ingapu_final',
                'myanaung_difference',
                'kyankhin_difference',
                'ingapu_difference',
                'total_difference',
            ]);
        });

        Schema::table('teacher_guide_summaries', function (Blueprint $table) {
            $table->dropColumn(['total_books', 'remaining_books']);
        });

        Schema::table('teacher_guide_issue_townships', function (Blueprint $table) {
            $table->dropColumn(['full_package_count', 'loose_book_count']);
        });
    }

    public function down(): void
    {
        Schema::table('quotas', function (Blueprint $table) {
            $table->integer('primary_total')->default(0);
            $table->integer('middle_total')->default(0);
            $table->integer('high_total')->default(0);
            $table->integer('grand_public')->default(0);
            $table->integer('grand_monk')->default(0);
            $table->integer('grand_private')->default(0);
            $table->integer('grand_total')->default(0);
            $table->integer('total_with_agriculture')->default(0);
            $table->integer('distribution_total')->default(0);
        });

        Schema::table('teacher_guides', function (Blueprint $table) {
            $table->integer('total_quota')->default(0);
            $table->unsignedInteger('total_myanaung_qty')->nullable();
            $table->unsignedInteger('total_kyankhin_qty')->nullable();
            $table->unsignedInteger('total_ingapu_qty')->nullable();
            $table->unsignedInteger('distributed_total')->nullable();
            $table->integer('remaining_total')->nullable();
        });

        Schema::table('allocation_plans', function (Blueprint $table) {
            $table->decimal('ratio', 12, 4)->default(0);
            $table->integer('eligible_students_total')->default(0);
            $table->integer('allocated_books_total')->default(0);
            $table->integer('student_count_total')->default(0);
            $table->integer('transferable_books_total')->default(0);
            $table->integer('available_total')->default(0);
            $table->integer('surplus_shortage_total')->default(0);
        });

        Schema::table('allocation_plan_details', function (Blueprint $table) {
            foreach ([
                'myanaung_students',
                'kyankhin_students',
                'ingapu_students',
                'myanaung_allocation',
                'kyankhin_allocation',
                'ingapu_allocation',
                'myanaung_package',
                'myanaung_loose',
                'kyankhin_package',
                'kyankhin_loose',
                'ingapu_package',
                'ingapu_loose',
                'myanaung_final',
                'kyankhin_final',
                'ingapu_final',
                'myanaung_difference',
                'kyankhin_difference',
                'ingapu_difference',
                'total_difference',
            ] as $column) {
                $table->integer($column)->default(0);
            }
        });

        Schema::table('teacher_guide_summaries', function (Blueprint $table) {
            $table->integer('total_books')->nullable();
            $table->integer('remaining_books')->nullable();
        });

        Schema::table('teacher_guide_issue_townships', function (Blueprint $table) {
            $table->unsignedInteger('full_package_count')->default(0);
            $table->unsignedInteger('loose_book_count')->default(0);
        });
    }
};
