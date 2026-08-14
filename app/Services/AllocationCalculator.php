<?php

namespace App\Services;

use App\Support\TownshipKeys;

class AllocationCalculator
{
    /**
     * Compute allocation plan totals and per-township breakdown.
     *
     * @param  array{
     *     received_books:int,
     *     books_per_package:int,
     *     myanaung_previous:int,
     *     kyankhin_previous:int,
     *     ingapu_previous:int,
     *     myanaung_total_students:int,
     *     kyankhin_total_students:int,
     *     ingapu_total_students:int,
     *     myanaung_transferable:int,
     *     kyankhin_transferable:int,
     *     ingapu_transferable:int
     * }  $input
     * @return array{
     *     plan: array<string, int>,
     *     townships: array<string, int>
     * }
     */
    public function compute(array $input): array
    {
        $districts = TownshipKeys::slugs();
        $unit = max(1, (int) ($input['books_per_package'] ?? 1));
        $received = (int) ($input['received_books'] ?? 0);

        $townships = [];

        foreach ($districts as $district) {
            $previous = (int) ($input["{$district}_previous"] ?? 0);
            $students = (int) ($input["{$district}_total_students"] ?? 0);
            $transferable = (int) ($input["{$district}_transferable"] ?? 0);

            $townships["{$district}_previous"] = $previous;
            $townships["{$district}_total_students"] = $students;
            $townships["{$district}_transferable"] = $transferable;
        }

        return [
            'plan' => [
                'received_books' => $received,
                'books_per_package' => $unit,
            ],
            'townships' => $townships,
        ];
    }
}
