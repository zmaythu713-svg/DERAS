# Database Design — Allocation vs Distribution in DERAS

## 3.x Database Design (with Allocation and Distribution)

The database of DERAS was designed using a relational model and implemented with MySQL. To make the system logic clear, the operational tables are grouped into two main parts: **Allocation** and **Distribution**.

- **Allocation** means planning. These tables store planned shares and quotas before materials are issued.  
- **Distribution** means delivery recording. These tables store the actual quantities that were distributed or issued.

Master data and user tables support both groups. This separation helps Admin and Super Admin plan first, then record real delivery results without mixing the two kinds of data.

---

### 3.x.1 Master Data and Users

These tables are shared by allocation and distribution modules.

| Table | Purpose |
|---|---|
| `academic_years` | School year used by all records |
| `townships` | Township reference |
| `grades` | Grade / class level reference |
| `book_names` | Book / subject reference |
| `users` | Admin and Super Admin login accounts |

---

### 3.x.2 Allocation Tables (Planning)

Allocation tables store **what should be shared**. They do not store the final issued delivery by themselves. They prepare the plan by academic year, township, grade, and item.

| Table | Purpose in Allocation |
|---|---|
| `student_quotas` | Student quota header by year and township |
| `quota_details` | Detailed student quantities by school level and ownership |
| `textbook_plans` | Textbook allocation plan header (received books, package size) |
| `township_plan_shares` | Planned share for each township under a textbook plan |
| `guide_receipts` | Teacher-guide receipt quota (planned guide amounts) |
| `school_supplies` | Planned school-supply allocation by item, grade, and township |

In the allocation stage, staff enter student quotas, create textbook plans, set township shares, record teacher-guide receipt quotas, and prepare school-supply plans. These records answer the question: **“How much is planned for each township?”**

---

### 3.x.3 Distribution Tables (Actual Issue / Delivery)

Distribution tables store **what was actually given out**. They are used after allocation planning.

| Table | Purpose in Distribution |
|---|---|
| `textbooks` | Regular textbook distribution records |
| `stocks` | Extra / stock distribution and balance-related records |
| `guide_distributions` | Teacher-guide township distribution, issue, and summary records |
| `supply_details` | Actual school-supply issue details |

In the distribution stage, staff record textbook issues, stock movements, teacher-guide distribution results, and supply issue totals. These records answer the question: **“How much was actually distributed?”**

---

### 3.x.4 Relationship between Allocation and Distribution

Allocation and distribution are linked through shared foreign keys such as `academic_year_id`, `township_id`, `grade_id`, and `book_name_id`.

1. First, allocation tables store the plan.  
2. Then, distribution tables store the delivered quantities.  
3. Reports can compare planned amounts and distributed amounts for the same year and township.

This design keeps planning data and delivery data separate, which improves clarity, reduces confusion, and supports accurate district reporting.

---

## Thesis paragraph (English — paste ready)

In the DERAS database design, operational data is divided into Allocation and Distribution. Allocation tables store planning information. The main allocation tables are `student_quotas`, `quota_details`, `textbook_plans`, `township_plan_shares`, `guide_receipts`, and `school_supplies`. These tables define planned shares by academic year, township, grade, and item. Distribution tables store actual delivery records. The main distribution tables are `textbooks`, `stocks`, `guide_distributions`, and `supply_details`. These tables record the quantities that were really issued or distributed. Both groups use the same master tables (`academic_years`, `townships`, `grades`, `book_names`) and the `users` table for secure access. By separating allocation from distribution, DERAS keeps planning and delivery data clear and supports reliable reporting.

---

## မြန်မာ စာပိုဒ် (နားလည်ရန် / ရှင်းပြရန်)

DERAS database design တွင် လုပ်ငန်းဒေတာကို **Allocation** နှင့် **Distribution** ဟူ၍ နှစ်စုခွဲထားသည်။  

**Allocation** သည် စီမံကိန်းအဆင့်ဖြစ်သည်။ `student_quotas`, `quota_details`, `textbook_plans`, `township_plan_shares`, `guide_receipts`, `school_supplies` တို့တွင် မြို့နယ်အလိုက် **ပေးမည့် ပမာဏ** ကို သိမ်းသည်။  

**Distribution** သည် အမှန်တကယ် ဖြန့်ဝေ/ထုတ်ပေးမှတ်တမ်းအဆင့်ဖြစ်သည်။ `textbooks`, `stocks`, `guide_distributions`, `supply_details` တို့တွင် **ပေးပြီးသား ပမာဏ** ကို သိမ်းသည်။  

နှစ်စုလုံးက `academic_years`, `townships`, `grades`, `book_names` ကဲ့သို့ master tables ကို မျှဝေသုံးသည်။ ဤခွဲခြားမှုကြောင့် စီမံကိန်းနှင့် ဖြန့်ဝေမှတ်တမ်း မရောထွေးဘဲ database design ထဲတွင် ရှင်းရှင်းလင်းလင်း ပေါ်နေစေသည်။
