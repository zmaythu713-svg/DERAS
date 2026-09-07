# DERAS Simplified Database Design — Table Explanation and Data Dictionary

This document describes the simplified 18-table database design shown in the ER diagram. `PK` means Primary Key and `FK` means Foreign Key.

## 1. academic_year

`academic_year` stores the academic years used in DERAS, such as 2025–2026. It is the parent table for quota, school supply, stock, previous-year balance, allocation plan, and teacher-guide township allocation records. This makes it possible to filter all operations by the selected academic year.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique academic-year identifier |
| name | varchar | — | Academic-year name, for example 2025-2026 |
| start_year | smallint | — | Beginning calendar year |
| end_year | smallint | — | Ending calendar year |
| is_current | boolean | — | Marks the current working academic year |
| is_active | boolean | — | Shows whether the academic year can be used |

## 2. township

`township` stores township names used for allocation and distribution. A township can have many users, stock records, balances, allocation details, teacher-guide allocations, issues, and school-supply records.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique township identifier |
| name | varchar | — | Township name |
| is_active | boolean | — | Shows whether the township is active |

## 3. role

`role` stores user roles such as Super Admin and Admin. Each role can be assigned to many users. The `slug` field is a short code used by the application for role checks.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique role identifier |
| name | varchar | — | Role name, such as Super Admin or Admin |
| slug | varchar | — | Unique code for the role |

## 4. user

`user` stores staff login accounts. Each user belongs to one role through `role_id` and may be assigned to one township through `township_id`. The email and password are used during authentication.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique user identifier |
| role_id | bigint | Foreign Key → role.id | Assigned system role |
| township_id | bigint | Foreign Key → township.id | Assigned township |
| name | varchar | — | User name |
| email | varchar | — | Login email address |
| password | varchar | — | Hashed login password |

## 5. school_level

`school_level` stores broad education levels, such as primary, middle, high, or agriculture. It is used to group grades and to categorize quota records.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique school-level identifier |
| name | varchar | — | School-level name |

## 6. grade

`grade` stores individual grades or class levels. Each grade belongs to one school level through `school_level_id`. Grades are used by quota, book, and school-supply records.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique grade identifier |
| school_level_id | bigint | Foreign Key → school_level.id | Parent school level |
| name | varchar | — | Grade name |

## 7. book_name

`book_name` stores book or subject titles. Each book belongs to one grade through `grade_id`. The table is used by textbook and teacher-guide records.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique book identifier |
| grade_id | bigint | Foreign Key → grade.id | Grade related to the book |
| name | varchar | — | Book or subject title |

## 8. textbook

`textbook` defines textbook information used by allocation and stock records. Each textbook refers to one book title through `book_name_id`.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique textbook identifier |
| book_name_id | bigint | Foreign Key → book_name.id | Related book title |
| books_per_set | integer | — | Number of books in one set |
| student_count | integer | — | Number of students used for the record |

## 9. teacher_guide

`teacher_guide` stores teacher-guide information for a book. Each guide refers to a book name through `book_name_id`. The table is the parent of teacher-guide township allocations.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique teacher-guide identifier |
| book_name_id | bigint | Foreign Key → book_name.id | Related book title |
| guide_type | varchar | — | Type of teacher guide |
| total_quota | integer | — | Total teacher-guide quantity received or planned |

## 10. quota

`quota` stores student quantities used during allocation planning. Each quota belongs to one academic year, school level, and grade. It records how many students are used as the basis for allocation.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique quota identifier |
| academic_year_id | bigint | Foreign Key → academic_year.id | Related academic year |
| school_level_id | bigint | Foreign Key → school_level.id | Related school level |
| grade_id | bigint | Foreign Key → grade.id | Related grade |
| student_quantity | integer | — | Number of students in the quota |

## 11. school_supply

`school_supply` stores school-supply allocation information. Each row is connected to an academic year, township, and grade. The supply name, rate, and quantity are stored in the same simplified table.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique school-supply identifier |
| academic_year_id | bigint | Foreign Key → academic_year.id | Related academic year |
| township_id | bigint | Foreign Key → township.id | Township receiving the supply |
| grade_id | bigint | Foreign Key → grade.id | Related grade |
| name | varchar | — | School-supply item name |
| rate | decimal | — | Rate used to calculate quantity |
| quantity | integer | — | Planned or allocated supply quantity |

## 12. stock

`stock` stores textbook stock information by academic year and township. It tracks the previous balance, transferred quantity, and required quantity for a textbook.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique stock identifier |
| academic_year_id | bigint | Foreign Key → academic_year.id | Related academic year |
| township_id | bigint | Foreign Key → township.id | Township where stock is located |
| textbook_id | bigint | Foreign Key → textbook.id | Related textbook |
| previous_balance | integer | — | Balance from the previous period |
| transferred | integer | — | Quantity transferred to or from the township |
| required_quantity | integer | — | Quantity required for the township |

## 13. previous_year_balance

`previous_year_balance` stores textbook balances brought forward from the previous academic year. It is linked to the academic year, township, and textbook.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique previous-balance identifier |
| academic_year_id | bigint | Foreign Key → academic_year.id | Current academic year |
| township_id | bigint | Foreign Key → township.id | Related township |
| textbook_id | bigint | Foreign Key → textbook.id | Related textbook |
| balance | integer | — | Carry-forward book balance |

## 14. allocation_plan

`allocation_plan` stores the main plan for textbook allocation in an academic year. It stores the books received and the number of books in each package. The detail rows for each township are stored in `allocation_plan_township`.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique allocation-plan identifier |
| academic_year_id | bigint | Foreign Key → academic_year.id | Related academic year |
| book_name_id | bigint | Foreign Key → book_name.id | Book planned for allocation |
| received_books | integer | — | Total books received |
| books_per_package | integer | — | Number of books in one package |

## 15. allocation_plan_township

`allocation_plan_township` stores the planned textbook share for each township. It links one allocation plan, one township, and one textbook. This table is the detail table of `allocation_plan`.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique allocation-plan detail identifier |
| allocation_plan_id | bigint | Foreign Key → allocation_plan.id | Parent allocation plan |
| township_id | bigint | Foreign Key → township.id | Township receiving the allocation |
| textbook_id | bigint | Foreign Key → textbook.id | Textbook included in the plan |
| total_students | integer | — | Total students used for calculation |
| allocated_quantity | integer | — | Planned quantity for the township |

## 16. tg_township_allocation

`tg_township_allocation` stores teacher-guide quantities planned for each township. It links the academic year, township, and teacher guide. The table is used before the actual teacher-guide issue is recorded.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique teacher-guide allocation identifier |
| academic_year_id | bigint | Foreign Key → academic_year.id | Related academic year |
| township_id | bigint | Foreign Key → township.id | Township receiving the guide |
| teacher_guide_id | bigint | Foreign Key → teacher_guide.id | Related teacher guide |
| allocated_quantity | integer | — | Planned teacher-guide quantity |

## 17. teacher_guide_issue

`teacher_guide_issue` stores the actual issue record created from a teacher-guide township allocation. It records the issue date and the quantity issued.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique teacher-guide issue identifier |
| tg_township_allocation_id | bigint | Foreign Key → tg_township_allocation.id | Source allocation record |
| issued_quantity | integer | — | Quantity issued |
| issue_date | date | — | Date of issue |

## 18. tg_issue_township

`tg_issue_township` stores township-level teacher-guide issue details. It links a teacher-guide issue to the township that received the materials.

| Column Name | Data Type | Key Constraint | Description |
|---|---|---|---|
| id | bigint | Primary Key | Unique teacher-guide issue detail identifier |
| teacher_guide_issue_id | bigint | Foreign Key → teacher_guide_issue.id | Parent issue record |
| township_id | bigint | Foreign Key → township.id | Township receiving the issue |
| issued_quantity | integer | — | Quantity issued to the township |

---

## Relationship Summary

| Parent Table | Child Table | Relationship |
|---|---|---|
| academic_year | quota, school_supply, stock, previous_year_balance, allocation_plan, tg_township_allocation | One-to-many |
| township | user, school_supply, stock, previous_year_balance, allocation_plan_township, tg_township_allocation, tg_issue_township | One-to-many |
| school_level | grade, quota | One-to-many |
| grade | book_name, quota, school_supply | One-to-many |
| role | user | One-to-many |
| book_name | textbook, teacher_guide, allocation_plan | One-to-many |
| textbook | stock, previous_year_balance, allocation_plan_township | One-to-many |
| teacher_guide | tg_township_allocation | One-to-many |
| allocation_plan | allocation_plan_township | One-to-many |
| tg_township_allocation | teacher_guide_issue | One-to-many |
| teacher_guide_issue | tg_issue_township | One-to-many |

## Short Burmese Summary

ဒီ simplified database design တွင် `academic_year`, `township`, `school_level`, `grade`, `book_name`, `role` တို့သည် အခြေခံ master tables များဖြစ်သည်။ `quota`, `allocation_plan`, `allocation_plan_township`, `tg_township_allocation`, နှင့် `school_supply` တို့သည် Allocation (စီမံခွဲဝေမှု) အတွက် အသုံးပြုသည်။ `textbook`, `stock`, `previous_year_balance`, `teacher_guide_issue`, နှင့် `tg_issue_township` တို့သည် Distribution (အမှန်တကယ် ဖြန့်ဝေ/ထုတ်ပေးမှု) အတွက် အသုံးပြုသည်။ Primary Key နှင့် Foreign Key များကို အသုံးပြုထားသောကြောင့် table များကြား ဆက်နွယ်မှုများကို မှန်ကန်စွာ ထိန်းသိမ်းနိုင်သည်။
