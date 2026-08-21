/**
 * Export an ExcelJS worksheet as PDF with the same cell values as Excel.
 * Tables are laid out to the PDF page width so columns are not clipped.
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

    function cellFillArgb(cell) {
        var fill = cell && cell.fill;
        if (!fill || fill.fgColor == null) {
            return '';
        }
        var argb = fill.fgColor.argb || fill.fgColor.theme;
        return argb ? String(argb).toUpperCase() : '';
    }

    function isFilledHeader(cell) {
        var argb = cellFillArgb(cell);
        if (!argb || argb === 'FFFFFFFF' || argb === 'FF000000' || argb === '00000000') {
            return false;
        }
        return true;
    }

    function isBoldCell(row, cell) {
        return !!(
            (cell && cell.font && cell.font.bold) ||
            (row && row.font && row.font.bold)
        );
    }

    function columnPercents(sheet, maxCol) {
        var widths = [];
        var total = 0;

        for (var c = 1; c <= maxCol; c++) {
            var col = sheet.getColumn(c);
            var width = col && col.width ? Number(col.width) : 12;
            if (!Number.isFinite(width) || width <= 0) {
                width = 12;
            }
            widths.push(width);
            total += width;
        }

        if (total <= 0) {
            return widths.map(function () {
                return (100 / maxCol);
            });
        }

        return widths.map(function (width) {
            return (width / total) * 100;
        });
    }

    function colgroupHtml(percents) {
        return '<colgroup>' + percents.map(function (pct) {
            return '<col style="width:' + pct.toFixed(3) + '%">';
        }).join('') + '</colgroup>';
    }

    function pageSpec(maxCol) {
        if (maxCol > 16) {
            return {
                format: 'a3',
                contentPx: 1520,
                fontSize: 8,
                headerFontSize: 8,
                rowsPerPage: 10
            };
        }
        if (maxCol > 12) {
            return {
                format: 'a3',
                contentPx: 1520,
                fontSize: 9,
                headerFontSize: 9,
                rowsPerPage: 12
            };
        }
        return {
            format: 'a4',
            contentPx: 1060,
            fontSize: 10,
            headerFontSize: 10,
            rowsPerPage: 16
        };
    }

    function rowIsEmpty(row, maxCol, merges, r) {
        for (var c = 1; c <= maxCol; c++) {
            if (merges.skip[r + ',' + c]) {
                continue;
            }
            if (String(displayCell(row.getCell(c))).trim() !== '') {
                return false;
            }
        }
        return true;
    }

    function buildTableHtml(sheet) {
        var range = usedRange(sheet);
        var merges = mergeLookup(sheet);
        var percents = columnPercents(sheet, range.maxCol);
        var titles = [];
        var html = '<table>' + colgroupHtml(percents);
        var inTable = false;

        for (var r = 1; r <= range.maxRow; r++) {
            var row = sheet.getRow(r);
            var empty = rowIsEmpty(row, range.maxCol, merges, r);
            var titleText = '';
            var isTitleRow = false;

            for (var c = 1; c <= range.maxCol; c++) {
                var key = r + ',' + c;
                if (merges.skip[key]) {
                    continue;
                }
                var span = merges.starts[key] || { rowspan: 1, colspan: 1 };
                var text = String(displayCell(row.getCell(c))).trim();
                if (span.colspan >= range.maxCol && r <= 5 && text) {
                    isTitleRow = true;
                    titleText = text;
                }
                break;
            }

            if (!inTable && (empty || isTitleRow)) {
                if (isTitleRow && titleText) {
                    titles.push(titleText);
                }
                continue;
            }

            inTable = true;
            if (empty) {
                continue;
            }

            html += '<tr>';

            for (var c = 1; c <= range.maxCol; c++) {
                var key = r + ',' + c;
                if (merges.skip[key]) {
                    continue;
                }

                var cell = row.getCell(c);
                var span = merges.starts[key] || { rowspan: 1, colspan: 1 };
                var text = displayCell(cell);
                var isHeader = isBoldCell(row, cell) || isFilledHeader(cell);
                var tag = isHeader ? 'th' : 'td';
                var attrs = '';

                if (span.rowspan > 1) {
                    attrs += ' rowspan="' + span.rowspan + '"';
                }
                if (span.colspan > 1) {
                    attrs += ' colspan="' + span.colspan + '"';
                }

                html += '<' + tag + attrs + '>' + escapeHtml(text) + '</' + tag + '>';
            }

            html += '</tr>';
        }

        html += '</table>';
        return { html: html, titles: titles, maxCol: range.maxCol, maxRow: range.maxRow };
    }

    function wait(ms) {
        return new Promise(function (resolve) {
            setTimeout(resolve, ms);
        });
    }

    function tableStyles(spec) {
        var size = spec.fontSize;
        return '*{box-sizing:border-box;}'
            + 'html,body{margin:0;padding:0;background:#fff;overflow:visible !important;height:auto !important;}'
            + 'body{padding:8px 10px;width:' + spec.contentPx + 'px;font-family:"Noto Sans Myanmar","Pyidaungsu","Myanmar Text",sans-serif;'
            + 'color:#111;font-size:' + size + 'px;line-height:1.45;}'
            + '.title{font-family:"Noto Sans Myanmar","Pyidaungsu","Myanmar Text",sans-serif;font-weight:700;'
            + 'font-size:' + size + 'px;text-align:center;padding:6px 8px 10px;line-height:1.45;color:#111;}'
            + 'table{border-collapse:collapse;width:100%;max-width:100%;table-layout:fixed;}'
            + 'th,td{border:1px solid #334155;padding:6px 5px;text-align:center;vertical-align:middle;'
            + 'white-space:normal;word-break:break-word;overflow-wrap:anywhere;color:#111;'
            + 'font-family:"Noto Sans Myanmar","Pyidaungsu","Myanmar Text",sans-serif;'
            + 'font-size:' + size + 'px !important;line-height:1.45;}'
            + 'th{background:#d9ead3;font-weight:700;}'
            + 'td{background:#fff;font-weight:400;}'
            + '.pdf-page{width:' + spec.contentPx + 'px;max-width:' + spec.contentPx + 'px;background:#fff;overflow:visible;}';
    }

    function addImageFitPage(pdf, imgData, imgWidth, imgHeight, margin) {
        var pageWidth = pdf.internal.pageSize.getWidth() - margin * 2;
        var pageHeight = pdf.internal.pageSize.getHeight() - margin * 2;
        var ratio = Math.min(pageWidth / imgWidth, pageHeight / imgHeight);
        pdf.addImage(imgData, 'JPEG', margin, margin, imgWidth * ratio, imgHeight * ratio);
    }

    function headerBodySplit(table) {
        var rows = Array.prototype.slice.call(table.querySelectorAll('tr'));
        var headerCount = 0;

        for (var i = 0; i < rows.length; i++) {
            var tr = rows[i];
            var title = tr.querySelector('td.title');
            var th = tr.querySelector('th');
            var td = tr.querySelector('td:not(.title)');
            var cells = tr.querySelectorAll('th,td');
            var empty = true;
            for (var n = 0; n < cells.length; n++) {
                if ((cells[n].textContent || '').trim() !== '') {
                    empty = false;
                    break;
                }
            }

            if (empty) {
                continue;
            }

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

    function packRowChunks(rows, heights, maxBodyPx) {
        var chunks = [];
        var current = [];
        var used = 0;
        var limit = Math.max(120, maxBodyPx);

        if (!rows.length) {
            return [[]];
        }

        for (var i = 0; i < rows.length; i++) {
            var h = heights[i] || 28;
            if (current.length && used + h > limit) {
                chunks.push(current);
                current = [];
                used = 0;
            }
            current.push(rows[i]);
            used += h;
        }

        if (current.length) {
            chunks.push(current);
        }

        return chunks;
    }

    function buildChunkTable(ownerDoc, headers, bodyRows, colgroup) {
        var table = ownerDoc.createElement('table');
        if (colgroup) {
            table.appendChild(colgroup.cloneNode(true));
        }
        headers.forEach(function (row) {
            table.appendChild(row.cloneNode(true));
        });
        bodyRows.forEach(function (row) {
            table.appendChild(row.cloneNode(true));
        });
        return table;
    }

    async function fromWorksheet(sheet, filename) {
        var html2canvasFn = window.html2canvas;
        var JsPDF = window.jspdf && window.jspdf.jsPDF;

        if (typeof html2canvasFn !== 'function' || typeof JsPDF !== 'function') {
            alert('PDF ထုတ်ရန် library မရှိပါ');
            return;
        }

        var built = buildTableHtml(sheet);
        var spec = pageSpec(built.maxCol);
        var hostWidth = spec.contentPx + 24;
        var pageLimitPx = spec.format === 'a3' ? 980 : 680;

        var titleHtml = (built.titles || []).map(function (text) {
            return '<div class="title">' + escapeHtml(text) + '</div>';
        }).join('');

        var iframe = document.createElement('iframe');
        iframe.setAttribute('data-deras-pdf-host', '1');
        iframe.style.cssText = [
            'position:fixed',
            'left:0',
            'top:0',
            'width:' + hostWidth + 'px',
            'height:8000px',
            'border:0',
            'background:#fff',
            'z-index:2147483645',
            'pointer-events:none'
        ].join(';');
        document.body.appendChild(iframe);

        var doc = iframe.contentDocument;
        doc.open();
        doc.write(
            '<!DOCTYPE html><html><head><meta charset="utf-8">'
            + '<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@400;700&display=swap" rel="stylesheet">'
            + '<style>' + tableStyles(spec) + '</style></head>'
            + '<body><div class="pdf-page">' + titleHtml + built.html + '</div></body></html>'
        );
        doc.close();

        await wait(400);
        if (doc.fonts && doc.fonts.ready) {
            try {
                await doc.fonts.ready;
            } catch (e) {
                // ignore
            }
        }
        await wait(150);

        var sourceTable = doc.querySelector('table');
        if (!sourceTable) {
            iframe.remove();
            alert('PDF ထုတ်ရန် အချက်အလက် မရှိပါ');
            return;
        }

        var titleNodes = Array.prototype.slice.call(doc.querySelectorAll('.pdf-page > .title'));
        var titleHeight = 0;
        titleNodes.forEach(function (node) {
            titleHeight += node.offsetHeight || 28;
        });
        var titleClones = titleNodes.map(function (node) {
            return node.cloneNode(true);
        });
        var colgroup = sourceTable.querySelector('colgroup');
        var split = headerBodySplit(sourceTable);
        var headerHeight = 0;
        split.headers.forEach(function (row) {
            headerHeight += row.offsetHeight || 32;
        });
        var rowHeights = split.body.map(function (row) {
            return row.offsetHeight || 28;
        });
        var headerClones = split.headers.map(function (row) {
            return row.cloneNode(true);
        });
        var bodyClones = split.body.map(function (row) {
            return row.cloneNode(true);
        });
        var chunks = packRowChunks(bodyClones, rowHeights, pageLimitPx - headerHeight - titleHeight - 20);

        var pdfName = String(filename || 'export.pdf');
        if (!/\.pdf$/i.test(pdfName)) {
            pdfName += '.pdf';
        }

        var pdf = new JsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: spec.format
        });

        try {
            for (var pageIndex = 0; pageIndex < chunks.length; pageIndex++) {
                var pageTable = buildChunkTable(doc, headerClones, chunks[pageIndex], colgroup);
                var pageWrap = doc.createElement('div');
                pageWrap.className = 'pdf-page';
                titleClones.forEach(function (node) {
                    pageWrap.appendChild(node.cloneNode(true));
                });
                pageWrap.appendChild(pageTable);
                doc.body.innerHTML = '';
                doc.body.appendChild(pageWrap);
                iframe.style.height = Math.max(pageWrap.scrollHeight, pageWrap.offsetHeight, pageLimitPx) + 60 + 'px';
                await wait(50);

                var canvas = await html2canvasFn(pageWrap, {
                    scale: 1.6,
                    useCORS: true,
                    allowTaint: true,
                    backgroundColor: '#ffffff',
                    logging: false
                });
                if (!canvas || canvas.width < 10 || canvas.height < 10) {
                    throw new Error('blank-canvas');
                }

                if (pageIndex > 0) {
                    pdf.addPage();
                }
                addImageFitPage(
                    pdf,
                    canvas.toDataURL('image/jpeg', 0.92),
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
