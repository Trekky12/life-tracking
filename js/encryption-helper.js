"use strict";

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

function deriveKEK(keyMaterial, salt, iterations) {
    return window.crypto.subtle.deriveKey(
        {
            "name": "PBKDF2",
            "salt": salt,
            "iterations": iterations,
            "hash": "SHA-512"
        },
        keyMaterial,
        { "name": "AES-GCM", "length": 256 },
        true,
        ["encrypt", "decrypt"]
    );
}

async function encryptData(key, data) {
    try {
        const iv = window.crypto.getRandomValues(new Uint8Array(16));

        const encryptedContent = await window.crypto.subtle.encrypt(
            {
                name: "AES-GCM",
                iv: iv,
            },
            key,
            new TextEncoder().encode(data)
        );

        const encryptedContentArr = new Uint8Array(encryptedContent);
        let buff = new Uint8Array(iv.byteLength + encryptedContentArr.byteLength);
        buff.set(iv, 0);
        buff.set(encryptedContentArr, iv.byteLength);
        const base64Buff = buff_to_base64(buff);
        return base64Buff;
    } catch (e) {
        console.error(`Error Encrypting - ${e}`);
        //alertDetail.innerHTML = lang.encrypt_error;
        //alert.classList.remove("hidden");
        throw e;
    }
}

async function decryptData(key, encryptedData) {
    try {
        const encryptedDataBuff = base64_to_buf(encryptedData);
        const iv = encryptedDataBuff.slice(0, 16);
        const data = encryptedDataBuff.slice(16);

        const decryptedContent = await window.crypto.subtle.decrypt(
            {
                name: "AES-GCM",
                iv: iv,
            },
            key,
            data
        );
        return new TextDecoder().decode(decryptedContent);
    } catch (e) {
        console.log(`Error Decrypting - ${e}`);
        throw e;
    }
}

async function createKeyObject(rawKey) {

    return window.crypto.subtle.importKey("raw", rawKey, "AES-GCM", false, [
        "encrypt",
        "decrypt",
    ]);
}

function buff_to_base64(buff) {
    return btoa(String.fromCharCode.apply(null, buff));
}

function base64_to_buf(b64) {
    return Uint8Array.from(atob(b64), (c) => c.charCodeAt(null));
}

function fromHexString(hexString) {
    return Uint8Array.from(hexString.match(/.{1,2}/g).map((byte) => parseInt(byte, 16)));
}

async function getEncryptionParameters() {
    let data = {};
    let token = await getCSRFToken()
    data["csrf_name"] = token.csrf_name;
    data["csrf_value"] = token.csrf_value;

    let response = await fetch(jsObject.timesheets_notice_params, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
    });
    let result = await response.json();
    if (result.status !== "success") {
        throw "Unable to retrieve parameters";
    }
    return result;
}

async function getRawMasterKeyFromKEK(KEK, testMessageEncryptedWithKEK, masterKeyEncryptedWithKEK) {
    try {
        const testMessage = await decryptData(KEK, testMessageEncryptedWithKEK);

        if (testMessage !== "test") {
            throw "Wrong message!";
        }
    } catch (e) {
        console.error(`Unable to decrypt test message - ${e}`);
        throw e;
    }

    let rawMasterKey;

    try {
        const savedMasterKey = await decryptData(KEK, masterKeyEncryptedWithKEK);
        rawMasterKey = base64_to_buf(savedMasterKey);
    } catch (e) {
        console.error(`Unable to decrypt masterKey - ${e}`);
        throw e;
    }

    return rawMasterKey;
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

async function getMasterKeyFromPW(pw, parameters, storeKEK = true) {

    const iterations = parameters.iterations;
    const salt = base64_to_buf(parameters.salt);
    const keyMaterial = await createKeyMaterial(pw);
    const newKEK = await deriveKEK(keyMaterial, salt, iterations);

    let rawMasterKey = await getRawMasterKeyFromKEK(newKEK, parameters.testMessageEncryptedWithKEK, parameters.masterKeyEncryptedWithKEK);

    if (storeKEK) {
        let store = await getStore();
        store.add({ 'project': projectID, 'key': newKEK });
    }

    return rawMasterKey;
}

async function getKEKfromStore() {
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

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

async function getMasterKeyFromStoreOrInput(projectID, parameters) {
    let rawMasterKey;

    let KEK = await getKEKfromStore();

    if (!KEK) {
        //let pw = window.prompt(lang.timesheets_notice_password);
        let pw = await inputDialog(lang.timesheets_notice_password);

        if (pw === false) {
            return false;
        }

        try {
            rawMasterKey = await getMasterKeyFromPW(pw, parameters.data, true);
        } catch (e) {
            console.log(e);
            return false;
        }

    } else {
        // Check stored KEK
        try {
            rawMasterKey = await getRawMasterKeyFromKEK(KEK, parameters.data.testMessageEncryptedWithKEK, parameters.data.masterKeyEncryptedWithKEK);
        } catch (e) {
            // delete saved KEK
            let store = await getStore();
            let deleteRequest = await store.delete(projectID);
            return false;
        }
    }
    return await createKeyObject(rawMasterKey);
}

function createInputDialog(message, callback) {
    var inputModal = document.createElement('div');
    inputModal.id = 'input-modal';
    inputModal.classList.add('modal');
    inputModal.classList.add('vertical-centered');

    var modalInner = document.createElement('div');
    modalInner.classList.add('modal-inner');

    var form = document.createElement('form');

    var modalContent = document.createElement('div');
    modalContent.classList.add('modal-content');
    var labelParagraph = document.createElement('p');
    labelParagraph.textContent = message;
    modalContent.appendChild(labelParagraph);

    let inputGroup = document.createElement('div');
    inputGroup.classList.add("form-group");
    var inputField = document.createElement('input');
    inputField.type = "password";
    inputField.classList.add("form-control");

    inputGroup.appendChild(inputField);
    modalContent.appendChild(inputGroup);

    let labelGroup = document.createElement('div');
    labelGroup.classList.add("form-group");

    let label = document.createElement('label');
    label.classList.add("form-control");
    var inputCheckbox = document.createElement('input');
    inputCheckbox.type = "checkbox";

    inputCheckbox.onclick = function () {
        if (inputField.type === "password") {
            inputField.type = "text";
        } else {
            inputField.type = "password";
        }
    };

    label.appendChild(inputCheckbox);

    label.appendChild(document.createTextNode(" Show password"));
    labelGroup.appendChild(label);

    modalContent.appendChild(labelGroup);

    var modalFooter = document.createElement('div');
    modalFooter.classList.add('modal-footer');

    var buttonsDiv = document.createElement('div');
    buttonsDiv.classList.add('buttons');

    var confirmButton = document.createElement('button');
    confirmButton.type = 'button';
    confirmButton.classList.add('button');
    confirmButton.textContent = lang.ok;

    var cancelButton = document.createElement('button');
    cancelButton.classList.add('button', 'button-text', 'cancel');
    cancelButton.type = 'button';
    cancelButton.textContent = lang.cancel;

    buttonsDiv.appendChild(confirmButton);
    buttonsDiv.appendChild(cancelButton);

    modalFooter.appendChild(buttonsDiv);

    form.appendChild(modalContent);
    form.appendChild(modalFooter);

    modalInner.appendChild(form);

    inputModal.appendChild(modalInner);

    document.body.appendChild(inputModal);

    inputModal.style.display = "block";

    inputField.focus();

    confirmButton.onclick = function () {
        inputModal.remove();
        callback(inputField.value);
    };

    cancelButton.onclick = function () {
        inputModal.remove();
        callback(false);
        document.removeEventListener('keydown', confirmKeyEvent);
    };

    document.addEventListener('keydown', confirmKeyEvent);

    function confirmKeyEvent(event) {
        if (event.key === "Enter") {
            event.preventDefault();
            inputModal.remove();
            callback(inputField.value);
            document.removeEventListener('keydown', confirmKeyEvent);
        } else if (event.key === 'Escape' || event.keyCode === 27) {
            event.preventDefault();
            inputModal.remove();
            callback(false);
            document.removeEventListener('keydown', confirmKeyEvent);
        }
    }
}

function inputDialog(message) {
    return new Promise((resolve, reject) => {
        createInputDialog(message, resolve);
    });
}