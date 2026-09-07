# DERAS 13-Table ERD Explanation

## Entity Relationship Diagram Overview

Figure 3.5 shows the Entity Relationship Diagram (ERD) of DERAS and explains how the 13 tables work together. The tables are divided into master-data tables, allocation and distribution tables, and user-management tables. Primary keys identify each record, while foreign keys link related records between tables.

## Table Descriptions

### Academic Year
The `academic_year` table stores academic-year information, such as the year name and current-year status. It is used by quota, allocation, and school-supply records.

### Township
The `township` table stores township names used for resource distribution. A township can be linked to many allocation, school-supply, and user records.

### School Level
The `school_level` table stores education-level categories, such as primary, middle, or high school. It is linked to the grade table.

### Grade
The `grade` table stores individual grade or class information. The `school_level_id` field links each grade to its related school level.

### Book Name
The `book_name` table stores textbook or subject titles. The `grade_id` field links each book name to the grade for which it is used.

### Quota
The `quota` table stores student quantities used for planning resource allocation. It links the quota record to an academic year, school level, and grade.

### Allocation Plan
The `allocation_plan` table stores the main textbook allocation plan, including the academic year and number of books received.

### Allocation Plan Township
The `allocation_plan_township` table stores the detailed allocation quantity for each township. It links an allocation plan, township, and textbook record.

### Textbook
The `textbook` table stores textbook information, such as the related book name, books per set, and student count. It supports textbook distribution planning.

### Teacher Guide
The `teacher_guide` table stores teacher-guide information, including the related book name, guide type, and total quota.

### School Supply
The `school_supply` table stores school-supply allocation records by academic year, township, and grade. It records the supply name and allocated quantity.

### Role
The `role` table stores system roles, such as Super Admin and Admin. It is used to control access to system functions.

### User
The `user` table stores staff account information, including name, email, role, and township assignment. The `role_id` field connects each user to a system role.
