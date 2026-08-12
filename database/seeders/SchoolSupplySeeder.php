<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Grade;
use App\Models\SchoolSupplyAllocation;
use App\Models\SchoolSupplyItem;
use App\Models\Township;
use Illuminate\Database\Seeder;

class SchoolSupplySeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::firstOrCreate(
            ['name' => '2025-2026'],
            [
                'is_active' => true,
                'is_current' => true,
                'start_year' => 2025,
                'end_year' => 2026,
                'status' => AcademicYear::STATUS_ACTIVE,
            ]
        );

        // Move any old 2024-2025 school-supply rows onto the current year.
        $oldYearId = AcademicYear::where('name', '2024-2025')->value('id');
        if ($oldYearId && (int) $oldYearId !== (int) $year->id) {
            SchoolSupplyAllocation::where('academic_year_id', $oldYearId)
                ->update(['academic_year_id' => $year->id]);
        }

        $grades = [
            'Grade-1' => [
                'rates' => [
                    'ခုန်ကြိုး (အတို)' => 2,
                    '(၁၂)ရောင်ပါ ရောင်စုံဖယောင်းခဲတံဘူး (Crayon)' => 5,
                    'ရောင်စုံစက္ကူ A4 (၂၀၀)ရွက်ပါ (၁)ထုပ်' => 1,
                    'ကတ်ကြေးအသေး' => 5,
                    'ရောင်စုံခဲတံဘူး (ချွန်စက်ပါ)' => 5,
                    'စည်းနှင့်ဝါး' => 3,
                    'ပလွေ (၆)ပေါက်ပါ' => 5,
                    'ဆေးရောင်ခြယ်ရုပ်ပုံစာအုပ် A4 (၃၀၀)ရွက်ပါ (၁)ထုပ်' => 1,
                    '(၁၂)ရောင်ပါ ရေဆေးဘူး (စုတ်တံပါ)' => 5,
                    'ပန်းချီဆွဲစုတ်တံ (၅)ချောင်းပါကတ်' => 3,
                ],
                'rows' => [
                    ['township', 'ဧရာဝတီ', 'မြန်အောင်', 272, [544, 1360, 272, 1360, 1360, 816, 1360, 272, 1360, 816]],
                    ['township', 'ဧရာဝတီ', 'ကြံခင်း', 124, [248, 620, 124, 620, 620, 372, 620, 124, 620, 372]],
                    ['township', 'ဧရာဝတီ', 'အင်္ဂပူ', 279, [558, 1395, 279, 1395, 1395, 837, 1395, 279, 1395, 837]],
                    ['township', 'ဧရာဝတီ', 'ခရိုင်အားလုံးစုစုပေါင်း', 675, [1350, 3375, 675, 3375, 3375, 2025, 3375, 675, 3375, 2025]],
                    // ['box', null, 'ပုံး', 0, [6, 11, 25, 4, 14, 10, 16, 27, 23, 4]],
                    // ['loose', null, 'အပြေ', 0, [150, 207, 25, 495, 15, 25, 175, 0, 63, 25]],
                ],
            ],

            'Grade-2' => [
                'rates' => [
                    'ရုပ်ပြကတ် (၂၀၂) ကတ်ပါဘူး' => 1,
                    '(၁၂)ရောင်ပါ ရောင်စုံဖယောင်းခဲတံဘူး (Crayon)' => 5,
                    'ရောင်စုံစက္ကူ A4 (၂၀၀)ရွက်ပါ (၁)ထုပ်' => 1,
                    'လက်ကိုင်မှန်ဘီလူး' => 1,
                    'ခုန်ကြိုး (အတို)' => 2,
                    'ရောင်စုံပလတ်စတစ် ဘောလုံးသေး' => 3,
                    'ရောင်စုံခဲတံဘူး (ချွန်စက်ပါ)' => 5,
                    'ဆေးရောင်ခြယ်ရုပ်ပုံစာအုပ် A4 (၃၀၀)ရွက်ပါ (၁)ထုပ်' => 1,
                    '(၁၂)ရောင်ပါ ရေဆေးဘူး (စုတ်တံပါ)' => 5,
                    'ပန်းချီဆွဲစုတ်တံ (၅)ချောင်းပါကတ်' => 3,
                ],
                'rows' => [
                    ['township', 'ဧရာဝတီ', 'မြန်အောင်', 272, [272, 1360, 272, 272, 544, 272, 1360, 272, 1360, 816]],
                    ['township', 'ဧရာဝတီ', 'ကြံခင်း', 124, [124, 620, 124, 124, 248, 124, 620, 124, 620, 372]],
                    ['township', 'ဧရာဝတီ', 'အင်္ဂပူ', 279, [279, 1395, 279, 279, 558, 279, 1395, 279, 1395, 837]],
                    ['township', 'ဧရာဝတီ', 'ခရိုင်အားလုံးစုစုပေါင်း', 675, [675, 3375, 675, 675, 1350, 675, 3375, 675, 3375, 2025]],
                    // ['box', null, 'ပုံး', 0, [27, 11, 25, 5, 6, 0, 14, 27, 23, 4]],
                    // ['loose', null, 'အပြေ', 0, [0, 207, 25, 75, 150, 0, 15, 0, 63, 25]],
                ],
            ],

            'Grade-3' => [
                'rates' => [
                    'ရုပ်ပြကတ် (၁၄၈) ကတ်ပါဘူး' => 1,
                    '(၁၂)ရောင်ပါ ရောင်စုံဖယောင်းခဲတံဘူး (Crayon)' => 5,
                    'စည်းနှင့်ဝါး' => 3,
                    'ပလွေ (၆)ပေါက်ပါ' => 5,
                    'ရောင်စုံခဲတံဘူး (ချွန်စက်ပါ)' => 2,
                    'ကျွန်ုပ်တို့ဆုံးဖြတ်ချက် စာရွက် A4 (၂၅၀)ရွက်ပါ (၁)ထုပ်' => 1,
                    'ဆေးရောင်ခြယ်ရုပ်ပုံစာအုပ် A4 (၃၀၀)ရွက်ပါ (၁)ထုပ်' => 1,
                    'ခုန်ကြိုး (အရှည်)' => 2,
                    'ရေဆေးဗူး (နီ၊ ဝါ၊ ပြာ)' => 1,
                    'ပန်းချီဆွဲစုတ်တံ (၅)ချောင်းပါကတ်' => 3,
                    'ဆေးစပ်ခွက်' => 4,
                ],
                'rows' => [
                    ['township', 'ဧရာဝတီ', 'မြန်အောင်', 272, [272, 1360, 816, 1360, 544, 272, 272, 544, 272, 816, 1088]],
                    ['township', 'ဧရာဝတီ', 'ကြံခင်း', 124, [124, 620, 372, 620, 248, 124, 124, 248, 124, 372, 496]],
                    ['township', 'ဧရာဝတီ', 'အင်္ဂပူ', 279, [279, 1395, 837, 1395, 558, 279, 279, 558, 279, 837, 1116]],
                    ['township', 'ဧရာဝတီ', 'ခရိုင်အားလုံးစုစုပေါင်း', 675, [675, 3375, 2025, 3375, 1350, 675, 675, 1350, 675, 2025, 2700]],
                    // ['box', null, 'ပုံး', 0, [22, 7, 5, 16, 5, 33, 42, 9, 9, 4, 7]],
                    // ['loose', null, 'အပြေ', 0, [15, 15, 25, 175, 150, 15, 3, 0, 27, 25, 180]],
                ],
            ],
        ];

        foreach ($grades as $gradeName => $gradeData) {
            $grade = Grade::firstOrCreate(
                ['name' => $gradeName],
                ['is_active' => true]
            );

            $itemIds = [];

            foreach ($gradeData['rates'] as $itemName => $rate) {

                $item = SchoolSupplyItem::where('name', $itemName)
                    ->first();

                if (!$item) {

                    $item = SchoolSupplyItem::create([
                        'name' => $itemName,
                        'rate' => $rate,
                        'is_active' => true,
                    ]);
                } else {

                    $item->update([
                        'rate' => $rate,
                        'is_active' => true,
                    ]);
                }

                $itemIds[] = $item->id;
            }

            foreach ($gradeData['rows'] as $row) {
                [$rowType, $region, $rowLabel, $schoolCount, $quantities] = $row;

                $townshipId = null;

                if ($rowType === 'township') {
                    // District total is a summary row — not a real township.
                    if ($rowLabel === 'ခရိုင်အားလုံးစုစုပေါင်း') {
                        $rowType = 'total';
                        $townshipId = null;
                    } else {
                        $township = Township::firstOrCreate(
                            ['name' => $rowLabel],
                            ['is_active' => true]
                        );
                        $townshipId = $township->id;
                    }
                }

                foreach ($itemIds as $index => $itemId) {
                    SchoolSupplyAllocation::updateOrCreate(
                        [
                            'academic_year_id' => $year->id,
                            'grade_id' => $grade->id,
                            'township_id' => $townshipId,
                            'school_supply_item_id' => $itemId,
                            'row_type' => $rowType,
                            'row_label' => $rowLabel,
                        ],
                        [
                            'region' => $region,
                            'school_count' => $schoolCount,
                            'quantity' => $quantities[$index],
                            'remark' => null,
                        ]
                    );
                }
            }
        }
    }
}
