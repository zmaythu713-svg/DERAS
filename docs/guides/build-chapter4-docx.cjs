/**
 * Convert Chapter4_System_Implementation.md → Chapter4_System_Implementation.docx
 */
const fs = require('fs');
const path = require('path');

function esc(s) {
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function u16(n) {
  const b = Buffer.alloc(2);
  b.writeUInt16LE(n, 0);
  return b;
}
function u32(n) {
  const b = Buffer.alloc(4);
  b.writeUInt32LE(n >>> 0, 0);
  return b;
}

const CRC_TABLE = (() => {
  const table = new Uint32Array(256);
  for (let i = 0; i < 256; i++) {
    let c = i;
    for (let k = 0; k < 8; k++) c = c & 1 ? 0xedb88320 ^ (c >>> 1) : c >>> 1;
    table[i] = c >>> 0;
  }
  return table;
})();

function crc32(buf) {
  let c = 0xffffffff;
  for (let i = 0; i < buf.length; i++) c = CRC_TABLE[(c ^ buf[i]) & 0xff] ^ (c >>> 8);
  return (c ^ 0xffffffff) >>> 0;
}

function zipStore(files) {
  const parts = [];
  const central = [];
  let offset = 0;
  for (const f of files) {
    const nameBuf = Buffer.from(f.name, 'utf8');
    const data = Buffer.isBuffer(f.data) ? f.data : Buffer.from(f.data, 'utf8');
    const crc = crc32(data);
    const local = Buffer.concat([
      u32(0x04034b50), u16(20), u16(0), u16(0), u16(0), u16(0),
      u32(crc), u32(data.length), u32(data.length),
      u16(nameBuf.length), u16(0), nameBuf, data,
    ]);
    parts.push(local);
    central.push(Buffer.concat([
      u32(0x02014b50), u16(20), u16(20), u16(0), u16(0), u16(0), u16(0),
      u32(crc), u32(data.length), u32(data.length),
      u16(nameBuf.length), u16(0), u16(0), u16(0), u16(0), u32(0), u32(offset), nameBuf,
    ]));
    offset += local.length;
  }
  const centralDir = Buffer.concat(central);
  return Buffer.concat([
    ...parts,
    centralDir,
    Buffer.concat([
      u32(0x06054b50), u16(0), u16(0), u16(files.length), u16(files.length),
      u32(centralDir.length), u32(offset), u16(0),
    ]),
  ]);
}

function runsFromText(text, base = {}) {
  const size = base.size || 22;
  const font = base.font || 'Calibri';
  const forceBold = !!base.bold;
  const color = base.color || null;
  const parts = [];
  const re = /(\*\*[^*]+\*\*|`[^`]+`)/g;
  let last = 0;
  let m;
  while ((m = re.exec(text)) !== null) {
    if (m.index > last) parts.push({ t: text.slice(last, m.index), bold: forceBold, code: false });
    const tok = m[0];
    if (tok.startsWith('**')) parts.push({ t: tok.slice(2, -2), bold: true, code: false });
    else parts.push({ t: tok.slice(1, -1), bold: forceBold, code: true });
    last = m.index + tok.length;
  }
  if (last < text.length) parts.push({ t: text.slice(last), bold: forceBold, code: false });
  if (!parts.length) parts.push({ t: text, bold: forceBold, code: false });

  return parts.map((p) => {
    const b = p.bold ? '<w:b/><w:bCs/>' : '';
    const f = p.code ? 'Consolas' : font;
    const c = color ? `<w:color w:val="${color}"/>` : (p.code ? '<w:color w:val="1F4E79"/>' : '');
    return `<w:r>
      <w:rPr>
        ${b}${c}
        <w:sz w:val="${size}"/><w:szCs w:val="${size}"/>
        <w:rFonts w:ascii="${f}" w:hAnsi="${f}" w:cs="${f}"/>
      </w:rPr>
      <w:t xml:space="preserve">${esc(p.t)}</w:t>
    </w:r>`;
  }).join('');
}

function pPr({ center = false, before = 80, after = 80, left = 0, shading = null } = {}) {
  return `<w:pPr>
    ${center ? '<w:jc w:val="center"/>' : ''}
    ${left ? `<w:ind w:left="${left}"/>` : ''}
    <w:spacing w:before="${before}" w:after="${after}" w:line="276" w:lineRule="auto"/>
    ${shading ? `<w:shd w:val="clear" w:fill="${shading}"/>` : ''}
  </w:pPr>`;
}

function paragraph(text, opts = {}) {
  return `<w:p>${pPr(opts)}${runsFromText(text, opts)}</w:p>`;
}

function emptyPara() {
  return `<w:p>${pPr({ before: 40, after: 40 })}<w:r><w:t></w:t></w:r></w:p>`;
}

function codeBlock(lines) {
  return lines.map((line) => paragraph(line.length ? line : ' ', {
    size: 18,
    font: 'Consolas',
    before: 20,
    after: 20,
    left: 200,
    shading: 'F2F2F2',
    color: '1F4E79',
  })).join('');
}

function parseMd(md) {
  const lines = md.replace(/\r\n/g, '\n').split('\n');
  let body = '';
  let i = 0;
  let inCode = false;
  let codeLines = [];

  while (i < lines.length) {
    const raw = lines[i];
    const t = raw.trim();

    if (t.startsWith('```')) {
      if (!inCode) {
        inCode = true;
        codeLines = [];
      } else {
        inCode = false;
        body += codeBlock(codeLines);
      }
      i++;
      continue;
    }
    if (inCode) {
      codeLines.push(raw);
      i++;
      continue;
    }

    if (!t || t === '---') { i++; continue; }

    if (t.startsWith('# ')) {
      body += paragraph(t.slice(2), { bold: true, size: 36, center: true, before: 120, after: 80, color: '1F4E79' });
    } else if (t.startsWith('## ')) {
      body += emptyPara();
      body += paragraph(t.slice(3), { bold: true, size: 28, before: 240, after: 100, color: '1F4E79' });
    } else if (t.startsWith('### ')) {
      body += paragraph(t.slice(4), { bold: true, size: 24, before: 180, after: 80, color: '2E75B6' });
    } else if (t.startsWith('- ') || t.startsWith('* ')) {
      body += paragraph('•  ' + t.slice(2), { size: 22, left: 288, before: 40, after: 40 });
    } else if (/^\d+\.\s/.test(t)) {
      body += paragraph(t, { size: 22, left: 144, before: 40, after: 40 });
    } else if (t.startsWith('**[Insert Figure') || t.startsWith('[Insert Figure')) {
      body += emptyPara();
      body += paragraph(t.replace(/^\*\*/, '').replace(/\*\*$/, ''), {
        bold: true, size: 20, center: true, before: 160, after: 60, color: 'C00000', shading: 'FFF2CC',
      });
      body += emptyPara();
    } else if (t.startsWith('*Figure') || t.startsWith('*Figure')) {
      body += paragraph(t.replace(/^\*/, '').replace(/\*$/, ''), {
        bold: false, size: 20, center: true, before: 40, after: 120, color: '595959',
      });
    } else {
      body += paragraph(t, { size: 22, before: 60, after: 60 });
    }
    i++;
  }
  return body;
}

function writeDocx(mdPath, outPath, title) {
  const md = fs.readFileSync(mdPath, 'utf8');
  const documentXml = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
 xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <w:body>
    ${parseMd(md)}
    <w:sectPr>
      <w:pgSz w:w="12240" w:h="15840"/>
      <w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1440"/>
    </w:sectPr>
  </w:body>
</w:document>`;

  const contentTypes = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>`;
  const rels = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>`;
  const wordRels = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>`;
  const core = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"
 xmlns:dc="http://purl.org/dc/elements/1.1/">
  <dc:title>${esc(title)}</dc:title>
  <dc:creator>DERAS</dc:creator>
</cp:coreProperties>`;
  const app = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">
  <Application>DERAS Guide Builder</Application>
</Properties>`;

  const buf = zipStore([
    { name: '[Content_Types].xml', data: contentTypes },
    { name: '_rels/.rels', data: rels },
    { name: 'word/document.xml', data: documentXml },
    { name: 'word/_rels/document.xml.rels', data: wordRels },
    { name: 'docProps/core.xml', data: core },
    { name: 'docProps/app.xml', data: app },
  ]);
  fs.writeFileSync(outPath, buf);
  console.log('Wrote', outPath);
}

const dir = __dirname;
writeDocx(
  path.join(dir, 'Chapter4_System_Implementation.md'),
  path.join(dir, 'Chapter4_System_Implementation.docx'),
  'DERAS Chapter 4 System Implementation'
);
