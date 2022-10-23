"use strict";


const timesheetNoticeWrapper = document.querySelector("#timesheetNoticeWrapper");
const loadingIconTimesheetNotice = document.querySelector("#loadingIconTimesheetNotice");

const timesheetNoticeForm = document.querySelector("#timesheetNoticeForm");


const alertError = document.querySelector("#alertError");
const alertErrorDetail = alertError.querySelector("#alertErrorDetail");

const projectID = parseInt(timesheetNoticeWrapper.dataset.project);

if (!window.crypto || !window.crypto.subtle) {
    alertErrorDetail.innerHTML = lang.decrypt_error;
    alertError.classList.remove("hidden");
    if (timesheetNoticeForm) {
        timesheetNoticeForm.querySelectorAll('textarea, select, input').forEach(function (element) {
            element.disabled = true;
        });
        timesheetNoticeForm.querySelector('button[type="submit"]').classList.add("hidden");
    }
}

let aesKey;

checkPassword();

async function checkPassword() {
    aesKey = await getAESKeyFromStore();

    if (!aesKey) {
        let pw = window.prompt(lang.timesheets_notice_password);

        var data = { 'password': pw };

        let token = await getCSRFToken()
        data["csrf_name"] = token.csrf_name;
        data["csrf_value"] = token.csrf_value;

        let response = await fetch(jsObject.timesheets_sheets_check_pw, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });
        let result = await response.json();

        if (result.status === "success") {
            const salt = base64_to_buf(result.data);
            const keyMaterial = await createKeyMaterial(pw);
            const newAESKey = await createsAESKey(keyMaterial, salt);

            let store = await getStore();
            store.add({ 'project': projectID, 'key': newAESKey });

            aesKey = await getAESKeyFromStore();
        } else if (result.status === "error" && result.reason) {
            alertErrorDetail.innerHTML = result.reason;
            alertError.classList.remove("hidden");
            loadingIconTimesheetNotice.classList.add("hidden");
            return;
        }
    }

    let notice_fields = Array.from(timesheetNoticeWrapper.querySelectorAll('.timesheet-notice'));

    // Sequential
    for (const notice_field of notice_fields) {
        let sheet_id = parseInt(notice_field.dataset.sheet);
        let notice = await getNotice(sheet_id);

        if (notice) {
            if (IsJsonString(notice)) {

                notice = JSON.parse(notice);

                for (const [field_name, field_value] of Object.entries(notice)) {
                    let field_value = notice[field_name];
                    let field_element = notice_field.querySelector('[data-name="' + field_name + '"]');

                    if (field_element) {

                        if (field_element.tagName && (field_element.tagName.toLowerCase() === "textarea" || field_element.tagName.toLowerCase() === "input" || field_element.tagName.toLowerCase() === "select")) {
                            field_element.value = field_value;
                        } else {

                            if (field_value) {
                                field_element.innerHTML = field_value.replace(/(?:\r\n|\r|\n)/g, '<br>');
                            } else {
                                // no value, hide field on export
                                field_element.parentElement.dataset.empty = 1;
                            }
                        }

                    }


                }

            } else {

                let default_field = notice_field.querySelector('[data-default="1"]');

                // Default: no notice fields, only one field
                if (default_field && default_field.tagName && (default_field.tagName.toLowerCase() === "textarea" || default_field.tagName.toLowerCase() === "input" || default_field.tagName.toLowerCase() === "select")) {
                    default_field.value = notice;
                } else {
                    if (notice) {
                        default_field.innerHTML = notice.replace(/(?:\r\n|\r|\n)/g, '<br>');
                    } else {
                        default_field.parentElement.dataset.empty = 1;
                    }

                    // possible other fields are apparently empty
                    notice_field.querySelectorAll('[data-default="0"]').forEach(function (el) {
                        el.parentElement.dataset.empty = 1;
                    });
                }
            }
        } else {
            notice_field.closest('.timesheet-notice-wrapper').dataset.empty = 1;
        }
    }

    // Parallel
    // await Promise.all(notice_fields.map(async (notice_field) => {
    //     let sheet_id = parseInt(notice_field.dataset.sheet);
    //     let notice = await getNotice(sheet_id);
    //     if (notice) {
    //         if (notice_field.tagName && notice_field.tagName.toLowerCase() === "textarea") {
    //             notice_field.value = notice;
    //         } else {
    //             notice_field.innerHTML = notice.replace(/(?:\r\n|\r|\n)/g, '<br>');
    //         }
    //     }
    // }));

    loadingIconTimesheetNotice.classList.add("hidden");
    timesheetNoticeWrapper.classList.remove("hidden");
}

async function getNotice(sheet_id) {
    let notice_response = await fetch(jsObject.timesheets_sheets_notice_data + '?sheet=' + sheet_id, {
        method: "GET",
        credentials: "same-origin",
    });
    let notice_result = await notice_response.json();
    if (notice_result.status !== "error" && notice_result.entry) {
        let notice = notice_result.entry.notice;

        if (notice) {
            if (!aesKey) {
                alertErrorDetail.innerHTML = lang.decrypt_error;
                alertError.classList.remove("hidden");
                return;
            }
            let decrypted_notice = await decryptData(notice, sheet_id);
            return decrypted_notice;
        }

    }
    return;
}

if (timesheetNoticeForm) {
    timesheetNoticeForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        alertError.classList.add("hidden");
        alertErrorDetail.innerHTML = "";

        document.getElementById("loading-overlay").classList.remove("hidden");

        let data = {};
        let notice_fields = Array.from(timesheetNoticeForm.querySelectorAll('input[type="text"], textarea, select'));
        if (notice_fields.length > 1) {
            let notice = {};
            for (const field of notice_fields) {
                if (field.tagName && field.tagName.toLowerCase() === "select") {
                    if (field.selectedIndex >= 0) {
                        notice[field.name] = field.options[field.selectedIndex].text;
                    }
                } else {
                    notice[field.name] = field.value;
                }
            }
            data["notice"] = await encryptData(JSON.stringify(notice));
        } else {
            data["notice"] = await encryptData(notice_fields[0].value);
        }

        getCSRFToken().then(function (token) {
            data["csrf_name"] = token.csrf_name;
            data["csrf_value"] = token.csrf_value;

            return fetch(timesheetNoticeForm.action, {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (data["status"] === "success") {
                allowedReload = true;
                window.location.reload(true);
            } else {
                document.getElementById("loading-overlay").classList.add("hidden");
                alertErrorDetail.innerHTML = data["message"];
                alertError.classList.remove("hidden");
            }
        }).catch(function (error) {
            console.log(error);

            if (document.body.classList.contains('offline')) {
                let formData = new URLSearchParams(data).toString();
                saveDataWhenOffline(timesheetNoticeForm.action, timesheetNoticeForm.method, formData);
            } else {
                document.getElementById("loading-overlay").classList.add("hidden");
                alertErrorDetail.innerHTML = error;
                alertError.classList.remove("hidden");
            }

        });
    });
}


function createKeyMaterial(password) {
    let enc = new TextEncoder();
    return window.crypto.subtle.importKey(
        "raw",
        enc.encode(password),
        { name: "PBKDF2" },
        false,
        ["deriveBits", "deriveKey"]
    );
}

function createsAESKey(keyMaterial, salt) {
    return window.crypto.subtle.deriveKey(
        {
            "name": "PBKDF2",
            "salt": salt,
            "iterations": 250000,
            "hash": "SHA-256"
        },
        keyMaterial,
        { "name": "AES-GCM", "length": 256 },
        true,
        ["encrypt", "decrypt"]
    );
}

async function encryptData(data) {
    try {
        const iv = window.crypto.getRandomValues(new Uint8Array(12));

        const encryptedContent = await window.crypto.subtle.encrypt(
            {
                name: "AES-GCM",
                iv: iv,
            },
            aesKey,
            new TextEncoder().encode(data)
        );

        const encryptedContentArr = new Uint8Array(encryptedContent);
        let buff = new Uint8Array(iv.byteLength + encryptedContentArr.byteLength);
        buff.set(iv, 0);
        buff.set(encryptedContentArr, iv.byteLength);
        const base64Buff = buff_to_base64(buff);
        return base64Buff;
    } catch (e) {
        console.log(`Error - ${e}`);
        alertErrorDetail.innerHTML = lang.encrypt_error;
        alertError.classList.remove("hidden");
        return "";
    }
}

function buff_to_base64(buff) {
    return btoa(String.fromCharCode.apply(null, buff));
}
function base64_to_buf(b64) {
    return Uint8Array.from(atob(b64), (c) => c.charCodeAt(null));
}

async function getStore() {
    if ('indexedDB' in window) {
        let openRequest = indexedDB.open('lifeTrackingData', 2);

        return new Promise(function (resolve, reject) {

            openRequest.onsuccess = function () {
                let db = openRequest.result;
                var transation = db.transaction("keys", "readwrite");
                var store = transation.objectStore("keys");

                resolve(store);
            };
        });


    }
}

async function getAESKeyFromStore() {
    let store = await getStore();
    let key = await new Promise(function (resolve, reject) {
        let request = store.get(projectID);
        request.onsuccess = function (e) {
            if (request.result) {
                resolve(request.result.key);
            }
            resolve(null);
        };
    });
    return key;
}


async function decryptData(encryptedData, sheet_id) {
    try {
        const encryptedDataBuff = base64_to_buf(encryptedData);
        const iv = encryptedDataBuff.slice(0, 12);
        const data = encryptedDataBuff.slice(12);

        const decryptedContent = await window.crypto.subtle.decrypt(
            {
                name: "AES-GCM",
                iv: iv,
            },
            aesKey,
            data
        );
        return new TextDecoder().decode(decryptedContent);
    } catch (e) {
        console.log(sheet_id);
        console.log(`Error - ${e}`);
        alertErrorDetail.innerHTML = lang.decrypt_error;
        alertError.classList.remove("hidden");
        return "";
    }
}

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

document.querySelector('#wordExport').addEventListener('click', function (e) {
    e.preventDefault();

    let filename = timesheetNoticeWrapper.dataset.sheetname ? timesheetNoticeWrapper.dataset.sheetname : timesheetNoticeWrapper.dataset.projectname;

    const word_elements = [];

    let notice_fields = Array.from(timesheetNoticeWrapper.querySelectorAll('.timesheet-notice-wrapper:not(.hidden)'));
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


let checkboxHideEmptySheets = document.getElementById('checkboxHideEmptySheets');
if (checkboxHideEmptySheets) {
    checkboxHideEmptySheets.addEventListener('click', function (event) {
        document.querySelectorAll('.timesheet-notice-wrapper[data-empty="1"]').forEach(function (el) {
            if (checkboxHideEmptySheets.checked) {
                el.classList.add("hidden");
            } else {
                el.classList.remove("hidden");
            }
        });
        return;
    });
}

let checkboxHideEmptyNoticeFields = document.getElementById('checkboxHideEmptyNoticeFields');
if (checkboxHideEmptyNoticeFields) {
    checkboxHideEmptyNoticeFields.addEventListener('click', function (event) {
        document.querySelectorAll('.timesheet-notice-field[data-empty="1"]').forEach(function (el) {
            if (checkboxHideEmptyNoticeFields.checked) {
                el.classList.add("hidden");
            } else {
                el.classList.remove("hidden");
            }
        });
        return;
    });
}