/**
 * Build sample-style Word guides:
 * - Bold titles / headings / labels
 * - Real Word tables (full columns, no pipe text)
 * - Code blocks and lists
 */
const fs = require('fs');
const path = require('path');
const monochrome = process.argv.includes('--monochrome');
const main10Only = process.argv.includes('--main10');
const roleOnly = process.argv.includes('--role');
const erd13Only = process.argv.includes('--erd13');
const guideOnly = process.argv.includes('--guide');

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

/** Split markdown inline **bold** and `code` into Word runs */
function runsFromText(text, base = {}) {
  const size = base.size || (monochrome ? 24 : 22);
  const font = base.font || (monochrome ? 'Times New Roman' : 'Calibri');
  const forceBold = !!base.bold;
  const forceItalic = !!base.italic;
  const color = monochrome ? '000000' : (base.color || null);
  const parts = [];
  const re = /(\*\*[^*]+\*\*|`[^`]+`)/g;
  let last = 0;
  let m;
  while ((m = re.exec(text)) !== null) {
    if (m.index > last) parts.push({ t: text.slice(last, m.index), bold: forceBold, italic: forceItalic, code: false });
    const tok = m[0];
    if (tok.startsWith('**')) parts.push({ t: tok.slice(2, -2), bold: true, italic: forceItalic, code: false });
    else parts.push({ t: tok.slice(1, -1), bold: forceBold, italic: forceItalic, code: true });
    last = m.index + tok.length;
  }
  if (last < text.length) parts.push({ t: text.slice(last), bold: forceBold, italic: forceItalic, code: false });
  if (!parts.length) parts.push({ t: text, bold: forceBold, italic: forceItalic, code: false });

  return parts.map((p) => {
    const b = p.bold ? '<w:b/><w:bCs/>' : '';
    const i = p.italic ? '<w:i/><w:iCs/>' : '';
    const f = monochrome ? 'Times New Roman' : (p.code ? 'Consolas' : font);
    const c = monochrome
      ? '<w:color w:val="000000"/>'
      : (color ? `<w:color w:val="${color}"/>` : (p.code ? '<w:color w:val="1F4E79"/>' : '<w:color w:val="000000"/>'));
    const shd = (!monochrome && p.code) ? '<w:shd w:val="clear" w:fill="F3F3F3"/>' : '';
    return `<w:r>
      <w:rPr>
        ${b}${i}${c}${shd}
        <w:sz w:val="${size}"/><w:szCs w:val="${size}"/>
        <w:rFonts w:ascii="${f}" w:hAnsi="${f}" w:eastAsia="Myanmar Text" w:cs="${f}"/>
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
  return `<w:p>${pPr({ before: 40, after: 40 })}<w:r><w:rPr><w:color w:val="000000"/><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:eastAsia="Myanmar Text" w:cs="Times New Roman"/></w:rPr><w:t></w:t></w:r></w:p>`;
}

function screenshotBox() {
  const font = 'Times New Roman';
  return `<w:tbl>
    <w:tblPr>
      <w:tblW w:w="9360" w:type="dxa"/>
      <w:jc w:val="center"/>
      <w:tblBorders>
        <w:top w:val="single" w:sz="8" w:space="0" w:color="000000"/>
        <w:left w:val="single" w:sz="8" w:space="0" w:color="000000"/>
        <w:bottom w:val="single" w:sz="8" w:space="0" w:color="000000"/>
        <w:right w:val="single" w:sz="8" w:space="0" w:color="000000"/>
      </w:tblBorders>
    </w:tblPr>
    <w:tblGrid><w:gridCol w:w="9360"/></w:tblGrid>
    <w:tr>
      <w:trPr><w:trHeight w:val="3200" w:hRule="atLeast"/></w:trPr>
      <w:tc>
        <w:tcPr>
          <w:tcW w:w="9360" w:type="dxa"/>
          <w:shd w:val="clear" w:color="auto" w:fill="FFFFFF"/>
          <w:vAlign w:val="center"/>
        </w:tcPr>
        <w:p>
          <w:pPr><w:jc w:val="center"/><w:spacing w:before="200" w:after="200"/></w:pPr>
          <w:r>
            <w:rPr>
              <w:i/><w:iCs/>
              <w:color w:val="000000"/>
              <w:sz w:val="22"/><w:szCs w:val="22"/>
              <w:rFonts w:ascii="${font}" w:hAnsi="${font}" w:eastAsia="Myanmar Text" w:cs="${font}"/>
            </w:rPr>
            <w:t>Insert screenshot here</w:t>
          </w:r>
        </w:p>
      </w:tc>
    </w:tr>
  </w:tbl>${emptyPara()}`;
}

function codeBlock(lines) {
  return lines.map((line) => paragraph(line.length ? line : ' ', {
    size: monochrome ? 22 : 18,
    font: monochrome ? 'Times New Roman' : 'Consolas',
    before: 20,
    after: 20,
    left: 200,
    shading: monochrome ? null : 'F2F2F2',
    color: monochrome ? '000000' : '1F4E79',
    bold: false,
  })).join('');
}

function table(rows, { header = true, colWidths = null } = {}) {
  const cols = rows[0].length;
  const total = 9360; // usable width twips (~6.5")
  const widths = colWidths || Array.from({ length: cols }, () => Math.floor(total / cols));
  // fix remainder
  widths[widths.length - 1] += total - widths.reduce((a, b) => a + b, 0);

  const grid = widths.map((w) => `<w:gridCol w:w="${w}"/>`).join('');

  const trs = rows.map((row, ri) => {
    const isHeader = header && ri === 0;
    const cells = row.map((cell, ci) => {
      const fill = monochrome ? 'FFFFFF' : (isHeader ? '1F4E79' : (ri % 2 === 1 ? 'F7F9FC' : 'FFFFFF'));
      const color = monochrome ? '000000' : (isHeader ? 'FFFFFF' : '000000');
      return `<w:tc>
        <w:tcPr>
          <w:tcW w:w="${widths[ci]}" w:type="dxa"/>
          <w:shd w:val="clear" w:color="auto" w:fill="${fill}"/>
          <w:vAlign w:val="center"/>
        </w:tcPr>
        <w:p>
          <w:pPr><w:spacing w:before="60" w:after="60"/></w:pPr>
          ${runsFromText(String(cell), { size: monochrome ? 24 : 20, bold: isHeader, color })}
        </w:p>
      </w:tc>`;
    }).join('');
    return `<w:tr>${cells}</w:tr>`;
  }).join('');

  return `<w:tbl>
    <w:tblPr>
      <w:tblStyle w:val="TableGrid"/>
      <w:tblW w:w="${total}" w:type="dxa"/>
      <w:tblBorders>
        <w:top w:val="single" w:sz="8" w:space="0" w:color="${monochrome ? '000000' : '1F4E79'}"/>
        <w:left w:val="single" w:sz="8" w:space="0" w:color="${monochrome ? '000000' : '1F4E79'}"/>
        <w:bottom w:val="single" w:sz="8" w:space="0" w:color="${monochrome ? '000000' : '1F4E79'}"/>
        <w:right w:val="single" w:sz="8" w:space="0" w:color="${monochrome ? '000000' : '1F4E79'}"/>
        <w:insideH w:val="single" w:sz="4" w:space="0" w:color="${monochrome ? '000000' : 'BDD1E8'}"/>
        <w:insideV w:val="single" w:sz="4" w:space="0" w:color="${monochrome ? '000000' : 'BDD1E8'}"/>
      </w:tblBorders>
      <w:tblLook w:val="04A0"/>
    </w:tblPr>
    <w:tblGrid>${grid}</w:tblGrid>
    ${trs}
  </w:tbl>${emptyPara()}`;
}

function parseMd(md) {
  const lines = md.replace(/\r\n/g, '\n').split('\n');
  let body = '';
  let i = 0;
  let inTitleBlock = false;

  while (i < lines.length) {
    const line = lines[i];

    // fenced code
    if (line.trim().startsWith('```')) {
      i++;
      const codeLines = [];
      while (i < lines.length && !lines[i].trim().startsWith('```')) {
        codeLines.push(lines[i]);
        i++;
      }
      i++; // closing ```
      body += codeBlock(codeLines);
      continue;
    }

    // markdown table
    if (line.trim().startsWith('|') && line.includes('|')) {
      const rows = [];
      while (i < lines.length && lines[i].trim().startsWith('|')) {
        const raw = lines[i].trim();
        const cells = raw.split('|').slice(1, -1).map((c) => c.trim());
        if (!cells.every((c) => /^:?-+:?$/.test(c))) rows.push(cells);
        i++;
      }
      if (rows.length) {
        const cols = rows[0].length;
        let colWidths = null;
        if (cols === 2) colWidths = [3600, 5760];
        if (cols === 3) colWidths = [2400, 3600, 3360];
        body += table(rows, { header: true, colWidths });
      }
      continue;
    }

    const t = line.trim();
    if (!t) { i++; continue; }

    if (t.startsWith('# ')) {
      body += paragraph(t.slice(2), { bold: true, size: monochrome ? 24 : 36, center: true, before: 0, after: 60 });
      inTitleBlock = true;
      i++;
      continue;
    }
    if (t.startsWith('## ')) {
      inTitleBlock = false;
      body += emptyPara();
      body += paragraph(t.slice(3), {
        bold: true,
        size: monochrome ? 26 : 26,
        before: 200,
        after: 100,
        color: monochrome ? '000000' : '1F4E79',
      });
      i++;
      continue;
    }
    if (t.startsWith('### ')) {
      body += paragraph(t.slice(4), {
        bold: true,
        size: 24,
        before: 160,
        after: 80,
        color: monochrome ? '000000' : '2E75B6',
      });
      i++;
      continue;
    }
    if (/^(\*\*)?\[Insert screenshot here\](\*\*)?$/i.test(t)) {
      body += screenshotBox();
      i++;
      continue;
    }
    if (/^\*Figure\s+\d+/i.test(t) || /^Figure\s+\d+/i.test(t)) {
      const caption = t.replace(/^\*/, '').replace(/\*$/, '').trim();
      body += paragraph(caption, { italic: true, size: 22, center: true, before: 80, after: 200, color: '000000' });
      i++;
      continue;
    }
    if (t.startsWith('- ') || t.startsWith('* ')) {
      body += paragraph('•  ' + t.slice(2), { size: 22, left: 288, before: 40, after: 40 });
      i++;
      continue;
    }
    if (/^\d+\.\s/.test(t)) {
      body += paragraph(t, { size: 22, left: 144, before: 40, after: 40 });
      i++;
      continue;
    }
    if (t === '---') { i++; continue; }

    // Centered bold subtitles under the main title (system name / university)
    if (inTitleBlock) {
      if (t.startsWith('This guide') || t.startsWith('This section') || t.startsWith('DERAS is') || t.startsWith('Recommended')) {
        inTitleBlock = false;
      } else {
        body += paragraph(t, { bold: true, size: 22, center: true, before: 40, after: 40 });
        i++;
        continue;
      }
    }

    body += paragraph(t, { size: 22, before: 60, after: 60 });
    i++;
  }

  return body;
}

function buildDocumentXml(md) {
  return `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
 xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <w:body>
    ${parseMd(md)}
    <w:sectPr>
      <w:pgSz w:w="12240" w:h="15840"/>
      <w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134"/>
    </w:sectPr>
  </w:body>
</w:document>`;
}

function writeDocx(mdPath, outPath, title) {
  const md = fs.readFileSync(mdPath, 'utf8');
  const documentXml = buildDocumentXml(md);
  const contentTypes = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>`;
  const rels = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>`;
  const styles = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults>
    <w:rPrDefault>
      <w:rPr>
        <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:eastAsia="Myanmar Text" w:cs="Times New Roman"/>
        <w:color w:val="000000"/>
        <w:sz w:val="24"/><w:szCs w:val="24"/>
      </w:rPr>
    </w:rPrDefault>
  </w:docDefaults>
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:qFormat/>
    <w:rPr>
      <w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:eastAsia="Myanmar Text" w:cs="Times New Roman"/>
      <w:color w:val="000000"/>
      <w:sz w:val="24"/><w:szCs w:val="24"/>
    </w:rPr>
  </w:style>
</w:styles>`;
  const wordRels = `<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>`;
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
    { name: 'word/styles.xml', data: styles },
    { name: 'word/_rels/document.xml.rels', data: wordRels },
    { name: 'docProps/core.xml', data: core },
    { name: 'docProps/app.xml', data: app },
  ]);
  fs.writeFileSync(outPath, buf);
  console.log('Wrote', outPath);
}

const dir = __dirname;
const outs = [
  {
    md: 'User_Guide.md',
    files: ['User_Guide.docx'],
    title: 'DERAS User Guide',
  },
  {
    md: 'User_Manual_Guide.md',
    files: ['User Manual Guide.docx', 'User Manual Guide (English).docx', 'User_Manual_Guide_EN.docx'],
    title: 'DERAS User Manual Guide',
  },
  {
    md: 'Installation_Guide.md',
    files: ['Installation Guide.docx', 'Installation Guide (English).docx', 'Installation_Guide_EN.docx'],
    title: 'DERAS Installation Guide',
  },
  {
    md: 'Main_10_Table_Data_Dictionary.md',
    files: ['Main_10_Table_Data_Dictionary.docx'],
    title: 'DERAS Main Table Data Dictionary',
  },
  {
    md: 'Role_Table_Data_Dictionary.md',
    files: ['Role_Table_Data_Dictionary.docx'],
    title: 'DERAS Role Table Data Dictionary',
  },
  {
    md: 'Thirteen_Table_ERD_Explanation.md',
    files: ['Thirteen_Table_ERD_Explanation.docx'],
    title: 'DERAS 13-Table ERD Explanation',
  },
];

for (const o of outs.filter((item) =>
  (!main10Only || item.md === 'Main_10_Table_Data_Dictionary.md')
  && (!roleOnly || item.md === 'Role_Table_Data_Dictionary.md')
  && (!erd13Only || item.md === 'Thirteen_Table_ERD_Explanation.md')
  && (!guideOnly || item.md === 'User_Guide.md')
)) {
  const mdPath = path.join(dir, o.md);
  let written = false;
  for (const name of o.files) {
    const outPath = path.join(dir, name);
    try {
      writeDocx(mdPath, outPath, o.title);
      written = true;
    } catch (e) {
      if (e && e.code === 'EBUSY') {
        console.warn('Locked, skip:', outPath);
        continue;
      }
      throw e;
    }
  }
  if (!written) {
    const fallback = path.join(dir, o.title.replace(/\s+/g, '_') + '_NEW.docx');
    writeDocx(mdPath, fallback, o.title);
  }
}
