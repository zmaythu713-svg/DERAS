# Database Design — Normalization to 3NF (DERAS)

## What is Normalization?

Normalization is the process of organizing database tables to reduce data duplication and keep data consistent. In DERAS, normalization was applied so that each fact is stored in one clear place and related records are linked by primary keys and foreign keys.

The common normal forms used in relational design are:

- **1NF (First Normal Form):** Each column holds atomic (single) values, and there are no repeating groups in one row.  
- **2NF (Second Normal Form):** The table is in 1NF, and every non-key attribute depends on the whole primary key (no partial dependency).  
- **3NF (Third Normal Form):** The table is in 2NF, and non-key attributes do not depend on other non-key attributes (no transitive dependency).

DERAS was designed to satisfy **Third Normal Form (3NF)**.

---

## Why 3NF Matters in DERAS

Without normalization, the system would store the same township names, book titles, or role names in many places. That would make updates difficult and increase the chance of errors. With 3NF:

1. Master data (year, township, grade, book, role) is stored once.  
2. Operational tables only store foreign keys and transaction values.  
3. Header–detail tables avoid wide repeating columns.  
4. Reports stay consistent because related rows share the same reference ids.

---

## How DERAS Reaches 3NF

### 1. Master tables remove repeated descriptive data (supports 1NF–3NF)

Descriptive attributes such as township name, grade name, and book title are stored in separate master tables:

- `academic_years`  
- `townships`  
- `grades`  
- `book_names`  
- `categories`  
- `roles`  
- `permissions`  
- `school_supply_items`  
- `supply_items`  

Operational tables such as `textbooks`, `stocks`, `quotas`, and `teacher_guides` store only foreign keys (for example `academic_year_id`, `township_id`, `grade_id`, `book_name_id`) plus quantity fields.  
This removes transitive dependency: quantity data depends on the transaction row, while names depend on master tables.

**Example**

| Before (not normalized) | After (3NF style) |
|---|---|
| textbooks stores year name, township name, grade name, book title in every row | textbooks stores `academic_year_id`, `township_id`, `grade_id`, `book_name_id` and joins master tables |

---

### 2. Header–detail structure removes repeating groups (supports 1NF and 2NF)

#### Student quotas

Earlier design ideas often put many fixed columns in one quota row (primary_public, primary_monk, middle_public, …). That creates repeating groups and makes the table hard to extend.

**Normalized design in DERAS**

- `quotas` stores one header per academic year and township (`academic_year_id`, `township_id`).  
- `quota_lines` stores each quantity line (`school_level`, `ownership`, `quantity`) with `quota_id` as foreign key.

This satisfies 1NF (no repeating group columns) and keeps each quantity dependent on its own line primary key.

#### Allocation plans

Township values are not stored as many fixed columns (for example myanaung_students, kyankhin_students, …).

**Normalized design**

- `allocation_plans` = plan header  
- `allocation_plan_townships` = one row per township (`allocation_plan_id`, `township_id`, `previous`, `total_students`, `transferable`)

#### Teacher guides

Township quantities are stored in `teacher_guide_township_allocations`, not as many township columns inside `teacher_guides`. Issue quantities by township are stored in `teacher_guide_issue_townships`.

---

### 3. Many-to-many relationships use an intersect table (supports 3NF)

Roles and permissions have a many-to-many relationship:

- One role can have many permissions.  
- One permission can belong to many roles.

This is resolved with the junction table `role_permission`:

- `role_permission.role_id` → `roles.id`  
- `role_permission.permission_id` → `permissions.id`

Users then reference a role through `users.role_id`. Permission names are not copied into the users table, so there is no transitive dependency from user → permission name.

---

### 4. Each non-key attribute depends on the key (2NF / 3NF check)

Examples from DERAS:

| Table | Primary Key | Non-key attributes depend on |
|---|---|---|
| quotas | id | academic_year_id, township_id (header facts) |
| quota_lines | id | quota_id, school_level, ownership, quantity |
| textbooks | id | year/township/grade/book FKs + books_per_set, student_count |
| allocation_plan_townships | id | allocation_plan_id, township_id + previous, total_students, transferable |
| users | id | name, email, password, role_id |
| role_permission | id | role_id, permission_id |

No table stores another table’s descriptive name as a dependent non-key field when a foreign key can be used instead.

---

## 3NF Summary for DERAS

| Normal Form | Applied in DERAS |
|---|---|
| **1NF** | Atomic fields; no repeating township/quota columns in one row |
| **2NF** | Detail quantities depend on detail-row keys (`quota_lines`, `allocation_plan_townships`) |
| **3NF** | Descriptive attributes live in master tables; operational tables use FKs; roles/permissions use junction table |

Overall, the DERAS database design follows **Third Normal Form (3NF)**. This normalized design reduces redundancy, improves update consistency, and supports clear reporting for quota planning, textbook allocation, school supplies, teacher-guide distribution, and secure user access.

---

## Short paragraph (for thesis insert)

The DERAS database was designed using relational normalization up to Third Normal Form (3NF). Master data such as academic years, townships, grades, book names, roles, and permissions is stored in separate tables. Operational tables keep only foreign keys and transaction values. Repeating groups were removed by using header–detail tables such as `quotas` with `quota_lines`, and `allocation_plans` with `allocation_plan_townships`. The many-to-many link between roles and permissions is handled by the intersect table `role_permission`. As a result, each non-key attribute depends on the primary key and not on other non-key attributes, which satisfies 3NF and keeps DERAS data consistent and easier to maintain.
