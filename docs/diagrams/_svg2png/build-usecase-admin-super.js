/**
 * DERAS Use Case — Admin (left) + Super Admin (right)
 * Super Admin generalizes Admin (inherits all Admin use cases).
 */
const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Times New Roman, Times, serif';

function save(name, svg, widthHint = 1700) {
  fs.writeFileSync(path.join(outDir, name + '.svg'), svg, 'utf8');
  const resvg = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: widthHint },
    background: 'white',
  });
  fs.writeFileSync(path.join(outDir, name + '.png'), resvg.render().asPng());
  console.log('OK', name);
}

function esc(t) {
  return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function actor(x, y, label) {
  let p = '';
  p += `<circle cx="${x}" cy="${y - 34}" r="12" fill="#fff" stroke="#000" stroke-width="1.5"/>`;
  p += `<line x1="${x}" y1="${y - 22}" x2="${x}" y2="${y + 10}" stroke="#000" stroke-width="1.5"/>`;
  p += `<line x1="${x - 18}" y1="${y - 8}" x2="${x + 18}" y2="${y - 8}" stroke="#000" stroke-width="1.5"/>`;
  p += `<line x1="${x}" y1="${y + 10}" x2="${x - 14}" y2="${y + 32}" stroke="#000" stroke-width="1.5"/>`;
  p += `<line x1="${x}" y1="${y + 10}" x2="${x + 14}" y2="${y + 32}" stroke="#000" stroke-width="1.5"/>`;
  p += `<text x="${x}" y="${y + 54}" text-anchor="middle" font-family="${FONT}" font-size="14" font-weight="bold">${esc(label)}</text>`;
  return { p, x, y };
}

/** UML generalization arrow: child → parent (hollow triangle at parent) */
function generalization(fromX, fromY, toX, toY) {
  // line to near parent, then hollow triangle pointing at parent
  const size = 12;
  const tipX = toX;
  const tipY = toY;
  // approach from below (Super is below Admin in our layout) or from side
  let p = `<line x1="${fromX}" y1="${fromY}" x2="${tipX}" y2="${tipY + size}" stroke="#000" stroke-width="1.3"/>`;
  p += `<path d="M${tipX},${tipY} L${tipX - 8},${tipY + size} L${tipX + 8},${tipY + size} Z" fill="#fff" stroke="#000" stroke-width="1.3"/>`;
  return p;
}

function oval(cx, cy, label) {
  const lines = String(label).split('\n');
  const h = 16 + lines.length * 13;
  const w = Math.min(230, 28 + Math.max(...lines.map((l) => l.length)) * 7.2);
  let p = `<ellipse cx="${cx}" cy="${cy}" rx="${w / 2}" ry="${h / 2}" fill="#fff" stroke="#000" stroke-width="1.2"/>`;
  const start = cy - ((lines.length - 1) * 6.5) + 4;
  lines.forEach((ln, i) => {
    p += `<text x="${cx}" y="${start + i * 13}" text-anchor="middle" font-family="${FONT}" font-size="11">${esc(ln)}</text>`;
  });
  return { p, cx, cy, w, h, left: cx - w / 2, right: cx + w / 2 };
}

function build() {
  const W = 1480;
  const H = 980;
  const sysX = 230;
  const sysY = 58;
  const sysW = 1020;
  const sysH = 820;

  const c1 = 400;
  const c2 = 700;
  const c3 = 1000;

  const mk = (id, label, x, y) => ({ id, label, ...oval(x, y, label), x, y });
  const u = {};

  [
    ['login', 'Login', 120],
    ['logout', 'Logout', 175],
    ['profile', 'Manage Profile', 230],
    ['year', 'Manage Academic Years', 300],
    ['town', 'Manage Townships', 365],
    ['grade', 'Manage Grades / Subjects', 430],
    ['book', 'Manage Book Names', 495],
    ['quota', 'Manage Student Quota', 570],
    ['alloc', 'Manage Allocation Plans', 645],
    ['tb', 'Manage Textbooks', 720],
    ['stock', 'Manage Stocks', 785],
  ].forEach(([id, label, y]) => { u[id] = mk(id, label, c1, y); });

  [
    ['ss', 'Manage School Supplies', 175],
    ['sd', 'Manage Supply Details', 245],
    ['company', 'Manage Company Contacts', 315],
    ['tgRecv', 'Manage Teacher Guide\nReceipt', 420],
    ['tgDist', 'Manage Teacher Guide\nDistribution', 520],
    ['tgIssue', 'Manage Teacher Guide\nIssues', 620],
    ['tgSum', 'Manage Teacher Guide\nSummaries', 720],
    ['dash', 'View Dashboard', 810],
  ].forEach(([id, label, y]) => { u[id] = mk(id, label, c2, y); });

  [
    ['rollover', 'Rollover Academic Year', 300],
    ['calc', 'Calculate Township\nAllocation', 470],
    ['sync', 'Sync Textbook from Plan', 620],
    ['users', 'Manage Admin Users', 760],
  ].forEach(([id, label, y]) => { u[id] = mk(id, label, c3, y); });

  let body = '';
  body += `<rect width="${W}" height="${H}" fill="#fff"/>`;
  body += `<text x="${W / 2}" y="34" text-anchor="middle" font-family="${FONT}" font-size="18" font-weight="bold">Figure 2: Use Case Diagram — DERAS</text>`;
  body += `<rect x="${sysX}" y="${sysY}" width="${sysW}" height="${sysH}" fill="none" stroke="#000" stroke-width="1.7"/>`;
  body += `<text x="${sysX + sysW / 2}" y="${sysY + 24}" text-anchor="middle" font-family="${FONT}" font-size="14" font-weight="bold">DERAS System</text>`;

  Object.values(u).forEach((uc) => { body += uc.p; });

  // Actors stacked on left: Admin above, Super Admin below with generalization
  const admin = actor(95, 280, 'Admin');
  const superA = actor(95, 520, 'Super Admin');
  body += admin.p + superA.p;

  // Super Admin ——▷ Admin  (inherits all Admin associations)
  body += generalization(superA.x, superA.y - 55, admin.x, admin.y + 58);
  body += `<text x="118" y="410" font-family="${FONT}" font-size="10" font-style="italic">inherits</text>`;

  // Admin bus → all day-to-day use cases
  const aBus = 200;
  const adminIds = [
    'login', 'logout', 'profile', 'year', 'town', 'grade', 'book',
    'quota', 'alloc', 'tb', 'stock', 'ss', 'sd', 'company',
    'tgRecv', 'tgDist', 'tgIssue', 'tgSum', 'dash',
  ];
  const aYs = adminIds.map((id) => u[id].y);
  body += `<line x1="${admin.x + 26}" y1="${admin.y}" x2="${aBus}" y2="${admin.y}" stroke="#000" stroke-width="1.1"/>`;
  body += `<line x1="${aBus}" y1="${Math.min(...aYs)}" x2="${aBus}" y2="${Math.max(...aYs)}" stroke="#000" stroke-width="1.1"/>`;
  adminIds.forEach((id) => {
    body += `<line x1="${aBus}" y1="${u[id].y}" x2="${u[id].left}" y2="${u[id].y}" stroke="#000" stroke-width="1.05"/>`;
  });

  // Super Admin → Super-only use cases (right column)
  const sBus = 1240;
  const superOnly = ['rollover', 'calc', 'sync', 'users'];
  const sYs = superOnly.map((id) => u[id].y);
  // route: Super right → across bottom/middle → sBus on right of system… 
  // Simpler: from Super go right under system then up — or connect from Super to right edge of system
  const midY = 880;
  body += `<line x1="${superA.x}" y1="${superA.y + 58}" x2="${superA.x}" y2="${midY}" stroke="#000" stroke-width="1.1"/>`;
  body += `<line x1="${superA.x}" y1="${midY}" x2="${sBus}" y2="${midY}" stroke="#000" stroke-width="1.1"/>`;
  body += `<line x1="${sBus}" y1="${midY}" x2="${sBus}" y2="${Math.min(...sYs)}" stroke="#000" stroke-width="1.1"/>`;
  superOnly.forEach((id) => {
    body += `<line x1="${sBus}" y1="${u[id].y}" x2="${u[id].right}" y2="${u[id].y}" stroke="#000" stroke-width="1.05"/>`;
  });

  function include(from, to, labelX, labelY) {
    body += `<line x1="${from.right}" y1="${from.y}" x2="${to.left}" y2="${to.y}" stroke="#000" stroke-width="1" stroke-dasharray="5 4"/>`;
    body += `<text x="${labelX}" y="${labelY}" font-family="${FONT}" font-size="10">&lt;&lt;include&gt;&gt;</text>`;
  }
  include(u.year, u.rollover, 800, 285);
  include(u.alloc, u.calc, 800, 530);
  include(u.tb, u.sync, 800, 660);

  body += `<line x1="${u.tgDist.cx}" y1="${u.tgDist.y - 24}" x2="${u.tgRecv.cx}" y2="${u.tgRecv.y + 24}" stroke="#000" stroke-width="1" stroke-dasharray="5 4"/>`;
  body += `<text x="${u.tgDist.cx + 8}" y="470" font-family="${FONT}" font-size="10">&lt;&lt;include&gt;&gt;</text>`;
  body += `<line x1="${u.tgIssue.cx}" y1="${u.tgIssue.y - 24}" x2="${u.tgDist.cx}" y2="${u.tgDist.y + 24}" stroke="#000" stroke-width="1" stroke-dasharray="5 4"/>`;
  body += `<text x="${u.tgIssue.cx + 8}" y="570" font-family="${FONT}" font-size="10">&lt;&lt;include&gt;&gt;</text>`;

  // Legend note
  body += `<rect x="1080" y="820" width="200" height="88" fill="#fff" stroke="#000" stroke-width="1"/>`;
  body += `<text x="1090" y="842" font-family="${FONT}" font-size="11" font-weight="bold">Actor generalization</text>`;
  body += `<text x="1090" y="862" font-family="${FONT}" font-size="10">Super Admin inherits</text>`;
  body += `<text x="1090" y="878" font-family="${FONT}" font-size="10">all Admin use cases, plus</text>`;
  body += `<text x="1090" y="894" font-family="${FONT}" font-size="10">Users / Rollover / Calc / Sync</text>`;

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
${body}
</svg>`;
}

const svg = build();
save('Figure2_Use_Case', svg, 1700);
save('Figure2_Use_Case_Admin_Super_BW', svg, 1700);
console.log('Done');
