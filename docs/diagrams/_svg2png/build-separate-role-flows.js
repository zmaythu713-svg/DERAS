const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Times New Roman, Times, serif';

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

function diamondPath(cx, cy, hw, hh) {
  return `M${cx},${cy - hh} L${cx + hw},${cy} L${cx},${cy + hh} L${cx - hw},${cy} Z`;
}

/**
 * Clean flow: no figure/role titles, no Delete.
 * Arrows: one path only — no crossing T-junctions on the Yes entry.
 */
function buildFlow({ modules }) {
  const midX = 300;
  const mainW = 250;
  const mainX = midX - mainW / 2;
  const leftBus = midX - 155;
  const exportW = 118;
  const exportX = mainX + mainW + 16;
  const rightBus = exportX + exportW + 28;
  const rowH = 44;
  const boxH = 26;

  let p = '';
  const oval = (cy, t) => {
    p += `<ellipse cx="${midX}" cy="${cy}" rx="44" ry="15" fill="#fff" stroke="#000" stroke-width="1.4"/>`;
    p += `<text x="${midX}" y="${cy + 5}" text-anchor="middle" font-family="${FONT}" font-size="13">${t}</text>`;
  };
  const box = (x, cy, w, t) => {
    p += `<rect x="${x}" y="${cy - boxH / 2}" width="${w}" height="${boxH}" fill="#fff" stroke="#000" stroke-width="1.35"/>`;
    p += `<text x="${x + w / 2}" y="${cy + 4}" text-anchor="middle" font-family="${FONT}" font-size="12">${t}</text>`;
  };
  const arrow = (x1, y1, x2, y2) => {
    p += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#000" stroke-width="1.25" marker-end="url(#a)"/>`;
  };
  const line = (x1, y1, x2, y2) => {
    p += `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#000" stroke-width="1.25"/>`;
  };

  // Start → Home → Login → Decision
  let y = 50;
  oval(y, 'Start');
  arrow(midX, y + 15, midX, y + 36);
  y += 52;
  box(mainX, y, mainW, 'View Home Page');
  arrow(midX, y + boxH / 2, midX, y + 36);
  y += 52;
  box(mainX, y, mainW, 'Login');
  const loginCy = y;
  arrow(midX, y + boxH / 2, midX, y + 38);
  y += 56;

  p += `<path d="${diamondPath(midX, y, 72, 34)}" fill="#fff" stroke="#000" stroke-width="1.4"/>`;
  p += `<text x="${midX}" y="${y - 3}" text-anchor="middle" font-family="${FONT}" font-size="11">Check email</text>`;
  p += `<text x="${midX}" y="${y + 12}" text-anchor="middle" font-family="${FONT}" font-size="11">and password</text>`;

  // No loop (left only) — does not cross Yes path
  const noX = leftBus - 36;
  p += `<text x="${midX - 80}" y="${y - 2}" text-anchor="end" font-family="${FONT}" font-size="12">No</text>`;
  line(midX - 72, y, noX, y);
  line(noX, y, noX, loginCy);
  arrow(noX, loginCy, mainX, loginCy);

  // Yes: down from diamond, then LEFT to leftBus only (no mid-T on top rail)
  p += `<text x="${midX + 80}" y="${y + 2}" font-family="${FONT}" font-size="12">Yes</text>`;
  const dropY = y + 48;
  line(midX, y + 34, midX, dropY);
  line(midX, dropY, leftBus, dropY);
  arrow(leftBus, dropY, leftBus, dropY + 18);

  const busTop = dropY + 18;
  const firstMid = busTop + 28;
  const lastMid = firstMid + (modules.length - 1) * rowH;

  // Left bus ends at last module (no stub past Change Password)
  line(leftBus, busTop, leftBus, lastMid);
  // Right bus covers all module rows
  line(rightBus, firstMid, rightBus, lastMid);

  // Gap so arrowheads stop at box edge (marker tip lands on edge)
  const leftIn = mainX;
  const leftFrom = leftBus;
  const rightOut = (m) => (m.export ? exportX + exportW : mainX + mainW);
  const arrowPad = 0.5;

  modules.forEach((m, i) => {
    const mid = firstMid + i * rowH;
    // left bus → module (stop at left edge; no overshoot past bus)
    arrow(leftFrom, mid, leftIn - arrowPad, mid);
    box(mainX, mid, mainW, m.label);
    if (m.export) {
      arrow(mainX + mainW + arrowPad, mid, exportX - arrowPad, mid);
      box(exportX, mid, exportW, 'Export Excel');
      arrow(exportX + exportW + arrowPad, mid, rightBus, mid);
    } else {
      arrow(mainX + mainW + arrowPad, mid, rightBus, mid);
    }
  });

  // Logout: enter from RIGHT side only (no line through the box)
  const logoutCy = lastMid + 56;
  line(rightBus, lastMid, rightBus, logoutCy);
  arrow(rightBus, logoutCy, mainX + mainW + arrowPad, logoutCy);
  box(mainX, logoutCy, mainW, 'Logout');
  arrow(midX, logoutCy + boxH / 2 + arrowPad, midX, logoutCy + 38);
  oval(logoutCy + 56, 'End');

  const height = logoutCy + 82;
  const width = rightBus + 48;

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
  <rect width="${width}" height="${height}" fill="#ffffff"/>
  <defs>
    <marker id="a" markerWidth="8" markerHeight="8" refX="7" refY="3.5" orient="auto" markerUnits="strokeWidth">
      <path d="M0,0 L7,3.5 L0,7 Z" fill="#000"/>
    </marker>
  </defs>
  ${p}
</svg>`;
}

const withExport = (label) => ({ label, export: true });
const plain = (label) => ({ label, export: false });

const superModules = [
  plain('View Dashboard'),
  withExport('Manage Student Quota'),
  withExport('Manage Allocation Plans'),
  withExport('Manage Textbooks'),
  withExport('Manage Stocks'),
  withExport('Manage School Supplies'),
  withExport('Manage Supply Details'),
  withExport('Teacher Guide Resources Management'),
  plain('Manage Academic Years'),
  plain('Year Rollover'),
  plain('Manage Townships'),
  plain('Manage Grades'),
  plain('Manage Book Names'),
  plain('Manage Grade–Subjects'),
  plain('Manage Company Contacts'),
  plain('User Management'),
  plain('Profile'),
  plain('Change Password'),
];

const adminModules = [
  plain('View Dashboard'),
  withExport('Manage Textbooks'),
  withExport('Manage Stocks'),
  withExport('Manage School Supplies'),
  withExport('Manage Supply Details'),
  withExport('Teacher Guide Resources Management'),
  plain('Manage Academic Years'),
  plain('Manage Townships'),
  plain('Manage Grades'),
  plain('Manage Book Names'),
  plain('Manage Grade–Subjects'),
  plain('Manage Company Contacts'),
  plain('View Reports'),
  plain('Profile'),
  plain('Change Password'),
];

save('Figure1a_Super_Admin_Flow_BW', buildFlow({ modules: superModules }));
save('Figure1b_Admin_Flow_BW', buildFlow({ modules: adminModules }));
