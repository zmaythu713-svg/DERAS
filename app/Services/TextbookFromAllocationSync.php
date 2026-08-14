<?php

namespace App\Services;

use App\Models\AllocationPlan;
use App\Models\Textbook;
use App\Models\Township;
use App\Support\TownshipKeys;

class TextbookFromAllocationSync
{
    /**
     * Push calculated per-township allocation quantities into ပုံမှန်ဖြန့်ဝေစာရင်း.
     * Auto fields: တစ်အိတ်ပါယူနစ် (books_per_set), ထုတ်ပေးသည့်အုပ်ရေ (student_count).
     */
    public function sync(AllocationPlan $plan): void
    {
        $plan->loadMissing('townships.township');

        if ($plan->townships->isEmpty()) {
            return;
        }

        $booksPerSet = (int) $plan->books_per_package;

        foreach (TownshipKeys::nameToSlugMap() as $townshipName => $key) {
            $township = Township::query()
                ->where('name', $townshipName)
                ->first();

            if (!$township) {
                continue;
            }

            $issuedQty = $plan->townshipComputedValue($key, 'allocation');

            Textbook::updateOrCreate(
                [
                    'academic_year_id' => $plan->academic_year_id,
                    'township_id' => $township->id,
                    'grade_id' => $plan->grade_id,
                    'book_name_id' => $plan->book_name_id,
                ],
                [
                    'books_per_set' => $booksPerSet,
                    'student_count' => $issuedQty,
                    'book_count' => $this->formatBagCount($issuedQty, $booksPerSet),
                    'remark' => $plan->remark,
                ]
            );
        }
    }

    /**
     * Remove textbooks that were synced from this allocation plan (same natural key).
     */
    public function removeForPlan(AllocationPlan $plan): void
    {
        Textbook::query()
            ->where('academic_year_id', $plan->academic_year_id)
            ->where('grade_id', $plan->grade_id)
            ->where('book_name_id', $plan->book_name_id)
            ->delete();
    }

    private function formatBagCount(int $issuedQty, int $booksPerSet): string
    {
        if ($booksPerSet <= 0) {
            return (string) $issuedQty;
        }

        $full = intdiv($issuedQty, $booksPerSet);
        $loose = $issuedQty % $booksPerSet;

        return "{$full}အိတ်{$loose}အုပ်";
    }
}
