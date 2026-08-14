<?php

namespace Database\Seeders;

use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\AllocationPlanSeeder;
use Database\Seeders\BookNameSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\CompanyContactSeeder;
use Database\Seeders\GradeSeeder;
use Database\Seeders\GradeSubjectSeeder;
use Database\Seeders\PreviousYearBalanceSeeder;
use Database\Seeders\QuotaSeeder;
use Database\Seeders\SchoolCountSeeder;
use Database\Seeders\SchoolSupplySeeder;
use Database\Seeders\StockSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\SuperAdminSeeder;
use Database\Seeders\TeacherGuideDistributionSeeder;
use Database\Seeders\TeacherGuideIssueSeeder;
use Database\Seeders\TeacherGuideSummarySeeder;
use Database\Seeders\TextbookSeeder;
use Database\Seeders\TownshipSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,
            AcademicYearSeeder::class,
            TownshipSeeder::class,
            CategorySeeder::class,
            GradeSeeder::class,
            SubjectSeeder::class,
            BookNameSeeder::class,
            GradeSubjectSeeder::class,
            PreviousYearBalanceSeeder::class,
            SchoolCountSeeder::class,
            TextbookSeeder::class,
            StockSeeder::class,
            TeacherGuideDistributionSeeder::class,
            QuotaSeeder::class,
            CompanyContactSeeder::class,
            SchoolSupplySeeder::class,
            SupplyDetailSeeder::class,
            TeacherGuideIssueSeeder::class,
            TeacherGuideSummarySeeder::class,
            AllocationPlanSeeder::class,
        ]);
    }
}
