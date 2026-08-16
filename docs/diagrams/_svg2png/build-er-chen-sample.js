/**
 * DERAS Chen ER
 * - Relationship lines leave exactly from Has diamond tips (vertices)
 * - No relationship line passes through attribute ovals
 */
const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const outDir = path.join(__dirname, '..', 'images_final');
const FONT = 'Times New Roman, Times, serif';
const SW = 1.2;

function save(name, svg, w = 1700) {
  fs.writeFileSync(path.join(outDir, name + '.svg'), svg, 'utf8');
  const r = new Resvg(Buffer.from(svg), {
    fitTo: { mode: 'width', value: w },
    background: 'white',
  });
  fs.writeFileSync(path.join(outDir, name + '.png'), r.render().asPng());
  console.log('OK', name);
}

const esc = (t) => String(t).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const card = (x, y, t) =>
  `<text x="${x}" y="${y}" text-anchor="middle" font-family="${FONT}" font-size="12">${esc(t)}</text>`;

function seg(x1, y1, x2, y2) {
  return `<line x1="${x1}" y1="${y1}" x2="${x2}" y2="${y2}" stroke="#000" stroke-width="${SW}" stroke-linecap="butt"/>`;
}

function entity(cx, cy, name) {
  const w = Math.max(130, 12 + name.length * 6.6);
  const h = 30;
  return {
    kind: 'rect', cx, cy, w, h,
    L: cx - w / 2, R: cx + w / 2, T: cy - h / 2, B: cy + h / 2,
    svg: `<rect x="${cx - w / 2}" y="${cy - h / 2}" width="${w}" height="${h}" fill="#fff" stroke="#000" stroke-width="1.5"/>
      <text x="${cx}" y="${cy + 4}" text-anchor="middle" font-family="${FONT}" font-size="12">${esc(name)}</text>`,
  };
}

function oval(cx, cy, name, pk = false) {
  const parts = String(name).split('\n');
  const h = 14 + parts.length * 9;
  const w = Math.min(120, Math.max(50, 12 + Math.max(...parts.map((p) => p.length)) * 5.3));
  const rx = w / 2;
  const ry = h / 2;
  let svg = `<ellipse cx="${cx}" cy="${cy}" rx="${rx}" ry="${ry}" fill="#fff" stroke="#000" stroke-width="1"/>`;
  const start = cy - ((parts.length - 1) * 4.5) + 3;
  parts.forEach((txt, i) => {
    svg += `<text x="${cx}" y="${start + i * 9}" text-anchor="middle" font-family="${FONT}" font-size="10"${pk ? ' text-decoration="underline"' : ''}>${esc(txt)}</text>`;
  });
  return { kind: 'ellipse', cx, cy, rx, ry, w, h, L: cx - rx, R: cx + rx, T: cy - ry, B: cy + ry, svg };
}

function diamond(cx, cy, label) {
  const hw = 32;
  const hh = 22;
  return {
    kind: 'diamond', cx, cy, hw, hh,
    L: cx - hw, R: cx + hw, T: cy - hh, B: cy + hh,
    svg: `<path d="M${cx},${cy - hh} L${cx + hw},${cy} L${cx},${cy + hh} L${cx - hw},${cy} Z" fill="#fff" stroke="#000" stroke-width="1.3"/>
      <text x="${cx}" y="${cy + 4}" text-anchor="middle" font-family="${FONT}" font-size="10">${esc(label)}</text>`,
  };
}

/** Exact diamond tip (no inset — line must leave the sharp corner) */
function tip(d, dir) {
  if (dir === 'L') return { x: d.L, y: d.cy };
  if (dir === 'R') return { x: d.R, y: d.cy };
  if (dir === 'T') return { x: d.cx, y: d.T };
  return { x: d.cx, y: d.B };
}

/** Border point on rect/ellipse facing (tx,ty); slight inset so stroke sits on rim */
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
  } else {
    const { rx, ry } = shape;
    const len = Math.hypot(dx / rx, dy / ry) || 1;
    x = shape.cx + dx / len;
    y = shape.cy + dy / len;
  }
  const ux = x - shape.cx;
  const uy = y - shape.cy;
  const ul = Math.hypot(ux, uy) || 1;
  return { x: x - (ux / ul) * inset, y: y - (uy / ul) * inset };
}

/** Entity/oval → diamond tip (tip exact; entity end inset so line does not enter box) */
function linkTip(shape, d, dir) {
  const t = tip(d, dir);
  const p = edgePoint(shape, t.x, t.y, SW + 0.4);
  return seg(p.x, p.y, t.x, t.y);
}

/** Attr oval ↔ entity (edge to edge) */
function link(a, b) {
  const p1 = edgePoint(a, b.cx, b.cy);
  const p2 = edgePoint(b, a.cx, a.cy);
  return seg(p1.x, p1.y, p2.x, p2.y);
}

function placeAttrs(e, items, side) {
  const n = items.length;
  const gapH = 118;
  const gapV = 58;
  const dist = side === 'T' || side === 'B' ? 56 : 90;
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

function build() {
  const PAD_L = 55; // left white margin so leftmost attrs are not flush
  const PAD_B = 55; // bottom white margin
  const W = 1640;
  const H = 1080 + PAD_B;

  const year = entity(200, 130, 'academic_year');
  const quota = entity(500, 130, 'quota');
  const qline = entity(800, 130, 'quota_line');
  const grade = entity(1100, 130, 'grade');
  const book = entity(1400, 130, 'book_name');

  const plan = entity(200, 360, 'allocation_plan');
  const apt = entity(500, 360, 'allocation_plan_township');
  const town = entity(800, 360, 'township');
  const role = entity(1100, 360, 'role');
  const user = entity(1400, 360, 'user');

  const tb = entity(200, 590, 'textbook');
  const stock = entity(500, 590, 'stock');
  const pyb = entity(800, 590, 'previous_year_balance');
  const ssi = entity(1100, 590, 'school_supply_item');
  const ssa = entity(1400, 590, 'school_supply_allocation');

  const tg = entity(200, 800, 'teacher_guide');
  const tgta = entity(500, 800, 'tg_township_allocation');
  const tgs = entity(800, 800, 'teacher_guide_summary');

  const tgi = entity(200, 990, 'teacher_guide_issue');
  const tgit = entity(500, 990, 'tg_issue_township');
  const si = entity(1100, 990, 'supply_item');
  const sd = entity(1400, 990, 'supply_detail');

  const entities = [year, quota, qline, grade, book, plan, apt, town, role, user,
    tb, stock, pyb, ssi, ssa, tg, tgta, tgs, tgi, tgit, si, sd];

  // pyb attrs on B (not R) so right-of-pyb corridor stays clear
  // ssi attrs on R (not T-left) so nothing sits on x≈980–1100 top fan over the old rail
  const attrPairs = [
    ...placeAttrs(year, [['id', 1], 'name', 'is_current'], 'T'),
    ...placeAttrs(quota, [['id', 1]], 'T'),
    ...placeAttrs(qline, [['id', 1], 'school_level', 'quantity'], 'T'),
    ...placeAttrs(grade, [['id', 1], 'name'], 'T'),
    ...placeAttrs(book, [['id', 1], 'name'], 'T'),
    ...placeAttrs(plan, [['id', 1], 'received_books', 'books_per_package'], 'L'),
    ...placeAttrs(apt, [['id', 1], 'previous', 'total_students', 'transferable'], 'B'),
    ...placeAttrs(town, [['id', 1], 'name', 'is_active'], 'T'),
    ...placeAttrs(role, [['id', 1], 'name', 'slug'], 'T'),
    ...placeAttrs(user, [['id', 1], 'name', 'email'], 'T'),
    ...placeAttrs(tb, [['id', 1], 'books_per_set', 'student_count'], 'L'),
    ...placeAttrs(stock, [['id', 1], 'previous_balance', 'required_qty'], 'B'),
    ...placeAttrs(pyb, [['id', 1], 'balance'], 'B'),
    ...placeAttrs(ssi, [['id', 1], 'name', 'rate'], 'T'),
    ...placeAttrs(ssa, [['id', 1], 'quantity'], 'T'),
    ...placeAttrs(tg, [['id', 1], 'guide_type', 'kg_to_g12_quota', 'g1_to_g5_quota'], 'L'),
    ...placeAttrs(tgta, [['id', 1], 'kg_g12_qty', 'g1_g5_qty'], 'B'),
    ...placeAttrs(tgs, [['id', 1], 'previous_balance', 'distributed_books'], 'B'),
    ...placeAttrs(tgi, [['id', 1], 'district_unit', 'package_unit'], 'L'),
    ...placeAttrs(tgit, [['id', 1], 'issued_quantity'], 'B'),
    ...placeAttrs(si, [['id', 1], 'name'], 'B'),
    ...placeAttrs(sd, [['id', 1], 'unit', 'issued_total'], 'B'),
  ];

  const diamonds = [];
  const cards = [];
  let lines = '';

  function relH(a, b, label, cL, cR) {
    const d = diamond((a.R + b.L) / 2, a.cy, label);
    diamonds.push(d);
    lines += linkTip(a, d, 'L') + linkTip(b, d, 'R');
    cards.push(card(a.R + 14, a.cy - 10, cL));
    cards.push(card(b.L - 14, b.cy - 10, cR));
  }

  function relV(a, b, label, cT, cB) {
    const d = diamond(a.cx, (a.B + b.T) / 2, label);
    diamonds.push(d);
    lines += linkTip(a, d, 'T') + linkTip(b, d, 'B');
    cards.push(card(d.cx + 14, a.B + 14, cT));
    cards.push(card(d.cx + 14, b.T - 6, cB));
  }

  relH(year, quota, 'Has', '1', 'M');
  relH(quota, qline, 'Has', '1', 'M');
  relH(grade, book, 'Has', '1', 'M');

  relV(year, plan, 'Has', '1', 'M');
  relV(plan, tb, 'Syncs', '1', 'M');
  relV(tg, tgi, 'Has', '1', 'M');

  relH(plan, apt, 'Has', '1', 'M');
  relH(apt, town, 'Has', 'M', '1');
  relH(role, user, 'Has', '1', 'M');

  relH(tb, stock, 'Has', '1', 'M');
  relH(stock, pyb, 'Has', '1', 'M');
  relH(ssi, ssa, 'Has', '1', 'M');

  relV(town, pyb, 'Has', '1', 'M');
  relV(ssi, si, 'Has', '1', 'M');
  relH(si, sd, 'Has', '1', 'M');

  relH(tg, tgta, 'Distributes', '1', 'M');
  relH(tgta, tgs, 'Has', '1', 'M');
  relH(tgi, tgit, 'Issues', '1', 'M');

  // town (1) Has (M) quota — quota enters LEFT tip; town via RIGHT tip
  {
    const d = diamond(650, 245, 'Has');
    diamonds.push(d);
    const tL = tip(d, 'L');
    const q = edgePoint(quota, quota.cx, d.cy);
    lines += seg(q.x, q.y, quota.cx, d.cy);
    lines += seg(quota.cx, d.cy, tL.x, tL.y);

    const tR = tip(d, 'R');
    const dropX = town.L - 24;
    lines += seg(tR.x, tR.y, dropX, tR.y);
    lines += seg(dropX, tR.y, dropX, town.cy);
    const tw = edgePoint(town, dropX, town.cy);
    lines += seg(dropX, town.cy, tw.x, tw.y);

    cards.push(card(quota.cx + 12, quota.B + 14, 'M'));
    cards.push(card(dropX - 12, town.cy - 10, '1'));
  }

  // town (1) Has (M) tgta — diamond ABOVE tgta; rail clear of pyb/ssi id ovals
  // rail → RIGHT tip; BOTTOM tip → tgta (not mixed with tgta–tgs Has)
  {
    const rail = 920;
    const midY = 700;
    const d = diamond(tgta.cx, midY, 'Has');
    diamonds.push(d);

    const tw = edgePoint(town, rail, town.cy);
    lines += seg(tw.x, tw.y, rail, town.cy);
    lines += seg(rail, town.cy, rail, midY);
    const tR = tip(d, 'R');
    lines += seg(rail, midY, tR.x, tR.y);

    lines += linkTip(tgta, d, 'B');

    cards.push(card(town.R + 18, town.cy - 10, '1'));
    cards.push(card(tgta.cx + 18, midY + 28, 'M'));
  }

  let attrLines = '';
  attrPairs.forEach(({ shape, entity: ent }) => {
    attrLines += link(shape, ent);
  });

  // Paint so Has tips stay visible: shapes first, then relationship lines on top
  let p = '';
  p += attrLines;
  attrPairs.forEach(({ shape }) => { p += shape.svg; });
  entities.forEach((e) => { p += e.svg; });
  diamonds.forEach((d) => { p += d.svg; });
  p += lines; // on top of diamonds → leave exact tips
  cards.forEach((c) => { p += c; });

  return `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="${W}" height="${H}" viewBox="0 0 ${W} ${H}">
  <rect width="${W}" height="${H}" fill="#fff"/>
  <g transform="translate(${PAD_L},0)">
  ${p}
  </g>
</svg>`;
}

const svg = build();
save('Figure3_ER_Chen_BW', svg);
save('Figure3_ER_Diagram', svg);
save('Figure3_ER_Normalized_BW', svg);
console.log('Done');
