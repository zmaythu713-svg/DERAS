# DERAS — Project Overview and Architecture Text (Academic, B2)

Use or adapt these paragraphs for the report. Module names match the implemented DERAS application.

---

## District Education Resource Allocation System — Project Overview

The District Education Resource Allocation System (DERAS) is an internal web application used to plan, allocate, and record the distribution of educational materials across townships. The main resources covered by the system are textbooks, teacher guides and handbooks, and school supplies. The work of this project focused on relational database design and the development of the core application modules, including Basic Data Management, Student Quota Calculation, Resource Allocation, Distribution Recording, Reporting, and User Management. For each module, the database tables, business rules, and application screens were designed and tested so that related records stay accurate and consistent as data moves from one stage to the next.

---

## Figure 3.2: System Architecture of the Resource Allocation System

Figure 3.2 presents the system architecture of DERAS. The workflow starts with basic organizational data such as academic years, townships, grades, subjects, and book names. This information supports student quota entry by township and school level. Using those quotas and related allocation rules, the system helps calculate how many textbooks and other materials each township should receive.

After allocation plans are prepared, the textbook and stock modules record regular and extra distribution quantities, including previous balances where needed. The school-supply modules calculate supply amounts and record issue details. The teacher-guide modules follow a linked sequence of receipt, township distribution, issue, and summary. The reporting features collect information from these modules for dashboard viewing and Excel export. User Management applies role-based access control so that daily allocation work is available to Admin users, while higher-level actions—such as managing admin accounts, deleting selected records, and academic-year rollover—are limited to Super Admin.

*Figure 3.2. System architecture of the District Education Resource Allocation System (DERAS).*

---

## Figure 3.3: Module Overview of the Resource Allocation System

Figure 3.3 shows the main modules of DERAS and how they relate to one another.

1. **Basic Data Management**  
   Maintains academic years, townships, grades, book names, grade–subject mapping, and company contacts used by later modules.

2. **Student Quota Calculation**  
   Records student numbers by township and school level so allocation amounts can be planned.

3. **Resource Allocation**  
   Covers allocation plans for textbooks and allocation calculations for school supplies and teacher-guide quotas.

4. **Distribution Recording**  
   Stores textbook lists, extra stock distribution, school-supply issues, and teacher-guide distribution and issue records.

5. **Reporting**  
   Provides dashboard views and Excel export from supported list screens for official record keeping.

6. **User Management**  
   Controls login accounts and permissions for Admin and Super Admin roles.

Together, these modules form a complete path from reference data and quota entry to allocation, distribution, and reporting within one academic year.

*Figure 3.3. Module overview of the District Education Resource Allocation System (DERAS).*
