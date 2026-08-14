const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const W = 920;
const FONT = 'Segoe UI, Arial, Helvetica, sans-serif';

function save(name, svg) {
  const svgPath = path.join(outDir, name + '.svg');
  const pngPath = path.join(outDir, name + '.png');
  fs.writeFileSync(svgPath, svg, 'utf8');
  const resvg = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: 2200 },
    background: 'white',
  });
  fs.writeFileSync(pngPath, resvg.render().asPng());
  console.log('OK', name, pngPath);
}

function rrect(x, y, w, h, label, lines = 1) {
  const r = 10;
  let t = `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="${r}" ry="${r}" fill="#ffffff" stroke="#1f2937" stroke-width="1.6"/>`;
  if (lines === 1) {
    t += `<text x="${x + w / 2}" y="${y + h / 2 + 5}" text-anchor="middle" font-family="${FONT}" font-size="13" fill="#111827">${label}</text>`;
  } else {
    const parts = label.split('\n');
    const start = y + h / 2 - (parts.length - 1) * 8;
    parts.forEach((p, i) => {
      t += `<text x="${x + w / 2}" y="${start + i * 16}" text-anchor="middle" font-family="${FONT}" font-size="12.5" fill="#111827">${p}</text>`;
    });
  }
  return t;
}

function oval(cx, cy, rx, ry, label) {
  return `<ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="#ffffff" stroke="#1f2937" stroke-width="1.6"/>
  <text x="${cx}" y="${cy + 5}" text-anchor="middle" font-family="${FONT}" font-size="13" fill="#111827">${label}</text>`;
}

function diamond(cx, cy, hw, hh, line1, line2) {
  return `<path d="M${cx},${cy - hh} L${cx + hw},${cy} L${cx},${cy + hh} L${cx - hw},${cy} Z" fill="#ffffff" stroke="#1f2937" stroke-width="1.6"/>
  <text x="${cx}" y="${cy - 4}" text-anchor="middle" font-family="${FONT}" font-size="12" fill="#111827">${line1}</text>
  <text x="${cx}" y="${cy + 12}" text-anchor="middle" font-family="${FONT}" font-size="12" fill="#111827">${line2}</text>`;
}

function section(x, y, w, h, title) {
  return `<rect x="${x}" y="${y}" width="${w}" height="${h}" rx="14" ry="14" fill="#f8fafc" stroke="#94a3b8" stroke-width="1.2" stroke-dasharray="5 4"/>
  <text x="${x + 16}" y="${y + 22}" font-family="${FONT}" font-size="12" font-weight="600" fill="#334155">${title}</text>`;
}

function arrow(x1, y1, x2, y2) {
  return `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#1f2937" stroke-width="1.45" marker-end="url(#arrow)"/>`;
}

function plain(x1, y1, x2, y2) {
  return `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#1f2937" stroke-width="1.45"/>`;
}

const cx = 460;
const boxW = 260;
const boxX = cx - boxW / 2;

let y = 70;
const parts = [];

// Title
parts.push(`<text x="${cx}" y="36" text-anchor="middle" font-family="${FONT}" font-size="18" font-weight="700" fill="#0f172a">Figure 1: System Flow Diagram</text>`);
parts.push(`<text x="${cx}" y="56" text-anchor="middle" font-family="${FONT}" font-size="12" fill="#475569">Web-Based Education Resource Allocation and Inventory Management System (DERAS)</text>`);

// ===== AUTHENTICATION =====
const authTop = 78;
const authH = 290;
parts.push(section(120, authTop, 680, authH, '1. Authentication'));

y = authTop + 48;
parts.push(oval(cx, y, 58, 18, 'Start'));
parts.push(arrow(cx, y + 18, cx, y + 40));
y += 58;
parts.push(rrect(boxX, y, boxW, 36, 'User Login'));
parts.push(arrow(cx, y + 36, cx, y + 56));
y += 78;
parts.push(diamond(cx, y, 110, 42, 'Is the user', 'authenticated?'));
parts.push(`<text x="${cx - 122}" y="${y - 4}" text-anchor="end" font-family="${FONT}" font-size="12" fill="#111827">No</text>`);
parts.push(plain(cx - 110, y, cx - 210, y));
parts.push(plain(cx - 210, y, cx - 210, y - 78));
parts.push(arrow(cx - 210, y - 78, boxX, y - 78));
parts.push(`<text x="${cx + 118}" y="${y - 4}" font-family="${FONT}" font-size="12" fill="#111827">Yes</text>`);
parts.push(arrow(cx, y + 42, cx, y + 64));
y += 82;
parts.push(rrect(boxX, y, boxW, 36, 'Access System Dashboard'));

const afterAuth = authTop + authH;
parts.push(arrow(cx, y + 36, cx, afterAuth + 28));

const masterTop = afterAuth + 16;
const masterH = 250;
parts.push(section(120, masterTop, 680, masterH, '2. Master Data Management'));

y = masterTop + 48;
parts.push(rrect(boxX, y, boxW, 36, 'Manage Academic Years'));
parts.push(arrow(cx, y + 36, cx, y + 54));
y += 70;
parts.push(rrect(boxX, y, boxW, 36, 'Manage Townships / Grades / Subjects'));
parts.push(arrow(cx, y + 36, cx, y + 54));
y += 70;
parts.push(rrect(boxX, y, boxW, 36, 'Manage Book Names / Contacts'));

const afterMaster = masterTop + masterH;
parts.push(arrow(cx, y + 36, cx, afterMaster + 28));

const invTop = afterMaster + 16;
const invH = 320;
parts.push(section(90, invTop, 740, invH, '3. Inventory Management'));

y = invTop + 48;
parts.push(rrect(boxX, y, boxW, 36, 'Update Stock / Supplies / Teacher Guides'));
parts.push(arrow(cx, y + 36, cx, y + 56));
y += 78;
parts.push(diamond(cx, y, 110, 42, 'Is sufficient', 'stock available?'));
parts.push(`<text x="${cx + 118}" y="${y - 4}" font-family="${FONT}" font-size="12" fill="#111827">No</text>`);
parts.push(plain(cx + 110, y, cx + 250, y));
parts.push(arrow(cx + 250, y, cx + 250, y + 58));
parts.push(rrect(cx + 250 - 100, y + 58, 200, 40, 'Request / Adjust\nInventory', 2));
parts.push(plain(cx + 250, y + 98, cx + 250, invTop + 66));
parts.push(arrow(cx + 250, invTop + 66, boxX + boxW, invTop + 66));
parts.push(`<text x="${cx - 122}" y="${y - 4}" text-anchor="end" font-family="${FONT}" font-size="12" fill="#111827">Yes</text>`);
parts.push(arrow(cx, y + 42, cx, y + 64));
y += 90;
parts.push(rrect(boxX, y, boxW, 36, 'Confirm Inventory Readiness'));

const afterInv = invTop + invH;
parts.push(arrow(cx, y + 36, cx, afterInv + 28));

const allocTop = afterInv + 16;
const allocH = 340;
parts.push(section(90, allocTop, 740, allocH, '4. Resource Allocation'));

y = allocTop + 48;
parts.push(rrect(boxX, y, boxW, 36, 'Enter Student Quota'));
parts.push(arrow(cx, y + 36, cx, y + 54));
y += 70;
parts.push(rrect(boxX, y, boxW, 36, 'Create Allocation Plan'));
parts.push(arrow(cx, y + 36, cx, y + 56));
y += 78;
parts.push(diamond(cx, y, 110, 42, 'Is the allocation', 'approved?'));
parts.push(`<text x="${cx - 122}" y="${y - 4}" text-anchor="end" font-family="${FONT}" font-size="12" fill="#111827">No</text>`);
parts.push(plain(cx - 110, y, cx - 250, y));
parts.push(arrow(cx - 250, y, cx - 250, y + 58));
parts.push(rrect(cx - 250 - 100, y + 58, 200, 40, 'Revise Allocation\nPlan', 2));
parts.push(plain(cx - 250, y + 98, cx - 250, allocTop + 136));
parts.push(arrow(cx - 250, allocTop + 136, boxX, allocTop + 136));
parts.push(`<text x="${cx + 118}" y="${y - 4}" font-family="${FONT}" font-size="12" fill="#111827">Yes</text>`);
parts.push(arrow(cx, y + 42, cx, y + 64));
y += 90;
parts.push(rrect(boxX, y, boxW, 36, 'Distribute Resources to Townships'));

const afterAlloc = allocTop + allocH;
parts.push(arrow(cx, y + 36, cx, afterAlloc + 28));

const repTop = afterAlloc + 16;
const repH = 230;
parts.push(section(120, repTop, 680, repH, '5. Reporting'));

y = repTop + 48;
parts.push(rrect(boxX, y, boxW, 36, 'Generate / Export Reports (XLSX)'));
parts.push(arrow(cx, y + 36, cx, y + 54));
y += 70;
parts.push(rrect(boxX, y, boxW, 36, 'Logout'));
parts.push(arrow(cx, y + 36, cx, y + 58));
y += 78;
parts.push(oval(cx, y, 50, 18, 'End'));

const H = y + 48;

const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <rect width="${W}" height="${H}" fill="#ffffff"/>
  <defs>
    <marker id="arrow" markerWidth="8" markerHeight="8" refX="7" refY="3.5" orient="auto">
      <path d="M0,0 L7,3.5 L0,7 Z" fill="#1f2937"/>
    </marker>
  </defs>
  ${parts.join('\n')}
</svg>`;

save('Figure1_System_Flow_Academic', svg);
