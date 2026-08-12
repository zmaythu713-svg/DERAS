<?php

use App\Support\MyanmarPhone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = DB::table('company_contacts')->select('id', 'phone')->get();

        foreach ($rows as $row) {
            $normalized = MyanmarPhone::normalize($row->phone);
            if ($normalized === null || $normalized === $row->phone) {
                continue;
            }

            DB::table('company_contacts')
                ->where('id', $row->id)
                ->update(['phone' => $normalized]);
        }
    }

    public function down(): void
    {
        // Irreversible normalize — no-op
    }
};
