/**
 * DERAS Chen ER — compact multi-column layout (not a long vertical strip).
 * Panels in a grid; short local FK paths; masters may repeat per panel.
 */
const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Times New Roman, Times, serif';
const SW = 1.15;

function save(name, svg, w) {
  fs.writeFileSync(path.join(outDir, name + '.svg'), svg, 'utf8');
  const r = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: w },
    background: 'white',
  });
  fs.writeFileSync(path.join(outDir, name + '.png'), r.render().asPng());
  console.log('OK', name, `${svg.match(/width="(\d+)/)[1]}x${svg.match(/height="(\d+)/)[1]}`);
}

const esc = (t) => String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const card = (x, y, t) =>
  `<text x="${x}" y="${y}" text-anchor="middle" font-family="${FONT}" font-size="9">${esc(t)}</text>`;

function seg(x1, y1, x2, y2) {
  return `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#000" stroke-width="${SW}" stroke-linecap="butt"/>`;
}

function entity(cx, cy, name) {
  const w = Math.max(100, Math.min(210, 8 + name.length * 5.0));
  const h = 26;
  const fontSize = name.length > 24 ? 8.5 : 10;
  return {
    kind: 'rect', cx, cy, w, h,
    L: cx - w / 2, R: cx + w / 2, T: cy - h / 2, B: cy + h / 2,
    svg: `<rect x="${cx - w / 2}" y="${cy - h / 2}" width="${w}" height="${h}" fill="#fff" stroke="#000" stroke-width="1.3"/>
      <text x="${cx}" y="${cy + 3.2}" text-anchor="middle" font-family="${FONT}" font-size="${fontSize}">${esc(name)}</text>`,
  };
}

function oval(cx, cy, name, pk = false) {
  const parts = String(name).split('\n');
  const h = 10 + parts.length * 7;
  const w = Math.min(72, Math.max(30, 7 + Math.max(...parts.map((p) => p.length)) * 3.8));
  const rx = w / 2;
  const ry = h / 2;
  let svg = `<ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="#fff" stroke="#000" stroke-width="1"/>`;
  const start = cy - ((parts.length - 1) * 3.5) + 2;
  parts.forEach((txt, i) => {
    svg += `<text x="${cx}" y="${start + i * 7}" text-anchor="middle" font-family="${FONT}" font-size="7.5"${pk ? ' text-decoration="underline"' : ''}>${esc(txt)}</text>`;
  });
  return { kind: 'ellipse', cx, cy, rx, ry, w, h, L: cx - rx, R: cx + rx, T: cy - ry, B: cy + ry, svg };
}

function diamond(cx, cy) {
  const hw = 16;
  const hh = 10;
  return {
    kind: 'diamond', cx, cy, hw, hh,
    L: cx - hw, R: cx + hw, T: cy - hh, B: cy + hh,
    svg: `<path d="M${cx},${cy - hh} L${cx + hw},${cy} L${cx},${cy + hh} L${cx - hw},${cy} Z" fill="#fff" stroke="#000" stroke-width="1"/>
      <text x="${cx}" y="${cy + 2.5}" text-anchor="middle" font-family="${FONT}" font-size="6.5">Has</text>`,
  };
}

function tip(d, dir) {
  if (dir === 'L') return { x: d.L, y: d.cy };
  if (dir === 'R') return { x: d.R, y: d.cy };
  if (dir === 'T') return { x: d.cx, y: d.T };
  return { x: d.cx, y: d.B };
}

function tipToward(d, shape) {
  const dx = shape.cx - d.cx;
  const dy = shape.cy - d.cy;
  if (Math.abs(dx) >= Math.abs(dy)) return tip(d, dx >= 0 ? 'R' : 'L');
  return tip(d, dy >= 0 ? 'B' : 'T');
}

function edgePoint(shape, tx, ty, inset = SW / 2) {
  const dx = tx - shape.cx;
  const dy = ty - shape.cy;
  if (dx === 0 && dy === 0) return { x: shape.cx, y: shape.cy };
  let x, y;
  if (shape.kind === 'rect') {
    const hw = shape.w / 2;
    const hh = shape.h / 2;
    const sx = dx !== 0 ? hw / Math.abs(dx) : Infinity;
    const sy = dy !== 0 ? hh / Math.abs(dy) : Infinity;
    const s = Math.min(sx, sy);
    x = shape.cx + dx * s;
    y = shape.cy + dy * s;
  } else if (shape.kind === 'ellipse') {
    const len = Math.hypot(dx / shape.rx, dy / shape.ry) || 1;
    x = shape.cx + dx / len;
    y = shape.cy + dy / len;
  } else {
    const len = Math.abs(dx) / shape.hw + Math.abs(dy) / shape.hh || 1;
    x = shape.cx + dx / len;
    y = shape.cy + dy / len;
  }
  const ux = x - shape.cx;
  const uy = y - shape.cy;
  const ul = Math.hypot(ux, uy) || 1;
  return { x: x - (ux / ul) * inset, y: y - (uy / ul) * inset };
}

function linkDiamond(shape, d) {
  const t = tipToward(d, shape);
  const p = edgePoint(shape, t.x, t.y, SW + 0.3);
  return seg(p.x, p.y, t.x, t.y);
}

function link(a, b) {
  const p1 = edgePoint(a, b.cx, b.cy);
  const p2 = edgePoint(b, a.cx, a.cy);
  return seg(p1.x, p1.y, p2.x, p2.y);
}

function placeAttrs(e, items, side) {
  const n = items.length;
  const gapH = 58;
  const gapV = 28;
  const dist = side === 'T' || side === 'B' ? 28 : 42;
  const out = [];
  items.forEach((it, i) => {
    const name = Array.isArray(it) ? it[0] : it;
    const pk = Array.isArray(it) ? !!it[1] : false;
    const off = i - (n - 1) / 2;
    let ax, ay;
    if (side === 'T') { ax = e.cx + off * gapH; ay = e.T - dist; }
    else if (side === 'B') { ax = e.cx + off * gapH; ay = e.B + dist; }
    else if (side === 'L') { ax = e.L - dist; ay = e.cy + off * gapV; }
    else { ax = e.R + dist; ay = e.cy + off * gapV; }
    out.push({ shape: oval(ax, ay, name, pk), entity: e });
  });
  return out;
}

/** Draw one panel; returns {svg, w, h} in local coords starting at 0,0 */
function makePanel(title, drawFn) {
  const ox = 0;
  const oy = 40;
  const diamonds = [];
  const cards = [];
  let lines = '';
  const entities = [];
  const attrPairs = [];

  const E = (x, y, name) => {
    const e = entity(ox + x, oy + y, name);
    entities.push(e);
    return e;
  };
  const A = (e, items, side) => placeAttrs(e, items, side).forEach((p) => attrPairs.push(p));
  const hasV = (a, b) => {
    const d = diamond(a.cx, (a.B + b.T) / 2);
    diamonds.push(d);
    lines += linkDiamond(a, d) + linkDiamond(b, d);
    cards.push(card(d.cx + 10, a.B + 8, '1'));
    cards.push(card(d.cx + 10, b.T - 3, 'M'));
  };
  const hasH = (a, b) => {
    const d = diamond((a.R + b.L) / 2, a.cy);
    diamonds.push(d);
    lines += linkDiamond(a, d) + linkDiamond(b, d);
    cards.push(card(a.R + 8, a.cy - 7, '1'));
    cards.push(card(b.L - 8, b.cy - 7, 'M'));
  };
  const linkParents = (parents, child) => {
    const sorted = [...parents].sort((a, b) => a.cx - b.cx);
    const n = sorted.length;
    const usable = Math.max(56, Math.min(child.w + 36, 32 * n));
    const left = child.cx - usable / 2;
    sorted.forEach((p, i) => {
      const dx = n === 1 ? child.cx : left + (i / (n - 1)) * usable;
      const dy = child.T - 30;
      const d = diamond(dx, dy);
      diamonds.push(d);
      const vx = p.cx;
      const ep = edgePoint(p, vx, p.cy);
      lines += seg(ep.x, ep.y, vx, p.cy);
      lines += seg(vx, p.cy, vx, dy);
      if (Math.abs(vx - dx) < 2) {
        lines += seg(vx, dy, tip(d, 'T').x, tip(d, 'T').y);
      } else {
        const side = vx < dx ? 'L' : 'R';
        lines += seg(vx, dy, tip(d, side).x, tip(d, side).y);
      }
      lines += linkDiamond(child, d);
      cards.push(card(dx, dy - 11, '1'));
      cards.push(card(dx, dy + 12, 'M'));
    });
  };

  drawFn({ E, A, hasV, hasH, linkParents });

  let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
  const bump = (L, R, T, B) => {
    minX = Math.min(minX, L); maxX = Math.max(maxX, R);
    minY = Math.min(minY, T); maxY = Math.max(maxY, B);
  };
  entities.forEach((e) => bump(e.L, e.R, e.T, e.B));
  attrPairs.forEach(({ shape: s }) => bump(s.L, s.R, s.T, s.B));
  diamonds.forEach((d) => bump(d.L, d.R, d.T, d.B));

  const pad = 16;
  const fx = minX - pad;
  const fy = 0;
  const fw = maxX - minX + pad * 2;
  const fh = maxY + pad;

  let body = '';
  body += `<rect x="${fx}" y="${fy}" width="${fw}" height="${fh}" fill="#fff" stroke="#444" stroke-width="1.2"/>`;
  body += `<text x="${fx + 10}" y="${fy + 18}" font-family="${FONT}" font-size="12" font-weight="bold">${esc(title)}</text>`;
  attrPairs.forEach(({ shape, entity: ent }) => { body += link(shape, ent); });
  body += lines;
  diamonds.forEach((d) => { body += d.svg; });
  entities.forEach((e) => { body += e.svg; });
  attrPairs.forEach(({ shape }) => { body += shape.svg; });
  cards.forEach((c) => { body += c; });

  // normalize to local 0,0
  const local = `<g transform="translate(${-fx},${0})">${body}</g>`;
  return { svg: local, w: fw, h: fh };
}

function build() {
  const panels = [];

  panels.push(makePanel('(A) Quota & School Count', ({ E, A, hasV, hasH, linkParents }) => {
    const year1 = E(120, 40, 'academic_year');
    const town1 = E(320, 40, 'township');
    A(year1, [['id', 1]], 'T');
    A(town1, [['id', 1]], 'T');
    const quota = E(160, 170, 'quota');
    const qline = E(340, 170, 'quota_line');
    A(quota, [['id', 1]], 'L');
    A(qline, [['id', 1]], 'R');
    hasV(year1, quota);
    hasH(quota, qline);
    linkParents([town1], quota);

    const year2 = E(120, 290, 'academic_year');
    const town2 = E(320, 290, 'township');
    const grade2 = E(520, 290, 'grade');
    A(grade2, [['id', 1]], 'T');
    const sc = E(320, 420, 'school_count');
    A(sc, [['id', 1]], 'B');
    linkParents([year2, town2, grade2], sc);
  }));

  panels.push(makePanel('(B) Grade–Book & Allocation', ({ E, A, hasV, hasH, linkParents }) => {
    const grade = E(140, 40, 'grade');
    const book = E(340, 40, 'book_name');
    const category = E(560, 40, 'category');
    A(grade, [['id', 1]], 'T');
    A(book, [['id', 1]], 'T');
    A(category, [['id', 1]], 'T');
    const gbn = E(340, 170, 'grade_book_name');
    A(gbn, [['id', 1]], 'R');
    hasV(grade, gbn);
    linkParents([book, category], gbn);

    const year = E(120, 290, 'academic_year');
    const town = E(300, 290, 'township');
    const grade2 = E(480, 290, 'grade');
    const book2 = E(680, 290, 'book_name');
    A(year, [['id', 1]], 'T');
    A(town, [['id', 1]], 'T');
    const plan = E(300, 430, 'allocation_plan');
    const apt = E(520, 430, 'allocation_plan_township');
    A(plan, [['id', 1]], 'L');
    A(apt, [['id', 1]], 'R');
    linkParents([year, grade2, book2], plan);
    hasH(plan, apt);
    linkParents([town], apt);
  }));

  panels.push(makePanel('(C) Textbook', ({ E, A, linkParents }) => {
    const year = E(100, 40, 'academic_year');
    const town = E(280, 40, 'township');
    const grade = E(460, 40, 'grade');
    const book = E(640, 40, 'book_name');
    A(year, [['id', 1]], 'T');
    A(town, [['id', 1]], 'T');
    A(grade, [['id', 1]], 'T');
    A(book, [['id', 1]], 'T');
    const tb = E(340, 180, 'textbook');
    A(tb, [['id', 1]], 'B');
    linkParents([year, town, grade, book], tb);
  }));

  panels.push(makePanel('(D) Stock', ({ E, A, linkParents }) => {
    const year = E(100, 40, 'academic_year');
    const town = E(280, 40, 'township');
    const grade = E(460, 40, 'grade');
    const book = E(640, 40, 'book_name');
    A(year, [['id', 1]], 'T');
    A(town, [['id', 1]], 'T');
    A(grade, [['id', 1]], 'T');
    A(book, [['id', 1]], 'T');
    const stock = E(340, 180, 'stock');
    A(stock, [['id', 1]], 'B');
    linkParents([year, town, grade, book], stock);
  }));

  panels.push(makePanel('(E) Previous-Year Balance', ({ E, A, linkParents }) => {
    const year = E(100, 40, 'academic_year');
    const town = E(280, 40, 'township');
    const grade = E(460, 40, 'grade');
    const book = E(640, 40, 'book_name');
    A(year, [['id', 1]], 'T');
    A(town, [['id', 1]], 'T');
    A(grade, [['id', 1]], 'T');
    A(book, [['id', 1]], 'T');
    const pyb = E(340, 180, 'previous_year_balance');
    A(pyb, [['id', 1]], 'B');
    linkParents([year, town, grade, book], pyb);
  }));

  panels.push(makePanel('(F) Teacher Guide Receipt', ({ E, A, linkParents }) => {
    const year = E(120, 40, 'academic_year');
    const grade = E(300, 40, 'grade');
    const book = E(480, 40, 'book_name');
    A(year, [['id', 1]], 'T');
    A(grade, [['id', 1]], 'T');
    A(book, [['id', 1]], 'T');
    const tg = E(300, 170, 'teacher_guide');
    A(tg, [['id', 1]], 'L');
    linkParents([year, grade, book], tg);

    const tg2 = E(200, 290, 'teacher_guide');
    const town = E(420, 290, 'township');
    A(town, [['id', 1]], 'T');
    const tgta = E(310, 420, 'teacher_guide_township_allocation');
    A(tgta, [['id', 1]], 'B');
    linkParents([tg2, town], tgta);
  }));

  panels.push(makePanel('(G) Teacher Guide Summary', ({ E, A, linkParents }) => {
    const tg = E(100, 40, 'teacher_guide');
    const year = E(280, 40, 'academic_year');
    const grade = E(460, 40, 'grade');
    const book = E(640, 40, 'book_name');
    A(tg, [['id', 1]], 'T');
    A(year, [['id', 1]], 'T');
    A(grade, [['id', 1]], 'T');
    A(book, [['id', 1]], 'T');
    const tgs = E(360, 180, 'teacher_guide_summary');
    A(tgs, [['id', 1]], 'B');
    linkParents([tg, year, grade, book], tgs);
  }));

  panels.push(makePanel('(H) Teacher Guide Issue', ({ E, A, hasV, linkParents }) => {
    const tg = E(140, 40, 'teacher_guide');
    const year = E(340, 40, 'academic_year');
    const grade = E(520, 40, 'grade');
    const book = E(700, 40, 'book_name');
    A(tg, [['id', 1]], 'T');
    A(year, [['id', 1]], 'T');
    A(grade, [['id', 1]], 'T');
    A(book, [['id', 1]], 'T');
    const tgi = E(140, 180, 'teacher_guide_issue');
    A(tgi, [['id', 1]], 'L');
    hasV(tg, tgi);
    linkParents([year, grade, book], tgi);

    const tgi2 = E(220, 300, 'teacher_guide_issue');
    const town = E(420, 300, 'township');
    A(town, [['id', 1]], 'T');
    const tgit = E(320, 430, 'teacher_guide_issue_township');
    A(tgit, [['id', 1]], 'B');
    linkParents([tgi2, town], tgit);
  }));

  panels.push(makePanel('(I) School Supplies', ({ E, A, hasH, linkParents }) => {
    const ssi = E(120, 170, 'school_supply_item');
    const year = E(320, 40, 'academic_year');
    const town = E(500, 40, 'township');
    const grade = E(680, 40, 'grade');
    A(ssi, [['id', 1]], 'L');
    A(year, [['id', 1]], 'T');
    A(town, [['id', 1]], 'T');
    A(grade, [['id', 1]], 'T');
    const ssa = E(500, 170, 'school_supply_allocation');
    A(ssa, [['id', 1]], 'R');
    hasH(ssi, ssa);
    linkParents([year, town, grade], ssa);
  }));

  panels.push(makePanel('(J) Supply Details', ({ E, A, hasH, linkParents }) => {
    const si = E(120, 170, 'supply_item');
    const year = E(320, 40, 'academic_year');
    const town = E(500, 40, 'township');
    const grade = E(680, 40, 'grade');
    A(si, [['id', 1]], 'L');
    A(year, [['id', 1]], 'T');
    A(town, [['id', 1]], 'T');
    A(grade, [['id', 1]], 'T');
    const sd = E(500, 170, 'supply_detail');
    A(sd, [['id', 1]], 'R');
    hasH(si, sd);
    linkParents([year, town, grade], sd);
  }));

  panels.push(makePanel('(K) Users & Roles', ({ E, A, hasV, linkParents }) => {
    const role = E(150, 40, 'role');
    const permission = E(380, 40, 'permission');
    A(role, [['id', 1]], 'T');
    A(permission, [['id', 1]], 'T');
    const user = E(150, 170, 'user');
    const rolePerm = E(380, 170, 'role_permission');
    A(user, [['id', 1]], 'B');
    A(rolePerm, [['id', 1]], 'R');
    hasV(role, user);
    hasV(permission, rolePerm);
    linkParents([role], rolePerm);
  }));

  // Pack into 3-column grid (compact page)
  const COLS = 3;
  const gapX = 24;
  const gapY = 24;
  const margin = 40;
  const titleH = 36;

  // normalize panel sizes: use max width per column row packing
  const colW = [];
  for (let i = 0; i < COLS; i++) colW[i] = 0;
  panels.forEach((p, i) => {
    const c = i % COLS;
    colW[c] = Math.max(colW[c], p.w);
  });

  const colX = [margin];
  for (let c = 1; c < COLS; c++) colX[c] = colX[c - 1] + colW[c - 1] + gapX;

  const rowH = [];
  const rows = Math.ceil(panels.length / COLS);
  for (let r = 0; r < rows; r++) {
    let h = 0;
    for (let c = 0; c < COLS; c++) {
      const p = panels[r * COLS + c];
      if (p) h = Math.max(h, p.h);
    }
    rowH[r] = h;
  }
  const rowY = [margin + titleH];
  for (let r = 1; r < rows; r++) rowY[r] = rowY[r - 1] + rowH[r - 1] + gapY;

  let body = '';
  body += `<text x="${margin}" y="${margin + 20}" font-family="${FONT}" font-size="18" font-weight="bold">Figure 3: Entity Relationship Diagram (DERAS)</text>`;

  panels.forEach((p, i) => {
    const c = i % COLS;
    const r = Math.floor(i / COLS);
    const x = colX[c];
    const y = rowY[r];
    body += `<g transform="translate(${x},${y})">${p.svg}</g>`;
  });

  const noteY = rowY[rows - 1] + rowH[rows - 1] + 28;
  body += `<text x="${margin}" y="${noteY}" font-family="${FONT}" font-size="11">Note: The same master entity may appear in more than one panel so foreign-key lines stay short and do not overlap.</text>`;

  const W = colX[COLS - 1] + colW[COLS - 1] + margin;
  const H = noteY + 30;

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <rect width="${W}" height="${H}" fill="#fff"/>
  ${body}
</svg>`;
}

const svg = build();
save('Figure3_ER_Chen_BW', svg, 2400);
save('Figure3_ER_Diagram', svg, 2400);
save('Figure3_ER_Normalized_BW', svg, 2400);
console.log('Done');
