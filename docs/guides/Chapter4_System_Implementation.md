# CHAPTER 4  
# SYSTEM IMPLEMENTATION

This chapter explains how the District Education Resource Allocation System (DERAS) was implemented. The backend was developed with **PHP** and the **Laravel** framework, and data was stored in a **MySQL** database. The chapter covers database design choices, table setup, main SQL operations, example controller functions, and the main user interfaces. Code and screenshot figures can be inserted in the marked places.

---

## 4.1 Database Design

Many languages can be used to build web applications. For DERAS, PHP was selected. The main strengths of PHP are summarized below.

- **Open license:** PHP can be obtained and shared without a paid license, which keeps development cost low.  
- **Strong support network:** A wide developer community provides manuals, learning materials, and online discussion spaces when problems arise.  
- **Cross-platform use:** PHP can run on different operating systems, including Windows, Linux, Unix, and macOS.  
- **Growth capacity:** PHP can support rising request volume and larger data sets as the system expands.  
- **Quick response:** PHP can process many requests at the same time, so it suits busy web applications.

In addition:

- PHP works smoothly with front-end technologies such as HTML, CSS, and JavaScript.  
- PHP can connect to several database systems, including MySQL, Oracle, and PostgreSQL, which makes it suitable for data-driven websites.  
- MySQL was used with PHP because it is a stable and commonly adopted database that provides good throughput, room to grow, solid protection features, and smooth compatibility with PHP.

Laravel was used on top of PHP to organize routes, controllers, models, and migrations in a clear structure for DERAS.

### Hardware and Software Requirements

- A computer with enough memory and storage for web development and database work  
- A display suitable for office use (recommended: 1366 × 768 or higher)  
- PHP 8.2 or newer, Composer, MySQL / MariaDB  
- Node.js and npm (for Vite front-end assets)  
- Laragon (local) or Apache / Nginx (server)  
- A modern web browser such as Google Chrome, Microsoft Edge, or Firefox  

In Figure 4.1, a MySQL database was prepared in phpMyAdmin to hold the tables required by DERAS. After the database was ready, it was linked to the application through the environment configuration.

**[Insert Figure: .env database settings]**  
*Figure 4.1 Connect to the database with the system*

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=deras
DB_USERNAME=root
DB_PASSWORD=
```

---

## 4.2 Backend Implementation

In DERAS, the backend handles authentication, database storage, validation, and business logic for quotas, allocation plans, textbooks, school supplies, and teacher guides. The main database operations used in the system are **SELECT**, **INSERT**, **UPDATE**, and **DELETE**. In Laravel, these operations are commonly performed through Eloquent models, while the database structure is created with migrations.

---

### 4.2.1 Database Connection

The database connection settings shown in Figure 4.1 allow the Laravel application to communicate with MySQL. After these values are correct, migrations can create the required tables.

---

### 4.2.2 Creating Main Database Tables

Several tables were created to store master data and allocation records. The examples below show important tables used in DERAS. In each table, `id` is the primary key.

#### Academic Years Table

In Figure 4.2, the `academic_years` table stores the school year names used across the system. The primary key is `id`.

**[Insert Figure: CREATE TABLE academic_years]**  
*Figure 4.2 Create academic_years table*

```sql
CREATE TABLE `academic_years` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Townships Table

In Figure 4.3, the `townships` table stores township names used in quota and distribution modules. The primary key is `id`.

**[Insert Figure: CREATE TABLE townships]**  
*Figure 4.3 Create townships table*

```sql
CREATE TABLE `townships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Users Table

In Figure 4.4, the `users` table holds account details for system operators by role. In the `users` table, the primary key is `id`, which is an integer type.

**[Insert Figure: CREATE TABLE users]**  
*Figure 4.4 Create users table*

```sql
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` enum('super','admin') NOT NULL DEFAULT 'admin',
  `password` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### Textbooks Table

In Figure 4.5, the `textbooks` table stores regular textbook distribution records. The primary key is `id`. The fields `academic_year_id`, `township_id`, `grade_id`, and `book_name_id` are foreign keys linked to related master tables.

**[Insert Figure: CREATE TABLE textbooks]**  
*Figure 4.5 Create textbooks table*

```sql
CREATE TABLE `textbooks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `academic_year_id` bigint(20) UNSIGNED NOT NULL,
  `township_id` bigint(20) UNSIGNED NOT NULL,
  `grade_id` bigint(20) UNSIGNED NOT NULL,
  `book_name_id` bigint(20) UNSIGNED NOT NULL,
  `books_per_set` int(11) NOT NULL DEFAULT 0,
  `student_count` int(11) NOT NULL DEFAULT 0,
  `book_count` varchar(255) DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

Other important tables in DERAS include `quotas`, `quota_lines`, `allocation_plans`, `allocation_plan_townships`, `stocks`, `school_supply_allocations`, `teacher_guides`, and related township tables. All of them follow the same primary-key and foreign-key pattern.

### PHP Query Functions

PHP query functions are the backend operations that let the application communicate with MySQL. Through these operations, DERAS can read saved records, add new data, change existing values, and remove unwanted rows. In this project, the main SQL statement types used through PHP query functions are **SELECT**, **INSERT**, **UPDATE**, and **DELETE**.

These four operations support the full life cycle of allocation data. SELECT is used when staff open list pages and need to view quotas, textbooks, supplies, or teacher-guide records. INSERT is used when a new form is submitted and a new row must be stored. UPDATE is used when an existing record is edited. DELETE is used when a record is no longer required and must be removed from the database.

In Laravel, these SQL actions are usually written through Eloquent models instead of long raw SQL strings. For example, methods such as `Model::all()`, `Model::create()`, `$model->update()`, and `$model->delete()` perform the same work as SELECT, INSERT, UPDATE, and DELETE. This approach keeps the code shorter, safer, and easier to maintain, while still using MySQL as the storage engine.

Before any protected query runs, the user must pass the login check. After login, role-based access controls which modules Admin or Super Admin can open. The following subsections explain the login process first, and then show examples of SELECT, INSERT, UPDATE, and DELETE used in DERAS.

### 4.2.3 Login System

In Figure 4.6, the login process is built for two roles: **Super Admin** and **Admin**. Login is the first security gate of DERAS. Without a successful sign-in, staff cannot open the dashboard or any allocation module. This design matches the system rule that DERAS has no public guest workspace for protected actions.

When a user opens the login page, the system asks for an **email** and a **password**. These values are required and must follow basic validation rules: the email must be present and correctly formatted, and the password must not be empty. After the form is submitted, the backend checks whether the email exists in the `users` table. If no matching account is found, the system returns an error and does not create a session.

If the email exists, the system then verifies the password. Laravel authentication compares the entered password with the hashed password stored in the database. Sign-in succeeds only when both the email and the password are correct. The account role (`super` or `admin`) is then used after login so that each user receives the correct access level for later module permissions. In short, access is allowed only when the credentials belong to a valid staff account.

The login process also includes rate limiting. If a user repeatedly enters wrong credentials, the system temporarily blocks further attempts. This reduces the risk of password guessing. When authentication succeeds, the failed-attempt counter is cleared, the session is regenerated for security, and the user is redirected to the dashboard. From the dashboard, Admin and Super Admin can continue with their allowed tasks according to role-based permissions.

**[Insert Figure: LoginRequest rules and authenticate]**  
*Figure 4.6 Login system of DERAS*

```php
public function rules(): array
{
    return [
        'email' => ['required', 'string', 'email'],
        'password' => ['required', 'string'],
    ];
}

public function authenticate(): void
{
    $this->ensureIsNotRateLimited();

    $user = \App\Models\User::where('email', $this->email)->first();

    if (!$user) {
        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages([
            'email' => 'No account found for this email.',
        ]);
    }

    if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
        RateLimiter::hit($this->throttleKey());

        throw ValidationException::withMessages([
            'password' => 'The password is incorrect.',
        ]);
    }

    RateLimiter::clear($this->throttleKey());
}
```

After authentication, the controller stores the login session and sends the user to the main dashboard route.

**[Insert Figure: AuthenticatedSessionController store method]**  
*Figure 4.6a Complete login request handling in DERAS*

```php
public function store(LoginRequest $request)
{
    $request->authenticate();
    $request->session()->regenerate();

    return redirect()->route('dashboard');
}
```

When the user logs out, the system ends the session, invalidates the old session data, regenerates the CSRF token, and returns the user to the public entry page.

**[Insert Figure: logout destroy method]**  
*Figure 4.6b Logout handling in DERAS*

```php
public function destroy(Request $request)
{
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/');
}
```

In summary, the DERAS login system validates input, checks the `users` table, confirms the password, applies rate limiting, creates a secure session, and then applies role-based permissions for Super Admin and Admin. Logout closes that session cleanly. Together, these steps protect allocation data and keep unauthorized users outside the working modules.

---

### 4.2.4 SELECT Statement

The SELECT statement is used to retrieve records from the database. In DERAS, list pages such as textbook index and quota index use SELECT-style queries through Eloquent.

In Figure 4.7, the system loads textbook-related records for display on the list page.

**[Insert Figure: SELECT / index query code]**  
*Figure 4.7 Retrieve data with SELECT (Eloquent example)*

```php
public function index(Request $request)
{
    $rows = Textbook::with(['academicYear', 'township', 'grade', 'bookName'])
        ->when($request->academic_year_id, function ($query) use ($request) {
            $query->where('academic_year_id', $request->academic_year_id);
        })
        ->orderBy('id')
        ->paginate(10);

    return view('textbook.index', compact('rows'));
}
```

This method reads data from the `textbooks` table and related tables, then sends the result to the view.

---

### 4.2.5 INSERT Statement

The INSERT statement is used when new data must be saved. In Laravel, this is often done with `Model::create()`.

In Figure 4.8, a new textbook record is inserted after validation.

**[Insert Figure: INSERT / store method]**  
*Figure 4.8 Insert data with INSERT (Eloquent create)*

```php
public function store(Request $request)
{
    Textbook::create($this->validatedData($request));

    return redirect()
        ->route('textbook.index')
        ->with('success', 'Record created successfully.');
}
```

The validated form values are stored as a new row in the `textbooks` table.

---

### 4.2.6 UPDATE Statement

The UPDATE statement is used when an existing record must be changed. In Laravel, this is commonly done with `$model->update()`.

In Figure 4.9, the selected textbook record is updated.

**[Insert Figure: UPDATE / update method]**  
*Figure 4.9 Update data with UPDATE (Eloquent update)*

```php
public function update(Request $request, Textbook $textbook)
{
    $textbook->update($this->validatedData($request, $textbook));

    return redirect()
        ->route('textbook.index')
        ->with('success', 'Record updated successfully.');
}
```

Only the selected record is modified, and then the user is redirected back to the list page.

---

### 4.2.7 DELETE Statement

When a record is no longer needed, the DELETE statement removes it from the database. In Laravel, this is done with `$model->delete()`.

In Figure 4.10, a textbook record is deleted from both the interface list and the database.

**[Insert Figure: DELETE / destroy method]**  
*Figure 4.10 Delete data with DELETE (Eloquent delete)*

```php
public function destroy(Textbook $textbook)
{
    $textbook->delete();

    return redirect()
        ->route('textbook.index')
        ->with('success', 'Record deleted successfully.');
}
```

After deletion, the system returns to the textbook index page with a success message.

---

### 4.2.8 Functions

In Figure 4.11, a function is a named section of program code written to complete one definite job and then reused whenever that job is needed again. Functions help divide the program into smaller parts and make the source easier to reuse, understand, and keep up to date.

A function normally includes a name, parameters, and a body. The name is how other code calls the function. The parameters carry values into the function. The body holds the statements that describe the work to be done. The common pattern for writing a function in many languages is:

```
function function-name(parameter1, parameter2, ...) {
    // function body
    // code to perform
    // return value (if any)
}
```

**[Insert Figure: dark code screenshot of quantity calculation]**  
*Figure 4.11 Functions from school supply quantity calculation in DERAS*

```php
$allocation = SchoolSupplyAllocation::create($validated);

$item = SchoolSupplyItem::find($validated['school_supply_item_id']);
$schoolCount = (int) ($validated['school_count'] ?? 0);
$rate = (int) ($item?->rate ?? 0);

$quantity = $rate * $schoolCount;

$allocation->update(['quantity' => $quantity]);

return redirect()->route('school-supplies.index')
    ->with('success', 'School supply record created successfully.');
```

---

### 4.2.9 GET Method

In Figure 4.12, the GET method places request data in the URL as a query string. This method is usually chosen when records need to be read from the database.

**[Insert Figure: GET route and controller]**  
*Figure 4.12 Implementation of the GET method in the system*

```php
// routes/web.php
Route::get('/textbook', [TextbookController::class, 'index'])
    ->name('textbook.index');

// app/Http/Controllers/TextbookController.php
public function index(Request $request)
{
    $rows = Textbook::with(['academicYear', 'township', 'grade', 'bookName'])
        ->orderBy('id')
        ->paginate(10);

    return view('textbook.index', compact('rows'));
}
```

When the user opens the textbook page, the browser sends a GET request, and the controller returns the related view with database records.

---

### 4.2.10 POST Method

In Figure 4.13, the POST method sends form data to the server in the request body. It is commonly used to insert new records.

**[Insert Figure: POST route and controller]**  
*Figure 4.13 Implementation of the POST method in the system*

```php
// routes/web.php
Route::post('/textbook', [TextbookController::class, 'store'])
    ->name('textbook.store');

// app/Http/Controllers/TextbookController.php
public function store(Request $request)
{
    $validated = $request->validate([
        'academic_year_id' => 'required|exists:academic_years,id',
        'township_id' => 'required|exists:townships,id',
        'grade_id' => 'required|exists:grades,id',
        'book_name_id' => 'required|exists:book_names,id',
        'student_count' => 'required|integer|min:0',
    ]);

    $textbook = Textbook::create($validated);

    return redirect()
        ->route('textbook.index')
        ->with('success', 'Textbook record added successfully.');
}
```

The form values are validated first. If validation succeeds, a new textbook row is inserted into the database.

---

## 4.3 User Interface Implementation

This section describes the main screens of DERAS. Screenshots can be inserted in each subsection.

---

### 4.3.1 Login Page

The login page is the entry point of the system. Only authorized staff can continue after successful authentication.

**[Insert Figure: Login Page]**  
*Figure 4.14 Login page of DERAS*

---

### 4.3.2 Dashboard Overview

After login, the dashboard shows the main menu and user options. From here, staff can open quota, textbook, school supply, teacher guide, and master-data modules.

**[Insert Figure: Dashboard]**  
*Figure 4.15 Dashboard overview of DERAS*

---

### 4.3.3 Master Data Management

Master data includes academic years, townships, grades, book names, grade–subject mapping, and company contacts. These records are required before allocation work.

**[Insert Figure: Master Data Screen]**  
*Figure 4.16 Master data management*

---

### 4.3.4 Student Quota Calculation

This module stores student numbers by township and school level. The saved values support later allocation planning.

**[Insert Figure: Quota Screen]**  
*Figure 4.17 Student quota calculation*

---

### 4.3.5 Textbook Allocation and Distribution

This part includes allocation plans, regular textbook lists, and extra stock lists. The system calculates township shares and stores distribution records.

**[Insert Figure: Allocation / Textbook Screens]**  
*Figure 4.18 Textbook allocation and distribution*

---

### 4.3.6 School Supply Management

School supply allocation uses item rates and school counts. Issue details are recorded in a related module.

**[Insert Figure: School Supply Screens]**  
*Figure 4.19 School supply management*

---

### 4.3.7 Teacher Guide Management

Teacher guide work follows receipt, township distribution, issue, and summary. Each step depends on the previous one.

**[Insert Figure: Teacher Guide Screens]**  
*Figure 4.20 Teacher guide management*

---

### 4.3.8 Excel Export

Supported list pages can export the current filtered data to an Excel file for office record keeping.

**[Insert Figure: Excel Export]**  
*Figure 4.21 Excel export feature*

---

### 4.3.9 User Management and Profile Security

Super Admin can manage Admin users. Every user can update profile details, change password, and log out securely.

**[Insert Figure: Users / Profile Screens]**  
*Figure 4.22 User management and profile security*

---

## 4.4 Hosting the Website

### 4.4.1 Local Hosting with Laragon

For development and demonstration, DERAS can run on Laragon.

1. Start Laragon services.  
2. Place the project in the `www` folder.  
3. Configure `.env` and create the MySQL database.  
4. Run `composer install`, `php artisan key:generate`, `php artisan migrate --seed`, `npm install`, and `npm run build`.  
5. Open the local site URL and log in.

**[Insert Figure: Local hosting]**  
*Figure 4.23 Local hosting with Laragon*

### 4.4.2 Server Deployment

For office use, the Laravel application can be deployed on a server with PHP, MySQL, and Apache/Nginx. The web root should point to the `public` folder, production `.env` values must be set, and demo passwords should be changed.

**[Insert Figure: Deployed site]**  
*Figure 4.24 Deployed DERAS website*

---

## 4.5 Summary

Chapter 4 presented the implementation of DERAS. The backend was built with PHP, Laravel, and MySQL, including database connection, table creation, and the main operations SELECT, INSERT, UPDATE, and DELETE. The chapter also described GET and POST handling, important controller functions, the main user interfaces, and hosting. Together, these parts show how DERAS stores and processes district education resource allocation data in a practical and organized way.
