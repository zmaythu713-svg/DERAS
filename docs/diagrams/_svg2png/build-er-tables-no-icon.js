/**
 * DERAS ER diagram — table cards with PK/FK (no green "E" icons).
 * Style similar to dbdiagram, black/white thesis-friendly.
 */
const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Segoe UI, Arial, sans-serif';

const tables = {
  academic_years: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['name', 'varchar', ''],
      ['is_current', 'boolean', ''],
      ['is_active', 'boolean', ''],
    ],
  },
  townships: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['name', 'varchar', ''],
      ['is_active', 'boolean', ''],
    ],
  },
  grades: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['name', 'varchar', ''],
      ['is_active', 'boolean', ''],
    ],
  },
  book_names: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['name', 'varchar', ''],
      ['is_active', 'boolean', ''],
    ],
  },
  categories: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['slug', 'varchar', ''],
      ['name_mm', 'varchar', ''],
    ],
  },
  grade_book_names: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['grade_id', 'bigint', 'FK'],
      ['book_name_id', 'bigint', 'FK'],
      ['category_id', 'bigint', 'FK'],
    ],
  },
  quotas: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
    ],
  },
  quota_lines: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['quota_id', 'bigint', 'FK'],
      ['school_level', 'varchar', ''],
      ['ownership', 'varchar', ''],
      ['quantity', 'int', ''],
    ],
  },
  allocation_plans: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['grade_id', 'bigint', 'FK'],
      ['book_name_id', 'bigint', 'FK'],
      ['received_books', 'int', ''],
      ['books_per_package', 'int', ''],
    ],
  },
  allocation_plan_townships: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['allocation_plan_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
      ['total_students', 'int', ''],
      ['transferable', 'int', ''],
    ],
  },
  textbooks: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
      ['grade_id', 'bigint', 'FK'],
      ['book_name_id', 'bigint', 'FK'],
      ['books_per_set', 'int', ''],
      ['student_count', 'int', ''],
    ],
  },
  stocks: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
      ['grade_id', 'bigint', 'FK'],
      ['book_name_id', 'bigint', 'FK'],
      ['previous_balance', 'int', ''],
      ['required_qty', 'int', ''],
    ],
  },
  previous_year_balances: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
      ['grade_id', 'bigint', 'FK'],
      ['book_name_id', 'bigint', 'FK'],
      ['balance', 'int', ''],
    ],
  },
  teacher_guides: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['grade_id', 'bigint', 'FK'],
      ['book_name_id', 'bigint', 'FK'],
      ['guide_type', 'varchar', ''],
      ['kg_to_g12_quota', 'int', ''],
      ['g1_to_g5_quota', 'int', ''],
    ],
  },
  teacher_guide_township_allocations: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['teacher_guide_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
      ['kg_g12_qty', 'int', ''],
      ['g1_g5_qty', 'int', ''],
    ],
  },
  teacher_guide_issues: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['teacher_guide_id', 'bigint', 'FK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['district_unit', 'int', ''],
      ['package_unit', 'int', ''],
    ],
  },
  teacher_guide_issue_townships: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['teacher_guide_issue_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
      ['issued_quantity', 'int', ''],
    ],
  },
  teacher_guide_summaries: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['teacher_guide_id', 'bigint', 'FK'],
      ['previous_balance', 'int', ''],
      ['distributed_books', 'int', ''],
    ],
  },
  school_supply_items: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['name', 'varchar', ''],
      ['rate', 'varchar', ''],
    ],
  },
  school_supply_allocations: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['grade_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
      ['school_supply_item_id', 'bigint', 'FK'],
      ['quantity', 'int', ''],
    ],
  },
  supply_items: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['name', 'varchar', ''],
    ],
  },
  supply_details: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
      ['grade_id', 'bigint', 'FK'],
      ['supply_item_id', 'bigint', 'FK'],
      ['issued_total', 'int', ''],
    ],
  },
  roles: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['name', 'varchar', ''],
      ['slug', 'varchar', ''],
    ],
  },
  permissions: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['name', 'varchar', ''],
      ['slug', 'varchar', ''],
    ],
  },
  role_permission: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['role_id', 'bigint', 'FK'],
      ['permission_id', 'bigint', 'FK'],
    ],
  },
  users: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['role_id', 'bigint', 'FK'],
      ['name', 'varchar', ''],
      ['email', 'varchar', ''],
    ],
  },
  company_contacts: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['company_name', 'varchar', ''],
      ['phone', 'varchar', ''],
    ],
  },
  school_counts: {
    cols: [
      ['id', 'bigint', 'PK'],
      ['academic_year_id', 'bigint', 'FK'],
      ['grade_id', 'bigint', 'FK'],
      ['township_id', 'bigint', 'FK'],
      ['school_count', 'int', ''],
    ],
  },
};

// Layout: [name, x, y] — compact grid, one unified canvas
const layout = [
  ['academic_years', 40, 60],
  ['townships', 280, 60],
  ['grades', 520, 60],
  ['book_names', 760, 60],
  ['categories', 1000, 60],
  ['roles', 1240, 60],
  ['permissions', 1480, 60],

  ['quotas', 40, 280],
  ['quota_lines', 280, 280],
  ['grade_book_names', 520, 280],
  ['allocation_plans', 760, 280],
  ['allocation_plan_townships', 1000, 280],
  ['users', 1240, 280],
  ['role_permission', 1480, 280],

  ['textbooks', 40, 540],
  ['stocks', 320, 540],
  ['previous_year_balances', 600, 540],
  ['school_counts', 900, 540],
  ['company_contacts', 1180, 540],

  ['teacher_guides', 40, 820],
  ['teacher_guide_township_allocations', 320, 820],
  ['teacher_guide_issues', 640, 820],
  ['teacher_guide_issue_townships', 960, 820],
  ['teacher_guide_summaries', 1280, 820],

  ['school_supply_items', 40, 1120],
  ['school_supply_allocations', 320, 1120],
  ['supply_items', 640, 1120],
  ['supply_details', 880, 1120],
];

const rels = [
  ['academic_years', 'quotas'],
  ['townships', 'quotas'],
  ['quotas', 'quota_lines'],
  ['grades', 'grade_book_names'],
  ['book_names', 'grade_book_names'],
  ['categories', 'grade_book_names'],
  ['academic_years', 'allocation_plans'],
  ['grades', 'allocation_plans'],
  ['book_names', 'allocation_plans'],
  ['allocation_plans', 'allocation_plan_townships'],
  ['townships', 'allocation_plan_townships'],
  ['academic_years', 'textbooks'],
  ['townships', 'textbooks'],
  ['grades', 'textbooks'],
  ['book_names', 'textbooks'],
  ['academic_years', 'stocks'],
  ['townships', 'stocks'],
  ['grades', 'stocks'],
  ['book_names', 'stocks'],
  ['academic_years', 'previous_year_balances'],
  ['townships', 'previous_year_balances'],
  ['grades', 'previous_year_balances'],
  ['book_names', 'previous_year_balances'],
  ['academic_years', 'school_counts'],
  ['grades', 'school_counts'],
  ['townships', 'school_counts'],
  ['academic_years', 'teacher_guides'],
  ['grades', 'teacher_guides'],
  ['book_names', 'teacher_guides'],
  ['teacher_guides', 'teacher_guide_township_allocations'],
  ['townships', 'teacher_guide_township_allocations'],
  ['teacher_guides', 'teacher_guide_issues'],
  ['teacher_guide_issues', 'teacher_guide_issue_townships'],
  ['townships', 'teacher_guide_issue_townships'],
  ['teacher_guides', 'teacher_guide_summaries'],
  ['school_supply_items', 'school_supply_allocations'],
  ['academic_years', 'school_supply_allocations'],
  ['grades', 'school_supply_allocations'],
  ['townships', 'school_supply_allocations'],
  ['supply_items', 'supply_details'],
  ['academic_years', 'supply_details'],
  ['townships', 'supply_details'],
  ['grades', 'supply_details'],
  ['roles', 'users'],
  ['roles', 'role_permission'],
  ['permissions', 'role_permission'],
];

const HEADER_H = 28;
const ROW_H = 18;
const PAD_X = 10;
const CARD_W = 220;

function cardHeight(name) {
  return HEADER_H + tables[name].cols.length * ROW_H + 6;
}

function buildCards() {
  const cards = {};
  for (const [name, x, y] of layout) {
    const h = cardHeight(name);
    cards[name] = { name, x, y, w: CARD_W, h, cx: x + CARD_W / 2, cy: y + h / 2 };
  }
  return cards;
}

function drawCard(name, x, y) {
  const cols = tables[name].cols;
  const h = cardHeight(name);
  let s = '';
  s += `<rect x="${x}" y="${y}" width="${CARD_W}" height="${h}" rx="4" fill="#fff" stroke="#222" stroke-width="1.2"/>`;
  // header — table name only (NO green E icon)
  s += `<rect x="${x}" y="${y}" width="${CARD_W}" height="${HEADER_H}" rx="4" fill="#f3f4f6" stroke="#222" stroke-width="1.2"/>`;
  s += `<rect x="${x}" y="${y + HEADER_H - 4}" width="${CARD_W}" height="4" fill="#f3f4f6"/>`;
  s += `<text x="${x + PAD_X}" y="${y + 19}" font-family="${FONT}" font-size="12" font-weight="700" fill="#111">${name}</text>`;
  s += `<line x1="${x}" y1="${y + HEADER_H}" x2="${x + CARD_W}" y2="${y + HEADER_H}" stroke="#222" stroke-width="1"/>`;

  cols.forEach((col, i) => {
    const [cname, ctype, key] = col;
    const yy = y + HEADER_H + 13 + i * ROW_H;
    const keyLabel = key ? ` ${key}` : '';
    const weight = key === 'PK' ? '600' : '400';
    const fill = key === 'PK' ? '#111' : key === 'FK' ? '#333' : '#444';
    s += `<text x="${x + PAD_X}" y="${yy}" font-family="${FONT}" font-size="10" font-weight="${weight}" fill="${fill}">${cname}</text>`;
    s += `<text x="${x + CARD_W - PAD_X}" y="${yy}" text-anchor="end" font-family="${FONT}" font-size="9" fill="#666">${ctype}${keyLabel}</text>`;
  });
  return s;
}

function edgePoint(card, towardX, towardY) {
  const dx = towardX - card.cx;
  const dy = towardY - card.cy;
  const hw = card.w / 2;
  const hh = card.h / 2;
  if (Math.abs(dx) / hw > Math.abs(dy) / hh) {
    return { x: card.cx + (dx >= 0 ? hw : -hw), y: card.cy };
  }
  return { x: card.cx, y: card.cy + (dy >= 0 ? hh : -hh) };
}

function crowFoot(x, y, towardX, towardY) {
  const dx = towardX - x;
  const dy = towardY - y;
  const len = Math.hypot(dx, dy) || 1;
  const ux = dx / len;
  const uy = dy / len;
  const px = -uy;
  const py = ux;
  const s = 7;
  const a1x = x + ux * s + px * 4;
  const a1y = y + uy * s + py * 4;
  const a2x = x + ux * s - px * 4;
  const a2y = y + uy * s - py * 4;
  return `<line x1="${x}" y1="${y}" x2="${a1x}" y2="${a1y}" stroke="#444" stroke-width="1.1"/>
    <line x1="${x}" y1="${y}" x2="${a2x}" y2="${a2y}" stroke="#444" stroke-width="1.1"/>
    <line x1="${x}" y1="${y}" x2="${x + ux * s}" y2="${y + uy * s}" stroke="#444" stroke-width="1.1"/>`;
}

function drawRel(a, b) {
  const p1 = edgePoint(a, b.cx, b.cy);
  const p2 = edgePoint(b, a.cx, a.cy);
  // one side near parent (a), many side near child (b)
  const midX = (p1.x + p2.x) / 2;
  const midY = (p1.y + p2.y) / 2;
  let s = `<line x1="${p1.x}" y1="${p1.y}" x2="${p2.x}" y2="${p2.y}" stroke="#888" stroke-width="1" fill="none"/>`;
  // bar on one side
  const dx = p2.x - p1.x;
  const dy = p2.y - p1.y;
  const len = Math.hypot(dx, dy) || 1;
  const ux = dx / len;
  const uy = dy / len;
  const px = -uy;
  const py = ux;
  const bx = p1.x + ux * 8;
  const by = p1.y + uy * 8;
  s += `<line x1="${bx + px * 4}" y1="${by + py * 4}" x2="${bx - px * 4}" y2="${by - py * 4}" stroke="#444" stroke-width="1.2"/>`;
  s += crowFoot(p2.x, p2.y, p1.x, p1.y);
  return s;
}

function build() {
  const cards = buildCards();
  let maxX = 0;
  let maxY = 0;
  for (const c of Object.values(cards)) {
    maxX = Math.max(maxX, c.x + c.w);
    maxY = Math.max(maxY, c.y + c.h);
  }
  const W = maxX + 40;
  const H = maxY + 50;

  let body = '';
  // relationships under cards
  let lines = '';
  for (const [from, to] of rels) {
    if (cards[from] && cards[to]) lines += drawRel(cards[from], cards[to]);
  }
  body += lines;
  for (const [name, x, y] of layout) {
    body += drawCard(name, x, y);
  }

  const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <rect width="100%" height="100%" fill="#fff"/>
  <text x="${W / 2}" y="32" text-anchor="middle" font-family="${FONT}" font-size="18" font-weight="700" fill="#111">District Education Resource Allocation System — ERD</text>
  ${body}
</svg>`;

  const base = 'Figure3_ER_Tables_NoIcon';
  fs.writeFileSync(path.join(outDir, base + '.svg'), svg, 'utf8');
  const r = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: Math.min(W, 2800) },
    background: 'white',
  });
  fs.writeFileSync(path.join(outDir, base + '.png'), r.render().asPng());
  console.log('Wrote', base + '.png', `${W}x${H}`);
}

build();
