<?php

namespace App\Http\Controllers;

use App\Models\AllocationPlan;
use App\Models\AllocationPlanTownship;
use App\Models\Category;
use App\Models\Grade;
use App\Models\Quota;
use App\Models\QuotaLine;
use App\Models\SupplyDetail;
use App\Models\TeacherGuideIssueTownship;
use App\Models\Textbook;
use App\Models\Township;
use App\Support\GradeSubjectMap;
use App\Support\TownshipKeys;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $plans = AllocationPlan::with(['townships.township'])
            ->get();

        $totalQuotaBooks = $this->calculateTotalAllocationBooks($plans);
        $handoverBooks = $this->calculateHandoverBooks();
        $distributedBooks = $this->calculateDistributedBooks();

        // လက်ကျန် = (ခွဲတမ်း + လက်ဆင့်ကမ်း) − ဖြန့်ဝေပြီး
        // လက်ဆင့်ကမ်းကြောင့် ဖြန့်ဝေပြီးက ခွဲတမ်းထက် များနိုင်သည်
        $remainingBooks = max(0, ($totalQuotaBooks + $handoverBooks) - $distributedBooks);

        $summary = [
            // ခွဲတမ်းစာအုပ်
            'total_quota_books' => $totalQuotaBooks,

            // လက်ဆင့်ကမ်းစာအုပ်
            'handover_books' => $handoverBooks,

            // ဖြန့်ဝေပြီး
            'distributed_books' => $distributedBooks,

            // လက်ကျန် (ခွဲတမ်း + လက်ဆင့်ကမ်း − ဖြန့်ဝေပြီး)
            'remaining_books' => $remainingBooks,

            // ကျောင်းသား
            'students' =>
            $this->calculateTotalStudents(),

            // ခဲတံ၊ ဘောပင်၊ ဝတ်စုံ ဖြန့်ဝေရန် ကျောင်းသားဦးရေ
            'quota_students' =>
            $this->calculateQuotaStudents(),
        ];

        $distributionChart = $this->pieChart();

        $barChart = $this->barChart();

        $quotaDonutChart = $this->quotaDonutChart();

        $quotaBarChart = $this->quotaBarChart();

        return view('dashboard', compact(
            'summary',
            'distributionChart',
            'barChart',
            'quotaDonutChart',
            'quotaBarChart'
        ));
    }

    private function calculateQuotaStudents()
    {
        return (int) QuotaLine::query()->sum('quantity');
    }

    private function calculateTotalAllocationBooks($plans)
    {
        // ခွဲတမ်းစာအုပ် = ခရိုင်ရရှိစာအုပ်စုစုပေါင်း
        return (int) $plans->sum('received_books');
    }

    /** လက်ဆင့်ကမ်းစာအုပ် (allocation plan township transferable) */
    private function calculateHandoverBooks(): int
    {
        return (int) AllocationPlanTownship::query()->sum('transferable');
    }

    private function calculateDistributedBooks()
    {
        // ပုံမှန်ဖြန့်ဝေ — student_count မှာ ထုတ်ပေးသည့်အုပ်ရေ သိမ်းထားသည်
        // (book_count က "၅၆အိတ်၇၄အုပ်" စာသားဖြစ်၍ SUM မရ)
        // UNSIGNED cast မသုံး — အနှုတ်တန်ဖိုးက 2^64 နီးပါး ဖြစ်သွားသည်
        return $this->sumIssuedTextbookQty();
    }

    /** Sum issued textbook qty; treat negatives as 0 (bad sync / overflow-safe). */
    private function sumIssuedTextbookQty(?int $townshipId = null): int
    {
        $query = Textbook::query()->selectRaw(
            'COALESCE(SUM(GREATEST(CAST(student_count AS SIGNED), 0)), 0) as total'
        );

        if ($townshipId !== null) {
            $query->where('township_id', $townshipId);
        }

        return (int) ($query->value('total') ?? 0);
    }

    private function calculateTotalStudents()
    {
        // ကျောင်းသားကတ် = donut chart (မူလ/အလယ်/အထက်/စက်စိုက်မွေး) နဲ့ တူညီစေရန်
        return (int) QuotaLine::query()->sum('quantity');
    }

    private function pieChart()
    {
        $labels = [];

        $data = [];


        $townships = Township::whereIn('name', [
            'မြန်အောင်',
            'ကြံခင်း',
            'အင်္ဂပူ'
        ])
            ->orderBy('id')
            ->get();

        foreach ($townships as $township) {

            $total = $this->sumIssuedTextbookQty((int) $township->id);

            $labels[] = $township->name;

            $data[] = $total;
        }

        return [

            'labels' => $labels,

            'data' => $data,

        ];
    }

    private function barChart()
    {
        $townships = Township::whereIn('name', TownshipKeys::names())
            ->orderBy('id')
            ->get();

        $textbookCounts = $this->subjectCountsByGrade(Category::TEXTBOOK);

        $labels = [];
        $textbooks = [];
        $teacherGuides = [];
        $supplies = [];

        foreach ($townships as $township) {
            $townshipId = (int) $township->id;

            $labels[] = $township->name;
            $textbooks[] = $this->sumIssuedTextbookSets($townshipId, $textbookCounts);
            $teacherGuides[] = $this->sumIssuedTeacherGuideQty($townshipId);
            $supplies[] = $this->sumIssuedSupplyQty($townshipId);
        }

        return [
            'labels' => $labels,
            'textbooks' => $textbooks,
            'teacher_guides' => $teacherGuides,
            'supplies' => $supplies,
        ];
    }

    /**
     * Expected subjects per grade for one category (one complete set).
     * Example: KG textbooks have 4 subjects → 4 books = 1 စုံ.
     *
     * @return array<int, int> grade_id => subject_count
     */
    private function subjectCountsByGrade(string $categorySlug): array
    {
        $counts = DB::table('grade_book_names')
            ->join('categories', 'categories.id', '=', 'grade_book_names.category_id')
            ->where('categories.slug', $categorySlug)
            ->select('grade_book_names.grade_id', DB::raw('COUNT(*) as subject_count'))
            ->groupBy('grade_book_names.grade_id')
            ->pluck('subject_count', 'grade_id')
            ->map(fn ($count) => (int) $count)
            ->all();

        foreach (Grade::query()->get(['id', 'name']) as $grade) {
            if (($counts[$grade->id] ?? 0) > 0) {
                continue;
            }

            $mapped = count(GradeSubjectMap::subjectsFor($grade->name, $categorySlug));
            if ($mapped > 0) {
                $counts[$grade->id] = $mapped;
            }
        }

        return $counts;
    }

    /**
     * Convert issued books into complete sets by grade subject count.
     *
     * @param  array<int, int>  $subjectCounts
     */
    private function sumIssuedTextbookSets(?int $townshipId, array $subjectCounts): int
    {
        $query = Textbook::query()
            ->select('grade_id')
            ->selectRaw('COALESCE(SUM(GREATEST(CAST(student_count AS SIGNED), 0)), 0) as total')
            ->groupBy('grade_id');

        if ($townshipId !== null) {
            $query->where('township_id', $townshipId);
        }

        $sets = 0;

        foreach ($query->get() as $row) {
            $subjects = (int) ($subjectCounts[(int) $row->grade_id] ?? 0);
            if ($subjects <= 0) {
                continue;
            }

            $sets += intdiv((int) $row->total, $subjects);
        }

        return $sets;
    }

    private function sumIssuedTeacherGuideQty(?int $townshipId = null): int
    {
        $query = TeacherGuideIssueTownship::query();

        if ($townshipId !== null) {
            $query->where('township_id', $townshipId);
        }

        return max(0, (int) $query->sum('issued_quantity'));
    }

    private function sumIssuedSupplyQty(?int $townshipId = null): int
    {
        $query = SupplyDetail::query();

        if ($townshipId !== null) {
            $query->where('township_id', $townshipId);
        }

        return max(0, (int) $query->sum('issued_total'));
    }

    private function quotaDonutChart()
    {
        $primaryTotal = (int) QuotaLine::where('school_level', 'primary')->sum('quantity');
        $middleTotal = (int) QuotaLine::where('school_level', 'middle')->sum('quantity');
        $highTotal = (int) QuotaLine::where('school_level', 'high')->sum('quantity');
        $agriTotal = (int) QuotaLine::where('school_level', 'agriculture')->sum('quantity');

        return [
            'labels' => [
                'မူလတန်း',
                'အလယ်တန်း',
                'အထက်တန်း',
                'စက်၊စိုက်၊မွေး'
            ],
            'data' => [
                $primaryTotal,
                $middleTotal,
                $highTotal,
                $agriTotal
            ],
        ];
    }

    private function quotaBarChart()
    {
        $townships = Township::whereIn('name', [
            'မြန်အောင်',
            'ကြံခင်း',
            'အင်္ဂပူ'
        ])
            ->orderBy('id')
            ->get();

        $labels = [];
        $distributionTotal = [];

        // Grade duplicate မဖြစ်အောင် ID ယူ
        $plans = AllocationPlan::with(['townships.township'])
            ->whereIn('id', function ($query) {
                $query->selectRaw('MIN(id)')
                    ->from('allocation_plans')
                    ->groupBy('grade_id');
            })
            ->get();

        foreach ($townships as $township) {
            $labels[] = $township->name;
            $quota = Quota::with('lines')->where('township_id', $township->id)->first();
            $val = $quota ? (int) $quota->distribution_total : 0;

            if ($val === 0) {
                if ($township->name == 'မြန်အောင်') {
                    $val = (int) $plans->sum(fn ($p) => ($d = $p->detailCompat()) ? $d->myanaung_total_students : 0);
                } elseif ($township->name == 'ကြံခင်း') {
                    $val = (int) $plans->sum(fn ($p) => ($d = $p->detailCompat()) ? $d->kyankhin_total_students : 0);
                } else {
                    $val = (int) $plans->sum(fn ($p) => ($d = $p->detailCompat()) ? $d->ingapu_total_students : 0);
                }
            }

            $distributionTotal[] = $val;
        }

        $labels[] = 'ခရိုင်အားလုံးစုစုပေါင်း';
        $distributionTotal[] = array_sum($distributionTotal);

        return [
            'labels' => $labels,
            'data' => $distributionTotal,
        ];
    }
}
