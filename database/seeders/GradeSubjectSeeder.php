<?php

namespace Database\Seeders;

use App\Models\BookName;
use App\Models\Category;
use App\Models\Grade;
use App\Support\GradeSubjectMap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Grade ↔ Subject ↔ Category (3-way):
 * ပြဋ္ဌာန်းစာအုပ် / ဆရာကိုင် / ဆရာလမ်းညွှန်
 */
class GradeSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $textbook = Category::where('slug', Category::TEXTBOOK)->firstOrFail();
        $handbook = Category::where('slug', Category::TEACHER_HANDBOOK)->firstOrFail();
        $guide = Category::where('slug', Category::TEACHER_GUIDE)->firstOrFail();

        // Rebuild links cleanly for the 3 categories
        DB::table('grade_book_names')->delete();

        foreach (GradeSubjectMap::textbookMap() as $gradeName => $subjects) {
            $this->attach($gradeName, $subjects, $textbook->id);
        }

        foreach (GradeSubjectMap::teacherHandbookMap() as $gradeName => $subjects) {
            $this->attach($gradeName, $subjects, $handbook->id);
        }

        foreach (GradeSubjectMap::teacherGuideMap() as $gradeName => $subjects) {
            $this->attach($gradeName, $subjects, $guide->id);
        }
    }

    private function attach(string $gradeName, array $subjects, int $categoryId): void
    {
        $grade = Grade::firstOrCreate(
            ['name' => $gradeName],
            ['is_active' => true]
        );

        foreach ($subjects as $subjectName) {
            $subjectName = trim($subjectName);
            if ($subjectName === '') {
                continue;
            }

            $book = BookName::firstOrCreate(
                ['name' => $subjectName],
                ['is_active' => true]
            );

            DB::table('grade_book_names')->insertOrIgnore([
                'grade_id' => $grade->id,
                'book_name_id' => $book->id,
                'category_id' => $categoryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
