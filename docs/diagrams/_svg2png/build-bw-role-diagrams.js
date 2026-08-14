const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');

function save(name, svg) {
  const svgPath = path.join(outDir, name + '.svg');
  const pngPath = path.join(outDir, name + '.png');
  fs.writeFileSync(svgPath, svg, 'utf8');
  const resvg = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: 1800 },
    background: 'white',
  });
  fs.writeFileSync(pngPath, resvg.render().asPng());
  console.log('OK', name);
}

function diamond(cx, cy, hw, hh) {
  return `M${cx},${cy - hh} L${cx + hw},${cy} L${cx},${cy + hh} L${cx - hw},${cy} Z`;
}

function roleColumn({ title, originX, modules }) {
  const midX = originX + 175;
  const boxW = 220;
  const boxX = midX - boxW / 2;
  const leftBus = midX - 128;
  const rightBus = midX + 128;
  const rowH = 40;
  let p = '';

  p += `<text x="${midX}" y="44" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="15" font-weight="bold">${title}</text>`;

  const oval = (cy, t) => {
    p += `<ellipse cx="${midX}" cy="${cy}" rx="40" ry="14" fill="#fff" stroke="#000" stroke-width="1.3"/>`;
    p += `<text x="${midX}" y="${cy + 4}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="12">${t}</text>`;
  };
  const box = (cy, t) => {
    p += `<rect x="${boxX}" y="${cy - 13}" width="${boxW}" height="26" fill="#fff" stroke="#000" stroke-width="1.3"/>`;
    p += `<text x="${midX}" y="${cy + 4}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="11.5">${t}</text>`;
  };
  const arrow = (x1, y1, x2, y2) => {
    p += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#000" stroke-width="1.15" marker-end="url(#a)"/>`;
  };
  const plain = (x1, y1, x2, y2) => {
    p += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#000" stroke-width="1.15"/>`;
  };

  let y = 76;
  oval(y, 'Start');
  arrow(midX, y + 14, midX, y + 34);
  y += 48;
  box(y, 'View Home Page');
  arrow(midX, y + 13, midX, y + 34);
  y += 48;
  box(y, 'Login');
  arrow(midX, y + 13, midX, y + 36);
  y += 52;

  p += `<path d="${diamond(midX, y, 64, 30)}" fill="#fff" stroke="#000" stroke-width="1.3"/>`;
  p += `<text x="${midX}" y="${y - 2}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="10.5">Check email</text>`;
  p += `<text x="${midX}" y="${y + 11}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="10.5">and password</text>`;
  p += `<text x="${midX - 72}" y="${y + 1}" text-anchor="end" font-family="Times New Roman, Times, serif" font-size="11">No</text>`;
  p += `<path d="M${midX - 64},${y} L${midX - 102},${y} L${midX - 102},${y - 52} L${boxX},${y - 52}" fill="none" stroke="#000" stroke-width="1.15" marker-end="url(#a)"/>`;
  p += `<text x="${midX + 72}" y="${y + 1}" font-family="Times New Roman, Times, serif" font-size="11">Yes</text>`;
  plain(midX + 64, y, rightBus, y);
  plain(rightBus, y, rightBus, y + 30);

  const busTop = y + 30;
  const first = busTop + 28;
  const last = first + (modules.length - 1) * rowH;
  const busBot = last + 20;

  plain(leftBus, busTop, rightBus, busTop);
  plain(leftBus, busTop, leftBus, busBot);
  plain(rightBus, busTop, rightBus, busBot);

  modules.forEach((t, i) => {
    const mid = first + i * rowH;
    const mw = 204;
    const mx = midX - mw / 2;
    arrow(leftBus, mid, mx, mid);
    p += `<rect x="${mx}" y="${mid - 11}" width="${mw}" height="22" fill="#fff" stroke="#000" stroke-width="1.25"/>`;
    p += `<text x="${midX}" y="${mid + 4}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="10.5">${t}</text>`;
    arrow(mx + mw, mid, rightBus, mid);
  });

  const logoutY = busBot + 38;
  plain(rightBus, busBot, rightBus, logoutY - 14);
  plain(rightBus, logoutY - 14, midX, logoutY - 14);
  arrow(midX, logoutY - 14, midX, logoutY - 12);
  box(logoutY, 'Logout');
  arrow(midX, logoutY + 13, midX, logoutY + 32);
  oval(logoutY + 48, 'End');

  return { parts: p, height: logoutY + 74 };
}

// ALL concrete DERAS features (permission from Role Permission table)
const superModules = [
  'View Dashboard',
  'Add / Edit',
  'Delete',
  'Manage Student Quota',
  'Manage Allocation Plans',
  'Manage Textbooks',
  'Manage Stocks',
  'Manage School Supplies',
  'Manage Supply Details',
  'Teacher Guide Receipt',
  'Teacher Guide Distribution',
  'Teacher Guide Issues',
  'Teacher Guide Summaries',
  'Manage Academic Years',
  'Year Rollover',
  'Manage Townships',
  'Manage Grades',
  'Manage Book Names',
  'Manage Grade–Subjects',
  'Manage Company Contacts',
  'Export Reports (XLSX)',
  'User Management',
  'Profile',
  'Change Password',
];

const adminModules = [
  'View Dashboard',
  'Add / Edit',
  'Manage Textbooks',
  'Manage Stocks',
  'Manage School Supplies',
  'Manage Supply Details',
  'Teacher Guide Receipt',
  'Teacher Guide Distribution',
  'Teacher Guide Issues',
  'Teacher Guide Summaries',
  'Manage Academic Years',
  'Manage Townships',
  'Manage Grades',
  'Manage Book Names',
  'Manage Grade–Subjects',
  'Manage Company Contacts',
  'View Reports',
  'Profile',
  'Change Password',
];

const L = roleColumn({ title: 'Super Admin', originX: 30, modules: superModules });
const R = roleColumn({ title: 'Admin', originX: 510, modules: adminModules });
const H = Math.max(L.height, R.height) + 36;

const flow = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="1020" height="${H}" viewBox="0 0 1020 ${H}">
  <rect width="1020" height="${H}" fill="#ffffff"/>
  <text x="510" y="24" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="16" font-weight="bold">Figure 1: System Flow Diagram</text>
  <defs>
    <marker id="a" markerWidth="7" markerHeight="7" refX="6" refY="3.5" orient="auto">
      <path d="M0,0 L7,3.5 L0,7 Z" fill="#000"/>
    </marker>
  </defs>
  ${L.parts}
  ${R.parts}
  <text x="510" y="${H - 12}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="10">
    Super Admin only: Delete, Student Quota, Allocation Plans, Year Rollover, User Management, Export Reports.
  </text>
</svg>`;

save('Figure1_System_Flow_Roles_BW', flow);

// Full tidy permission table — one row per feature
const rows = [
  ['Dashboard', '✓', '✓'],
  ['Add / Edit', '✓', '✓'],
  ['Delete', '✓', '✓'],
  ['Resource Allocation', '✓', '✗'],
  ['Student Quota', '✓', '✗'],
  ['Allocation Plans', '✓', '✗'],
  ['Resource Distribution', '✓', '✓'],
  ['Textbooks', '✓', '✓'],
  ['Stocks', '✓', '✓'],
  ['School Supplies', '✓', '✓'],
  ['Supply Details', '✓', '✓'],
  ['Teacher Guide Receipt', '✓', '✓'],
  ['Teacher Guide Distribution', '✓', '✓'],
  ['Teacher Guide Issues', '✓', '✓'],
  ['Teacher Guide Summaries', '✓', '✓'],
  ['Academic Years', '✓', '✓'],
  ['Year Rollover', '✓', '✗'],
  ['Townships', '✓', '✓'],
  ['Grades', '✓', '✓'],
  ['Book Names', '✓', '✓'],
  ['Grade–Subjects', '✓', '✓'],
  ['Company Contacts', '✓', '✓'],
  ['Reports', 'View / Print / Export', 'View'],
  ['User Management', '✓', '✗'],
  ['Profile', '✓', '✓'],
  ['Change Password', '✓', '✓'],
];

const tw = 860;
const c1 = 340;
const c2 = 260;
const c3 = 260;
const tx = 50;
const ty = 58;
const thh = 40;
const trh = 32;
const tH = thh + rows.length * trh;

let body = '';
rows.forEach((r, i) => {
  const y = ty + thh + i * trh;
  const bg = i % 2 === 0 ? '#ffffff' : '#f4f4f4';
  const cell = (v, cx) => {
    if (v === '✓' || v === '✗') {
      return `<text x="${cx}" y="${y + 21}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="14" font-weight="bold">${v}</text>`;
    }
    return `<text x="${cx}" y="${y + 20}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="10.5">${v}</text>`;
  };
  body += `
    <rect x="${tx}" y="${y}" width="${tw}" height="${trh}" fill="${bg}" stroke="#000" stroke-width="0.85"/>
    <line x1="${tx + c1}" y1="${y}" x2="${tx + c1}" y2="${y + trh}" stroke="#000" stroke-width="0.85"/>
    <line x1="${tx + c1 + c2}" y1="${y}" x2="${tx + c1 + c2}" y2="${y + trh}" stroke="#000" stroke-width="0.85"/>
    <text x="${tx + 12}" y="${y + 20}" font-family="Times New Roman, Times, serif" font-size="11">${r[0]}</text>
    ${cell(r[1], tx + c1 + c2 / 2)}
    ${cell(r[2], tx + c1 + c2 + c3 / 2)}`;
});

const perm = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="960" height="${ty + tH + 36}" viewBox="0 0 960 ${ty + tH + 36}">
  <rect width="960" height="${ty + tH + 36}" fill="#ffffff"/>
  <text x="480" y="32" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="15" font-weight="bold">Table 1: Role Based Permission</text>
  <rect x="${tx}" y="${ty}" width="${tw}" height="${thh}" fill="#000" stroke="#000"/>
  <line x1="${tx + c1}" y1="${ty}" x2="${tx + c1}" y2="${ty + thh}" stroke="#fff"/>
  <line x1="${tx + c1 + c2}" y1="${ty}" x2="${tx + c1 + c2}" y2="${ty + thh}" stroke="#fff"/>
  <text x="${tx + c1 / 2}" y="${ty + 26}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="12" font-weight="bold" fill="#fff">Module</text>
  <text x="${tx + c1 + c2 / 2}" y="${ty + 26}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="12" font-weight="bold" fill="#fff">Super Admin</text>
  <text x="${tx + c1 + c2 + c3 / 2}" y="${ty + 26}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="12" font-weight="bold" fill="#fff">Admin</text>
  ${body}
  <rect x="${tx}" y="${ty}" width="${tw}" height="${tH}" fill="none" stroke="#000" stroke-width="1.35"/>
  <text x="480" y="${ty + tH + 22}" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="10">✓ = Allowed    ✗ = Not allowed</text>
</svg>`;

save('Figure5_Role_Permissions_BW', perm);
