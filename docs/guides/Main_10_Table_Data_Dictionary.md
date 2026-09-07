# Main Tables — Data Dictionary

## Table 3.1 Academic Year Table

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

The Academic Year table stores the school years used throughout DERAS. The `id` field is the primary key, while `name`, `start_year`, and `end_year` identify the period. The `is_current` and `is_active` fields control the current working year and whether data entry is allowed.

## Table 3.2 Township Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null |
| is_active | Boolean | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The Township table stores the township names used in allocation and distribution work. Its `id` field is the primary key. Other tables refer to this table when a quota, allocation, stock, or issue record must be associated with a township.

## Table 3.3 School Level Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (100) | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The School Level table stores broad education levels, such as primary, middle, high, and agriculture. The `id` field is the primary key. School levels are used to organize grades and student quota information.

## Table 3.4 Grade Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| school_level_id | Bigint | Foreign Key, Not Null |
| name | Varchar (255) | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The Grade table stores individual class or grade levels. The `id` field is the primary key, and `school_level_id` is a foreign key linked to the School Level table. This relationship shows which school level contains each grade.

## Table 3.5 Book Name Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| grade_id | Bigint | Foreign Key, Not Null |
| name | Varchar (255) | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The Book Name table stores textbook and subject titles. The `id` field is the primary key, while `grade_id` connects each title to its grade. Book names are then used by textbook, allocation-plan, and teacher-guide records.

## Table 3.6 Quota Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| school_level_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| student_quantity | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The Quota table stores the number of students used for allocation planning. The `id` field is the primary key. The academic year, school level, and grade fields are foreign keys, which ensure that every quota belongs to the correct year and education level.

## Table 3.7 Allocation Plan Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| book_name_id | Bigint | Foreign Key, Not Null |
| received_books | Integer | Not Null |
| books_per_package | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The Allocation Plan table stores the planned textbook quantities for an academic year. The `id` field is the primary key. It records the book title, total books received, and package size before shares are assigned to townships.

## Table 3.8 Textbook Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| book_name_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| books_per_set | Integer | Not Null |
| student_count | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The Textbook table stores textbook information used during distribution. The `id` field is the primary key. The book and grade foreign keys identify the material, while `books_per_set` and `student_count` support the calculation of required quantities.

## Table 3.9 Teacher Guide Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| book_name_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| guide_type | Varchar (255) | Not Null |
| total_quota | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The Teacher Guide table stores teacher-guide details and planned quantities. The `id` field is the primary key. It links to a book name and grade and records the guide type and total quota for later township allocation.

## Table 3.10 School Supply Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| academic_year_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Not Null |
| grade_id | Bigint | Foreign Key, Not Null |
| name | Varchar (255) | Not Null |
| rate | Decimal (10,2) | Not Null |
| quantity | Integer | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The School Supply table stores school-supply allocation records. The `id` field is the primary key. Academic year, township, and grade are foreign keys. The `rate`, `school_count`, and `quantity` fields support the calculation and recording of supplies for each township.

## Table 3.11 User Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| role_id | Bigint | Foreign Key, Not Null |
| township_id | Bigint | Foreign Key, Null |
| name | Varchar (255) | Not Null |
| email | Varchar (255) | Not Null, Unique |
| password | Varchar (255) | Not Null |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The User table stores staff accounts that can access DERAS. The `id` field is the primary key. The `role_id` foreign key assigns each account a system role, such as Super Admin or Admin. The optional `township_id` foreign key identifies a township associated with the account. The `email` and `password` fields are used during login; passwords must be stored as hashes rather than plain text.