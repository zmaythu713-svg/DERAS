# DERAS Table Names — Option A (`alloc_` / `dist_`)

Naming rule:
- **Allocation (planning)** tables start with `alloc_`
- **Distribution (actual issue/delivery)** tables start with `dist_`
- Master and user tables have no prefix

Total: **15 tables**

---

## Master + Users (5)

| # | Table name | Burmese meaning |
|---|---|---|
| 1 | `academic_years` | ပညာသင်နှစ် |
| 2 | `townships` | မြို့နယ် |
| 3 | `grades` | အတန်း |
| 4 | `book_names` | စာအုပ်အမည် |
| 5 | `users` | အသုံးပြုသူ (Admin / Super Admin) |

---

## Allocation — `alloc_` (6)

| # | Table name | Burmese meaning |
|---|---|---|
| 6 | `alloc_student_quotas` | ကျောင်းသားခွဲတမ်း header |
| 7 | `alloc_quota_details` | ခွဲတမ်းအသေးစိတ် |
| 8 | `alloc_textbook_plans` | စာအုပ်ခွဲဝေစီမံကိန်း |
| 9 | `alloc_township_shares` | မြို့နယ်အလိုက် စီမံခွဲဝေမှု |
| 10 | `alloc_guide_receipts` | ဆရာလမ်းညွှန် လက်ခံခွဲတမ်း |
| 11 | `alloc_school_supplies` | ကျောင်းသုံးပစ္စည်း စီမံခွဲဝေမှု |

FK examples:
- `alloc_quota_details.alloc_student_quota_id` → `alloc_student_quotas.id`
- `alloc_township_shares.alloc_textbook_plan_id` → `alloc_textbook_plans.id`

---

## Distribution — `dist_` (4)

| # | Table name | Burmese meaning |
|---|---|---|
| 12 | `dist_textbooks` | ပုံမှန်စာအုပ် ဖြန့်ဝေမှတ်တမ်း |
| 13 | `dist_stocks` | စတော့ / ပိုလျှံ ဖြန့်ဝေမှတ်တမ်း |
| 14 | `dist_guide_issues` | ဆရာလမ်းညွှန် ဖြန့်ဝေ / ထုတ်ပေးမှတ်တမ်း |
| 15 | `dist_supply_issues` | ကျောင်းသုံးပစ္စည်း ထုတ်ပေးမှတ်တမ်း |

FK example:
- `dist_guide_issues.alloc_guide_receipt_id` → `alloc_guide_receipts.id` (optional link from plan to issue)

---

## Names only (copy list)

1. academic_years  
2. townships  
3. grades  
4. book_names  
5. users  
6. alloc_student_quotas  
7. alloc_quota_details  
8. alloc_textbook_plans  
9. alloc_township_shares  
10. alloc_guide_receipts  
11. alloc_school_supplies  
12. dist_textbooks  
13. dist_stocks  
14. dist_guide_issues  
15. dist_supply_issues  

---

## Short thesis sentence

In DERAS, allocation tables use the `alloc_` prefix to store planning data, while distribution tables use the `dist_` prefix to store actual issued quantities. This naming rule makes Allocation and Distribution clearly visible in the database design.
