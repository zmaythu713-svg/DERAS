/**
 * Export an ExcelJS worksheet as PDF with the same cell values as Excel.
 */
window.DerasPdf = (function () {
    function cellText(value) {
        if (value == null || value === '') {
            return '';
        }
        if (typeof value === 'number') {
            if (!Number.isFinite(value)) {
                return '';
            }
            if (Number.isInteger(value)) {
                return String(value);
            }
            return String(value);
        }
        if (typeof value === 'boolean') {
            return value ? 'TRUE' : 'FALSE';
        }
        if (typeof value === 'object') {
            if (Array.isArray(value.richText)) {
                return value.richText.map(function (part) {
                    return part.text || '';
                }).join('');
            }
            if (value.result != null && value.result !== '') {
                return cellText(value.result);
            }
            if (value.text != null) {
                return String(value.text);
            }
            if (value.hyperlink) {
                return String(value.text || value.hyperlink);
            }
            if (value instanceof Date) {
                return value.toISOString().slice(0, 10);
            }
            return '';
        }
        return String(value);
    }

    function displayCell(cell) {
        if (!cell) {
            return '';
        }
        if (typeof cell.text === 'string' && cell.text !== '' && cell.value != null && typeof cell.value !== 'object') {
            return cell.text;
        }
        return cellText(cell.value);
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function decodeCell(addr) {
        var match = String(addr).toUpperCase().match(/^([A-Z]+)(\d+)$/);
        if (!match) {
            return null;
        }
        var col = 0;
        var letters = match[1];
        for (var i = 0; i < letters.length; i++) {
            col = col * 26 + (letters.charCodeAt(i) - 64);
        }
        return { col: col, row: parseInt(match[2], 10) };
    }

    function collectMergeRefs(sheet) {
        var refs = [];

        function pushRef(ref) {
            if (!ref) {
                return;
            }
            var text = String(ref);
            if (text.indexOf(':') !== -1 && refs.indexOf(text) === -1) {
                refs.push(text);
            }
        }

        function pushList(list) {
            if (!list) {
                return;
            }
            if (Array.isArray(list)) {
                list.forEach(pushRef);
                return;
            }
            if (typeof list === 'object') {
                Object.keys(list).forEach(pushRef);
            }
        }

        if (sheet.model) {
            pushList(sheet.model.merges);
        }
        if (sheet._merges) {
            pushList(sheet._merges.merges);
            pushList(sheet._merges.mergeData);
            if (typeof sheet._merges.mergeCells === 'object') {
                pushList(sheet._merges.mergeCells);
            }
        }

        return refs;
    }

    function mergeLookup(sheet) {
        var starts = {};
        var skip = {};

        collectMergeRefs(sheet).forEach(function (ref) {
            var parts = String(ref).split(':');
            if (parts.length !== 2) {
                return;
            }
            var a = decodeCell(parts[0]);
            var b = decodeCell(parts[1]);
            if (!a || !b) {
                return;
            }
            var r1 = Math.min(a.row, b.row);
            var r2 = Math.max(a.row, b.row);
            var c1 = Math.min(a.col, b.col);
            var c2 = Math.max(a.col, b.col);
            starts[r1 + ',' + c1] = {
                rowspan: r2 - r1 + 1,
                colspan: c2 - c1 + 1
            };
            for (var r = r1; r <= r2; r++) {
                for (var c = c1; c <= c2; c++) {
                    if (r === r1 && c === c1) {
                        continue;
                    }
                    skip[r + ',' + c] = true;
                }
            }
        });

        return { starts: starts, skip: skip };
    }

    function usedRange(sheet) {
        var maxCol = sheet.actualColumnCount || sheet.columnCount || 0;
        var maxRow = sheet.actualRowCount || sheet.rowCount || 0;

        sheet.eachRow({ includeEmpty: true }, function (row, rowNumber) {
            maxRow = Math.max(maxRow, rowNumber);
            if (row.cellCount) {
                maxCol = Math.max(maxCol, row.cellCount);
            }
            row.eachCell({ includeEmpty: true }, function (cell, colNumber) {
                maxCol = Math.max(maxCol, colNumber);
            });
        });

        collectMergeRefs(sheet).forEach(function (ref) {
            String(ref).split(':').forEach(function (part) {
                var decoded = decodeCell(part);
                if (!decoded) {
                    return;
                }
                maxRow = Math.max(maxRow, decoded.row);
                maxCol = Math.max(maxCol, decoded.col);
            });
        });

        return {
            maxCol: Math.max(1, maxCol),
            maxRow: Math.max(1, maxRow)
        };
    }

    function buildTableHtml(sheet) {
        var range = usedRange(sheet);
        var merges = mergeLookup(sheet);
        var html = '<table>';

        for (var r = 1; r <= range.maxRow; r++) {
            var row = sheet.getRow(r);
            html += '<tr>';

            for (var c = 1; c <= range.maxCol; c++) {
                var key = r + ',' + c;
                if (merges.skip[key]) {
                    continue;
                }

                var span = merges.starts[key] || { rowspan: 1, colspan: 1 };
                var text = displayCell(row.getCell(c));
                var isTitle = span.colspan >= range.maxCol && r <= 3;
                var tag = (row.font && row.font.bold) || (row.getCell(c).font && row.getCell(c).font.bold) || r <= 5
                    ? 'th'
                    : 'td';

                if (isTitle) {
                    tag = 'td';
                }

                var attrs = '';
                if (span.rowspan > 1) {
                    attrs += ' rowspan="' + span.rowspan + '"';
                }
                if (span.colspan > 1) {
                    attrs += ' colspan="' + span.colspan + '"';
                }
                if (isTitle) {
                    attrs += ' class="title"';
                }

                html += '<' + tag + attrs + '>' + escapeHtml(text) + '</' + tag + '>';
            }

            html += '</tr>';
        }

        html += '</table>';
        return { html: html, maxCol: range.maxCol, maxRow: range.maxRow };
    }

    function wait(ms) {
        return new Promise(function (resolve) {
            setTimeout(resolve, ms);
        });
    }

    function tableStyles() {
        return 'html,body{margin:0;padding:0;background:#fff;}'
            + 'body{padding:10px;font-family:"Noto Sans Myanmar","Pyidaungsu","Myanmar Text",sans-serif;'
            + 'color:#111;font-size:11px;line-height:1.4;}'
            + 'table{border-collapse:collapse;width:auto;max-width:none;table-layout:auto;}'
            + 'th,td{border:1px solid #334155;padding:5px 7px;text-align:center;vertical-align:middle;'
            + 'white-space:nowrap;word-break:keep-all;background:#fff;color:#111;font-weight:400;}'
            + 'th{background:#d9ead3;font-weight:700;}'
            + 'td.title{font-weight:700;font-size:14px;padding:10px 8px;white-space:normal;}';
    }

    function addImageFitPage(pdf, imgData, imgWidth, imgHeight, margin) {
        var pageWidth = pdf.internal.pageSize.getWidth() - margin * 2;
        var pageHeight = pdf.internal.pageSize.getHeight() - margin * 2;
        var ratio = Math.min(pageWidth / imgWidth, pageHeight / imgHeight);
        var renderWidth = imgWidth * ratio;
        var renderHeight = imgHeight * ratio;
        pdf.addImage(imgData, 'JPEG', margin, margin, renderWidth, renderHeight);
    }

    function headerBodySplit(table) {
        var rows = Array.prototype.slice.call(table.querySelectorAll('tr'));
        var headerCount = 0;

        for (var i = 0; i < rows.length; i++) {
            var tr = rows[i];
            var title = tr.querySelector('td.title');
            var th = tr.querySelector('th');
            var td = tr.querySelector('td:not(.title)');
            if (title || (th && !td)) {
                headerCount += 1;
                continue;
            }
            break;
        }

        if (headerCount === 0) {
            headerCount = Math.min(2, rows.length);
        }

        return {
            headers: rows.slice(0, headerCount),
            body: rows.slice(headerCount)
        };
    }

    function buildChunkTable(headers, bodyRows) {
        var table = document.createElement('table');
        headers.forEach(function (row) {
            table.appendChild(row.cloneNode(true));
        });
        bodyRows.forEach(function (row) {
            table.appendChild(row.cloneNode(true));
        });
        return table;
    }

    async function captureElement(html2canvasFn, element) {
        var width = Math.max(element.scrollWidth, element.offsetWidth, 400);
        var height = Math.max(element.scrollHeight, element.offsetHeight, 80);

        return html2canvasFn(element, {
            scale: 1.8,
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff',
            logging: false,
            windowWidth: width,
            windowHeight: height,
            width: width,
            height: height,
            scrollX: 0,
            scrollY: 0
        });
    }

    async function fromWorksheet(sheet, filename) {
        var html2canvasFn = window.html2canvas;
        var JsPDF = window.jspdf && window.jspdf.jsPDF;

        if (typeof html2canvasFn !== 'function' || typeof JsPDF !== 'function') {
            alert('PDF ထုတ်ရန် library မရှိပါ');
            return;
        }

        var built = buildTableHtml(sheet);
        var wide = built.maxCol > 8;
        var veryWide = built.maxCol > 16;
        var rowsPerPage = veryWide ? 8 : (wide ? 12 : 22);

        var iframe = document.createElement('iframe');
        iframe.style.cssText = [
            'position:absolute',
            'left:0',
            'top:0',
            'width:1400px',
            'height:900px',
            'border:0',
            'background:#fff',
            'z-index:2147483646'
        ].join(';');
        document.body.appendChild(iframe);

        var doc = iframe.contentDocument;
        doc.open();
        doc.write(
            '<!DOCTYPE html><html><head><meta charset="utf-8">'
            + '<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@400;700&display=swap" rel="stylesheet">'
            + '<style>' + tableStyles() + '</style></head><body>'
            + built.html + '</body></html>'
        );
        doc.close();

        await wait(350);
        if (doc.fonts && doc.fonts.ready) {
            try {
                await doc.fonts.ready;
            } catch (e) {
                // ignore
            }
        }

        var sourceTable = doc.querySelector('table');
        if (!sourceTable) {
            iframe.remove();
            alert('PDF ထုတ်ရန် အချက်အလက် မရှိပါ');
            return;
        }

        var split = headerBodySplit(sourceTable);
        var headerClones = split.headers.map(function (row) {
            return row.cloneNode(true);
        });
        var bodyClones = split.body.map(function (row) {
            return row.cloneNode(true);
        });

        var chunks = [];
        if (bodyClones.length === 0) {
            chunks.push([]);
        } else {
            for (var i = 0; i < bodyClones.length; i += rowsPerPage) {
                chunks.push(bodyClones.slice(i, i + rowsPerPage));
            }
        }

        var pdfName = String(filename || 'export.pdf');
        if (!/\.pdf$/i.test(pdfName)) {
            pdfName += '.pdf';
        }

        var pdf = new JsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: veryWide ? 'a3' : 'a4'
        });

        try {
            for (var pageIndex = 0; pageIndex < chunks.length; pageIndex++) {
                var pageTable = buildChunkTable(headerClones, chunks[pageIndex]);
                doc.body.innerHTML = '';
                doc.body.appendChild(pageTable);

                var captureWidth = Math.max(pageTable.scrollWidth, pageTable.offsetWidth, 600);
                var captureHeight = Math.max(pageTable.scrollHeight, pageTable.offsetHeight, 120);
                iframe.style.width = (captureWidth + 40) + 'px';
                iframe.style.height = (captureHeight + 40) + 'px';
                await wait(80);

                var canvas = await captureElement(html2canvasFn, doc.body);
                if (!canvas || canvas.width < 10 || canvas.height < 10) {
                    throw new Error('blank-canvas');
                }

                if (pageIndex > 0) {
                    pdf.addPage();
                }
                addImageFitPage(
                    pdf,
                    canvas.toDataURL('image/jpeg', 0.95),
                    canvas.width,
                    canvas.height,
                    8
                );
            }

            pdf.save(pdfName);
        } catch (err) {
            console.error(err);
            alert('PDF ထုတ်၍မရပါ။ ပြန်လည်ကြိုးစားပါ။');
        } finally {
            iframe.remove();
        }
    }

    async function downloadWorkbook(workbook, sheet, filename, format) {
        var base = String(filename || 'export')
            .replace(/\.xlsx$/i, '')
            .replace(/\.pdf$/i, '');

        if (format === 'pdf') {
            await fromWorksheet(sheet, base + '.pdf');
            return;
        }

        var buffer = await workbook.xlsx.writeBuffer();
        saveAs(
            new Blob([buffer], {
                type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            }),
            base + '.xlsx'
        );
    }

    return {
        fromWorksheet: fromWorksheet,
        downloadWorkbook: downloadWorkbook
    };
})();
