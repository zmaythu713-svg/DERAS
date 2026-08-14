const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');

function save(name, svg) {
  const svgPath = path.join(outDir, name + '.svg');
  const pngPath = path.join(outDir, name + '.png');
  fs.writeFileSync(svgPath, svg, 'utf8');
  const resvg = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: 1600 },
    background: 'white',
  });
  fs.writeFileSync(pngPath, resvg.render().asPng());
  console.log('OK', name);
}

function diamond(cx, cy, halfW, halfH) {
  return `M${cx},${cy - halfH} L${cx + halfW},${cy} L${cx},${cy + halfH} L${cx - halfW},${cy} Z`;
}

/**
 * Build a role system-flow SVG.
 * modules: [{ label, export?: boolean, highlight?: 'super-only' }]
 */
function buildFlow({ title, subtitle, modules, note, showSuperLegend = false }) {
  const rowH = 70;
  const topBus = 440;
  const firstMid = 470;
  const lastMid = firstMid + (modules.length - 1) * rowH;
  const busBottom = lastMid + 100;
  const stopY = busBottom + 115;
  const height = stopY + 70;
  const leftBus = 380;
  const rightBus = 980;
  const boxX = 420;
  const boxW = 320;
  const exportX = 770;
  const exportW = 130;

  let rows = '';
  modules.forEach((m, i) => {
    const mid = firstMid + i * rowH;
    const y = mid - 20;
    const fill = m.highlight === 'super-only' ? '#fff7ed' : '#ffffff';
    const stroke = m.highlight === 'super-only' ? '#c2410c' : '#000';
    rows += `
    <line x1="${leftBus}" y1="${mid}" x2="${boxX}" y2="${mid}" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>
    <rect x="${boxX}" y="${y}" width="${boxW}" height="40" fill="${fill}" stroke="${stroke}" stroke-width="1.5"/>
    <text x="${boxX + boxW / 2}" y="${mid + 5}" text-anchor="middle" font-family="Arial" font-size="13">${m.label}</text>`;
    if (m.export) {
      rows += `
    <line x1="${boxX + boxW}" y1="${mid}" x2="${exportX}" y2="${mid}" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>
    <rect x="${exportX}" y="${y}" width="${exportW}" height="40" fill="#fff" stroke="#000" stroke-width="1.5"/>
    <text x="${exportX + exportW / 2}" y="${mid + 5}" text-anchor="middle" font-family="Arial" font-size="13">Export XLSX</text>
    <line x1="${exportX + exportW}" y1="${mid}" x2="${rightBus}" y2="${mid}" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>`;
    } else {
      rows += `
    <line x1="${boxX + boxW}" y1="${mid}" x2="${rightBus}" y2="${mid}" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>`;
    }
  });

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="1100" height="${height}" viewBox="0 0 1100 ${height}">
  <rect width="1100" height="${height}" fill="#ffffff"/>
  <text x="550" y="32" text-anchor="middle" font-family="Arial" font-size="20" font-weight="bold">${title}</text>
  <text x="550" y="54" text-anchor="middle" font-family="Arial" font-size="12" fill="#444">${subtitle}</text>
  <defs>
    <marker id="a" markerWidth="8" markerHeight="8" refX="7" refY="3.5" orient="auto">
      <path d="M0,0 L7,3.5 L0,7 Z" fill="#000"/>
    </marker>
  </defs>

  <ellipse cx="200" cy="90" rx="48" ry="18" fill="#fff" stroke="#000" stroke-width="1.5"/>
  <text x="200" y="95" text-anchor="middle" font-family="Arial" font-size="14">Start</text>
  <line x1="200" y1="108" x2="200" y2="130" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>

  <rect x="130" y="130" width="140" height="34" fill="#fff" stroke="#000" stroke-width="1.5"/>
  <text x="200" y="152" text-anchor="middle" font-family="Arial" font-size="14">User</text>
  <line x1="200" y1="164" x2="200" y2="186" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>

  <rect x="130" y="186" width="140" height="34" fill="#fff" stroke="#000" stroke-width="1.5"/>
  <text x="200" y="208" text-anchor="middle" font-family="Arial" font-size="14">Home Page</text>
  <line x1="200" y1="220" x2="200" y2="242" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>

  <rect x="130" y="242" width="140" height="34" fill="#fff" stroke="#000" stroke-width="1.5"/>
  <text x="200" y="264" text-anchor="middle" font-family="Arial" font-size="14">Login</text>
  <line x1="200" y1="276" x2="200" y2="310" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>

  <path d="${diamond(200, 350, 75, 50)}" fill="#fff" stroke="#000" stroke-width="1.5"/>
  <text x="200" y="345" text-anchor="middle" font-family="Arial" font-size="12">Check Email</text>
  <text x="200" y="362" text-anchor="middle" font-family="Arial" font-size="12">and Password</text>

  <text x="112" y="346" text-anchor="end" font-family="Arial" font-size="12">No</text>
  <path d="M125,350 L70,350 L70,259 L130,259" fill="none" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>

  <text x="290" y="346" font-family="Arial" font-size="12">Yes</text>
  <line x1="275" y1="350" x2="${leftBus}" y2="350" stroke="#000" stroke-width="1.5"/>
  <line x1="${leftBus}" y1="350" x2="${leftBus}" y2="${topBus}" stroke="#000" stroke-width="1.5"/>

  <line x1="${leftBus}" y1="${topBus}" x2="${rightBus}" y2="${topBus}" stroke="#000" stroke-width="1.5"/>
  <line x1="${leftBus}" y1="${topBus}" x2="${leftBus}" y2="${busBottom}" stroke="#000" stroke-width="1.5"/>
  <line x1="${rightBus}" y1="${topBus}" x2="${rightBus}" y2="${busBottom}" stroke="#000" stroke-width="1.5"/>

  <g fill="#000">${rows}</g>

  <line x1="${rightBus}" y1="${busBottom}" x2="${rightBus}" y2="${busBottom + 50}" stroke="#000" stroke-width="1.5"/>
  <line x1="${rightBus}" y1="${busBottom + 50}" x2="580" y2="${busBottom + 50}" stroke="#000" stroke-width="1.5"/>
  <line x1="580" y1="${busBottom + 50}" x2="580" y2="${stopY - 25}" stroke="#000" stroke-width="1.5" marker-end="url(#a)"/>
  <ellipse cx="580" cy="${stopY}" rx="48" ry="18" fill="#fff" stroke="#000" stroke-width="1.5"/>
  <text x="580" y="${stopY + 5}" text-anchor="middle" font-family="Arial" font-size="14">Stop</text>

  <text x="550" y="${height - 22}" text-anchor="middle" font-family="Arial" font-size="11" fill="#444">${note}</text>
  ${showSuperLegend ? `<rect x="40" y="480" width="18" height="18" fill="#fff7ed" stroke="#c2410c" stroke-width="1.5"/>
  <text x="65" y="494" font-family="Arial" font-size="11" fill="#444">Super-only action</text>` : ''}
</svg>`;
}

const sharedDataModules = [
  { label: 'View Dashboard' },
  { label: 'Manage Academic Years' },
  { label: 'Manage Townships' },
  { label: 'Manage Grades' },
  { label: 'Manage Book Names' },
  { label: 'Manage Grade–Subjects' },
  { label: 'Manage Student Quota', export: true },
  { label: 'Manage Allocation Plans', export: true },
  { label: 'Manage Textbooks', export: true },
  { label: 'Manage Stocks', export: true },
  { label: 'Manage School Supplies', export: true },
  { label: 'Manage Supply Details', export: true },
  { label: 'Teacher Guide Receipt', export: true },
  { label: 'Teacher Guide Distribution', export: true },
  { label: 'Teacher Guide Issues', export: true },
  { label: 'Teacher Guide Summaries', export: true },
  { label: 'Manage Company Contacts' },
];

const superModules = [
  ...sharedDataModules.slice(0, 2),
  { label: 'Year Rollover (Super)', highlight: 'super-only' },
  ...sharedDataModules.slice(2),
  { label: 'Manage Admin Users (Super)', highlight: 'super-only' },
  { label: 'Delete Records (Super)', highlight: 'super-only' },
  { label: 'Manage Profile' },
  { label: 'Logout' },
];

const adminModules = [
  ...sharedDataModules,
  { label: 'Manage Profile' },
  { label: 'Logout' },
];

save(
  'Figure1a_Super_Admin_Flow',
  buildFlow({
    title: 'Figure 1a: System Flow — Super Admin',
    subtitle: 'Full access: create/edit + delete + user management + academic year rollover',
    modules: superModules,
    note: 'Highlighted boxes are Super-only. Admin Users / Delete / Rollover require Super permission.',
    showSuperLegend: true,
  })
);

save(
  'Figure1b_Admin_Flow',
  buildFlow({
    title: 'Figure 1b: System Flow — Admin',
    subtitle: 'Daily operations: view + create/edit records. No delete, no user management, no rollover.',
    modules: adminModules,
    note: 'Admin cannot manage users, delete records, or rollover academic years (Super-only).',
  })
);
