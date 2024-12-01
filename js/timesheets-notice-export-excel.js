"use strict";

const wrapper = document.querySelector("#timesheetNoticeWrapper");

document.querySelector('#excelExport').addEventListener('click', function (e) {
    e.preventDefault();

    let filename = wrapper.dataset.name ? wrapper.dataset.name : wrapper.dataset.projectname;

    let fieldsCount = parseInt(document.querySelector("#overviewTable").dataset.fields);
    let maxColumnWidths = [];

    const headerText = document.getElementById("reportHeadline").textContent;
    const headerRow = [
        {
            value: headerText.trim(),
            fontWeight: 'bold',
            fontSize: 16
        }
    ];

    const excelHeaderTime = document.getElementById("excelHeaderTime");
    const from = excelHeaderTime.dataset.from;
    const to = excelHeaderTime.dataset.to;

    const subHeaderText = excelHeaderTime.textContent;
    const subHeaderRow = [
        {
            value: subHeaderText.trim(),
            fontWeight: 'bold',
            fontSize: 14
        }
    ];

    const emptyRow = [{ value: "" }];

    const thead = document.querySelector("#overviewTable thead");
    const theadRow = Array.from(thead.rows[0].cells).map((cell, index) => {

        const content = cell.textContent.trim();
        maxColumnWidths[index] = Math.max(maxColumnWidths[index] || 0, content.length);

        return {
            value: content,
            fontWeight: 'bold',
            bottomBorderStyle: 'thin'
        };
    });

    const tbody = document.querySelector("#overviewTable tbody");
    const tbodyRows = Array.from(tbody.rows).map(row =>
        Array.from(row.cells).map((cell, index) => {

            const content = cell.textContent.trim();
            maxColumnWidths[index] = Math.max(maxColumnWidths[index] || 0, content.length);

            return {
                value: index === (fieldsCount + 1) ? parseInt(content) : content,
                type: index === (fieldsCount + 1) ? Number : String,
            };
        })
    );

    const tfoot = document.querySelector("#overviewTable tfoot");
    const tfootRow = Array.from(tfoot.rows[0].cells).map((cell, index) => {
        const content = cell.textContent.trim();
        maxColumnWidths[index] = Math.max(maxColumnWidths[index] || 0, content.length);

        return {
            value: index === (fieldsCount + 1) ? parseInt(content) : content,
            type: index === (fieldsCount + 1) ? Number : String,
            fontWeight: 'bold',
            topBorderStyle: 'thin'
        };
    });

    let data = [];
    if (headerText != "") {
        data.push(headerRow);
    }
    data.push(subHeaderRow);
    data.push(emptyRow);
    data.push(theadRow);
    data.push(...tbodyRows);
    data.push(tfootRow);

    writeXlsxFile(data, {
        fileName: filename + "_" + from + "-" + to + "_Export.xlsx",
        sheet: 'Sheet1',
        columns: maxColumnWidths.map(width => ({ width: width + 2 }))
    });
});