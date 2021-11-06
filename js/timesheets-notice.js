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

        var data = {'password': pw};

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
            store.add({'project': projectID, 'key': newAESKey});

            aesKey = await getAESKeyFromStore();
        } else if (result.status === "error" && result.reason) {
            alertErrorDetail.innerHTML = result.reason;
            alertError.classList.remove("hidden");
            loadingIconTimesheetNotice.classList.add("hidden");
            return;
        }
    }

    let notice_fields = Array.from(timesheetNoticeWrapper.querySelectorAll('.timesheet-notice'));
    await Promise.all(notice_fields.map(async (notice_field) => {
        let sheet_id = parseInt(notice_field.dataset.sheet);
        let notice = await getNotice(sheet_id);
        if (notice) {
            if (notice_field.tagName && notice_field.tagName.toLowerCase() === "textarea") {
                notice_field.value = notice;
            } else {
                notice_field.innerHTML = notice.replace(/(?:\r\n|\r|\n)/g, '<br>');
            }
        }
    }));

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

        // old entries are not client side encrypted
        if (notice && notice_result.entry.encrypted === 0) {
            return notice;
        }
        if (notice && notice_result.entry.encrypted > 0) {
            if (!aesKey) {
                alertErrorDetail.innerHTML = lang.decrypt_error;
                alertError.classList.remove("hidden");
                return;
            }
            let decrypted_notice = await decryptData(notice);
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

        const timesheetNotice = timesheetNoticeForm.querySelector("#inputNotice");

        var data = {};
        data["notice"] = await encryptData(timesheetNotice.value);
        // temporary save the notice without client encryption
        data["notice2"] = timesheetNotice.value;
        data["encrypted"] = 1;

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
                window.location.reload();
            } else {
                document.getElementById("loading-overlay").classList.add("hidden");
                alertErrorDetail.innerHTML = data["message"];
                alertError.classList.remove("hidden");
            }
        }).catch(function (error) {
            console.log(error);
            document.getElementById("loading-overlay").classList.add("hidden");
        });
    });
}


function createKeyMaterial(password) {
    let enc = new TextEncoder();
    return window.crypto.subtle.importKey(
            "raw",
            enc.encode(password),
            {name: "PBKDF2"},
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
            {"name": "AES-GCM", "length": 256},
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


async function decryptData(encryptedData) {
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
        console.log(`Error - ${e}`);
        alertErrorDetail.innerHTML = lang.decrypt_error;
        alertError.classList.remove("hidden");
        return "";
    }
}