/**
 * Compact black-and-white ER diagram for the DERAS main 10 tables.
 */
const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Times New Roman, Times, serif';
const CARD_W = 220;
const HEADER_H = 27;
const ROW_H = 16;

const tables = {
  academic_year: [
    ['id', 'bigint', 'PK'], ['name', 'varchar', 'UK'], ['start_year', 'smallint', ''],
    ['end_year', 'smallint', ''], ['is_active', 'boolean', ''], ['is_current', 'boolean', ''],
  ],
  township: [['id', 'bigint', 'PK'], ['name', 'varchar', 'UK'], ['is_active', 'boolean', '']],
  school_level: [['id', 'bigint', 'PK'], ['name', 'varchar', 'UK']],
  grade: [['id', 'bigint', 'PK'], ['school_level_id', 'bigint', 'FK'], ['name', 'varchar', '']],
  book_name: [['id', 'bigint', 'PK'], ['grade_id', 'bigint', 'FK'], ['name', 'varchar', '']],
  quota: [
    ['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'],
    ['grade_id', 'bigint', 'FK'], ['student_quantity', 'integer', ''],
  ],
  allocation_plan: [
    ['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['book_name_id', 'bigint', 'FK'],
    ['received_books', 'integer', ''], ['books_per_package', 'integer', ''],
  ],
  textbook: [
    ['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['book_name_id', 'bigint', 'FK'],
    ['books_per_set', 'integer', ''],
  ],
  teacher_guide: [
    ['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['book_name_id', 'bigint', 'FK'],
    ['guide_type', 'varchar', ''], ['total_quota', 'integer', ''],
  ],
  school_supply: [
    ['id', 'bigint', 'PK'], ['academic_year_id', 'bigint', 'FK'], ['township_id', 'bigint', 'FK'],
    ['grade_id', 'bigint', 'FK'], ['name', 'varchar', ''], ['rate', 'decimal', ''], ['quantity', 'integer', ''],
  ],
  role: [['id', 'bigint', 'PK'], ['name', 'varchar', 'UK'], ['slug', 'varchar', 'UK']],
  user: [
    ['id', 'bigint', 'PK'], ['role_id', 'bigint', 'FK'], ['name', 'varchar', ''],
    ['email', 'varchar', 'UK'], ['password', 'varchar', ''],
  ],
};

const layout = {
  academic_year: [350, 80],
  school_level: [350, 230],
  grade: [350, 340],
  book_name: [350, 470],
  township: [350, 600],
  quota: [40, 180],
  allocation_plan: [40, 410],
  textbook: [660, 100],
  teacher_guide: [660, 270],
  school_supply: [660, 450],
  role: [40, 620],
  user: [40, 725],
};

function height(name) {
  return HEADER_H + tables[name].length * ROW_H + 7;
}

function card(name, x, y) {
  const h = height(name);
  let svg = `<rect x="${x}" y="${y}" width="${CARD_W}" height="${h}" fill="#ffffff" stroke="#000000" stroke-width="1.1"/>`;
  svg += `<rect x="${x}" y="${y}" width="${CARD_W}" height="${HEADER_H}" fill="#ffffff" stroke="#000000" stroke-width="1.1"/>`;
  svg += `<text x="${x + 9}" y="${y + 18}" font-family="${FONT}" font-size="12" font-weight="bold">${name}</text>`;
  tables[name].forEach(([field, type, key], i) => {
    const yy = y + HEADER_H + 12 + i * ROW_H;
    svg += `<text x="${x + 9}" y="${yy}" font-family="${FONT}" font-size="10" font-weight="${key === 'PK' ? 'bold' : 'normal'}">${key ? `${key}  ` : ''}${field}</text>`;
    svg += `<text x="${x + CARD_W - 9}" y="${yy}" text-anchor="end" font-family="${FONT}" font-size="9">${type}</text>`;
  });
  return svg;
}

function box(name) {
  const [x, y] = layout[name];
  return { x, y, w: CARD_W, h: height(name), cx: x + CARD_W / 2, cy: y + height(name) / 2 };
}

function fkRowPoint(name, rowIndex, side) {
  const card = box(name);
  const index = Number.isFinite(rowIndex) ? rowIndex : 0;
  return {
    x: side === 'left' ? card.x : card.x + card.w,
    y: card.y + HEADER_H + 12 + index * ROW_H,
  };
}

function pkPoint(name, side) {
  const card = box(name);
  return {
    x: side === 'left' ? card.x : card.x + card.w,
    y: card.y + HEADER_H + 12,
  };
}

function crowFoot(x, y, direction) {
  const size = 9;
  if (direction === 'left' || direction === 'right') {
    const dx = direction === 'right' ? size : -size;
    return `<line x1="${x}" y1="${y}" x2="${x + dx}" y2="${y - 6}" stroke="#000" stroke-width="1.1"/>
      <line x1="${x}" y1="${y}" x2="${x + dx}" y2="${y + 6}" stroke="#000" stroke-width="1.1"/>
      <line x1="${x}" y1="${y}" x2="${x + dx}" y2="${y}" stroke="#000" stroke-width="1.1"/>`;
  }

  const dy = direction === 'down' ? size : -size;
  return `<line x1="${x}" y1="${y}" x2="${x - 6}" y2="${y + dy}" stroke="#000" stroke-width="1.1"/>
    <line x1="${x}" y1="${y}" x2="${x + 6}" y2="${y + dy}" stroke="#000" stroke-width="1.1"/>
    <line x1="${x}" y1="${y}" x2="${x}" y2="${y + dy}" stroke="#000" stroke-width="1.1"/>`;
}

function zeroMany(x, y, direction) {
  const offset = 9;
  if (direction === 'left' || direction === 'right') {
    const dx = direction === 'right' ? 1 : -1;
    const circleX = x + dx * offset * 2;
    const crowX = x + dx * offset;
    const tipX = x;
    return `<circle cx="${circleX}" cy="${y}" r="3.4" fill="#ffffff" stroke="#000" stroke-width="1.1"/>
      <line x1="${crowX}" y1="${y}" x2="${tipX}" y2="${y - 6}" stroke="#000" stroke-width="1.1"/>
      <line x1="${crowX}" y1="${y}" x2="${tipX}" y2="${y + 6}" stroke="#000" stroke-width="1.1"/>
      <line x1="${crowX}" y1="${y}" x2="${tipX}" y2="${y}" stroke="#000" stroke-width="1.1"/>`;
  }

  const dy = direction === 'down' ? 1 : -1;
  const circleY = y + dy * offset * 2;
  const crowY = y + dy * offset;
  const tipY = y;
  return `<circle cx="${x}" cy="${circleY}" r="3.4" fill="#ffffff" stroke="#000" stroke-width="1.1"/>
    <line x1="${x}" y1="${crowY}" x2="${x - 6}" y2="${tipY}" stroke="#000" stroke-width="1.1"/>
    <line x1="${x}" y1="${crowY}" x2="${x + 6}" y2="${tipY}" stroke="#000" stroke-width="1.1"/>
    <line x1="${x}" y1="${crowY}" x2="${x}" y2="${tipY}" stroke="#000" stroke-width="1.1"/>`;
}

function oneBars(x, y, direction) {
  if (direction === 'left' || direction === 'right') {
    const dx = direction === 'right' ? 5 : -5;
    return `<line x1="${x}" y1="${y - 6}" x2="${x}" y2="${y + 6}" stroke="#000" stroke-width="1.2"/>
      <line x1="${x + dx}" y1="${y - 6}" x2="${x + dx}" y2="${y + 6}" stroke="#000" stroke-width="1.2"/>`;
  }
  const dy = direction === 'down' ? 5 : -5;
  return `<line x1="${x - 6}" y1="${y}" x2="${x + 6}" y2="${y}" stroke="#000" stroke-width="1.2"/>
    <line x1="${x - 6}" y1="${y + dy}" x2="${x + 6}" y2="${y + dy}" stroke="#000" stroke-width="1.2"/>`;
}

function drawFk(parent, child, childRow, laneX) {
  const p = box(parent);
  const c = box(child);
  const parentIsLeft = p.cx < c.cx;

  if (Math.abs(p.cx - c.cx) < 20) {
    const childAbove = c.cy < p.cy;
    const start = {
      x: c.cx,
      y: childAbove ? c.y + c.h : c.y,
    };
    const end = {
      x: p.cx,
      y: childAbove ? p.y : p.y + p.h,
    };
    const direction = childAbove ? 'down' : 'up';
    const parentDirection = childAbove ? 'up' : 'down';
    return `<path d="M ${start.x} ${start.y} V ${end.y}" fill="none" stroke="#000" stroke-width="1.15"/>
      ${zeroMany(start.x, start.y, direction)}
      ${oneBars(end.x, end.y, parentDirection)}`;
  }

  const childIsLeft = childIsLeftOf(parent, child);
  const startSide = childIsLeft ? 'right' : 'left';
  const endSide = parentIsLeft ? 'right' : 'left';
  const start = fkRowPoint(child, childRow, startSide);
  const end = pkPoint(parent, endSide);
  const direction = childIsLeft ? 'right' : 'left';
  const parentDirection = parentIsLeft ? 'right' : 'left';
  return `<path d="M ${start.x} ${start.y} H ${laneX} V ${end.y} H ${end.x}"
    fill="none" stroke="#000" stroke-width="1.15"/>
    ${zeroMany(start.x, start.y, direction)}
    ${oneBars(end.x, end.y, parentDirection)}`;
}

function childIsLeftOf(parent, child) {
  return box(child).cx < box(parent).cx;
}

const foreignKeys = [
  ['school_level', 'grade', 1, 328],
  ['grade', 'book_name', 1, 592],
  ['academic_year', 'quota', 1, 270],
  ['township', 'quota', 2, 285],
  ['grade', 'quota', 3, 300],
  ['academic_year', 'allocation_plan', 1, 315],
  ['book_name', 'allocation_plan', 2, 330],
  ['academic_year', 'textbook', 1, 652],
  ['book_name', 'textbook', 2, 640],
  ['academic_year', 'teacher_guide', 1, 628],
  ['book_name', 'teacher_guide', 2, 616],
  ['academic_year', 'school_supply', 1, 604],
  ['township', 'school_supply', 2, 592],
  ['grade', 'school_supply', 3, 580],
  ['role', 'user', 1, 28],
];

function build() {
  const width = 925;
  const height = 865;
  const connectors = foreignKeys.map(([parent, child, row, lane]) => drawFk(parent, child, row, lane)).join('');
  const cards = Object.entries(layout).map(([name, [x, y]]) => card(name, x, y)).join('');
  const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
  <rect width="100%" height="100%" fill="#ffffff"/>
  <text x="${width / 2}" y="35" text-anchor="middle" font-family="${FONT}" font-size="17" font-weight="bold">DERAS Normalized Database Design — 12 Tables</text>
  <text x="${width / 2}" y="55" text-anchor="middle" font-family="${FONT}" font-size="10">PK = Primary Key, FK = Foreign Key, UK = Unique Key, || = One, o&lt; = Zero or Many</text>
  ${connectors}
  ${cards}
</svg>`;

  const base = 'Figure3_ER_Normalized_12Tables_BW';
  fs.writeFileSync(path.join(outDir, `${base}.svg`), svg, 'utf8');
  const renderer = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: 2000 },
    background: 'white',
  });
  fs.writeFileSync(path.join(outDir, `${base}.png`), renderer.render().asPng());
  console.log(`Wrote ${base}.svg and ${base}.png`);
}

build();
