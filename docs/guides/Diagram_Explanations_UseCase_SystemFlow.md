# Diagram Explanations — DERAS (Academic Tone, B2 Level)

---

## Figure 3.1: System Flow Diagram

Figure 3.1 shows the system flow diagram of DERAS. Before login, a user can only open the login page and cannot use any working module. Admin and Super Admin must sign in successfully. After login, work follows a clear order: open the dashboard, prepare basic data, enter student quotas, create allocation plans, record textbook and stock distribution, manage school supplies, complete teacher-guide work from receipt to summary, update profiles, and export files when the system allows it.

The system flow diagram explains how work moves through the District Education Resource Allocation System. It shows the main steps in sequence: login, basic data setup, student quota entry, allocation planning, textbook and stock recording, school-supply processing, teacher-guide distribution, and report export. The system checks each record using the related academic year, township, grade, and item. This helps keep quantities and distribution lists correct. Finished records are saved in the database and can be viewed or exported later.

Because Admin and Super Admin do not have the same level of control, their flows are described separately below.

### Figure 3.1 (a): Admin System Flow

For Admin, the process starts when the administrator logs in successfully. Next, Admin can manage basic data such as academic years, townships, grades, book names, and company contacts. Student quotas are then entered by township. After that, allocation plans are created so township shares can be calculated, and textbook or stock records can be added. School-supply allocations and issue details are recorded next. For teacher guides, Admin records received quotas, distributes them to townships, issues the materials, and updates the summary. Admin can also update a personal profile and password and export selected lists when the feature is available.

Overall, the Admin flow focuses on day-to-day allocation work. It supports regular office tasks while keeping sensitive actions such as user management, record deletion, and academic-year rollover outside Admin authority.

### Figure 3.1 (b): Super Admin System Flow

For Super Admin, the process also starts with login. After login, Super Admin can perform the same daily steps as Admin: prepare basic data, enter student quotas, create allocation plans, record textbook and stock distribution, manage school supplies, complete teacher-guide work from receipt to summary, update the profile, and export files when allowed.

In addition, Super Admin has extra control steps in the flow. Super Admin can manage admin accounts, delete selected records when removal is required, and move the system to a new academic year through rollover. These higher-level actions appear after or alongside the shared allocation steps, depending on office needs.

Overall, Figure 3.1 presents a clear working order for allocation tasks. The Admin path supports routine recording and planning, while the Super Admin path adds account control, selected deletion, and year rollover. This split helps avoid repeated work, protects sensitive operations, and supports stable use during the academic year.

*Figure 3.1. System flow diagram of the District Education Resource Allocation System (DERAS), with Admin and Super Admin paths.*

---

## Figure 3.2: Use Case Diagram

In Figure 3.2, the Use Case Diagram shows how DERAS works with its two main staff roles: Admin and Super Admin. DERAS does not offer a public guest module; every protected action needs a valid login. This section describes the staff functions shown in the diagram and gives a clear summary of what each role can do inside the system.

The diagram presents the system as a set of use cases linked to actors. An actor is a type of user who interacts with DERAS. A use case is a task that the actor can perform, such as managing quotas, recording textbook distribution, or exporting Excel files. By grouping these tasks under Admin and Super Admin, the diagram makes the boundary of the system easy to understand: only authenticated staff can enter the working modules.

Because the two roles do not have the same authority, their use cases are described separately below.

### Figure 3.2 (a): Admin Use Cases

Admin is the main operator for day-to-day allocation work. After a successful login, Admin can open the dashboard and use the shared office functions of DERAS.

**Admin use cases include:**
- Log in to the system
- View the dashboard
- Manage academic years
- Manage townships
- Manage grades and subjects
- Manage book names
- Manage company contacts
- Manage student quotas
- Manage allocation plans
- Manage textbooks
- Manage stocks
- Manage school supplies
- Manage supply details
- Manage teacher-guide receipt
- Manage teacher-guide distribution
- Manage teacher-guide issues
- Manage teacher-guide summaries
- Export lists to Excel
- Manage profile and password
- Log out of the system

For Admin, these use cases support the regular district office workflow. Admin mainly creates, updates, and views records for basic data, quotas, textbooks, stocks, supplies, and teacher guides. Teacher-guide use cases follow a fixed order: receipt comes first, then distribution, then issues, and finally summaries. Admin cannot manage admin user accounts, delete selected records, or roll over the academic year.

### Figure 3.2 (b): Super Admin Use Cases

Super Admin shares the same daily use cases as Admin and also holds higher control. After login, Super Admin can perform all Admin tasks listed above, including basic-data management, student quotas, allocation plans, textbook and stock recording, school-supply work, teacher-guide processing, Excel export, and profile updates.

**Additional use cases for Super Admin:**
- Manage admin users
- Delete records
- Roll over the academic year

These extra use cases protect sensitive operations under a higher role. Super Admin can create and control admin accounts, remove selected records when deletion is required, and start a new academic year through rollover. This separation keeps routine work efficient for Admin while giving Super Admin clear authority for supervision and system control.

Overall, Figure 3.2 shows that DERAS is a staff-only system with clear role-based responsibilities. The Admin path covers planning and recording, while the Super Admin path adds account control, selected deletion, and year rollover. This design helps keep records consistent, reduces unauthorized changes, and supports clear office supervision within the district education workflow.

*Figure 3.2. Use case diagram of the District Education Resource Allocation System (DERAS), with Admin and Super Admin actors.*
