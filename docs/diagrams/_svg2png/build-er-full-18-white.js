const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Times New Roman, Times, serif';
const W = 1640;
const H = 1040;
const CARD_W = 245;
const HEADER_H = 28;
const ROW_H = 18;

const tables = {
  school_level: [['id', 'bigint', 'PK'], ['name', 'varchar', '']],
  grade: [['id', 'bigint', 'PK'], ['school_level_id', 'bigint', 'FK'], ['name', 'varchar', '']],
  book_name: [['id', 'bigint', 'PK'], ['grade_id', 'bigint', 'FK'], ['name', 'varchar', '']],
  academic_year: [['id', 'bigint', 'PK'], ['name', 'varchar', ''], ['start_year', 'smallint', ''], ['end_year', 'smallint', ''], ['is_current', 'boolean', ''], ['is_active', 'boolean', '']],
  township: [['id', 'bigint', 'PK'], ['name', 'varchar', ''], ['is_active', 'boolean', '']],
  quota: [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['grade_id', 'bigint', 'FK'], ['student_quantity', 'integer', '']],
  textbook: [['id', 'bigint', 'PK'], ['book_name_id', 'bigint', 'FK'], ['books_per_set', 'integer', ''], ['student_count', 'integer', '']],
  allocation_plan: [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['book_name_id', 'bigint', 'FK'], ['received_books', 'integer', ''], ['books_per_package', 'integer', '']],
  allocation_plan_township: [['id', 'bigint', 'PK'], ['allocation_plan_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['textbook_id', 'bigint', 'FK'], ['allocated_quantity', 'integer', '']],
  stock: [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['grade_id', 'bigint', 'FK'], ['book_name_id', 'bigint', 'FK'], ['previous_balance', 'integer', ''], ['required_quantity', 'integer', '']],
  previous_year_balance: [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['grade_id', 'bigint', 'FK'], ['book_name_id', 'bigint', 'FK'], ['balance', 'integer', '']],
  teacher_guide: [['id', 'bigint', 'PK'], ['book_name_id', 'bigint', 'FK'], ['guide_type', 'varchar', ''], ['total_quota', 'integer', '']],
  tg_township_allocation: [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['teacher_guide_id', 'bigint', 'FK'], ['allocated_quantity', 'integer', '']],
  teacher_guide_issue: [['id', 'bigint', 'PK'], ['tg_township_allocation_id', 'bigint', 'FK'], ['issued_quantity', 'integer', ''], ['issue_date', 'date', '']],
  tg_issue_township: [['id', 'bigint', 'PK'], ['teacher_guide_issue_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['issued_quantity', 'integer', '']],
  school_supply: [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['grade_id', 'bigint', 'FK'], ['name', 'varchar', ''], ['rate', 'decimal', ''], ['quantity', 'integer', '']],
  role: [['id', 'bigint', 'PK'], ['name', 'varchar', ''], ['slug', 'varchar', '']],
  user: [['id', 'bigint', 'PK'], ['role_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['name', 'varchar', ''], ['email', 'varchar', ''], ['password', 'varchar', '']],
};

const layout = {
  school_level: [45, 100], quota: [45, 250], school_supply: [45, 490],
  grade: [355, 100], academic_year: [355, 270], stock: [355, 500], allocation_plan: [355, 770],
  book_name: [665, 100], township: [665, 270], previous_year_balance: [665, 500], allocation_plan_township: [665, 770],
  teacher_guide: [975, 100], role: [975, 300], tg_township_allocation: [975, 460], user: [975, 760],
  textbook: [1285, 100], tg_issue_township: [1285, 320], teacher_guide_issue: [1285, 540],
};

function cardH(name) { return HEADER_H + tables[name].length * ROW_H + 8; }
function box(name) {
  const [x, y] = layout[name];
  return { x, y, w: CARD_W, h: cardH(name), cx: x + CARD_W / 2, cy: y + cardH(name) / 2 };
}
function drawCard(name) {
  const { x, y, w, h } = box(name);
  let s = `<rect x="${x}" y="${y}" width="${w}" height="${h}" fill="#fff" stroke="#000" stroke-width="1.1"/>`;
  s += `<line x1="${x}" y1="${y + HEADER_H}" x2="${x + w}" y2="${y + HEADER_H}" stroke="#000"/>`;
  s += `<text x="${x + 10}" y="${y + 19}" font-family="${FONT}" font-size="13" font-weight="bold">${name}</text>`;
  tables[name].forEach(([field, type, key], i) => {
    const yy = y + HEADER_H + 13 + i * ROW_H;
    s += `<text x="${x + 10}" y="${yy}" font-family="${FONT}" font-size="10.5" font-weight="${key === 'PK' ? 'bold' : 'normal'}">${key ? `${key}  ` : ''}${field}</text>`;
    s += `<text x="${x + w - 10}" y="${yy}" text-anchor="end" font-family="${FONT}" font-size="9.5">${type}</text>`;
  });
  return s;
}
function point(a, toward) {
  if (Math.abs(toward.cx - a.cx) > Math.abs(toward.cy - a.cy)) {
    return { x: toward.cx > a.cx ? a.x + a.w : a.x, y: a.cy };
  }
  return { x: a.cx, y: toward.cy > a.cy ? a.y + a.h : a.y };
}
function ends(parent, child) {
  const a = box(parent), b = box(child);
  return [point(a, b), point(b, a)];
}
function crow(x, y, dx, dy) {
  const px = -dy, py = dx, d = 9;
  return `<line x1="${x}" y1="${y}" x2="${x + dx * d + px * 6}" y2="${y + dy * d + py * 6}" stroke="#000"/>
    <line x1="${x}" y1="${y}" x2="${x + dx * d - px * 6}" y2="${y + dy * d - py * 6}" stroke="#000"/>`;
}
function rel(parent, child, offset) {
  const [a, b] = ends(parent, child);
  const horizontal = Math.abs(b.x - a.x) >= Math.abs(b.y - a.y);
  const mid = horizontal ? (a.y + b.y) / 2 + offset : (a.x + b.x) / 2 + offset;
  const path = horizontal
    ? `M ${a.x} ${a.y} H ${mid} V ${b.y} H ${b.x}`
    : `M ${a.x} ${a.y} V ${mid} H ${b.x} V ${b.y}`;
  const dx = Math.sign(a.x - b.x) || 0;
  const dy = Math.sign(a.y - b.y) || 0;
  return `<path d="${path}" fill="none" stroke="#000" stroke-width="0.85"/>
    <line x1="${a.x - (horizontal ? 0 : 6)}" y1="${a.y - (horizontal ? 6 : 0)}" x2="${a.x + (horizontal ? 0 : 6)}" y2="${a.y + (horizontal ? 6 : 0)}" stroke="#000"/>
    ${crow(b.x, b.y, dx, dy)}`;
}

const relationships = [
  ['school_level', 'grade', -20], ['grade', 'book_name', -10], ['academic_year', 'quota', -42],
  ['township', 'quota', -18], ['grade', 'quota', 8], ['academic_year', 'school_supply', -58],
  ['township', 'school_supply', -34], ['grade', 'school_supply', -10], ['academic_year', 'allocation_plan', 32],
  ['book_name', 'allocation_plan', 16], ['allocation_plan', 'allocation_plan_township', 0],
  ['township', 'allocation_plan_township', 24], ['textbook', 'allocation_plan_township', -18],
  ['book_name', 'textbook', 8], ['academic_year', 'stock', -18], ['township', 'stock', 8],
  ['grade', 'stock', 28], ['book_name', 'stock', 48], ['academic_year', 'previous_year_balance', -18],
  ['township', 'previous_year_balance', 8], ['grade', 'previous_year_balance', 28], ['book_name', 'previous_year_balance', 48],
  ['book_name', 'teacher_guide', 0], ['teacher_guide', 'tg_township_allocation', -15],
  ['academic_year', 'tg_township_allocation', 20], ['township', 'tg_township_allocation', 40],
  ['tg_township_allocation', 'teacher_guide_issue', 0], ['teacher_guide_issue', 'tg_issue_township', 0],
  ['township', 'tg_issue_township', 22], ['role', 'user', 0], ['township', 'user', 48],
];

let lines = relationships.map(([a, b, o]) => rel(a, b, o)).join('');
let cards = Object.keys(layout).map(drawCard).join('');
const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <rect width="100%" height="100%" fill="#ffffff"/>
  <text x="${W / 2}" y="38" text-anchor="middle" font-family="${FONT}" font-size="20" font-weight="bold">DERAS Entity Relationship Diagram — 18 Tables</text>
  <text x="${W / 2}" y="59" text-anchor="middle" font-family="${FONT}" font-size="11">PK = Primary Key, FK = Foreign Key</text>
  ${lines}${cards}
</svg>`;

const base = 'Figure3_ER_Full18_White';
fs.writeFileSync(path.join(outDir, `${base}.svg`), svg, 'utf8');
fs.writeFileSync(path.join(outDir, `${base}.png`), new Resvg(Buffer.from(svg), {
  fitTo: { mode: 'width', value: 2600 },
  background: 'white',
}).render().asPng());
console.log(`Wrote ${base}.svg and ${base}.png`);
