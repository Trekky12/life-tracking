"use strict";

let totalValue = document.querySelector('#overviewTable th.total-value');
let additionalFields = document.querySelectorAll('#overviewTable th .additional-field');
let bodySum = document.querySelector('#overviewTable th.body-sum');

if (totalValue && bodySum) {

    additionalFields.forEach(function (input, idx) {
        input.addEventListener('input', function (e) {

            let sum = 0;
            additionalFields.forEach(input => sum += parseInt(input.value) || 0);

            if (sum > 0) {
                totalValue.textContent = parseInt(bodySum.textContent) + sum;
            } else {
                totalValue.textContent = parseInt(bodySum.textContent);
            }
        });
    });
}

function indexToColumn(index) {
    let columnName = "";
    index += 1; // Convert to 1-based index

    while (index > 0) {
        index--; // Adjust for Excel's 1-based column numbering
        columnName = String.fromCharCode((index % 26) + 65) + columnName;
        index = Math.floor(index / 26);
    }

    return columnName;
}

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

    let data = [];
    if (headerText != "") {
        data.push(headerRow);
    }
    data.push(subHeaderRow);
    data.push(emptyRow);

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

    data.push(theadRow);

    let firstDataRowIdx = data.length;

    const tfoot = document.querySelector("#overviewTable tfoot");
    const tfootRows = Array.from(tfoot.rows).map(row =>
        Array.from(row.cells).map((cell, index) => {

            let inputField = cell.querySelector("input");
            const content = inputField ? inputField.value : cell.textContent.trim();

            maxColumnWidths[index] = Math.max(maxColumnWidths[index] || 0, content.length);

            let value = content;
            let type = String;
            if (index === (fieldsCount + 1)) {
                value = parseInt(content);
                type = Number;

                if (cell.dataset.type == "sum-body") {
                    value = "SUM(" + indexToColumn(index) + (firstDataRowIdx + 1) + ":" + indexToColumn(index) + (firstDataRowIdx + tbody.rows.length) + ")";
                    console.log(value);
                    type = "Formula";
                }

                if (cell.dataset.type == "sum-foot") {
                    console.log("test");
                    value = "SUM(" + indexToColumn(index) + (firstDataRowIdx + tbody.rows.length + 1) + ":" + indexToColumn(index) + (firstDataRowIdx + tbody.rows.length + tfoot.rows.length - 1) + ")";
                    console.log(value);
                    type = "Formula";
                }
            }

            return {
                value: value,
                type: type,
                fontWeight: 'bold',
                topBorderStyle: 'thin'
            };
        })
    );

    data.push(...tbodyRows);
    data.push(...tfootRows);

    writeXlsxFile(data, {
        fileName: filename + "_" + from + "-" + to + "_Export.xlsx",
        sheet: 'Sheet1',
        columns: maxColumnWidths.map(width => ({ width: width + 2 }))
    });
});