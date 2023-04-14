"use strict";

const wrapper = document.querySelector("#timesheetNoticeWrapper");

document.querySelector('#wordExport').addEventListener('click', function (e) {
    e.preventDefault();

    let filename = wrapper.dataset.sheetname ? wrapper.dataset.sheetname : wrapper.dataset.projectname;

    const word_elements = [];

    let notice_fields = Array.from(wrapper.querySelectorAll('.timesheet-notice-wrapper:not(.hidden)'));
    for (const notice_field of notice_fields) {

        let customerElement = notice_field.querySelector('.sheet_customer');
        let customer = customerElement ? customerElement.innerHTML.replace(/&nbsp;/g, ' ') : "";

        let categoriesElement = notice_field.querySelector('.sheet_categories');
        let categories = categoriesElement ? categoriesElement.innerHTML.replace(/&nbsp;/g, ' ') : "";
        let title = notice_field.querySelector('.sheet_title').innerHTML;

        const headline = new docx.Paragraph({
            heading: docx.HeadingLevel.HEADING_1,
            children: [
                new docx.TextRun({
                    text: title,
                })
            ]
        });

        const subheadline = new docx.Paragraph({
            children: [
                new docx.TextRun({
                    text: customer,
                    italics: true,
                    size: 24
                })
            ],
            spacing: {
                after: 200,
            },
        });

        const subheadline2 = new docx.Paragraph({
            children: [
                new docx.TextRun({
                    text: categories,
                    italics: true,
                    size: 24
                })
            ],
            spacing: {
                after: 200,
            },
        });

        word_elements.push(headline);
        word_elements.push(subheadline);
        word_elements.push(subheadline2);


        let field_element_wrappers = notice_field.querySelectorAll('.timesheet-notice-field:not(.hidden)');

        field_element_wrappers.forEach(function (field_element_wrapper) {

            field_element_wrapper.querySelectorAll('input[type="text"], textarea, select, p.notice-field').forEach(function (field_element) {

                let notices = [new docx.TextRun({
                    text: field_element.previousElementSibling.innerHTML,
                    underline: {}
                })];

                let content = field_element.tagName.toLowerCase() === "p" ? field_element.innerHTML.replace(/<br ?\/?>/g, "\n").replaceAll("&amp;", "&").replaceAll("&gt;", ">").replaceAll("&lt;", "<") : field_element.value;
                const textRuns = content.split("\n").map(line => new docx.TextRun({ text: line, break: 1 }));
                notices.push(...textRuns);
                const value = new docx.Paragraph({
                    children: notices,
                    spacing: {
                        after: 400,
                    }
                });

                word_elements.push(value);
            });
        });

        // Page break if not last item
        if (notice_fields.length - 1 !== notice_fields.indexOf(notice_field)) {
            word_elements.push(new docx.Paragraph({
                children: [new docx.PageBreak()]
            }));
        }
    }

    const doc = new docx.Document({
        styles: {
            default: {
                heading1: {
                    run: {
                        size: 48,
                        color: "000000",
                        font: "Calibri",
                    },
                    paragraph: {
                        spacing: {
                            after: 120,
                        },
                    },
                },
            },
            paragraphStyles: [
                {
                    name: 'Normal',
                    run: {
                        size: 24,
                        font: "Calibri",
                    },
                },
            ],
        },
        sections: [
            {
                properties: {},
                children: word_elements
            }
        ]
    });

    docx.Packer.toBlob(doc).then((blob) => {
        saveAs(blob, filename + "_Export.docx");
    });
});