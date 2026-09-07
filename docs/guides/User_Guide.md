# USER GUIDE
Education Resources Allocation and Distribution System (DERAS)

This section explains how to use DERAS step by step, starting from login. Screenshot places are marked so the matching screen can be inserted under each figure name.

---

## 1. Login

1. Open a web browser and go to `http://deras.test` (or `http://127.0.0.1:8000`).
2. The login page **အကောင့်ဝင်ရန်** appears.
3. Enter the email in **အီးမေးလ်**.
4. Enter the password in **စကားဝှက်**.
5. Click **ဝင်ရောက်မည်**.
6. After a successful login, the Dashboard opens.

**[Insert screenshot here]**

*Figure 1. Login page of DERAS.*

---

## 2. Dashboard

After login, the Dashboard (**ဒက်ရှ်ဘုတ်**) shows summary cards such as **ခွဲတမ်းစာအုပ်**, **လက်ဆင့်ကမ်းစာအုပ်**, **ဖြန့်ဝေပြီးစာအုပ်**, **လက်ကျန်စာအုပ်**, and **ကျောင်းသား**. Charts show township-level textbook distribution.

1. Check the summary cards for the current academic year.
2. Use the left sidebar to open a working module.

**[Insert screenshot here]**

*Figure 2. Dashboard of DERAS.*

---

## 3. Main Menu (Sidebar)

The left sidebar contains all modules. Click a menu name to open it, or click a parent menu to see the sub-menus.

| Menu | Sub-menu |
| --- | --- |
| ဒက်ရှ်ဘုတ် | — |
| ပြဌာန်းစာအုပ် | ခွဲတမ်းတွက်ချက်မှု, ပုံမှန်ဖြန့်ဝေစာရင်း, ထပ်ဆောင်းဖြန့်ဝေစာရင်း |
| ကျောင်းသားဦးရေတွက်ချက်မှု | — |
| သင်ထောက်ကူပစ္စည်းများ | ခွဲတမ်းတွက်ချက်မှု, ထုတ်ပေးမှု |
| ဆရာကိုင်နှင့်လမ်းညွှန် | လက်ခံရရှိမှု, ဖြန့်ဝေရန်ခွဲတမ်း, ဖြန့်ဝေစာရင်း, စာရင်းချုပ် |
| အခြေခံအချက်အလက်များ | မြို့နယ်များ, ပညာသင်နှစ်များ, အတန်းများ, ဘာသာရပ်များ, အတန်း–ဘာသာရပ်, ကုမ္ပဏီများ |

**[Insert screenshot here]**

*Figure 3. Sidebar menu of DERAS.*

---

## 4. Master Data (အခြေခံအချက်အလက်များ)

Master data must be entered before quota, allocation, and distribution work.

### 4.1 Townships (မြို့နယ်များ)

1. Click **အခြေခံအချက်အလက်များ → မြို့နယ်များ**.
2. The township list appears.
3. To add a township, click **ဖန်တီးပါ**.
4. Enter the township name and click **သိမ်းဆည်းရန်**.
5. To edit, click the blue pen button, update the name, then click **သိမ်းဆည်းရန်**.
6. To go back without saving, click **နောက်သို့**.
7. To delete (Super Admin only), click the red trash button and confirm **ဖျက်မည်**.

**[Insert screenshot here]**

*Figure 4. Township list page.*

**[Insert screenshot here]**

*Figure 5. Create township form.*

### 4.2 Academic years (ပညာသင်နှစ်များ)

1. Click **ပညာသင်နှစ်များ**.
2. Click **ဖန်တီးပါ** and enter the academic year name.
3. Mark the current year if required, then click **သိမ်းဆည်းရန်**.
4. Use **ရှာဖွေရန်** to search, or **ပြန်လည်သတ်မှတ်** to clear the search.

**[Insert screenshot here]**

*Figure 6. Academic year list page.*

### 4.3 Grades, subjects, and mapping

1. Open **အတန်းများ**, click **ဖန်တီးပါ**, enter the grade name, then save.
2. Open **ဘာသာရပ်များ**, click **ဖန်တီးပါ**, enter the book / subject name, then save.
3. Open **အတန်း–ဘာသာရပ်**, click **ဖန်တီးပါ**, and link each grade with the correct subjects.
4. Open **ကုမ္ပဏီများ** if supplier contacts are needed, then save.

**[Insert screenshot here]**

*Figure 7. Grade–subject mapping page.*

---

## 5. Student Quota Calculation (ကျောင်းသားဦးရေတွက်ချက်မှု)

1. Click **ကျောင်းသားဦးရေတွက်ချက်မှု**.
2. Select **ပညာသင်နှစ်**.
3. Click **ကျောင်းသားဦးရေတွက်ချက်ရန်**.
4. Select **ပညာသင်နှစ်** and **မြို့နယ်**.
5. Enter student numbers for **မူလတန်း**, **အလယ်တန်း**, and **အထက်တန်း** under **အခြေခံ**, **ဘက**, and **ကိုယ်ပိုင်**.
6. Enter **စက်၊စိုက်၊မွေး** if required.
7. Click **သိမ်းဆည်းရန်**.
8. To export the list, click **Excel ထုတ်ပါ**.

**[Insert screenshot here]**

*Figure 8. Student quota list page.*

**[Insert screenshot here]**

*Figure 9. Student quota entry form.*

---

## 6. Textbooks (ပြဌာန်းစာအုပ်)

Textbook work follows this order: allocation plan → regular distribution → extra distribution.

### 6.1 Allocation plan (ခွဲတမ်းတွက်ချက်မှု)

1. Click **ပြဌာန်းစာအုပ် → ခွဲတမ်းတွက်ချက်မှု**.
2. Select the academic year if needed.
3. Click **ဖန်တီးပါ**.
4. Select academic year, grade, and book name.
5. Enter received books and books per package.
6. Review township figures for previous balance, total students, and transferable quantity. Eligible and allocated amounts are calculated by the system.
7. Click **သိမ်းဆည်းရန်**.

**[Insert screenshot here]**

*Figure 10. Textbook allocation plan list.*

**[Insert screenshot here]**

*Figure 11. Textbook allocation plan form.*

### 6.2 Regular distribution (ပုံမှန်ဖြန့်ဝေစာရင်း)

1. Click **ပြဌာန်းစာအုပ် → ပုံမှန်ဖြန့်ဝေစာရင်း**.
2. Click **ဖန်တီးပါ**.
3. Select academic year, township, grade, and book name.
4. Enter books per set and student count. Related quantities may auto-fill.
5. Add a remark if needed, then click **သိမ်းဆည်းရန်**.
6. Click **Excel ထုတ်ပါ** if an office file is required.

**[Insert screenshot here]**

*Figure 12. Regular textbook distribution list.*

**[Insert screenshot here]**

*Figure 13. Regular textbook distribution form.*

### 6.3 Extra distribution (ထပ်ဆောင်းဖြန့်ဝေစာရင်း)

1. Click **ပြဌာန်းစာအုပ် → ထပ်ဆောင်းဖြန့်ဝေစာရင်း**.
2. Click **ဖန်တီးပါ**.
3. Enter extra / stock distribution details, including previous balance where shown.
4. Click **သိမ်းဆည်းရန်**.

**[Insert screenshot here]**

*Figure 14. Extra textbook distribution (stock) list.*

**[Insert screenshot here]**

*Figure 15. Extra textbook distribution form.*

---

## 7. School Supplies (သင်ထောက်ကူပစ္စည်းများ)

### 7.1 Allocation (ခွဲတမ်းတွက်ချက်မှု)

1. Click **သင်ထောက်ကူပစ္စည်းများ → ခွဲတမ်းတွက်ချက်မှု**.
2. Select **ပညာသင်နှစ်**.
3. Click **ဖန်တီးပါ**.
4. Fill the required supply fields. Amounts are calculated from related quota and school-count data.
5. Click **သိမ်းဆည်းရန်**.

**[Insert screenshot here]**

*Figure 16. School supply allocation list.*

**[Insert screenshot here]**

*Figure 17. School supply allocation form.*

### 7.2 Issue details (ထုတ်ပေးမှု)

1. Click **သင်ထောက်ကူပစ္စည်းများ → ထုတ်ပေးမှု**.
2. Click **ဖန်တီးပါ**.
3. Record the issue / delivery details.
4. Click **သိမ်းဆည်းရန်**.
5. Click **Excel ထုတ်ပါ** if needed.

**[Insert screenshot here]**

*Figure 18. School supply issue list.*

**[Insert screenshot here]**

*Figure 19. School supply issue form.*

---

## 8. Teacher Guides and Handbooks (ဆရာကိုင်နှင့်လမ်းညွှန်)

This module follows a linked sequence: receipt → distribution quota → issue list → summary.

### 8.1 Receipt (လက်ခံရရှိမှု)

1. Click **ဆရာကိုင်နှင့်လမ်းညွှန် → လက်ခံရရှိမှု**.
2. Click **ဖန်တီးပါ**.
3. Record the received teacher guides / handbooks.
4. Click **သိမ်းဆည်းရန်**.

**[Insert screenshot here]**

*Figure 20. Teacher guide receipt list.*

**[Insert screenshot here]**

*Figure 21. Teacher guide receipt form.*

### 8.2 Distribution quota (ဖြန့်ဝေရန်ခွဲတမ်း)

1. Click **ဖြန့်ဝေရန်ခွဲတမ်း**.
2. Click **ဖန်တီးပါ**.
3. Enter township-level distribution quota from the receipt data.
4. Click **သိမ်းဆည်းရန်**.

**[Insert screenshot here]**

*Figure 22. Teacher guide distribution quota page.*

### 8.3 Issue list (ဖြန့်ဝေစာရင်း)

1. Click **ဖြန့်ဝေစာရင်း**.
2. Click **ဖန်တီးပါ**.
3. Record issues to townships.
4. Click **သိမ်းဆည်းရန်**.

**[Insert screenshot here]**

*Figure 23. Teacher guide issue list.*

### 8.4 Summary (စာရင်းချုပ်)

1. Click **စာရင်းချုပ်**.
2. Review the summary register.
3. Click **Excel ထုတ်ပါ** for official record keeping.

**[Insert screenshot here]**

*Figure 24. Teacher guide summary page.*

---

## 9. Edit and Delete a Record

These steps are the same on most list pages.

**To edit**
1. Find the row in the list.
2. Click the blue pen button (**ပြင်ဆင်ရန်**).
3. Update the fields.
4. Click **သိမ်းဆည်းရန်**.

**[Insert screenshot here]**

*Figure 25. Edit record form.*

**To delete (Super Admin only)**
1. Click the red trash button (**ဖျက်ရန်**).
2. In the confirmation box, click **ဖျက်မည်**.
3. Admin users do not see the delete button.

**[Insert screenshot here]**

*Figure 26. Delete confirmation dialog.*

---

## 10. Profile, Password, and Admin Users

### 10.1 Update profile

1. Click the user name at the top right.
2. Click **ကိုယ်ရေးအချက်အလက်**.
3. Update **အမည်** and **အီးမေးလ်**.
4. Click **ပြင်ဆင်ရန်**.
5. Click **နောက်သို့** to return to the Dashboard.

**[Insert screenshot here]**

*Figure 27. User menu in the top-right corner.*

**[Insert screenshot here]**

*Figure 28. Profile page.*

### 10.2 Change password

1. Click **စကားဝှက်ပြောင်းရန်**.
2. Enter the current password and the new password.
3. Click **သိမ်းဆည်းရန်**.

**[Insert screenshot here]**

*Figure 29. Change password page.*

### 10.3 Manage admin users (Super Admin only)

1. Click **စီမံခန့်ခွဲသူများစာရင်း**.
2. Click **ဖန်တီးပါ**.
3. Enter name, email, password, and role.
4. Click **သိမ်းဆည်းရန်**.
5. Admin users cannot open this menu.

**[Insert screenshot here]**

*Figure 30. Admin user list.*

**[Insert screenshot here]**

*Figure 31. Create admin user form.*

---

## 11. Log Out

1. Click the user name at the top right.
2. Click **အကောင့်မှ ထွက်မည်**.
3. The login page appears again.

**[Insert screenshot here]**

*Figure 32. Logout from DERAS.*

---

## 12. Recommended Working Order

1. Login
2. Master data (year, townships, grades, subjects, mapping)
3. Student quota calculation
4. Textbook allocation, then regular and extra distribution
5. School-supply allocation, then issue details
6. Teacher-guide receipt, distribution quota, issue list, and summary
7. Dashboard review and Excel export
8. Log out
