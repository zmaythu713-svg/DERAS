const fs = require('fs');
const path = require('path');
const { Resvg } = require('@resvg/resvg-js');

const dir = path.join(__dirname, '..', 'images_final');
const files = [
  'Figure1_System_Flow.svg',
  'Figure2_Use_Case.svg',
  'Figure3_ER_Diagram.svg',
  'Figure4_Sequence.svg',
];

for (const file of files) {
  const svgPath = path.join(dir, file);
  const pngPath = path.join(dir, file.replace(/\.svg$/i, '.png'));
  const svg = fs.readFileSync(svgPath);
  const resvg = new Resvg(svg, {
    fitTo: { mode: 'width', value: 1600 },
    background: 'white',
  });
  const pngData = resvg.render();
  const png = pngData.asPng();
  fs.writeFileSync(pngPath, png);
  console.log('OK', path.basename(pngPath), png.length, 'bytes');
}
