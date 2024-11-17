"use strict";


const timesheetNoticeWrapper = document.querySelector("#timesheetNoticeWrapper");
const loadingIconTimesheetNotice = document.querySelector("#loadingIconTimesheetNotice");

const timesheetNoticeForm = document.querySelector("#timesheetNoticeForm");
const timesheetLastSavedWrapper = document.querySelector("#lastSavedWrapper");
const timesheetLastSaved = document.querySelector("#lastSaved");

const alertError = document.querySelector("#alertError");
const alertErrorDetail = alertError.querySelector("#alertErrorDetail");

const projectID = parseInt(timesheetNoticeWrapper.dataset.project);

let checkboxHideEmptyNoticeFields = document.getElementById('checkboxHideEmptyNoticeFields');

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

    let notice_fields = Array.from(timesheetNoticeWrapper.querySelectorAll('.timesheet-notice'));

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
            if (IsJsonString(notice)) {

                notice = JSON.parse(notice);

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

    loadingIconTimesheetNotice.classList.add("hidden");
    timesheetNoticeWrapper.classList.remove("hidden");
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
            const decryptedCEK = await decryptData(masterKey, encryptedCEK);
            const rawCEK = base64_to_buf(decryptedCEK);
            CEK = await createKeyObject(rawCEK);
        } catch (e) {
            console.error(`Unable to decrypt CEK - ${e}`);
            throw e;
        }

        if (notice) {
            try {
                let decrypted_notice = await decryptData(CEK, notice);

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
    data["encryptedCEK"] = await encryptData(masterKey, buff_to_base64(rawCEK));

    // encrypt data with CEK
    data["notice"] = await encryptData(CEK, noticeData);

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
            } else {
                el.classList.remove("hidden");
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
