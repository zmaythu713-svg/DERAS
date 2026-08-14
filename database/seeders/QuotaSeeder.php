<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Quota;
use App\Models\Township;
use Illuminate\Database\Seeder;

class QuotaSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('name', '2025-2026')->firstOrFail();

        $rows = [
            [
                'township' => 'မြန်အောင်',
                'primary_public' => 23368,
                'primary_monk' => 206,
                'primary_private' => 0,
                'middle_public' => 7261,
                'middle_monk' => 58,
                'middle_private' => 0,
                'high_public' => 3294,
                'high_monk' => 0,
                'high_private' => 0,
                'agriculture' => 0,
            ],
            [
                'township' => 'ကြံခင်း',
                'primary_public' => 8666,
                'primary_monk' => 122,
                'primary_private' => 0,
                'middle_public' => 3218,
                'middle_monk' => 0,
                'middle_private' => 0,
                'high_public' => 1526,
                'high_monk' => 0,
                'high_private' => 95,
                'agriculture' => 128,
            ],
            [
                'township' => 'အင်္ဂပူ',
                'primary_public' => 21680,
                'primary_monk' => 218,
                'primary_private' => 0,
                'middle_public' => 7155,
                'middle_monk' => 3,
                'middle_private' => 15,
                'high_public' => 3794,
                'high_monk' => 0,
                'high_private' => 24,
                'agriculture' => 0,
            ],
        ];

        foreach ($rows as $row) {
            $township = Township::where('name', $row['township'])->firstOrFail();
            unset($row['township']);

            $quota = Quota::firstOrNew([
                'academic_year_id' => $academicYear->id,
                'township_id' => $township->id,
            ]);
            $quota->save();
            $quota->syncLines($row);
        }
    }
}
