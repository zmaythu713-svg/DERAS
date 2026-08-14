<?php

namespace App\Http\Controllers;

use App\Models\AllocationPlan;
use App\Models\Quota;
use App\Models\QuotaLine;
use App\Models\Textbook;
use App\Models\Township;

class DashboardController extends Controller
{
    public function index()
    {
        $plans = AllocationPlan::with(['townships.township'])
            ->get();

        $totalQuotaBooks = $this->calculateTotalAllocationBooks($plans);
        $distributedBooks = $this->calculateDistributedBooks();

        $summary = [
            // ခွဲတမ်းစာအုပ်
            'total_quota_books' => $totalQuotaBooks,

            // ဖြန့်ဝေပြီး
            'distributed_books' => $distributedBooks,

            // ယခုနှစ်လက်ကျန် = ခွဲတမ်း − ဖြန့်ဝေပြီး
            'remaining_books' => max(0, $totalQuotaBooks - $distributedBooks),

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

    private function calculateDistributedBooks()
    {
        // ပုံမှန်ဖြန့်ဝေ — student_count မှာ ထုတ်ပေးသည့်အုပ်ရေ သိမ်းထားသည်
        // (book_count က "၅၆အိတ်၇၄အုပ်" စာသားဖြစ်၍ SUM မရ)
        return (int) (Textbook::selectRaw(
            'SUM(CAST(student_count AS UNSIGNED)) as total'
        )
            ->value('total') ?? 0);
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

            $total = Textbook::where(
                'township_id',
                $township->id
            )
                ->selectRaw(
                    'SUM(CAST(student_count AS UNSIGNED)) as total'
                )
                ->value('total') ?? 0;

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
        $labels = [];

        $students = [];

        $distributed = [];

        $townships = [
            'မြန်အောင်',
            'ကြံခင်း',
            'အင်္ဂပူ'
        ];

        // Grade duplicate မဖြစ်အောင် ID ယူ
        $plans = AllocationPlan::with(['townships.township'])
            ->whereIn('id', function ($query) {

                $query->selectRaw('MIN(id)')
                    ->from('allocation_plans')
                    ->groupBy('grade_id');
            })
            ->get();

        foreach ($townships as $township) {
            $labels[] = $township;

            if ($township == 'မြန်အောင်') {

                $studentTotal = $plans->sum(function ($plan) {
                    $detail = $plan->detailCompat();

                    return $detail
                        ? $detail->myanaung_total_students
                        : 0;
                });
            } elseif ($township == 'ကြံခင်း') {
                $studentTotal = $plans->sum(function ($plan) {
                    $detail = $plan->detailCompat();

                    return $detail
                        ? $detail->kyankhin_total_students
                        : 0;
                });
            } else {

                $studentTotal = $plans->sum(function ($plan) {
                    $detail = $plan->detailCompat();

                    return $detail
                        ? $detail->ingapu_total_students
                        : 0;
                });
            }

            $students[] = $studentTotal;

            // Distributed
            $townshipId = Township::where(
                'name',
                $township
            )
                ->value('id');

            $distributedTotal = Textbook::where(
                'township_id',
                $townshipId
            )
                ->selectRaw(
                    'SUM(CAST(student_count AS UNSIGNED)) as total'
                )
                ->value('total') ?? 0;

            $distributed[] = $distributedTotal;
        }

        // Total
        $labels[] = 'ခရိုင်အားလုံးစုစုပေါင်း';

        $students[] = array_sum($students);

        $distributed[] = array_sum($distributed);

        return [
            'labels' => $labels,

            'students' => $students,

            'distributed' => $distributed,
        ];
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
