# Role Table Data Dictionary

## Role Table

| Column Name | Data Type | Key Constraints |
|---|---|---|
| id | Bigint | Primary Key |
| name | Varchar (255) | Not Null, Unique |
| slug | Varchar (255) | Not Null, Unique |
| created_at | Timestamp | Null |
| updated_at | Timestamp | Null |

The `role` table stores system roles, such as **Super Admin** and **Admin**. The `id` field is the primary key. The `user.role_id` foreign key links each user account to one role and supports role-based access control in DERAS.
