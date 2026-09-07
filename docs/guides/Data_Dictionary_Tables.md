# 3.4 Data Dictionary — DERAS

Each table below follows this format: **table title**, **column dictionary**, and a **short paragraph**.

---

### Table 3.1 Academic Years Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null, Unique |
| start_year | Smallint | Null |
| end_year | Smallint | Null |
| is_active | Boolean | Not Null |
| is_current | Boolean | Not Null |
| status | Varchar (20) | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the Academic Years table, there will be attributes such as id, name, start_year, end_year, is_active, is_current, and status. The Academic Years table has necessary information for the academic year used across DERAS. The id is the primary key of the academic_years table, and name, start_year, end_year, is_active, is_current, and status are attributes. Admin and Super Admin use this table when selecting the working year for quota, allocation, and distribution modules.

---

### Table 3.2 Townships Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null |
| is_active | Boolean | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the Townships table, there will be attributes such as id, name, and is_active. The Townships table has necessary information for township locations used in DERAS. The id is the primary key of the townships table, and name and is_active are attributes. This table is referenced by quotas, allocation plan townships, textbooks, stocks, and other distribution records.

---

### Table 3.3 Grades Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null |
| is_active | Boolean | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the Grades table, there will be attributes such as id, name, and is_active. The Grades table has necessary information for grade or class levels in DERAS. The id is the primary key of the grades table, and name and is_active are attributes. This table is referenced by textbooks, stocks, allocation plans, school supplies, and teacher guides.

---

### Table 3.4 Book Names Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null |
| is_active | Boolean | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the Book Names table, there will be attributes such as id, name, and is_active. The Book Names table has necessary information for subject or book titles. The id is the primary key of the book_names table, and name and is_active are attributes. Book names are linked to textbooks, stocks, allocation plans, and teacher guides through book_name_id.

---

### Table 3.5 Categories Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| slug | Varchar (255) | Not Null, Unique |
| name_en | Varchar (255) | Not Null |
| name_mm | Varchar (255) | Not Null |
| is_active | Boolean | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the Categories table, there will be attributes such as id, slug, name_en, name_mm, and is_active. The Categories table has necessary information for book-type categories used in grade–book mapping. The id is the primary key of the categories table, and slug, name_en, name_mm, and is_active are attributes.

---

### Table 3.6 Grade Book Names Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| grade_id | Bigint | Foreign Key, Not Null |
| book_name_id | Bigint | Foreign Key, Not Null |
| category_id | Bigint | Foreign Key, Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The grade_book_names table has six attributes such as id, grade_id, book_name_id, category_id, created_at, and updated_at. The id is the primary key. The fields grade_id, book_name_id, and category_id are foreign keys that reference grades, book_names, and categories. This table links a grade, a book name, and a category for valid mapping in DERAS.

---

### Table 3.7 Quotas Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The quotas table has attributes such as id, academic_year_id, and township_id. The id is the primary key. The fields academic_year_id and township_id are foreign keys. One quota record belongs to one academic year and one township. Admin can enter student quotas for each township in the selected year.

---

### Table 3.8 Quota Lines Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| quota_id | Bigint | Foreign Key, Not Null |
| school_level | Varchar (20) | Not Null |
| ownership | Varchar (20) | Not Null |
| quantity | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The quota_lines table has attributes such as id, quota_id, school_level, ownership, and quantity. The id is the primary key. The field quota_id is a foreign key that references the quotas table. School level values include primary, middle, high, and agriculture. Ownership values include public, monk, and private. This header–detail design keeps the database normalized.

---

### Table 3.9 School Counts Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Null |
| school_count | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The school_counts table has attributes such as id, academic_year_id, grade_id, township_id, and school_count. The id is the primary key. The fields academic_year_id, grade_id, and township_id are foreign keys used in supply-related calculations. Admin uses school_count values when preparing school supply allocations.

---

### Table 3.10 Allocation Plans Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| book_name_id | Bigint | Foreign Key, Not Null |
| sequence_no | Integer | Not Null |
| received_books | Integer | Not Null |
| books_per_package | Integer | Not Null |
| ratio | Decimal (12,4) | Not Null |
| eligible_students_total | Integer | Not Null |
| allocated_books_total | Integer | Not Null |
| student_count_total | Integer | Not Null |
| transferable_books_total | Integer | Not Null |
| available_total | Integer | Not Null |
| surplus_shortage_total | Integer | Not Null |
| remark | Text | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The allocation_plans table has attributes such as id, academic_year_id, grade_id, book_name_id, sequence_no, received_books, books_per_package, and related total fields. The id is the primary key. The fields academic_year_id, grade_id, and book_name_id are foreign keys. This table supports textbook planning before township distribution in DERAS.

---

### Table 3.11 Allocation Plan Townships Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| allocation_plan_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Not Null |
| previous | Integer | Not Null |
| total_students | Integer | Not Null |
| transferable | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The allocation_plan_townships table has attributes such as id, allocation_plan_id, township_id, previous, total_students, and transferable. The id is the primary key. The fields allocation_plan_id and township_id are foreign keys. This table stores township-level plan details and keeps the design normalized.

---

### Table 3.12 Textbooks Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| book_name_id | Bigint | Foreign Key, Not Null |
| books_per_set | Integer | Not Null |
| student_count | Integer | Not Null |
| book_count | Varchar (255) | Null |
| remark | Varchar (255) | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The textbooks table has attributes such as id, academic_year_id, township_id, grade_id, book_name_id, books_per_set, student_count, book_count, and remark. The id is the primary key. The fields academic_year_id, township_id, grade_id, and book_name_id are foreign keys. A unique combination of year, township, grade, and book name prevents duplicate rows. Admin uses this table to record regular textbook distribution.

---

### Table 3.13 Stocks Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| book_name_id | Bigint | Foreign Key, Not Null |
| previous_balance | Integer | Not Null |
| transferred | Integer | Not Null |
| enrolled_need | Integer | Not Null |
| required_qty | Integer | Not Null |
| remark | Varchar (255) | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The stocks table has attributes such as id, academic_year_id, township_id, grade_id, book_name_id, previous_balance, transferred, enrolled_need, required_qty, and remark. The id is the primary key. The related id fields are foreign keys used to track stock needs by location and book. Admin can view and manage stock records to support allocation decisions.

---

### Table 3.14 Previous Year Balances Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| book_name_id | Bigint | Foreign Key, Not Null |
| balance | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The previous_year_balances table has attributes such as id, academic_year_id, township_id, grade_id, book_name_id, and balance. The id is the primary key. The other id fields are foreign keys. This table stores carry-forward balances and supports rollover and balance lookup in DERAS.

---

### Table 3.15 Teacher Guides Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| book_name_id | Bigint | Foreign Key, Not Null |
| group_no | Integer | Not Null |
| group_title | Text | Not Null |
| guide_type | Varchar (255) | Not Null |
| sequence_no | Integer | Not Null |
| kg_to_g12_quota | Integer | Not Null |
| g1_to_g5_quota | Integer | Not Null |
| total_quota | Integer | Not Null |
| remark | Text | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The teacher_guides table has attributes such as id, academic_year_id, grade_id, book_name_id, group_no, group_title, guide_type, sequence_no, kg_to_g12_quota, g1_to_g5_quota, total_quota, and remark. The id is the primary key. The fields academic_year_id, grade_id, and book_name_id are foreign keys. This table stores teacher-guide receipt quotas for DERAS.

---

### Table 3.16 Teacher Guide Township Allocations Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| teacher_guide_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Not Null |
| kg_g12_qty | Integer | Not Null |
| g1_g5_qty | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The teacher_guide_township_allocations table has attributes such as id, teacher_guide_id, township_id, kg_g12_qty, and g1_g5_qty. The id is the primary key. The fields teacher_guide_id and township_id are foreign keys. This table stores township distribution quantities for each teacher guide.

---

### Table 3.17 Teacher Guide Issues Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| teacher_guide_id | Bigint | Foreign Key, Not Null |
| academic_year_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| book_name_id | Bigint | Foreign Key, Not Null |
| group_no | Integer | Not Null |
| group_title | Text | Not Null |
| guide_type | Varchar (255) | Not Null |
| sequence_no | Integer | Not Null |
| district_unit | Integer | Not Null |
| package_unit | Integer | Not Null |
| remark | Text | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The teacher_guide_issues table has attributes such as id, teacher_guide_id, academic_year_id, grade_id, book_name_id, district_unit, package_unit, and related fields. The id is the primary key. The field teacher_guide_id is a foreign key that links the issue to the teacher-guide family. Admin uses this table when recording teacher-guide issues.

---

### Table 3.18 Teacher Guide Issue Townships Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| teacher_guide_issue_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Not Null |
| issued_quantity | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The teacher_guide_issue_townships table has attributes such as id, teacher_guide_issue_id, township_id, and issued_quantity. The id is the primary key. The fields teacher_guide_issue_id and township_id are foreign keys. This table stores issued quantities by township for each issue record.

---

### Table 3.19 Teacher Guide Summaries Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| teacher_guide_id | Bigint | Foreign Key, Not Null |
| academic_year_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| book_name_id | Bigint | Foreign Key, Not Null |
| group_no | Integer | Not Null |
| group_title | Text | Not Null |
| guide_type | Varchar (255) | Not Null |
| sequence_no | Integer | Not Null |
| previous_balance | Integer | Null |
| fiscal_year_quota | Integer | Null |
| total_books | Integer | Null |
| distributed_books | Integer | Null |
| remaining_books | Integer | Null |
| remark | Text | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The teacher_guide_summaries table has attributes such as id, teacher_guide_id, previous_balance, fiscal_year_quota, total_books, distributed_books, and remaining_books. The id is the primary key. The field teacher_guide_id is a foreign key that keeps summary data linked to the same guide family. Admin uses this table to review distributed and remaining teacher-guide totals.

---

### Table 3.20 School Supply Items Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null, Unique |
| rate | Varchar (255) | Null |
| is_active | Boolean | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the School Supply Items table, there will be attributes such as id, name, rate, and is_active. The School Supply Items table has necessary information for supply item names and rates. The id is the primary key of the school_supply_items table, and name, rate, and is_active are attributes. The rate is used when calculating allocation quantity.

---

### Table 3.21 School Supply Allocations Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Null |
| school_supply_item_id | Bigint | Foreign Key, Not Null |
| region | Varchar (255) | Null |
| row_type | Enum | Not Null |
| row_label | Varchar (255) | Null |
| school_count | Integer | Not Null |
| quantity | Integer | Not Null |
| remark | Varchar (255) | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The school_supply_allocations table has attributes such as id, academic_year_id, grade_id, township_id, school_supply_item_id, school_count, quantity, and related row fields. The id is the primary key. The related id fields are foreign keys. Quantity is commonly calculated as rate × school count. Admin uses this table to manage school supply allocations in DERAS.

---

### Table 3.22 Supply Items Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null, Unique |
| is_active | Boolean | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the Supply Items table, there will be attributes such as id, name, and is_active. The Supply Items table has necessary information for issue item names used in supply detail records. The id is the primary key of the supply_items table, and name and is_active are attributes.

---

### Table 3.23 Supply Details Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| supply_item_id | Bigint | Foreign Key, Not Null |
| sequence_no | Integer | Not Null |
| unit | Integer | Not Null |
| issued_total | Integer | Not Null |
| package_count | Integer | Not Null |
| loose_count | Integer | Not Null |
| remark | Varchar (255) | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The supply_details table has attributes such as id, academic_year_id, township_id, grade_id, supply_item_id, unit, issued_total, package_count, and loose_count. The id is the primary key. The related id fields are foreign keys. Admin uses this table to record issued supply totals in DERAS.

---

### Table 3.24 Company Contacts Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| company_name | Varchar (255) | Not Null |
| lot | Varchar (255) | Null |
| responsible_name | Varchar (255) | Not Null |
| phone | Varchar (255) | Null |
| is_active | Boolean | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The company_contacts table has attributes such as id, company_name, lot, responsible_name, phone, and is_active. The id is the primary key. Company name, lot, responsible name, and phone are attributes of this table. Admin can manage company contact information used in related workflows.

---

### Table 3.25 Roles Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null |
| slug | Varchar (255) | Not Null, Unique |
| description | Varchar (255) | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the Roles table, there will be attributes such as id, name, slug, and description. The Roles table has necessary information for system roles such as Super Admin and Admin. The id is the primary key of the roles table, and name, slug, and description are attributes.

---

### Table 3.26 Permissions Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null |
| slug | Varchar (255) | Not Null, Unique |
| group | Varchar (255) | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the Permissions table, there will be attributes such as id, name, slug, and group. The Permissions table has necessary information for access rights in DERAS. The id is the primary key of the permissions table, and name, slug, and group are attributes. Permissions control viewing, managing, deleting records, managing users, and academic-year rollover.

---

### Table 3.27 Role Permission Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| role_id | Bigint | Foreign Key, Not Null |
| permission_id | Bigint | Foreign Key, Not Null |

The role_permission table has attributes such as id, role_id, and permission_id. The id is the primary key. The fields role_id and permission_id are foreign keys. This intersect table resolves the many-to-many relationship between roles and permissions in DERAS.

---

### Table 3.28 Users Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null |
| email | Varchar (255) | Not Null, Unique |
| role | Enum | Not Null |
| role_id | Bigint | Foreign Key, Null |
| password | Varchar (255) | Not Null |
| email_verified_at | Timestamp | Null |
| remember_token | Varchar (100) | Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

In the Users table, there will be attributes such as id, name, email, role, role_id, password, email_verified_at, and remember_token. The Users table has necessary information for Admin and Super Admin accounts in DERAS. The id is the primary key of the users table. The field role_id is a foreign key that references roles. Email and password are used for login, and role-based permissions decide which modules the user can open.
