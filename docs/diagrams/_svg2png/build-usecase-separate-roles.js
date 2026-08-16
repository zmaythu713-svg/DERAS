/**
 * DERAS — Separate Use Case diagrams (Super Admin / Admin)
 * Classic academic UML: actor left, system box, clean ovals, minimal includes.
 */
const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Times New Roman, Times, serif';

function save(name, svg, w = 1000) {
  fs.writeFileSync(path.join(outDir, `${name}.svg`), svg, 'utf8');
  const resvg = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: w },
    background: 'white',
  });
  fs.writeFileSync(path.join(outDir, `${name}.png`), resvg.render().asPng());
  console.log('OK', name);
}

function esc(s) {
  return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function stick(x, y, name) {
  let p = '';
  p += `<circle cx="${x}" cy="${y - 28}" r="10" fill="#fff" stroke="#000" stroke-width="1.5"/>`;
  p += `<line x1="${x}" y1="${y - 18}" x2="${x}" y2="${y + 6}" stroke="#000" stroke-width="1.5"/>`;
  p += `<line x1="${x - 14}" y1="${y - 6}" x2="${x + 14}" y2="${y - 6}" stroke="#000" stroke-width="1.5"/>`;
  p += `<line x1="${x}" y1="${y + 6}" x2="${x - 11}" y2="${y + 24}" stroke="#000" stroke-width="1.5"/>`;
  p += `<line x1="${x}" y1="${y + 6}" x2="${x + 11}" y2="${y + 24}" stroke="#000" stroke-width="1.5"/>`;
  p += `<text x="${x}" y="${y + 44}" text-anchor="middle" font-family="${FONT}" font-size="13" font-weight="bold">${esc(name)}</text>`;
  return p;
}

function uc(cx, cy, label) {
  const lines = label.split('\n');
  const rh = 15 + lines.length * 12;
  const rw = Math.min(250, Math.max(175, 20 + Math.max(...lines.map((l) => l.length)) * 7.2));
  let p = `<ellipse cx="${cx}" cy="${cy}" rx="${rw / 2}" ry="${rh / 2}" fill="#fff" stroke="#000" stroke-width="1.25"/>`;
  const y0 = cy - ((lines.length - 1) * 6) + 4;
  lines.forEach((ln, i) => {
    p += `<text x="${cx}" y="${y0 + i * 12}" text-anchor="middle" font-family="${FONT}" font-size="11.5">${esc(ln)}</text>`;
  });
  return { p, cx, cy, left: cx - rw / 2, right: cx + rw / 2, top: cy - rh / 2, bottom: cy + rh / 2 };
}

/**
 * Grouped layout: packages as dashed boxes with use cases inside.
 * Much cleaner than one long column.
 */
function build({ title, actorName, packages, includes, footnote }) {
  const actorX = 95;
  const sysX = 210;
  const sysY = 55;
  const sysW = 720;
  const colW = 320;
  const colGap = 40;
  const pkgPad = 18;
  const ucGap = 44;

  // Place packages in 2 columns
  const cols = [[], []];
  packages.forEach((pkg, i) => {
    cols[i % 2].push(pkg);
  });

  // Measure column heights
  function pkgHeight(pkg) {
    return 28 + pkg.cases.length * ucGap + 12;
  }

  const colHeights = cols.map((list) =>
    list.reduce((sum, pkg) => sum + pkgHeight(pkg) + 20, 40)
  );
  const sysH = Math.max(...colHeights) + 20;
  const footH = footnote ? 70 : 20;
  const H = sysY + sysH + footH + 20;
  const W = sysX + sysW + 30;

  const placed = {}; // label -> oval metrics
  let body = '';

  body += `<rect width="${W}" height="${H}" fill="#fff"/>`;
  body += `<text x="${W / 2}" y="32" text-anchor="middle" font-family="${FONT}" font-size="17" font-weight="bold">${esc(title)}</text>`;
  body += `<rect x="${sysX}" y="${sysY}" width="${sysW}" height="${sysH}" fill="none" stroke="#000" stroke-width="1.7"/>`;
  body += `<text x="${sysX + sysW / 2}" y="${sysY + 24}" text-anchor="middle" font-family="${FONT}" font-size="14" font-weight="bold">DERAS System</text>`;

  cols.forEach((list, ci) => {
    let y = sysY + 42;
    const x0 = sysX + 30 + ci * (colW + colGap);
    list.forEach((pkg) => {
      const ph = pkgHeight(pkg);
      body += `<rect x="${x0}" y="${y}" width="${colW}" height="${ph}" fill="#fff" stroke="#000" stroke-width="1" stroke-dasharray="4 3"/>`;
      body += `<text x="${x0 + colW / 2}" y="${y + 18}" text-anchor="middle" font-family="${FONT}" font-size="12" font-weight="bold">${esc(pkg.title)}</text>`;

      pkg.cases.forEach((label, j) => {
        const cy = y + 42 + j * ucGap;
        const o = uc(x0 + colW / 2, cy, label);
        placed[label] = o;
        body += o.p;
      });
      y += ph + 20;
    });
  });

  // Actor + bus
  const ys = Object.values(placed).map((o) => o.cy);
  const actY = (Math.min(...ys) + Math.max(...ys)) / 2;
  body += stick(actorX, actY, actorName);

  const bus = 175;
  body += `<line x1="${actorX + 22}" y1="${actY}" x2="${bus}" y2="${actY}" stroke="#000" stroke-width="1.15"/>`;
  body += `<line x1="${bus}" y1="${Math.min(...ys)}" x2="${bus}" y2="${Math.max(...ys)}" stroke="#000" stroke-width="1.15"/>`;
  Object.values(placed).forEach((o) => {
    body += `<line x1="${bus}" y1="${o.cy}" x2="${o.left}" y2="${o.cy}" stroke="#000" stroke-width="1.05"/>`;
  });

  // Includes — only within same package / short paths on the right side of ovals
  (includes || []).forEach(({ from, to }) => {
    const a = placed[from];
    const b = placed[to];
    if (!a || !b) return;
    const x = Math.max(a.right, b.right) + 16;
    body += `<path d="M${a.right},${a.cy} H${x} V${b.cy} H${b.right}" fill="none" stroke="#000" stroke-width="1" stroke-dasharray="5 4"/>`;
    body += `<text x="${x + 4}" y="${(a.cy + b.cy) / 2 + 3}" font-family="${FONT}" font-size="9.5">&lt;&lt;include&gt;&gt;</text>`;
  });

  if (footnote) {
    const ny = sysY + sysH + 14;
    body += `<rect x="${sysX}" y="${ny}" width="${sysW}" height="56" fill="#fff" stroke="#000"/>`;
    footnote.forEach((ln, i) => {
      body += `<text x="${sysX + 12}" y="${ny + 20 + i * 16}" font-family="${FONT}" font-size="11" font-weight="${i === 0 ? 'bold' : 'normal'}">${esc(ln)}</text>`;
    });
  }

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
${body}
</svg>`;
}

const adminPackages = [
  {
    title: 'Authentication',
    cases: ['Login / Logout', 'View Dashboard', 'Manage Profile / Password'],
  },
  {
    title: 'Master Data',
    cases: [
      'Manage Academic Years',
      'Manage Townships',
      'Manage Grades / Subjects',
      'Manage Book Names',
      'Manage Company Contacts',
    ],
  },
  {
    title: 'Textbook Allocation',
    cases: [
      'Manage Student Quota',
      'Manage Allocation Plans',
      'Manage Textbooks',
      'Manage Stocks',
    ],
  },
  {
    title: 'School Supplies',
    cases: ['Manage School Supplies', 'Manage Supply Details'],
  },
  {
    title: 'Teacher Guides',
    cases: [
      'Manage Teacher Guide Receipt',
      'Manage Teacher Guide Distribution',
      'Manage Teacher Guide Issues',
      'Manage Teacher Guide Summaries',
    ],
  },
];

const superPackages = [
  {
    title: 'Authentication',
    cases: ['Login / Logout', 'View Dashboard', 'Manage Profile / Password'],
  },
  {
    title: 'Master Data',
    cases: [
      'Manage Academic Years',
      'Rollover Academic Year',
      'Manage Townships',
      'Manage Grades / Subjects',
      'Manage Book Names',
      'Manage Company Contacts',
      'Manage Admin Users',
    ],
  },
  {
    title: 'Textbook Allocation',
    cases: [
      'Manage Student Quota',
      'Manage Allocation Plans',
      'Calculate Township Allocation',
      'Manage Textbooks',
      'Sync Textbook from Plan',
      'Manage Stocks',
      'Delete Records',
    ],
  },
  {
    title: 'School Supplies',
    cases: ['Manage School Supplies', 'Manage Supply Details'],
  },
  {
    title: 'Teacher Guides',
    cases: [
      'Manage Teacher Guide Receipt',
      'Manage Teacher Guide Distribution',
      'Manage Teacher Guide Issues',
      'Manage Teacher Guide Summaries',
    ],
  },
];

save(
  'Figure2a_Use_Case_Super_Admin_BW',
  build({
    title: 'Figure 2a: Use Case Diagram — Super Admin',
    actorName: 'Super Admin',
    packages: superPackages,
    includes: [
      { from: 'Manage Academic Years', to: 'Rollover Academic Year' },
      { from: 'Manage Allocation Plans', to: 'Calculate Township Allocation' },
      { from: 'Manage Textbooks', to: 'Sync Textbook from Plan' },
      { from: 'Manage Teacher Guide Distribution', to: 'Manage Teacher Guide Receipt' },
      { from: 'Manage Teacher Guide Issues', to: 'Manage Teacher Guide Distribution' },
    ],
    footnote: [
      'Super Admin only: Manage Admin Users, Rollover Academic Year,',
      'Calculate Township Allocation, Sync Textbook from Plan, Delete Records',
    ],
  }),
  1100
);

save(
  'Figure2b_Use_Case_Admin_BW',
  build({
    title: 'Figure 2b: Use Case Diagram — Admin',
    actorName: 'Admin',
    packages: adminPackages,
    includes: [
      { from: 'Manage Teacher Guide Distribution', to: 'Manage Teacher Guide Receipt' },
      { from: 'Manage Teacher Guide Issues', to: 'Manage Teacher Guide Distribution' },
    ],
    footnote: [
      'Admin cannot: Manage Admin Users, Rollover Academic Year, Delete Records',
    ],
  }),
  1100
);

console.log('Done');
