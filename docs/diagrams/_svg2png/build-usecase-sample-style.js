/**
 * DERAS Use Case — compact (no figure title / footer).
 * Groups Teacher Guides like sample Manage Class-Subject → Class / Subjects.
 */
const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Times New Roman, Times, serif';

function save(name, svg, widthHint = 980) {
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
  p += `<circle cx="${x}" cy="${y - 26}" r="9" fill="#fff" stroke="#000" stroke-width="1.4"/>`;
  p += `<line x1="${x}" y1="${y - 17}" x2="${x}" y2="${y + 6}" stroke="#000" stroke-width="1.4"/>`;
  p += `<line x1="${x - 13}" y1="${y - 5}" x2="${x + 13}" y2="${y - 5}" stroke="#000" stroke-width="1.4"/>`;
  p += `<line x1="${x}" y1="${y + 6}" x2="${x - 10}" y2="${y + 22}" stroke="#000" stroke-width="1.4"/>`;
  p += `<line x1="${x}" y1="${y + 6}" x2="${x + 10}" y2="${y + 22}" stroke="#000" stroke-width="1.4"/>`;
  p += `<text x="${x}" y="${y + 38}" text-anchor="middle" font-family="${FONT}" font-size="12" font-weight="bold">${esc(label)}</text>`;
  return { p, x, y };
}

function oval(cx, cy, label) {
  const lines = String(label).split('\n');
  const h = 16 + lines.length * 10;
  const longest = Math.max(...lines.map((l) => l.length));
  const w = Math.min(240, Math.max(120, 16 + longest * 6.2));
  let p = `<ellipse cx="${cx}" cy="${cy}" rx="${w / 2}" ry="${h / 2}" fill="#fff" stroke="#000" stroke-width="1.2"/>`;
  const start = cy - ((lines.length - 1) * 5) + 3;
  lines.forEach((ln, i) => {
    p += `<text x="${cx}" y="${start + i * 10}" text-anchor="middle" font-family="${FONT}" font-size="11">${esc(ln)}</text>`;
  });
  return { p, cx, cy, w, h, left: cx - w / 2, right: cx + w / 2 };
}

function build({ actorLabel, cases, exportHosts = [], sideIncludes = [] }) {
  const actorX = 78;
  const sysX = 170;
  const mainX = 385;
  const relX = 690;
  const topPad = 32;
  const gap = 36;
  const sysY = 10;
  const sysH = topPad + (cases.length - 1) * gap + 26;
  const H = sysY + sysH + 6;
  const W = 860;

  const placed = {};
  let body = '';

  body += `<rect width="${W}" height="${H}" fill="#fff"/>`;
  body += `<rect x="${sysX}" y="${sysY}" width="${W - sysX - 8}" height="${sysH}" fill="none" stroke="#000" stroke-width="1.6"/>`;

  cases.forEach((label, i) => {
    const cy = sysY + topPad + i * gap;
    placed[label] = oval(mainX, cy, label);
    body += placed[label].p;
  });

  // Group side includes by host so multiple includes stack (sample Class + Subjects)
  const byHost = {};
  sideIncludes.forEach((item) => {
    if (!byHost[item.from]) byHost[item.from] = [];
    byHost[item.from].push(item.to);
  });

  const usedRelY = new Set();

  if (exportHosts.length) {
    const midHost = exportHosts.includes('Manage Stocks')
      ? 'Manage Stocks'
      : exportHosts[Math.min(2, exportHosts.length - 1)];
    const exportY = placed[midHost].cy;
    const exp = oval(relX, exportY, 'Export Excel');
    body += exp.p;
    usedRelY.add(exportY);

    exportHosts.forEach((host) => {
      const h = placed[host];
      if (!h) return;
      body += `<line x1="${exp.left}" y1="${exp.cy}" x2="${h.right}" y2="${h.cy}" stroke="#000" stroke-width="1" stroke-dasharray="4 3" marker-end="url(#arrow)"/>`;
    });
    body += `<text x="${(mainX + relX) / 2 + 18}" y="${exportY - 10}" text-anchor="middle" font-family="${FONT}" font-size="9">&lt;&lt;extends&gt;&gt;</text>`;
  }

  Object.entries(byHost).forEach(([from, tos]) => {
    const host = placed[from];
    if (!host) return;
    const n = tos.length;
    const spread = 30;
    tos.forEach((to, i) => {
      let cy = host.cy + (i - (n - 1) / 2) * spread;
      while ([...usedRelY].some((y) => Math.abs(y - cy) < 26)) cy += 28;
      usedRelY.add(cy);
      const side = oval(relX, cy, to);
      body += side.p;
      body += `<line x1="${host.right}" y1="${host.cy}" x2="${side.left}" y2="${side.cy}" stroke="#000" stroke-width="1.1" stroke-dasharray="4 3" marker-end="url(#arrow)"/>`;
      body += `<text x="${(host.right + side.left) / 2}" y="${(host.cy + side.cy) / 2 - 6}" text-anchor="middle" font-family="${FONT}" font-size="9">&lt;&lt;includes&gt;&gt;</text>`;
    });
  });

  const ys = cases.map((c) => placed[c].cy);
  const act = actor(actorX, (Math.min(...ys) + Math.max(...ys)) / 2, actorLabel);
  body += act.p;
  // Draw a separate association line from the actor to every permitted use case.
  cases.forEach((label) => {
    const o = placed[label];
    body += `<line x1="${act.x + 18}" y1="${act.y}" x2="${o.left}" y2="${o.cy}" stroke="#000" stroke-width="1"/>`;
  });

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <defs>
    <marker id="arrow" markerWidth="7" markerHeight="7" refX="6" refY="3" orient="auto">
      <path d="M0,0 L6,3 L0,6 Z" fill="#000"/>
    </marker>
  </defs>
  ${body}
</svg>`;
}

const exportHosts = [
  'Manage Student Quota',
  'Manage Allocation Plans',
  'Manage Textbooks',
  'Manage Stocks',
  'Manage School Supplies',
];

const adminCases = [
  'Login',
  'View Dashboard',
  'Manage Student Quota',
  'Manage Allocation Plans',
  'Manage Textbooks',
  'Manage Stocks',
  'Manage School Supplies',
  'Manage Teacher Guides',
  'Manage Academic Years',
  'Manage Townships',
  'Manage Grades / Subjects',
  'Manage Book Names',
  'Manage Company Contacts',
  'Manage Profile',
  'Logout',
];

const superCases = [
  'Login',
  'View Dashboard',
  'Manage Student Quota',
  'Manage Allocation Plans',
  'Manage Textbooks',
  'Manage Stocks',
  'Manage School Supplies',
  'Manage Teacher Guides',
  'Manage Academic Years',
  'Manage Townships',
  'Manage Grades / Subjects',
  'Manage Book Names',
  'Manage Company Contacts',
  'Manage Admin Users',
  'Delete Records',
  'Manage Profile',
  'Logout',
];

const tgIncludes = [
  { from: 'Manage Teacher Guides', to: 'Teacher Guide Receipt' },
  { from: 'Manage Teacher Guides', to: 'Teacher Guide Distribution' },
  { from: 'Manage Teacher Guides', to: 'Teacher Guide Issues' },
  { from: 'Manage Teacher Guides', to: 'Teacher Guide Summaries' },
];

save(
  'Figure2b_Use_Case_Admin_BW',
  build({
    actorLabel: 'Admin',
    cases: adminCases,
    exportHosts,
    sideIncludes: [
      { from: 'Manage School Supplies', to: 'Supply Details' },
      ...tgIncludes,
    ],
  })
);

save(
  'Figure2a_Use_Case_Super_Admin_BW',
  build({
    actorLabel: 'Super Admin',
    cases: superCases,
    exportHosts,
    sideIncludes: [
      { from: 'Manage Allocation Plans', to: 'Calculate Township Allocation' },
      { from: 'Manage Textbooks', to: 'Sync Textbook from Plan' },
      { from: 'Manage School Supplies', to: 'Supply Details' },
      ...tgIncludes,
      { from: 'Manage Academic Years', to: 'Rollover Academic Year' },
    ],
  })
);

console.log('Done');
