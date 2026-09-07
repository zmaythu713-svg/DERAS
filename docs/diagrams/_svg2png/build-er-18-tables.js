const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Arial, sans-serif';
const W = 2360;
const H = 1750;
const CW = 210;
const HH = 27;
const RH = 18;

const specs = [
  ['academic_year', 70, 65, [['id', 'bigint', 'PK'], ['name', 'varchar(255)', ''], ['start_year', 'smallint', ''], ['end_year', 'smallint', ''], ['is_current', 'boolean', '']]],
  ['township', 360, 65, [['id', 'bigint', 'PK'], ['name', 'varchar(255)', ''], ['is_active', 'boolean', '']]],
  ['school_level', 650, 65, [['id', 'bigint', 'PK'], ['name', 'varchar(100)', '']]],
  ['grade', 940, 65, [['id', 'bigint', 'PK'], ['school_level_id', 'bigint', 'FK'], ['name', 'varchar(255)', '']]],
  ['book_name', 1230, 65, [['id', 'bigint', 'PK'], ['grade_id', 'bigint', 'FK'], ['name', 'varchar(255)', '']]],
  ['role', 1520, 65, [['id', 'bigint', 'PK'], ['name', 'varchar(255)', ''], ['slug', 'varchar(255)', '']]],
  ['user', 1810, 65, [['id', 'bigint', 'PK'], ['role_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['name', 'varchar(255)', ''], ['email', 'varchar(255)', '']]],

  ['quota', 70, 370, [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['school_level_id', 'bigint', 'FK'], ['grade_id', 'bigint', 'FK'], ['student_quantity', 'integer', '']]],
  ['allocation_plan', 400, 370, [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['book_name_id', 'bigint', 'FK'], ['received_books', 'integer', ''], ['books_per_package', 'integer', '']]],
  ['textbook', 730, 370, [['id', 'bigint', 'PK'], ['book_name_id', 'bigint', 'FK'], ['grade_id', 'bigint', 'FK'], ['books_per_set', 'integer', ''], ['student_count', 'integer', '']]],
  ['teacher_guide', 1060, 370, [['id', 'bigint', 'PK'], ['book_name_id', 'bigint', 'FK'], ['grade_id', 'bigint', 'FK'], ['guide_type', 'varchar(255)', ''], ['total_quota', 'integer', '']]],
  ['school_supply', 1390, 370, [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['grade_id', 'bigint', 'FK'], ['name', 'varchar(255)', ''], ['quantity', 'integer', '']]],

  ['allocation_plan_township', 250, 780, [['id', 'bigint', 'PK'], ['allocation_plan_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['textbook_id', 'bigint', 'FK'], ['total_students', 'integer', ''], ['allocated_quantity', 'integer', '']]],
  ['stock', 600, 780, [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['textbook_id', 'bigint', 'FK'], ['previous_balance', 'integer', ''], ['required_quantity', 'integer', '']]],
  ['previous_year_balance', 950, 780, [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['textbook_id', 'bigint', 'FK'], ['balance', 'integer', '']]],
  ['tg_township_allocation', 1300, 780, [['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['teacher_guide_id', 'bigint', 'FK'], ['allocated_quantity', 'integer', '']]],

  ['teacher_guide_issue', 1300, 1190, [['id', 'bigint', 'PK'], ['tg_township_allocation_id', 'bigint', 'FK'], ['issue_date', 'date', ''], ['issued_quantity', 'integer', '']]],
  ['tg_issue_township', 1640, 1190, [['id', 'bigint', 'PK'], ['teacher_guide_issue_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'], ['issued_quantity', 'integer', '']]],
];

const cards = Object.fromEntries(specs.map(([name, x, y, cols]) => {
  const h = HH + cols.length * RH + 7;
  return [name, { name, x, y, w: CW, h, cx: x + CW / 2, cy: y + h / 2, cols }];
}));

function esc(value) {
  return String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function card(c) {
  let result = `<rect x="${c.x}" y="${c.y}" width="${c.w}" height="${c.h}" fill="#ffffff" stroke="#222" stroke-width="1.2"/>`;
  result += `<rect x="${c.x}" y="${c.y}" width="${c.w}" height="${HH}" fill="#eeeeee" stroke="#222" stroke-width="1.2"/>`;
  result += `<text x="${c.cx}" y="${c.y + 18}" text-anchor="middle" font-family="${FONT}" font-size="12" font-weight="bold">${esc(c.name)}</text>`;
  c.cols.forEach(([field, type, key], index) => {
    const yy = c.y + HH + 13 + index * RH;
    result += `<text x="${c.x + 8}" y="${yy}" font-family="${FONT}" font-size="10"${key === 'PK' ? ' font-weight="bold"' : ''}>${esc(field)}</text>`;
    result += `<text x="${c.x + c.w - 8}" y="${yy}" text-anchor="end" font-family="${FONT}" font-size="9" fill="#444">${esc(type)}${key ? `  ${key}` : ''}</text>`;
  });
  return result;
}

function point(c, toward) {
  const dx = toward.cx - c.cx;
  const dy = toward.cy - c.cy;
  if (Math.abs(dx / c.w) > Math.abs(dy / c.h)) return { x: c.cx + (dx > 0 ? c.w / 2 : -c.w / 2), y: c.cy };
  return { x: c.cx, y: c.cy + (dy > 0 ? c.h / 2 : -c.h / 2) };
}

function manyMark(p, toward) {
  const dx = toward.x - p.x;
  const dy = toward.y - p.y;
  const l = Math.hypot(dx, dy) || 1;
  const ux = dx / l;
  const uy = dy / l;
  const px = -uy;
  const py = ux;
  const q1 = [p.x + ux * 11 + px * 6, p.y + uy * 11 + py * 6];
  const q2 = [p.x + ux * 11 - px * 6, p.y + uy * 11 - py * 6];
  return `<path d="M ${p.x} ${p.y} L ${q1[0]} ${q1[1]} M ${p.x} ${p.y} L ${q2[0]} ${q2[1]} M ${p.x} ${p.y} L ${p.x + ux * 11} ${p.y + uy * 11}" stroke="#333" fill="none" stroke-width="1.2"/>`;
}

function oneMark(p, toward) {
  const dx = toward.x - p.x;
  const dy = toward.y - p.y;
  const l = Math.hypot(dx, dy) || 1;
  const px = -dy / l;
  const py = dx / l;
  const x = p.x + (toward.x - p.x) / l * 7;
  const y = p.y + (toward.y - p.y) / l * 7;
  return `<line x1="${x + px * 5}" y1="${y + py * 5}" x2="${x - px * 5}" y2="${y - py * 5}" stroke="#333" stroke-width="1.2"/>`;
}

function relation(a, b, label, routeY = null) {
  const p1 = point(cards[a], cards[b]);
  const p2 = point(cards[b], cards[a]);
  const path = routeY === null
    ? `M ${p1.x} ${p1.y} L ${p2.x} ${p2.y}`
    : `M ${p1.x} ${p1.y} L ${p1.x} ${routeY} L ${p2.x} ${routeY} L ${p2.x} ${p2.y}`;
  const lx = routeY === null ? (p1.x + p2.x) / 2 : (p1.x + p2.x) / 2;
  const ly = routeY === null ? (p1.y + p2.y) / 2 - 5 : routeY - 5;
  return `<path d="${path}" stroke="#777" fill="none" stroke-width="1"/>
    ${oneMark(p1, p2)}${manyMark(p2, p1)}
    <text x="${lx}" y="${ly}" text-anchor="middle" font-family="${FONT}" font-size="9" fill="#333">${esc(label)}</text>`;
}

const relations = [
  ['school_level', 'grade', 'contains'],
  ['grade', 'book_name', 'has'],
  ['role', 'user', 'assigned to'],
  ['township', 'user', 'belongs to', 255],

  ['academic_year', 'quota', 'has'],
  ['school_level', 'quota', 'sets', 300],
  ['grade', 'quota', 'sets', 330],
  ['academic_year', 'allocation_plan', 'has'],
  ['book_name', 'allocation_plan', 'uses', 335],
  ['book_name', 'textbook', 'defines'],
  ['grade', 'textbook', 'has', 340],
  ['book_name', 'teacher_guide', 'defines', 340],
  ['grade', 'teacher_guide', 'has', 355],
  ['academic_year', 'school_supply', 'has', 355],
  ['township', 'school_supply', 'allocated to', 320],
  ['grade', 'school_supply', 'uses', 350],

  ['allocation_plan', 'allocation_plan_township', 'contains'],
  ['township', 'allocation_plan_township', 'allocated to', 665],
  ['textbook', 'allocation_plan_township', 'allocated in', 710],
  ['academic_year', 'stock', 'has', 700],
  ['township', 'stock', 'located in', 735],
  ['textbook', 'stock', 'tracked in', 755],
  ['academic_year', 'previous_year_balance', 'has', 745],
  ['township', 'previous_year_balance', 'located in', 770],
  ['textbook', 'previous_year_balance', 'tracked in', 790],
  ['teacher_guide', 'tg_township_allocation', 'allocated in'],
  ['academic_year', 'tg_township_allocation', 'has', 760],
  ['township', 'tg_township_allocation', 'allocated to', 785],

  ['tg_township_allocation', 'teacher_guide_issue', 'issues'],
  ['teacher_guide_issue', 'tg_issue_township', 'details'],
  ['township', 'tg_issue_township', 'issued to', 1120],
];

let svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
<rect width="100%" height="100%" fill="white"/>
<text x="${W / 2}" y="34" text-anchor="middle" font-family="${FONT}" font-size="19" font-weight="bold">District Education Resource Allocation System — ER Diagram</text>
<text x="${W / 2}" y="52" text-anchor="middle" font-family="${FONT}" font-size="11">PK = Primary Key   |   FK = Foreign Key   |   1 —&lt; many</text>`;

svg += relations.map(([a, b, label, route]) => relation(a, b, label, route)).join('');
svg += specs.map(([name]) => card(cards[name])).join('');
svg += `</svg>`;

fs.writeFileSync(path.join(outDir, 'Figure3_ER_18Tables_Clear.svg'), svg);
const resvg = new Resvg(Buffer.from(svg), { fitTo: { mode: 'width', value: 2800 }, background: 'white' });
fs.writeFileSync(path.join(outDir, 'Figure3_ER_18Tables_Clear.png'), resvg.render().asPng());
console.log('Created Figure3_ER_18Tables_Clear.png');
