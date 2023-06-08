"use strict";

const alert = document.querySelector("#passwordAlert");
const alertDetail = alert.querySelector("#alertDetail");

const saveBtn = document.querySelector('button#save_notice_password');
const oldPassword = document.querySelector('input#inputOldPassword');
const newPassword1 = document.querySelector('input#inputNewPassword1');
const newPassword2 = document.querySelector('input#inputNewPassword2');

if (!window.crypto || !window.crypto.subtle) {
    alertDetail.innerHTML = lang.decrypt_error;
    alert.classList.remove("hidden");
    alert.classList.add("danger");
    document.querySelector('input#inputSetPassword').disabled = true;
    document.querySelector('input#inputSetPassword2').disabled = true;
}

saveBtn.addEventListener('click', async function (e) {
    e.preventDefault();

    alertDetail.innerHTML = "";
    alert.classList.add("hidden");
    alert.classList.remove("danger");
    alert.classList.remove("success");

    // Check if passwords are equal
    if (newPassword1.value == '' || newPassword2.value == '' || newPassword1.value !== newPassword2.value) {
        alertDetail.innerHTML = lang.timesheets_notice_password_no_match;
        alert.classList.remove("hidden");
        alert.classList.add("danger");
        alert.classList.remove("success");
    } else {
        const result = await setPassword();
        if (result > 0) {
            alertDetail.innerHTML = lang.timesheets_notice_password_wrong;
            alert.classList.remove("hidden");
            alert.classList.add("danger");
            alert.classList.remove("success");
        } else {
            alertDetail.innerHTML = lang.timesheets_notice_password_success;
            alert.classList.remove("hidden");
            alert.classList.remove("danger");
            alert.classList.add("success");

            oldPassword.parentElement.classList.remove("hidden");
        }
    }

    document.querySelector('form').reset();
    loadingWindowOverlay.classList.add("hidden");
});


async function setPassword() {
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
        console.error(`Unable to retrieve parameters`);
        return 1;
    }

    const iterations = result.data.iterations;
    const oldEncryptedTestMessage = result.data.test;
    let rawKEK;

    // update entry
    if (oldEncryptedTestMessage) {
        let oldSalt = base64_to_buf(result.data.salt);
        const oldKeyMaterial = await createKeyMaterial(oldPassword.value);
        const oldAESKey = await deriveAESKey(oldKeyMaterial, oldSalt, iterations);

        try {
            const testMessage = await decryptData(oldAESKey, oldEncryptedTestMessage);

            if (testMessage !== "test") {
                throw "Wrong message!";
            }
        } catch (e) {
            console.error(`Unable to decrypt test message - ${e}`);
            return 1;
        }

        try {
            const oldEncryptedKEK = result.data.KEK;
            const oldKEK = await decryptData(oldAESKey, oldEncryptedKEK);
            rawKEK = base64_to_buf(oldKEK);
        } catch (e) {
            console.error(`Unable to decrypt KEK - ${e}`);
            return 1;
        }

    } else {
        rawKEK = window.crypto.getRandomValues(new Uint8Array(32));
    }

    // Create new AES Key
    let salt = window.crypto.getRandomValues(new Uint8Array(16));
    const keyMaterial = await createKeyMaterial(newPassword1.value);
    const AESKey = await deriveAESKey(keyMaterial, salt, iterations);

    // Encrypt test message with new key
    const encryptedTestMessage = await encryptData(AESKey, "test");

    // Encrypt rawKEY with new key
    const encryptedKEK = await encryptData(AESKey, buff_to_base64(rawKEK));

    // save new data
    let newData = { 'salt': buff_to_base64(salt), 'iterations': iterations, 'KEK': encryptedKEK, 'test': encryptedTestMessage };
    let newToken = await getCSRFToken()
    newData["csrf_name"] = newToken.csrf_name;
    newData["csrf_value"] = newToken.csrf_value;

    let newResponse = await fetch(jsObject.timesheets_notice_params_save, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(newData),
    });
    let newResult = await newResponse.json();

    if (newResult.status !== "success") {
        console.error(`Unable to save parameters`);
        return 1;
    }

    return 0;
}