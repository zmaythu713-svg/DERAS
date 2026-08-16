/**
 * DERAS — Use Case (by role) + Normalized ER diagrams (B&W, academic).
 */
const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Times New Roman, Times, serif';

function save(name, svg, widthHint = 1600) {
  const svgPath = path.join(outDir, name + '.svg');
  const pngPath = path.join(outDir, name + '.png');
  fs.writeFileSync(svgPath, svg, 'utf8');
  const resvg = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: widthHint },
    background: 'white',
  });
  fs.writeFileSync(pngPath, resvg.render().asPng());
  console.log('OK', name);
}

function esc(t) {
  return String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function actor(x, y, label) {
  let p = '';
  p += `<circle cx="${x}" cy="${y - 28}" r="10" fill="#fff" stroke="#000" stroke-width="1.4"/>`;
  p += `<line x1="${x}" y1="${y - 18}" x2="${x}" y2="${y + 8}" stroke="#000" stroke-width="1.4"/>`;
  p += `<line x1="${x - 16}" y1="${y - 6}" x2="${x + 16}" y2="${y - 6}" stroke="#000" stroke-width="1.4"/>`;
  p += `<line x1="${x}" y1="${y + 8}" x2="${x - 12}" y2="${y + 28}" stroke="#000" stroke-width="1.4"/>`;
  p += `<line x1="${x}" y1="${y + 8}" x2="${x + 12}" y2="${y + 28}" stroke="#000" stroke-width="1.4"/>`;
  p += `<text x="${x}" y="${y + 48}" text-anchor="middle" font-family="${FONT}" font-size="13" font-weight="bold">${esc(label)}</text>`;
  return p;
}

function ucOval(cx, cy, label) {
  const lines = String(label).split('\n');
  const h = 18 + lines.length * 12;
  const w = 210;
  let p = `<ellipse cx="${cx}" cy="${cy}" rx="${w / 2}" ry="${h / 2}" fill="#fff" stroke="#000" stroke-width="1.2"/>`;
  const startY = cy - ((lines.length - 1) * 6.5) + 3;
  lines.forEach((ln, i) => {
    p += `<text x="${cx}" y="${startY + i * 13}" text-anchor="middle" font-family="${FONT}" font-size="11">${esc(ln)}</text>`;
  });
  return { p, h };
}

function buildUseCase({ title, actorLabel, packages, note }) {
  const actorX = 80;
  const sysX = 180;
  const sysW = 980;
  const colW = 300;
  const gapX = 24;
  const gapY = 28;
  const topY = 100;

  // measure package heights
  const measured = packages.map((pkg) => {
    let h = 36;
    pkg.cases.forEach((c) => {
      const lines = String(c).split('\n').length;
      h += 18 + lines * 12 + 14;
    });
    h += 10;
    return { ...pkg, h };
  });

  // 3-column layout
  const cols = [[], [], []];
  const colHeights = [0, 0, 0];
  measured.forEach((pkg) => {
    let best = 0;
    for (let i = 1; i < 3; i++) if (colHeights[i] < colHeights[best]) best = i;
    cols[best].push(pkg);
    colHeights[best] += pkg.h + gapY;
  });

  let body = '';
  const pkgCenters = [];

  cols.forEach((colPkgs, ci) => {
    let y = topY + 28;
    const x = sysX + 28 + ci * (colW + gapX);
    colPkgs.forEach((pkg) => {
      body += `<rect x="${x}" y="${y}" width="${colW}" height="${pkg.h}" fill="#fff" stroke="#000" stroke-width="1" stroke-dasharray="5 3"/>`;
      body += `<text x="${x + colW / 2}" y="${y + 20}" text-anchor="middle" font-family="${FONT}" font-size="12" font-weight="bold">${esc(pkg.title)}</text>`;
      let cy = y + 48;
      pkg.cases.forEach((c) => {
        const { p, h } = ucOval(x + colW / 2, cy, c);
        body += p;
        pkgCenters.push({ x: x + 8, y: cy });
        cy += h + 14;
      });
      y += pkg.h + gapY;
    });
  });

  const sysH = Math.max(...colHeights) + 40;
  const actorY = topY + sysH / 2;
  body = actor(actorX, actorY, actorLabel) + body;

  // one association line per package column entry point (cleaner than every UC)
  const entryYs = [];
  cols.forEach((colPkgs, ci) => {
    let y = topY + 28;
    const x = sysX + 28 + ci * (colW + gapX);
    colPkgs.forEach((pkg) => {
      entryYs.push({ x, y: y + 40 });
      y += pkg.h + gapY;
    });
  });
  entryYs.forEach((e) => {
    body += `<line x1="${actorX + 22}" y1="${actorY}" x2="${e.x}" y2="${e.y}" stroke="#000" stroke-width="1"/>`;
  });

  let noteBlock = '';
  let extraH = 0;
  if (note) {
    extraH = 70 + note.lines.length * 16;
    const nx = sysX;
    const ny = topY + sysH + 24;
    noteBlock += `<rect x="${nx}" y="${ny}" width="420" height="${extraH - 10}" fill="#fff" stroke="#000" stroke-width="1"/>`;
    noteBlock += `<text x="${nx + 12}" y="${ny + 22}" font-family="${FONT}" font-size="12" font-weight="bold">${esc(note.title)}</text>`;
    note.lines.forEach((ln, i) => {
      noteBlock += `<text x="${nx + 12}" y="${ny + 44 + i * 16}" font-family="${FONT}" font-size="11">${esc(ln)}</text>`;
    });
  }

  const height = topY + sysH + extraH + 60;
  const width = sysX + sysW + 30;

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
  <rect width="${width}" height="${height}" fill="#ffffff"/>
  <text x="${width / 2}" y="36" text-anchor="middle" font-family="${FONT}" font-size="18" font-weight="bold">${esc(title)}</text>
  <rect x="${sysX}" y="${topY}" width="${sysW}" height="${sysH}" fill="none" stroke="#000" stroke-width="1.6"/>
  <text x="${sysX + sysW / 2}" y="${topY - 10}" text-anchor="middle" font-family="${FONT}" font-size="13" font-weight="bold">DERAS System</text>
  ${body}
  ${noteBlock}
</svg>`;
}

function ent(x, y, w, h, name) {
  return `<rect x="${x}" y="${y}" width="${w}" height="${h}" fill="#fff" stroke="#000" stroke-width="1.5"/>
  <text x="${x + w / 2}" y="${y + h / 2 + 4}" text-anchor="middle" font-family="${FONT}" font-size="11">${esc(name)}</text>`;
}

function rel(cx, cy, label) {
  const hw = 26, hh = 13;
  return `<path d="M${cx},${cy - hh} L${cx + hw},${cy} L${cx},${cy + hh} L${cx - hw},${cy} Z" fill="#fff" stroke="#000" stroke-width="1.15"/>
  <text x="${cx}" y="${cy + 3}" text-anchor="middle" font-family="${FONT}" font-size="9">${esc(label)}</text>`;
}

function buildER() {
  const W = 1480;
  const H = 1120;
  let p = '';

  p += `<text x="${W / 2}" y="28" text-anchor="middle" font-family="${FONT}" font-size="18" font-weight="bold">Figure 3: ER Diagram — DERAS (Normalized)</text>`;
  p += `<text x="${W / 2}" y="48" text-anchor="middle" font-family="${FONT}" font-size="11" fill="#444">3NF schema: quota_lines · township child tables · roles / permissions · teacher_guide FK family</text>`;

  const masters = [
    [40, 70, 120, 28, 'academic_year'],
    [180, 70, 100, 28, 'township'],
    [300, 70, 90, 28, 'grade'],
    [410, 70, 100, 28, 'book_name'],
    [530, 70, 95, 28, 'category'],
    [650, 70, 70, 28, 'role'],
    [740, 70, 95, 28, 'permission'],
    [860, 70, 70, 28, 'user'],
  ];
  masters.forEach(([x, y, w, h, n]) => { p += ent(x, y, w, h, n); });

  p += ent(690, 130, 110, 28, 'role_permission');
  p += rel(685, 114, 'Has');
  p += `<line x1="685" y1="98" x2="685" y2="100" stroke="#000"/>`;
  p += `<line x1="720" y1="144" x2="787" y2="98" stroke="#000"/>`;
  p += `<line x1="895" y1="98" x2="895" y2="114" stroke="#000"/>`;
  p += rel(895, 114, 'Has');
  p += `<line x1="867" y1="114" x2="867" y2="114" stroke="#000"/>`;
  p += `<text x="660" y="108" font-family="${FONT}" font-size="10">1</text>`;
  p += `<text x="710" y="108" font-family="${FONT}" font-size="10">M</text>`;
  p += `<text x="870" y="108" font-family="${FONT}" font-size="10">1</text>`;
  p += `<text x="920" y="108" font-family="${FONT}" font-size="10">M</text>`;

  p += ent(380, 130, 130, 28, 'grade_book_names');
  p += `<line x1="345" y1="98" x2="400" y2="130" stroke="#000"/>`;
  p += `<line x1="460" y1="98" x2="445" y2="130" stroke="#000"/>`;
  p += `<line x1="577" y1="98" x2="500" y2="130" stroke="#000"/>`;

  // Zone 1
  p += `<rect x="40" y="180" width="420" height="200" fill="#fafafa" stroke="#555" rx="4"/>`;
  p += `<text x="55" y="200" font-family="${FONT}" font-size="12" font-weight="bold">1. Student Quota</text>`;
  p += ent(90, 230, 90, 28, 'quota');
  p += ent(250, 300, 120, 28, 'quota_line');
  p += rel(200, 265, 'Has');
  p += `<line x1="180" y1="244" x2="174" y2="265" stroke="#000"/>`;
  p += `<line x1="226" y1="265" x2="250" y2="314" stroke="#000"/>`;
  p += `<line x1="100" y1="98" x2="100" y2="230" stroke="#000"/>`;
  p += `<line x1="230" y1="98" x2="230" y2="200" stroke="#000"/><line x1="230" y1="200" x2="135" y2="230" stroke="#000"/>`;
  p += `<text x="250" y="360" font-family="${FONT}" font-size="10" fill="#444">school_level · ownership · quantity</text>`;
  p += `<text x="250" y="376" font-family="${FONT}" font-size="10" fill="#444">unique(quota_id, level, ownership)</text>`;

  // Zone 2
  p += `<rect x="480" y="180" width="960" height="200" fill="#fafafa" stroke="#555" rx="4"/>`;
  p += `<text x="495" y="200" font-family="${FONT}" font-size="12" font-weight="bold">2. Allocation Plan → Textbook / Stock</text>`;
  p += ent(560, 230, 140, 28, 'allocation_plan');
  p += ent(820, 230, 200, 28, 'allocation_plan_township');
  p += rel(740, 244, 'Has');
  p += `<line x1="700" y1="244" x2="714" y2="244" stroke="#000"/>`;
  p += `<line x1="766" y1="244" x2="820" y2="244" stroke="#000"/>`;
  p += ent(560, 310, 110, 28, 'textbook');
  p += ent(720, 310, 90, 28, 'stock');
  p += ent(860, 310, 170, 28, 'previous_year_balance');
  p += `<text x="630" y="280" font-family="${FONT}" font-size="10" fill="#444">FK: year, grade, book · Syncs → textbook</text>`;

  // Zone 3
  p += `<rect x="40" y="410" width="1100" height="360" fill="#fafafa" stroke="#555" rx="4"/>`;
  p += `<text x="55" y="430" font-family="${FONT}" font-size="12" font-weight="bold">3. Teacher Guide family</text>`;
  p += ent(80, 470, 150, 28, 'teacher_guide');
  p += ent(360, 470, 250, 28, 'teacher_guide_township_allocation');
  p += rel(300, 484, 'Dist.');
  p += `<line x1="230" y1="484" x2="274" y2="484" stroke="#000"/>`;
  p += `<line x1="326" y1="484" x2="360" y2="484" stroke="#000"/>`;
  p += ent(80, 560, 170, 28, 'teacher_guide_issue');
  p += ent(360, 560, 240, 28, 'teacher_guide_issue_township');
  p += rel(300, 574, 'Issues');
  p += `<line x1="250" y1="574" x2="274" y2="574" stroke="#000"/>`;
  p += `<line x1="326" y1="574" x2="360" y2="574" stroke="#000"/>`;
  p += ent(80, 650, 190, 28, 'teacher_guide_summary');
  p += `<line x1="155" y1="498" x2="155" y2="560" stroke="#000"/>`;
  p += `<line x1="155" y1="588" x2="155" y2="650" stroke="#000"/>`;
  p += `<text x="280" y="530" font-family="${FONT}" font-size="10" fill="#444">receipt header (year, grade, book, guide_type, quotas)</text>`;
  p += `<text x="280" y="620" font-family="${FONT}" font-size="10" fill="#444">issue / summary → teacher_guide_id NOT NULL</text>`;
  p += `<text x="280" y="700" font-family="${FONT}" font-size="10" fill="#444">issue: district_unit, package_unit · summary: previous_balance, fiscal_year_quota, distributed_books</text>`;

  // Zone 4
  p += `<rect x="1160" y="410" width="280" height="360" fill="#fafafa" stroke="#555" rx="4"/>`;
  p += `<text x="1175" y="430" font-family="${FONT}" font-size="12" font-weight="bold">4. Supplies &amp; others</text>`;
  p += ent(1190, 460, 150, 28, 'school_supply_item');
  p += ent(1175, 520, 210, 28, 'school_supply_allocation');
  p += ent(1200, 590, 120, 28, 'supply_item');
  p += ent(1190, 650, 140, 28, 'supply_detail');
  p += ent(1190, 710, 120, 28, 'school_count');
  p += ent(1180, 760, 150, 28, 'company_contact');
  p += `<line x1="1265" y1="488" x2="1265" y2="520" stroke="#000"/>`;
  p += `<line x1="1260" y1="618" x2="1260" y2="650" stroke="#000"/>`;

  // Legend
  p += `<rect x="1160" y="180" width="280" height="200" fill="#fff" stroke="#000"/>`;
  p += `<text x="1300" y="205" text-anchor="middle" font-family="${FONT}" font-size="12" font-weight="bold">Legend</text>`;
  p += ent(1185, 225, 70, 24, 'Entity');
  p += rel(1305, 237, 'Rel');
  p += `<text x="1185" y="280" font-family="${FONT}" font-size="11">1 / M = cardinality</text>`;
  p += `<text x="1185" y="305" font-family="${FONT}" font-size="11">Normalized steps:</text>`;
  p += `<text x="1185" y="325" font-family="${FONT}" font-size="10">• quota → quota_line</text>`;
  p += `<text x="1185" y="343" font-family="${FONT}" font-size="10">• plan / TG → township child</text>`;
  p += `<text x="1185" y="361" font-family="${FONT}" font-size="10">• user.role_id → roles</text>`;

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <rect width="${W}" height="${H}" fill="#ffffff"/>
  ${p}
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
      'Manage Grades & Subjects',
      'Manage Book Names',
      'Manage Company Contacts',
    ],
  },
  {
    title: 'Textbook Ops',
    cases: ['Manage Textbooks', 'Manage Stock Balance'],
  },
  {
    title: 'School Supplies',
    cases: ['Manage School Supply Allocations', 'Manage Supply Details'],
  },
  {
    title: 'Teacher Guides',
    cases: [
      'Record Guide Receipt Quota',
      'Distribute Guide to Townships',
      'Issue Guides to Townships',
      'Teacher Guide Summary',
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
      'Manage Grades & Subjects',
      'Manage Book Names',
      'Manage Company Contacts',
      'Manage Admin Users',
    ],
  },
  {
    title: 'Textbook Allocation',
    cases: [
      'Manage Student Quota',
      'Create Allocation Plan',
      'Manage Textbooks',
      'Manage Stock Balance',
      'Delete Records',
    ],
  },
  {
    title: 'School Supplies',
    cases: ['Manage School Supply Allocations', 'Manage Supply Details'],
  },
  {
    title: 'Teacher Guides',
    cases: [
      'Record Guide Receipt Quota',
      'Distribute Guide to Townships',
      'Issue Guides to Townships',
      'Teacher Guide Summary',
    ],
  },
];

save(
  'Figure2a_Use_Case_Super_Admin_BW',
  buildUseCase({
    title: 'Figure 2a: Use Case Diagram — Super Admin',
    actorLabel: 'Super Admin',
    packages: superPackages,
    note: {
      title: 'Super Admin only',
      lines: [
        '• Manage Admin Users',
        '• Academic Year Rollover',
        '• Student Quota / Allocation Plans',
        '• Delete Records',
      ],
    },
  }),
  1400
);

save(
  'Figure2b_Use_Case_Admin_BW',
  buildUseCase({
    title: 'Figure 2b: Use Case Diagram — Admin',
    actorLabel: 'Admin',
    packages: adminPackages,
    note: {
      title: 'Admin restrictions',
      lines: [
        '• Cannot manage users',
        '• Cannot rollover academic year',
        '• Cannot manage quota / allocation plans',
        '• Cannot delete records',
      ],
    },
  }),
  1400
);

save('Figure3_ER_Diagram', buildER(), 1600);
save('Figure3_ER_Normalized_BW', buildER(), 1600);

console.log('Done.');
