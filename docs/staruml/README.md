# DERAS diagrams (StarUML)

Open these diagrams in **StarUML**.

## Files

| File | Diagram |
|------|---------|
| `docs/staruml/DERAS.mdj` | StarUML project (Use Case, Activity/Flow, ER as Class, Sequence) |
| `docs/diagrams/01_use_case.puml` | Use Case (PlantUML) |
| `docs/diagrams/02_system_flow.puml` | System Flow / Activity (PlantUML) |
| `docs/diagrams/03_er_diagram.puml` | ER Diagram (PlantUML) |
| `docs/diagrams/04_sequence_allocation.puml` | Sequence — Allocation Plan |
| `docs/diagrams/05_sequence_teacher_guide.puml` | Sequence — Teacher Guide pipeline |

## Open in StarUML

### A) Native project
1. Install [StarUML](https://staruml.io/)
2. **File → Open** → `docs/staruml/DERAS.mdj`
3. Model Explorer မှာ 4 models ရှိမယ်:
   - `1. Use Case Model` → add **Use Case Diagram** view if empty, drag actors/use cases onto canvas
   - `2. System Flow (Activity)` → open **DERAS System Flow**
   - `3. ER Model` → open **DERAS ER Diagram**, drag classes onto canvas
   - `4. Sequence Model` → open **Create Allocation Plan Sequence**

> Note: `.mdj` ထဲက diagram views က model elements ပါပြီးသား။ Canvas ပေါ် auto-layout မရှိရင် elements ကို Explorer ကနေ drag လုပ်ပြီး Arrange လုပ်ပါ။

### B) PlantUML (recommended for full layout)
1. StarUML မှာ extension: **PlantUML** (Extension Manager)
2. သို့မဟုတ် [PlantUML Online](https://www.plantuml.com/plantuml/uml/) / VS Code PlantUML extension
3. `docs/diagrams/*.puml` ဖိုင်များ ဖွင့်ပါ — layout အပြည့်အစုံ ပါပြီးသား

### C) ERD extension (optional)
StarUML **ERD** extension တပ်ပြီး `03_er_diagram.puml` ကို ကိုးကားကာ Entity များ ပြန်ဆွဲနိုင်သည်။  
လက်ရှိ `.mdj` မှာ ER ကို **Class Diagram** အဖြစ် ထားထားသည် (StarUML default နဲ့ ဖွင့်ရလွယ်ရန်)။

## Roles (quick)
- **Admin** — day-to-day CRUD (no user manage / no destroy on locked masters depending on middleware)
- **Super Admin** — admin users, academic-year rollover, destroy
