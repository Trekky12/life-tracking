"use strict";

const timesheetEncryptionWrapper = document.querySelector('#timesheetEncryptionWrapper');
if (!timesheetEncryptionWrapper) {
    throw "No notices here";
}

const timesheetNoticeWrapper = document.querySelector("#timesheetNoticeWrapper");
const loadingIconTimesheetNotice = document.querySelector("#loadingIconTimesheetNotice");

const timesheetNoticeForm = document.querySelector("#timesheetNoticeForm");
const timesheetLastSavedWrapper = document.querySelector("#lastSavedWrapper");
const timesheetLastSaved = document.querySelector("#lastSaved");

const alertError = document.querySelector("#alertError");
const alertErrorDetail = alertError.querySelector("#alertErrorDetail");

const projectID = parseInt(timesheetNoticeWrapper.dataset.project);

let checkboxHideEmptyNoticeFields = document.getElementById('checkboxHideEmptyNoticeFields');

const loadingFilesIcon = document.getElementById('loadingIconFileUpload');
const fileInput = document.querySelector('#fileupload');

if (!window.crypto || !window.crypto.subtle) {
    alertErrorDetail.innerHTML = lang.decrypt_error;
    alertError.classList.remove("hidden");
    loadingIconTimesheetNotice.classList.add("hidden");
    if (timesheetNoticeForm) {
        timesheetNoticeForm.querySelectorAll('textarea, select, input').forEach(function (element) {
            element.disabled = true;
        });
        timesheetNoticeForm.querySelector('button[type="submit"]').classList.add("hidden");
    }
}

let currentNotice = getNoticeData();

let masterKey;

loadData();

async function loadData() {

    const parameters = await getEncryptionParameters();
    const testMessageEncryptedWithKEK = parameters.data.testMessageEncryptedWithKEK;

    if (!testMessageEncryptedWithKEK) {
        alertErrorDetail.innerHTML = lang.timesheets_no_password_set;
        alertError.classList.remove("hidden");
        loadingIconTimesheetNotice.classList.add("hidden");
        return;
    }

    masterKey = await getMasterKeyFromStoreOrInput(projectID, parameters);

    if (masterKey === false) {
        alertErrorDetail.innerHTML = lang.decrypt_error;
        alertError.classList.remove("hidden");
        loadingIconTimesheetNotice.classList.add("hidden");
        return;
    }

    let notice_fields = Array.from(timesheetEncryptionWrapper.querySelectorAll('.timesheet-notice'));

    // Sequential
    for (const notice_field of notice_fields) {
        let notice;

        try {
            notice = await getNotice(notice_field.dataset);
        } catch (e) {
            alertErrorDetail.innerHTML = lang.decrypt_error;
            alertError.classList.remove("hidden");
            document.getElementById("loading-overlay").classList.add("hidden");

            loadingIconTimesheetNotice.classList.add("hidden");
            return;
        }

        if (notice) {
            // default no fields
            let hasFields = false;

            if (IsJsonString(notice)) {
                notice = JSON.parse(notice);

                // only one field named "notice"?
                if ("notice" in notice && notice.length == 1) {
                    hasFields = false;
                    notice = notice["notice"];
                } else {
                    hasFields = true;
                }
            }

            if (hasFields) {

                for (const [field_name, field_value] of Object.entries(notice)) {
                    let field_value = notice[field_name];
                    let field_element = notice_field.querySelector('[data-name="' + field_name + '"]');

                    if (field_element) {

                        field_element.dataset.saved = 1;

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

                    } else {

                        let view = notice_field.dataset.view;

                        if (view == "edit") {
                            console.log(field_name + " not found, appending manually!");

                            const field = document.createElement('div');
                            field.className = 'timesheet-notice-field';

                            const formGroup = document.createElement('div');
                            formGroup.className = 'form-group';

                            const label = document.createElement('label');
                            label.setAttribute('for', `input_${field_name}`);
                            label.innerHTML = field_name;

                            const textarea = document.createElement('textarea');
                            textarea.className = 'form-control';
                            textarea.id = `input_${field_name}`;
                            textarea.name = field_name;
                            textarea.dataset.name = field_name;
                            textarea.dataset.default = 0;
                            textarea.dataset.saved = 1;
                            textarea.value = field_value;

                            formGroup.appendChild(label);
                            formGroup.appendChild(textarea);

                            field.appendChild(formGroup);

                            notice_field.appendChild(field);
                        } else if (view == "view") {
                            console.log(field_name + " not found, appending manually!");

                            const field = document.createElement('div');
                            field.className = 'timesheet-notice-field';

                            const label = document.createElement('h4');
                            label.innerHTML = field_name + ":";

                            const content = document.createElement('p');
                            content.className = 'notice-field';
                            content.dataset.name = field_name;
                            content.dataset.default = 0;
                            content.dataset.saved = 1;
                            content.innerHTML = field_value;

                            field.appendChild(label);
                            field.appendChild(content);

                            notice_field.appendChild(field);
                        }

                    }

                }

                // "new" fields which where not saved previously are empty
                notice_field.querySelectorAll('[data-saved="0"]').forEach(function (el) {
                    el.parentElement.dataset.empty = 1;
                });

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
            // no notice saved
            notice_field.closest('.timesheet-notice-wrapper').dataset.empty = 1;
        }

        // Hide empty fields 
        if (checkboxHideEmptyNoticeFields && checkboxHideEmptyNoticeFields.checked) {
            document.querySelectorAll('.timesheet-notice-field[data-empty="1"], .timesheet-notice-wrapper[data-empty="1"] .timesheet-notice-field').forEach(function (el) {
                el.classList.add("hidden");
            });
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

    let notice_file_fields = Array.from(timesheetEncryptionWrapper.querySelectorAll('.timesheet-files'));
    for (const file_field of notice_file_fields) {
        await loadFiles(file_field);
    }

    loadingIconTimesheetNotice.classList.add("hidden");
    timesheetEncryptionWrapper.classList.remove("hidden");
}

async function getNotice(dataset) {

    if (!masterKey) {
        console.error(`masterKey missing`);
        throw "masterKey missing";
    }

    let parent_id = dataset.id;
    let type = dataset.type

    let url = "";
    if (type == "sheet") {
        url = jsObject.timesheets_sheets_notice_data;
    } else if (type == "customer") {
        url = jsObject.timesheets_customers_notice_data;
    } else if (type == "project") {
        url = jsObject.timesheets_project_notice_data;
    }

    let notice_response = await fetch(url + '?id=' + parent_id, {
        method: "GET",
        credentials: "same-origin",
    });
    let notice_result = await notice_response.json();

    if (notice_result.status !== "error" && notice_result.entry) {
        let notice = notice_result.entry.notice;
        let encryptedCEK = notice_result.entry.encryptedCEK;

        if (timesheetLastSavedWrapper) {
            if (timesheetLastSaved) {
                timesheetLastSaved.innerHTML = moment(notice_result.entry.changedOn).format(i18n.dateformatJS.datetime);
                timesheetLastSavedWrapper.classList.remove("hidden");
            } else {
                timesheetLastSavedWrapper.classList.add("hidden");
            }
        }

        let CEK;
        try {
            const decryptedCEK = await decryptTextData(masterKey, encryptedCEK);
            const rawCEK = base64_to_buf(decryptedCEK);
            CEK = await createKeyObject(rawCEK);
        } catch (e) {
            console.error(`Unable to decrypt CEK - ${e}`);
            throw e;
        }

        if (notice) {
            try {
                let decrypted_notice = await decryptTextData(CEK, notice);

                if (timesheetLastSaved) {
                    currentNotice = decrypted_notice;
                }

                return decrypted_notice;
            } catch (e) {
                console.error(`Unable to decrypt notice - ${e}`);
                throw e;
            }
        }

    }
    return;
}

/**
 * Save Notice
 */
if (timesheetNoticeForm) {
    timesheetNoticeForm.addEventListener("submit", async function (e) {
        e.preventDefault();
        saveNotice(false);
    });

    /**
     * Auto Save
     */
    setInterval(async function () {
        saveNotice(true);
    }, 2 * 60 * 1000);
}

function getNoticeData() {
    if (!timesheetNoticeForm) {
        return;
    }
    let notice_fields = Array.from(timesheetNoticeForm.querySelectorAll('input[type="text"], textarea, select'));
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
    return JSON.stringify(notice);
}

async function saveNotice(autoSave = false) {
    if (!masterKey) {
        alertErrorDetail.innerHTML = lang.encrypt_error;
        alertError.classList.remove("hidden");
        document.getElementById("loading-overlay").classList.add("hidden");
        return;
    }

    if (!autoSave) {
        alertError.classList.add("hidden");
        alertErrorDetail.innerHTML = "";
        document.getElementById("loading-overlay").classList.remove("hidden");
    }

    let noticeData = getNoticeData();
    if (autoSave && currentNotice && currentNotice === noticeData) {
        console.log("No changes, not saving!");
        return;
    }

    let data = {};
    data["is_autosave"] = autoSave ? 1 : 0;

    // create CEK
    const rawCEK = window.crypto.getRandomValues(new Uint8Array(32));
    const CEK = await createKeyObject(rawCEK);

    // encrypt CEK with masterKey
    data["encryptedCEK"] = await encryptTextData(masterKey, buff_to_base64(rawCEK));

    // encrypt data with CEK
    data["notice"] = await encryptTextData(CEK, noticeData);

    try {
        let token = await getCSRFToken();

        data["csrf_name"] = token.csrf_name;
        data["csrf_value"] = token.csrf_value;

        let response = await fetch(timesheetNoticeForm.action, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify(data),
        });
        let result = await response.json();
        if (result["status"] === "success") {
            // store updated notice for next update
            currentNotice = noticeData;
            // update last saved

            if (timesheetLastSavedWrapper) {
                if (timesheetLastSaved) {
                    timesheetLastSaved.innerHTML = moment(result.entry.changedOn).format(i18n.dateformatJS.datetime);
                    timesheetLastSavedWrapper.classList.remove("hidden");
                } else {
                    timesheetLastSavedWrapper.classList.add("hidden");
                }
            }
        }
        if (!autoSave) {
            if (result["status"] === "success") {
                allowedReload = true;
                window.location.reload(true);
            } else {
                document.getElementById("loading-overlay").classList.add("hidden");
                alertErrorDetail.innerHTML = data["error"];
                alertError.classList.remove("hidden");
            }
        }
    } catch (error) {
        console.log(error);

        if (!autoSave) {
            if (document.body.classList.contains('offline')) {
                let formData = new URLSearchParams(data).toString();
                saveDataWhenOffline(timesheetNoticeForm.action, timesheetNoticeForm.method, formData);
            } else {
                document.getElementById("loading-overlay").classList.add("hidden");
                alertErrorDetail.innerHTML = error;
                alertError.classList.remove("hidden");
            }
        }
    }
}

let checkboxHideEmptySheets = document.getElementById('checkboxHideEmptySheets');
if (checkboxHideEmptySheets) {
    checkboxHideEmptySheets.addEventListener('click', function (event) {
        document.querySelectorAll('.timesheet-notice-wrapper[data-empty="1"]').forEach(function (el) {
            if (checkboxHideEmptySheets.checked) {
                el.classList.add("hidden");
                el.dataset.hidden = 1;
            } else {
                el.classList.remove("hidden");
                el.dataset.hidden = 0;
            }
        });

        document.querySelectorAll('.timesheet-wrapper').forEach(function (wrapper) {
            const notice = wrapper.querySelector('.timesheet-notice-wrapper');
            const files = wrapper.querySelector('.timesheet-files-wrapper');

            const noticeEmpty = notice?.dataset.empty === "1";
            const filesEmpty = files?.dataset.empty === "1";

            if (checkboxHideEmptySheets.checked && noticeEmpty && filesEmpty) {
                wrapper.classList.add("hidden");
            } else {
                wrapper.classList.remove("hidden");
            }
        });

        return;
    });
}


if (checkboxHideEmptyNoticeFields) {
    checkboxHideEmptyNoticeFields.addEventListener('click', function (event) {
        document.querySelectorAll('.timesheet-notice-field[data-empty="1"], .timesheet-notice-wrapper[data-empty="1"] .timesheet-notice-field').forEach(function (el) {
            if (checkboxHideEmptyNoticeFields.checked) {
                el.classList.add("hidden");
            } else {
                el.classList.remove("hidden");
            }
        });
        return;
    });
}


async function loadFiles(filesContainer) {

    let parent_id = filesContainer.dataset.id;

    const response = await fetch(jsObject.timesheets_sheets_files + '?id=' + parent_id, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json'
        }
    });

    const data = await response.json();

    if (data.length == 0) {
        const wrapper = filesContainer.closest(".timesheet-files-wrapper");
        if (wrapper) {
            wrapper.classList.add("hidden");
            wrapper.dataset.hidden = 1;
            wrapper.dataset.empty = 1;
        }
    } else {
        data.forEach(async function (el) {
            await addFile(filesContainer, el);
        });
    }

    if (fileInput) {
        fileInput.classList.remove('hidden');
    }

}

async function addFile(filesContainer, data) {

    let CEK;
    try {
        const decryptedCEK = await decryptTextData(masterKey, data["encryptedCEK"]);
        const rawCEK = base64_to_buf(decryptedCEK);
        CEK = await createKeyObject(rawCEK);
    } catch (e) {
        console.error(`Unable to decrypt CEK - ${e}`);
        throw e;
    }

    try {
        let fileBuff = await decryptData(CEK, data["data"]);
        let base64file = buff_to_base64(fileBuff);
        const wrapper = document.createElement("div");
        wrapper.className = "timesheet-notice-file";

        if (data["type"].startsWith("image/")) {
            const img = document.createElement('img');
            img.src = `data:${data["type"]};base64,${base64file}`;
            img.alt = data["name"];

            wrapper.appendChild(img);

        } else {
            const par = document.createElement('p');
            par.textContent = data["name"];

            wrapper.appendChild(par);
        }

        let iconWrapper = document.createElement("div");
        iconWrapper.className = "timesheet-notice-file-icons"

        let a_download = document.createElement("a");
        a_download["href"] = `data:${data["type"]};base64,${base64file}`;
        a_download.download = data["name"];
        let span_download = document.createElement("span");
        span_download.innerHTML = document.getElementById('iconDownload').innerHTML;
        a_download.appendChild(span_download);

        iconWrapper.appendChild(a_download);

        let iconTrash = document.getElementById('iconTrash');

        if (iconTrash) {

            let a_delete = document.createElement("a");
            a_delete["href"] = "#";
            a_delete.dataset.url = data['delete'];
            a_delete.className = 'delete-timesheet-file'
            let span_delete = document.createElement("span");
            span_delete.innerHTML = document.getElementById('iconTrash').innerHTML;
            a_delete.appendChild(span_delete);

            iconWrapper.appendChild(a_delete);

        }

        wrapper.appendChild(iconWrapper);

        filesContainer.appendChild(wrapper);

    } catch (e) {
        console.error(`Unable to decrypt file - ${e}`);
        throw e;
    }
}

if (fileInput) {
    fileInput.addEventListener('change', async function (e) {

        const file = fileInput.files[0];
        if (!file) return;

        if (!masterKey) {
            return;
        }

        let token = await getCSRFToken();

        loadingFilesIcon.classList.remove('hidden');

        const arrayBuffer = await file.arrayBuffer();

        // create CEK
        const rawCEK = window.crypto.getRandomValues(new Uint8Array(32));
        const CEK = await createKeyObject(rawCEK);

        // encrypt CEK with masterKey
        const encryptedCEK = await encryptTextData(masterKey, buff_to_base64(rawCEK));

        const encryptedBytes = await encryptBinaryData(CEK, arrayBuffer);

        const encryptedBlob = new Blob([encryptedBytes], { type: 'application/octet-stream' });

        const form = new FormData();
        form.append('file', encryptedBlob, file.name);
        form.append('type', file.type);
        form.append('encryptedCEK', encryptedCEK);
        form.append('csrf_name', token.csrf_name);
        form.append('csrf_value', token.csrf_value);

        try {
            const response = await fetch(fileInput.dataset.url, {
                method: 'POST',
                credentials: "same-origin",
                body: form
            });
            const data = await response.json();

            loadingFilesIcon.classList.add('hidden');

            if (data['status'] === 'success') {
                fileInput.value = "";

                addFile(fileInput.closest('#timesheetFilesWrapper').querySelector(".timesheet-files"), data);

            }
        } catch (error) {
            loadingFilesIcon.classList.add('hidden');
            console.log(error);
        }
    });
}

document.addEventListener('click', async function (event) {
    let deleteTimesheetFile = event.target.closest('.delete-timesheet-file');

    if (deleteTimesheetFile) {
        event.preventDefault();

        let confirm_text = lang.really_delete;

        if (!await confirmDialog(confirm_text, null)) {
            loadingWindowOverlay.classList.add("hidden");
            return false;
        }

        let url = deleteTimesheetFile.dataset.url;

        loadingFilesIcon.classList.remove('hidden');

        try {
            let token = await getCSRFToken(true);
            let data = {};
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            let response = await fetch(url, {
                method: 'DELETE',
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });

            let result = await response.json();
            if (result["is_deleted"]) {
                deleteTimesheetFile.parentElement.parentElement.remove();
            }
        } catch (error) {
            console.log(error);
        }

        loadingFilesIcon.classList.add('hidden');
    }

});