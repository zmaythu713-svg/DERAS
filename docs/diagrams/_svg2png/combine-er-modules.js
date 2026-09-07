/**
 * Combine Figure3a / 3b / 3c module ER PNGs into one thesis figure.
 * Uses external image hrefs (no base64) so Resvg can parse cleanly.
 */
const fs = require('fs');
const path = require('path');
const { pathToFileURL } = require('url');
const { Resvg } = require('@resvg/resvg-js');

const dir = path.join(__dirname, '..', 'images_final');
const sections = [
  ['A. Textbook / Quota / Allocation', 'Figure3a_ER_Textbook.png'],
  ['B. Teacher Guide', 'Figure3b_ER_TeacherGuide.png'],
  ['C. School Supplies &amp; Auth', 'Figure3c_ER_Supplies_Auth.png'],
];

function pngSize(buf) {
  return { w: buf.readUInt32BE(16), h: buf.readUInt32BE(20) };
}

const pad = 28;
const gap = 36;
const titleH = 30;
const topTitle = 48;

const items = sections.map(([title, name]) => {
  const file = path.join(dir, name);
  const buf = fs.readFileSync(file);
  const { w, h } = pngSize(buf);
  return { title, w, h, href: pathToFileURL(file).href };
});

const maxW = Math.max(...items.map((i) => i.w));
let y = topTitle;
let body = '';
for (const it of items) {
  body += `<text x="${pad}" y="${y + 20}" font-family="Times New Roman, Times, serif" font-size="16" font-weight="bold">${it.title}</text>\n`;
  y += titleH;
  const x = pad + Math.floor((maxW - it.w) / 2);
  body += `<image x="${x}" y="${y}" width="${it.w}" height="${it.h}" href="${it.href}"/>\n`;
  y += it.h + gap;
}

const W = maxW + pad * 2;
const H = y + pad;

const svg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <rect width="100%" height="100%" fill="#fff"/>
  <text x="${W / 2}" y="32" text-anchor="middle" font-family="Times New Roman, Times, serif" font-size="20" font-weight="bold">Figure 3 Entity Relationship Diagram of DERAS</text>
  ${body}
</svg>`;

const outSvg = path.join(dir, 'Figure3_ER_Combined.svg');
const outPng = path.join(dir, 'Figure3_ER_Combined.png');
fs.writeFileSync(outSvg, svg, 'utf8');

const renderW = Math.min(W, 2400);
const r = new Resvg(Buffer.from(svg), {
  fitTo: { mode: 'width', value: renderW },
  background: 'white',
});
fs.writeFileSync(outPng, r.render().asPng());
console.log('Wrote', outPng, `${W}x${H} -> width ${renderW}`);
