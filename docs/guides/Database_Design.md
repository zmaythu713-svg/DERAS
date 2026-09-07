# Database Design — District Education Resource Allocation System (DERAS)

## 3.x Database Design

The database of DERAS was designed using a relational model and implemented with MySQL. Each main business concept is stored in its own table, and related records are linked through primary keys and foreign keys. This design supports data consistency, reduces duplication, and allows the system to retrieve allocation and distribution information efficiently. The schema is organized into several groups: master data, student quotas, textbook allocation and stock, school supplies, teacher guides, and user authentication.

### Master Data Tables

Master data tables store shared reference values used across the system.

- **academic_years** stores school-year records, including year range fields and flags for the current and active year.
- **townships** stores township names used in quota, allocation, and distribution modules.
- **grades** stores grade or class levels.
- **book_names** stores subject or book titles.
- **categories** stores book-type categories.
- **grade_book_names** links a grade, a book name, and a category so the system can map valid grade–book combinations.
- **company_contacts** stores supplier or company contact information used in related workflows.
- **school_counts** stores the number of schools by academic year, grade, and township for supply-related calculations.

### Student Quota Tables

Student quota data is stored in a header–detail structure.

- **quotas** stores one quota header per academic year and township.
- **quota_lines** stores the detailed quantities for each quota, including school level (primary, middle, high, agriculture) and ownership type (public, monk, private).

This normalized design avoids wide fixed columns and makes quota reporting more flexible.

### Textbook Allocation and Stock Tables

Textbook planning and distribution use the following tables.

- **allocation_plans** stores the plan header for a given academic year, grade, and book name, including received books and books-per-package values.
- **allocation_plan_townships** stores township-level plan details such as previous balance, total students, and transferable quantity.
- **textbooks** stores regular textbook distribution records by academic year, township, grade, and book name.
- **stocks** stores extra or stock distribution records with previous balance, transferred quantity, and required quantity.
- **previous_year_balances** stores carry-forward balances used when a new academic year starts.

Unique constraints are applied on key combinations (for example, academic year + township + grade + book name) so duplicate distribution rows are avoided.

### School Supply Tables

School supply management is divided into item definitions and allocation or issue records.

- **school_supply_items** stores supply item names and rates.
- **school_supply_allocations** stores allocated quantities by academic year, grade, township, and supply item.
- **supply_items** stores issue item names.
- **supply_details** stores issued quantities and related unit information by academic year, township, grade, and supply item.

### Teacher Guide Tables

Teacher guide processing follows a linked workflow: receipt, township allocation, issue, and summary.

- **teacher_guides** stores receipt quota data by academic year, grade, book name, and guide type.
- **teacher_guide_township_allocations** stores township distribution quantities linked to each teacher guide record.
- **teacher_guide_issues** stores issue headers linked to the related teacher guide.
- **teacher_guide_issue_townships** stores township-level issued quantities for each issue.
- **teacher_guide_summaries** stores previous balance, fiscal-year quota, and distributed totals linked to the teacher guide family.

Foreign keys such as `teacher_guide_id` keep these related records connected as one family of data.

### Authentication and Access Control Tables

User access is managed with role-based tables.

- **users** stores login accounts (name, email, password) and links each user to a role through `role_id`.
- **roles** stores system roles such as Admin and Super Admin.
- **permissions** stores named access rights.
- **role_permission** is the pivot table that assigns permissions to roles.

This structure allows the system to control module access according to each user’s role.

### Relationships and Design Approach

Most relationships in DERAS are one-to-many. For example, one academic year can have many quotas, allocation plans, textbook records, and supply allocations. One township can appear in many distribution and allocation records. Header tables such as `quotas`, `allocation_plans`, and `teacher_guide_issues` relate to detail tables through foreign keys.

The database design follows normalization principles. Township-specific values and quota breakdowns were moved into separate child tables instead of storing many fixed columns in one wide table. As a result, the schema is easier to maintain, supports clearer reporting, and keeps referential integrity through foreign key constraints.

Overall, the DERAS database design provides a stable foundation for master data management, student quota calculation, textbook and supply allocation, teacher guide distribution, and secure user authentication.
