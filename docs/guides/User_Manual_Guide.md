# User Manual Guide
District Education Resource Allocation System (DERAS)
University of Computer Studies, Hinthada (UCSH)

## 1. Introduction

**Purpose of the System:**  
DERAS helps district education offices plan, calculate, and record the allocation and distribution of textbooks, teacher guides / handbooks, and school supplies by academic year, township, and grade.

**Scope:**  
Only authorized staff can use the system after login. Access depends on role (Super Admin or Admin).

**Audience:**
- Super Admin (system owner / IT or district lead)
- Admin (daily data-entry staff)

## 2. System Requirements

- **Web Browser:** Google Chrome, Microsoft Edge, or Firefox (latest version)
- **Internet / Network:** Local network or internet as configured for the server
- **Device Support:** Desktop / Laptop (recommended); tablet usable for viewing

## 3. Getting Started

1. Open the system URL in a browser (local Laragon example: http://deras.test or http://localhost/DERAS/public).
2. The Login page appears.
3. Enter the email and password provided by the administrator.
4. After successful login, the Dashboard opens with the left sidebar menu.

## 4. User Roles

### 4.1 Admin (daily work)

**Login required.** Credentials are created by Super Admin.

**Features:**
- View Dashboard
- Create and edit records for textbooks, stocks, quotas, school supplies, teacher guides, and master data
- Export lists to Excel where the button is available
- Update own profile and password

**Restrictions:**
- Cannot manage admin users
- Cannot delete records (requires delete permission)
- Cannot run academic-year rollover

**Steps:**
1. Open the Login page.
2. Enter Email and Password.
3. Click Login.
4. Use the sidebar to open modules.

### 4.2 Super Admin (system owner)

**Login required.** Full access including Admin features, plus:
- Manage Admin Users
- Delete records (where allowed)
- Academic year Rollover

## 5. Main Menu (Sidebar)

| Menu | Purpose |
| --- | --- |
| Dashboard | Home overview after login |
| Textbooks → Allocation Plan | Textbook quota / allocation calculation |
| Textbooks → Regular Distribution | Regular textbook distribution list |
| Textbooks → Extra Distribution (Stock) | Extra / stock distribution list |
| Student Quota Calculation | Student headcount / quota calculation |
| School Supplies → Allocation | School supply allocation |
| School Supplies → Issue Details | School supply issue / delivery details |
| Teacher Guides → Receipt | Teacher guide / handbook receipt |
| Teacher Guides → Distribution Quota | Distribution quota by township |
| Teacher Guides → Issue List | Issue / distribution list |
| Teacher Guides → Summary | Summary register |
| Master Data | Townships, academic years, grades, subjects, grade–subject map, companies |

**Top-right user menu:** Profile, Change Password, Admin Users (Super Admin only), Log Out.

## 6. Common Workflows

### 6.1 Master data first

Before using allocation modules, ensure these exist under Master Data:
1. Academic years — set the current year if needed
2. Townships
3. Grades
4. Book names / subjects
5. Grade–subject mapping
6. Company contacts — if used for printing / delivery notes

### 6.2 Add or edit a record

1. Open the module from the sidebar.
2. Use filters (academic year, township, grade, etc.) if shown.
3. Click Create / Add.
4. Fill the required fields. Some quantities auto-fill from related data (for example school count or previous balance).
5. Click Save.
6. To change a row, open Edit, update, then Save.

### 6.3 Export to Excel

On list pages that show Export Excel:
1. Apply filters (for example academic year).
2. Click Export Excel.
3. The browser downloads an .xlsx file for record keeping or printing preparation.

### 6.4 Academic year rollover (Super Admin only)

1. Open Academic Years.
2. Use Rollover when starting a new academic year (follow your district process).
3. Confirm carefully — this affects year-based data carry-forward.

### 6.5 Manage users (Super Admin only)

1. Open the user menu → Admin Users.
2. Create Admin accounts (email + password + role).
3. Edit accounts as needed according to office policy.

## 7. System Features

- Role-based login (Super Admin / Admin)
- Dashboard and sidebar navigation
- Textbook allocation plan, distribution, and stock modules
- Student quota calculation
- School supply allocation and issue
- Teacher guide receipt → distribution → issue → summary flow
- Master data maintenance (township, year, grade, subject)
- Excel export on supported list screens
- Profile and password change
- Pagination and filters on index pages

## 8. Troubleshooting

| Problem | What to try |
| --- | --- |
| Login failed | Check email/password; contact Super Admin for reset |
| Menu item missing / forbidden | Your role may not allow that action |
| Auto-fill empty (quantity / balance) | Check related master data and previous-year / school-count records |
| Excel button does nothing | Allow downloads in the browser; retry after the page fully loads |
| Page not loading / styles broken | Refresh; ask the installer to run npm run build and clear cache |
| Cannot delete a record | Only Super Admin (delete permission) can delete |

## 9. Example seeded accounts (local / demo only)

Change these passwords after first login in production.

| Role | Email | Password |
| --- | --- | --- |
| Super Admin | super@email.com | password |
| Admin | admin@email.com | password |
