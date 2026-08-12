<?php

namespace Database\Seeders;

use App\Models\CompanyContact;
use Illuminate\Database\Seeder;

class CompanyContactSeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'company_name' => 'Mahar Akari',
                'lot' => "Lot-1, Lot-6",
                'responsible_name' => 'ဦးသာထူးညီညီ',
                'phone' => '09-409348822'
            ],
            [
                'company_name' => 'Adapt Advertising',
                'lot' => "Lot-2, Lot-16",
                'responsible_name' => 'ဦးထွန်းထွန်းဦး',
                'phone' => '09-409348822'
            ],
            [
                'company_name' => 'Vast Global',
                'lot' => "Lot-3, Lot-27",
                'responsible_name' => 'ဦးဥက္ကာသိန်း',
                'phone' => '09-408483355'
            ],
            [
                'company_name' => 'Dana Shwe Taung',
                'lot' => "Lot-4, Lot-18",
                'responsible_name' => 'ဦးကျော်ဝင်းအောင်',
                'phone' => '09-760183887'
            ],
            [
                'company_name' => 'Home Plus',
                'lot' => "Lot-5",
                'responsible_name' => 'ဦးကျော်စွာထွန်း',
                'phone' => '09-409348822'
            ],
            [
                'company_name' => 'Macro Dragon',
                'lot' => "Lot-7, Lot-17",
                'responsible_name' => 'ဦးပိုင်ဇော်ဟိန်း',
                'phone' => '09-450026669'
            ],
            [
                'company_name' => 'Diamond Octagon',
                'lot' => "Lot-8, Lot-13",
                'responsible_name' => 'ဒေါ်ခိုင်ယုလင်း',
                'phone' => '09-450708675'
            ],
            [
                'company_name' => 'Amara Myanmar',
                'lot' => "Lot-9, Lot-20",
                'responsible_name' => 'ဒေါ်သူဇာထွန်း',
                'phone' => '09-409100300'
            ],
            [
                'company_name' => 'City Global Mark Garment',
                'lot' => "Lot-10, Lot-12",
                'responsible_name' => 'ဒေါ်မြမဥ္ဇူမိုး',
                'phone' => '09-408483377'
            ],
            [
                'company_name' => 'City Global Mark Services',
                'lot' => "Lot-11, Lot-15",
                'responsible_name' => 'ဒေါ်မြမဥ္ဇူမိုး',
                'phone' => '09-408483377'
            ],
            [
                'company_name' => 'Asia Dragon',
                'lot' => "Lot-14, Lot-29",
                'responsible_name' => 'ဒေါ်ညိုဝေလွင်',
                'phone' => '09-777432240'
            ],
            [
                'company_name' => 'Sancho',
                'lot' => "Lot-19, Lot-23",
                'responsible_name' => 'ဒေါ်တန်ခူးထွန်း',
                'phone' => '09-773334489'
            ],
            [
                'company_name' => 'ရွှေငွေခိုင်',
                'lot' => "Lot-21",
                'responsible_name' => 'ဒေါ်ကေခိုင်ဦး',
                'phone' => '09-450708675'
            ],
            [
                'company_name' => 'ရွှေဉာဏ်လင်း',
                'lot' => "Lot-22",
                'responsible_name' => 'ဒေါ်ကေခိုင်ဦး',
                'phone' => '09-450708675'
            ],
            [
                'company_name' => 'Mascots Didactic and Analytical',
                'lot' => "Lot-24",
                'responsible_name' => 'ဦးဥက္ကာကျော်',
                'phone' => '09-420330010'
            ],
            [
                'company_name' => "Student's Guide",
                'lot' => "Lot-25",
                'responsible_name' => 'ဦးကျော်ထွေး',
                'phone' => '09-550672800'
            ],
            [
                'company_name' => 'ဆုတောင်းပြည့်',
                'lot' => "Lot-26",
                'responsible_name' => 'ဒေါ်ခိုင်ယုဝင်း',
                'phone' => '09-773334489'
            ],
            [
                'company_name' => 'Myanmar Golden Earth',
                'lot' => "Lot-28",
                'responsible_name' => 'ဒေါ်ဟေမာဇော်',
                'phone' => '09-254171160'
            ],
            [
                'company_name' => 'ကောင်းမြတ်ပိုင်',
                'lot' => "Lot-30",
                'responsible_name' => 'ဒေါ်အေးမြကြည်',
                'phone' => '09-450708675'
            ],
        ];

        foreach ($companies as $item) {
            CompanyContact::updateOrCreate(
                [
                    'company_name' => $item['company_name'],
                ],
                [
                    'lot' => $item['lot'],
                    'responsible_name' => $item['responsible_name'],
                    'phone' => $item['phone'],
                    'is_active' => true,
                ]
            );
        }
    }
}
