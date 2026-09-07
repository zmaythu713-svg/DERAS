# 3.3 Entity Relationship Diagram — DERAS (Expanded Thesis Text)

Use this text with figure: `docs/diagrams/images_final/Figure3_ER_Tables_NoIcon.png`

---

## 3.3 Entity Relationship Diagram

An Entity Relationship Diagram, also known as an entity relationship model, is a graphical representation that depicts relationships among people, objects, places, concepts, or events within an Information Technology (IT) system. An ERD uses data modeling techniques that can help define business processes and serve as the blueprint of a relational database. In DERAS, the ER diagram shows how academic years, townships, grades, books, quotas, allocation plans, textbooks, stocks, school supplies, teacher guides, and user accounts are connected through primary keys and foreign keys.

In relational database design, a primary key identifies a unique record in a table. A foreign key is a field (or set of fields) that refers to the primary key of another table and creates a link between the two tables. Cardinality notation defines how many records in one table can relate to records in another table. The three main relationship types used in DERAS are described below.

**A one-to-one relationship (1:1)** is a link between information in two tables where each record in each table appears only once. For example, one user account is linked to one role assignment at a time through `users.role_id`.

**A one-to-many relationship (1:M)** means a row from one table can have multiple matching rows in another table. This is created using a primary key–foreign key relationship. For example, one academic year can have many quotas, many allocation plans, many textbook records, and many teacher-guide records. One township can appear in many distribution and allocation rows. One quota can have many quota lines.

**A many-to-many relationship (M:N)** is resolved by creating an intersect (junction) table that forms two one-to-many relationships. In DERAS, roles and permissions have a many-to-many relationship. The intersect table is `role_permission`. One role can have many permissions, and one permission can belong to many roles.

---

## Figure 3.5 Entity Relation Diagram of the System

In the database, there are many related data tables. Figure 3.5 shows all main data tables and their relationships in DERAS. Unlike a small inventory system centered on one admin table, DERAS is organized around shared master tables and operational tables. The main master tables are `academic_years`, `townships`, `grades`, `book_names`, and `categories`. Their primary key is `id`. These `id` values are used as foreign keys in quotas, allocation plans, textbooks, stocks, school supplies, and teacher-guide tables to keep referential integrity.

Login and access control are handled by `users`, `roles`, `permissions`, and `role_permission`. Admin and Super Admin accounts are stored in `users`. The foreign key `role_id` links each user to a role. Permissions are assigned to roles through the junction table `role_permission`.

In daily work, authorized staff can open the dashboard, manage basic data, enter student quotas, create allocation plans, record textbook and stock distribution, manage school supplies, complete teacher-guide work from receipt to summary, update profiles, and export Excel reports when the system allows it. Super Admin can also manage admin users, delete selected records, and roll over the academic year.

*Figure 3.5. Entity Relation Diagram of the District Education Resource Allocation System (DERAS).*

---

## Table descriptions (after the ER figure)

### Academic Years table
The `academic_years` table stores school-year records used across the system. It contains fields such as `id`, `name`, `start_year`, `end_year`, `is_active`, `is_current`, and `status`. The field `id` serves as the primary key. The field `name` stores the academic year label (for example, 2025-2026). The fields `is_current` and `is_active` show which year is currently used for data entry.

### Townships table
The `townships` table stores township information. It contains `id`, `name`, and `is_active`. The field `id` is the primary key. The field `name` is the township name used in quota, allocation, and distribution modules.

### Grades table
The `grades` table stores grade or class-level information. It contains `id`, `name`, and `is_active`. The field `id` is the primary key. This table is referenced by textbooks, stocks, allocation plans, school supplies, and teacher guides.

### Book Names table
The `book_names` table stores subject or book titles. It contains `id`, `name`, and `is_active`. The field `id` is the primary key. Book names are linked to textbooks, stocks, allocation plans, and teacher guides through `book_name_id`.

### Categories table
The `categories` table stores book-type categories used for grade–book mapping. It contains `id`, `slug`, `name_en`, `name_mm`, and `is_active`. The field `id` is the primary key. The category name helps classify books in the mapping table.

### Grade Book Names table
The `grade_book_names` table links grades, book names, and categories. It contains `id`, `grade_id`, `book_name_id`, and `category_id`. The field `id` is the primary key. The fields `grade_id`, `book_name_id`, and `category_id` are foreign keys that reference `grades`, `book_names`, and `categories`.

### Quotas table
The `quotas` table stores student quota headers by academic year and township. It contains `id`, `academic_year_id`, and `township_id`. The field `id` is the primary key. The fields `academic_year_id` and `township_id` are foreign keys. One quota record belongs to one year and one township.

### Quota Lines table
The `quota_lines` table stores detailed student quantities for each quota. It contains `id`, `quota_id`, `school_level`, `ownership`, and `quantity`. The field `id` is the primary key. The field `quota_id` is a foreign key that references `quotas`. School level values include primary, middle, high, and agriculture. Ownership values include public, monk, and private. This header–detail design keeps the database normalized.

### School Counts table
The `school_counts` table stores the number of schools by academic year, grade, and township. It contains `id`, `academic_year_id`, `grade_id`, `township_id`, and `school_count`. The field `id` is the primary key. The other id fields are foreign keys used in supply-related calculations.

### Allocation Plans table
The `allocation_plans` table stores textbook allocation plan headers. It contains `id`, `academic_year_id`, `grade_id`, `book_name_id`, `sequence_no`, `received_books`, `books_per_package`, and related total fields. The field `id` is the primary key. The fields `academic_year_id`, `grade_id`, and `book_name_id` are foreign keys. This table supports planning before township distribution.

### Allocation Plan Townships table
The `allocation_plan_townships` table stores township-level details for each allocation plan. It contains `id`, `allocation_plan_id`, `township_id`, `previous`, `total_students`, and `transferable`. The field `id` is the primary key. The fields `allocation_plan_id` and `township_id` are foreign keys. This table replaces wide township-specific columns and keeps the design normalized.

### Textbooks table
The `textbooks` table stores regular textbook distribution records. It contains `id`, `academic_year_id`, `township_id`, `grade_id`, `book_name_id`, `books_per_set`, `student_count`, `book_count`, and `remark`. The field `id` is the primary key. The four related id fields are foreign keys. A unique combination of year, township, grade, and book name prevents duplicate rows.

### Stocks table
The `stocks` table stores extra or stock distribution records. It contains `id`, `academic_year_id`, `township_id`, `grade_id`, `book_name_id`, `previous_balance`, `transferred`, `enrolled_need`, `required_qty`, and `remark`. The field `id` is the primary key. The related id fields are foreign keys used to track stock needs by location and book.

### Previous Year Balances table
The `previous_year_balances` table stores carry-forward balances for a new academic year. It contains `id`, `academic_year_id`, `township_id`, `grade_id`, `book_name_id`, and `balance`. The field `id` is the primary key. The other id fields are foreign keys. This table supports rollover and balance lookup.

### Teacher Guides table
The `teacher_guides` table stores teacher-guide receipt quotas. It contains `id`, `academic_year_id`, `grade_id`, `book_name_id`, `group_no`, `group_title`, `guide_type`, `sequence_no`, `kg_to_g12_quota`, `g1_to_g5_quota`, `total_quota`, and `remark`. The field `id` is the primary key. The fields `academic_year_id`, `grade_id`, and `book_name_id` are foreign keys.

### Teacher Guide Township Allocations table
The `teacher_guide_township_allocations` table stores township distribution quantities for each teacher guide. It contains `id`, `teacher_guide_id`, `township_id`, `kg_g12_qty`, and `g1_g5_qty`. The field `id` is the primary key. The fields `teacher_guide_id` and `township_id` are foreign keys.

### Teacher Guide Issues table
The `teacher_guide_issues` table stores issue headers for teacher guides. It contains `id`, `teacher_guide_id`, `academic_year_id`, `grade_id`, `book_name_id`, `district_unit`, `package_unit`, and related fields. The field `id` is the primary key. The field `teacher_guide_id` links the issue to the teacher-guide family.

### Teacher Guide Issue Townships table
The `teacher_guide_issue_townships` table stores issued quantities by township. It contains `id`, `teacher_guide_issue_id`, `township_id`, and `issued_quantity`. The field `id` is the primary key. The fields `teacher_guide_issue_id` and `township_id` are foreign keys.

### Teacher Guide Summaries table
The `teacher_guide_summaries` table stores summary balances and distributed totals. It contains `id`, `teacher_guide_id`, `previous_balance`, `fiscal_year_quota`, `total_books`, `distributed_books`, and `remaining_books`. The field `id` is the primary key. The field `teacher_guide_id` is a foreign key that keeps summary data linked to the same guide family.

### School Supply Items table
The `school_supply_items` table stores supply item names and rates. It contains `id`, `name`, `rate`, and `is_active`. The field `id` is the primary key. The field `name` is the supply item name. The field `rate` is used when calculating allocation quantity.

### School Supply Allocations table
The `school_supply_allocations` table stores allocated supply quantities. It contains `id`, `academic_year_id`, `grade_id`, `township_id`, `school_supply_item_id`, `school_count`, `quantity`, and related row fields. The field `id` is the primary key. The related id fields are foreign keys. Quantity is commonly calculated as rate × school count.

### Supply Items table
The `supply_items` table stores issue item names used in supply detail records. It contains `id`, `name`, and `is_active`. The field `id` is the primary key.

### Supply Details table
The `supply_details` table stores issued supply totals. It contains `id`, `academic_year_id`, `township_id`, `grade_id`, `supply_item_id`, `unit`, `issued_total`, `package_count`, and `loose_count`. The field `id` is the primary key. The related id fields are foreign keys.

### Company Contacts table
The `company_contacts` table stores company or contact information used in related workflows. It contains `id`, `company_name`, `lot`, `responsible_name`, `phone`, and `is_active`. The field `id` is the primary key.

### Roles table
The `roles` table stores system roles such as Super Admin and Admin. It contains `id`, `name`, `slug`, and `description`. The field `id` is the primary key. The field `slug` uniquely identifies the role in code.

### Permissions table
The `permissions` table stores named access rights. It contains `id`, `name`, `slug`, and `group`. The field `id` is the primary key. Permissions control actions such as viewing records, managing records, deleting records, managing users, and academic-year rollover.

### Role Permission table
The `role_permission` table is the intersect table for roles and permissions. It contains `id`, `role_id`, and `permission_id`. The field `id` is the primary key. The fields `role_id` and `permission_id` are foreign keys. This table resolves the many-to-many relationship between roles and permissions.

### Users table
The `users` table stores login accounts for Admin and Super Admin. It contains `id`, `name`, `email`, `role`, `role_id`, `password`, `email_verified_at`, and `remember_token`. The field `id` is the primary key. The field `role_id` is a foreign key that references `roles`. Email and password are used for login. After successful login, role-based permissions decide which modules the user can open.

---

## System database summary

The DERAS database is designed so that authorized staff can manage district education resources in one unified schema. Through the linked tables, Admin and Super Admin can work with the dashboard, basic data, student quotas, allocation plans, textbooks, stocks, school supplies, teacher guides, and Excel reports. Foreign keys keep relationships clear, and normalized header–detail tables avoid duplicated wide columns. This structure supports accurate recording, reporting, and controlled access during each academic year.

---

## 3.4 Data Dictionary

A data dictionary provides centralized and structured documentation of an organization’s data assets. It helps administrators and developers understand the meaning, relationships, and usage of data elements. A data dictionary is important for database management, data modeling, and software development because it keeps naming and definitions consistent.

In DERAS, the data dictionary describes each table, its fields, primary keys, foreign keys, and the purpose of each attribute. This documentation ensures consistency, clarity, and efficient data management so that everyone working on the system understands how data should be stored, linked, and used.

### Sample data dictionary entries

| Table | Field | Type | Key | Description |
|---|---|---|---|---|
| academic_years | id | bigint | PK | Unique academic year identifier |
| academic_years | name | varchar | UK | Academic year label |
| academic_years | is_current | boolean |  | Marks the current working year |
| townships | id | bigint | PK | Unique township identifier |
| townships | name | varchar |  | Township name |
| quotas | academic_year_id | bigint | FK | References academic_years.id |
| quotas | township_id | bigint | FK | References townships.id |
| quota_lines | quota_id | bigint | FK | References quotas.id |
| quota_lines | quantity | int |  | Student quantity for the line |
| allocation_plans | received_books | int |  | Books received for the plan |
| allocation_plan_townships | transferable | int |  | Transferable books for township |
| textbooks | student_count | int |  | Distributed / student-related quantity |
| stocks | required_qty | int |  | Required stock quantity |
| teacher_guides | guide_type | varchar |  | Type of teacher guide |
| teacher_guide_township_allocations | teacher_guide_id | bigint | FK | References teacher_guides.id |
| school_supply_allocations | quantity | int |  | Allocated supply quantity |
| users | email | varchar | UK | Login email |
| users | role_id | bigint | FK | References roles.id |
| role_permission | role_id | bigint | FK | References roles.id |
| role_permission | permission_id | bigint | FK | References permissions.id |

(Complete field lists for every table can follow the same pattern in the final thesis appendix or continued pages.)

---

## Short caption under Figure 3.5 (optional)

In the database, the main tables are linked by primary keys and foreign keys. Figure 3.5 shows these tables and their relationships. Master tables such as `academic_years`, `townships`, `grades`, and `book_names` provide shared references. Operational tables store quotas, allocation plans, textbooks, stocks, supplies, and teacher-guide records. The `users` table stores login accounts, and `role_id` connects each user to a role for access control.
