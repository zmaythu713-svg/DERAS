<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\BookName;
use App\Models\Grade;
use App\Models\TeacherGuide;
use Illuminate\Database\Seeder;

class TeacherGuideSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::firstOrCreate(
            ['name' => '2025-2026'],
            ['is_active' => true]
        );

        $primaryGuideSubjects = [
            'မြန်မာစာ',
            'အင်္ဂလိပ်စာ',
            'သင်္ချာ',
            'လူမှုရေး',
            'သိပ္ပံ',
            'စာရိတ္တနှင့်ပြည်သူ့နီတိ',
            'ဘဝတွက်တာကျွမ်းကျင်စရာ',
            'ကာယပညာ',
            'အနုပညာ(ပန်းချီ)',
            'အနုပညာ(ဂီတ)',
        ];

        $primaryTeacherSubjects = [
            'ဘဝတွက်တာကျွမ်းကျင်စရာ',
            'ကာယပညာ',
            'အနုပညာ(ပန်းချီ)',
            'အနုပညာ(ဂီတ)',
        ];

        $middleSubjects = [
            'မြန်မာစာ',
            'အင်္ဂလိပ်စာ',
            'သင်္ချာ',
            'ပထဝီဝင်',
            'သမိုင်း',
            'သိပ္ပံ',
            'စာရိတ္တနှင့်ပြည်သူ့နီတိ',
            'ဘဝတွက်တာကျွမ်းကျင်စရာ',
            'ကာယပညာ',
            'အနုပညာ(ပန်းချီ)',
            'အနုပညာ(ဂီတ)',
        ];

        $grade6Subjects = [
            'မြန်မာစာ',
            'အင်္ဂလိပ်စာ',
            'သင်္ချာ(၁)',
            'သင်္ချာ(၂)',
            'သိပ္ပံ',
            'ပထဝီ',
            'သမိုင်း',
            'စာရိတ္တနှင့်ပြည်သူ့နီတိ',
            'အနုပညာ(ပန်းချီ)',
            'အနုပညာ(ဂီတ)',
            'ကာယပညာ',
            'ဘဝတွက်တာကျွမ်းကျင်စရာ',
        ];

        $highSubjects = [
            'မြန်မာစာ',
            'အင်္ဂလိပ်စာ',
            'သင်္ချာ',
            'စာရိတ္တနှင့်ပြည်သူ့နီတိ',
            'ဘဝတွက်တာကျွမ်းကျင်စရာ',
            'ကာယပညာ',
            'အနုပညာ(ပန်းချီ)',
            'အနုပညာ(ဂီတ)',
            'ဓာတုဗေဒ',
            'ရူပဗေဒ',
            'ဇီဝဗေဒ',
            'လူမှုရေးသိပ္ပံ(ဝီ+မိုင်း+ဘော)',
            'ပထဝီဝင်',
            'သမိုင်း',
            'ဘောဂဗေဒ',
            'သိပ္ပံ(ဓာတု+ရူပ+ဇီဝ)',
            'လူမှုရေးသိပ္ပံ(ဝီ+မိုင်း)(ဓာ၊ရူ၊ဘော)',
        ];

        $grade12Subjects = [
            1 => 'မြန်မာစာ',
            2 => 'အင်္ဂလိပ်စာ',
            3 => 'သင်္ချာ',
            4 => 'ဓာတုဗေဒ',
            6 => 'ရူပဗေဒ',
            8 => 'ဇီဝဗေဒ',
            10 => 'လူမှုရေးသိပ္ပံ(ဝီ+မိုင်း+ဘော)',
            11 => 'ပထဝီဝင်',
            12 => 'သမိုင်း',
            13 => 'ဘောဂဗေဒ',
            14 => 'သိပ္ပံ(ဓာတု+ရူပ+ဇီဝ)',
            15 => 'လူမှုရေးသိပ္ပံ(ဝီ+မိုင်း)(ဓာ၊ရူ၊ဘော)',
        ];

        $groups = [
            [
                'group_no' => 1,
                'grade' => 'KG',
                'title' => "သူငယ်တန်း\nKG\n(ဆရာလမ်းညွှန်)",
                'type' => 'ဆရာလမ်းညွှန်',
                'subjects' => [
                    1 => 'ဘာသာရပ်စကားမှီငြမ်းပြုလမ်းညွှန်',
                    2 => 'ဆရာလမ်းညွှန်',
                    3 => 'သင်ပြမှုပုံစံ',
                ],
                'value' => [1365, 0, 1365],
            ],

            ['group_no' => 2, 'grade' => 'Grade-1', 'title' => "Grade-1\n(ဆရာကိုင်)", 'type' => 'ဆရာကိုင်', 'subjects' => $primaryTeacherSubjects],
            ['group_no' => 3, 'grade' => 'Grade-1', 'title' => "Grade-1\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $primaryGuideSubjects],

            ['group_no' => 4, 'grade' => 'Grade-2', 'title' => "Grade-2\n(ဆရာကိုင်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာကိုင်', 'subjects' => $primaryTeacherSubjects],
            ['group_no' => 5, 'grade' => 'Grade-2', 'title' => "Grade-2\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $primaryGuideSubjects],

            ['group_no' => 6, 'grade' => 'Grade-3', 'title' => "Grade-3\n(ဆရာကိုင်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာကိုင်', 'subjects' => $primaryTeacherSubjects],
            ['group_no' => 7, 'grade' => 'Grade-3', 'title' => "Grade-3\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $primaryGuideSubjects],

            ['group_no' => 8, 'grade' => 'Grade-4', 'title' => "Grade-4\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $primaryGuideSubjects],
            ['group_no' => 9, 'grade' => 'Grade-5', 'title' => "Grade-5\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $primaryGuideSubjects],

            ['group_no' => 10, 'grade' => 'Grade-6', 'title' => "Grade-6\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $grade6Subjects, 'middle' => true],
            ['group_no' => 11, 'grade' => 'Grade-7', 'title' => "Grade-7\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $middleSubjects, 'middle' => true],
            ['group_no' => 12, 'grade' => 'Grade-8', 'title' => "Grade-8\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $middleSubjects, 'middle' => true],
            ['group_no' => 13, 'grade' => 'Grade-9', 'title' => "Grade-9\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $middleSubjects, 'middle' => true],

            ['group_no' => 14, 'grade' => 'Grade-10', 'title' => "Grade-10\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $highSubjects, 'high' => true],
            ['group_no' => 15, 'grade' => 'Grade-11', 'title' => "Grade-11\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $highSubjects, 'high' => true],
            ['group_no' => 16, 'grade' => 'Grade-12', 'title' => "Grade-12\n(ဆရာလမ်းညွှန်)\n(သင်ရိုးသစ်)", 'type' => 'ဆရာလမ်းညွှန်', 'subjects' => $grade12Subjects, 'grade12' => true],
        ];

        foreach ($groups as $group) {
            $grade = Grade::firstOrCreate(
                ['name' => $group['grade']],
                ['is_active' => true]
            );

            foreach ($group['subjects'] as $index => $subject) {
                $sequenceNo = is_int($index) ? $index : $index + 1;

                $value = $this->resolveValue($group, $subject);

                $bookName = BookName::firstOrCreate(
                    ['name' => $subject],
                    ['is_active' => true]
                );

                TeacherGuide::updateOrCreate(
                    [
                        'academic_year_id' => $year->id,
                        'grade_id' => $grade->id,
                        'book_name_id' => $bookName->id,
                        'guide_type' => $group['type'],
                        'sequence_no' => $sequenceNo,
                    ],
                    [
                        'group_no' => $group['group_no'],
                        'group_title' => $group['title'],
                        'kg_to_g12_quota' => $value[0],
                        'g1_to_g5_quota' => $value[1],
                        'remark' => null,
                    ]
                );
            }
        }
    }

    private function resolveValue(array $group, string $subject): array
    {
        if (isset($group['value'])) {
            return $group['value'];
        }

        if (!empty($group['high'])) {
            if ($subject === 'ဘဝတွက်တာကျွမ်းကျင်စရာ') {
                return [15, 0, 15];
            }

            return [163, 0, 163];
        }

        if (!empty($group['grade12'])) {
            return [163, 0, 163];
        }

        if (!empty($group['middle'])) {
            if ($subject === 'ဘဝတွက်တာကျွမ်းကျင်စရာ') {
                return [15, 0, 15];
            }

            return [327, 0, 327];
        }

        if ($subject === 'ဘဝတွက်တာကျွမ်းကျင်စရာ') {
            return [675, 10, 685];
        }

        return [675, 685, 1360];
    }
}
