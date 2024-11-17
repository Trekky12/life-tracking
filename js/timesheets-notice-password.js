"use strict";

const alert = document.querySelector("#passwordAlert");
const alertDetail = alert.querySelector("#alertDetail");

const saveBtn = document.querySelector('button#save_notice_password');
const oldPassword = document.querySelector('input#inputOldPassword');
const newPassword1 = document.querySelector('input#inputNewPassword1');
const newPassword2 = document.querySelector('input#inputNewPassword2');

const forgotPassword = document.querySelector("a#forgotPassword");
const recoveryCodeWrapper = document.querySelector("#recoveryCodeWrapper");
const recoveryCodeInput = document.querySelector('textarea#inputRecoveryCode');

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

    // Check if old password or recovery key are inserted
    if (!oldPassword.getAttribute("disabled") && oldPassword.value == '' && recoveryCodeInput.value == '') {
        alertDetail.innerHTML = lang.timesheets_insert_password_or_recovery;
        alert.classList.remove("hidden");
        alert.classList.add("danger");
        alert.classList.remove("success");
    }
    // Check recovery key length = 24 
    else if (recoveryCodeInput.value != '' && recoveryCodeInput.value.split(" ").length != 24) {
        alertDetail.innerHTML = lang.timesheets_recovery_wrong_size;
        alert.classList.remove("hidden");
        alert.classList.add("danger");
        alert.classList.remove("success");
    }
    // Check if passwords are equal
    else if (newPassword1.value == '' || newPassword2.value == '' || newPassword1.value !== newPassword2.value) {
        alertDetail.innerHTML = lang.timesheets_notice_password_no_match;
        alert.classList.remove("hidden");
        alert.classList.add("danger");
        alert.classList.remove("success");
    } else {
        try {
            await setPassword();
            alertDetail.innerHTML = lang.timesheets_notice_password_success;
            alert.classList.remove("hidden");
            alert.classList.remove("danger");
            alert.classList.add("success");

            oldPassword.parentElement.classList.remove("hidden");
            oldPassword.removeAttribute("disabled")

            recoveryCodeWrapper.classList.add("hidden");
            forgotPassword.classList.remove("hidden");

        } catch (e) {
            console.log(e);
            alertDetail.innerHTML = lang.timesheets_notice_password_wrong;
            alert.classList.remove("hidden");
            alert.classList.add("danger");
            alert.classList.remove("success");
        }
    }

    document.querySelector('form').reset();
    loadingWindowOverlay.classList.add("hidden");
});


forgotPassword.addEventListener('click', async function (e) {
    e.preventDefault();

    recoveryCodeWrapper.classList.toggle("hidden");

    if (!oldPassword.getAttribute("disabled")) {
        oldPassword.setAttribute("disabled", true);
    } else {
        oldPassword.removeAttribute("disabled");
    }
});


async function setPassword() {

    let parameters = await getEncryptionParameters();

    const iterations = parameters.data.iterations;
    const oldTestMessageEncryptedWithKEK = parameters.data.testMessageEncryptedWithKEK;
    const oldTestMessageEncryptedWithRecoveryKey = parameters.data.testMessageEncryptedWithRecoveryKey;

    let rawMasterKey;
    let masterKey;

    // update entry
    if (oldPassword.value != "" && oldTestMessageEncryptedWithKEK) {
        console.log("Update password from old password");
        let oldSalt = base64_to_buf(parameters.data.salt);
        const oldKeyMaterial = await createKeyMaterial(oldPassword.value);
        const oldKEK = await deriveKEK(oldKeyMaterial, oldSalt, iterations);

        rawMasterKey = await getRawMasterKeyFromKEK(oldKEK, parameters.data.testMessageEncryptedWithKEK, parameters.data.masterKeyEncryptedWithKEK);
    }
    else if (recoveryCodeInput.value != "" && recoveryCodeInput.value.split(" ").length == 24 && oldTestMessageEncryptedWithRecoveryKey) {
        console.log("update password from recovery code");

        const recoveryKeyHex = bip39.mnemonicToEntropy(recoveryCodeInput.value);
        const rawRecoveryKey = fromHexString(recoveryKeyHex);
        const recoveryKey = await createKeyObject(rawRecoveryKey);

        const masterKeyEncryptedWithRecoveryKey = parameters.data.masterKeyEncryptedWithRecoveryKey;
        const masterKey = await decryptData(recoveryKey, masterKeyEncryptedWithRecoveryKey);
        rawMasterKey = base64_to_buf(masterKey);
    }
    // no testmessage => no masterkey => create new entry
    else if (!oldTestMessageEncryptedWithKEK) {
        console.log("No password set so set new password");
        rawMasterKey = window.crypto.getRandomValues(new Uint8Array(32));
    }
    else {
        throw "Unknown error";
    }

    masterKey = await createKeyObject(rawMasterKey)


    let newData = {};

    // Create new Recovery Key (complete new entries and "old" entries without recovery key)
    if (!oldTestMessageEncryptedWithRecoveryKey) {

        let rawRecoveryKey = window.crypto.getRandomValues(new Uint8Array(32));
        let recoveryKey = await createKeyObject(rawRecoveryKey)

        let masterKeyEncryptedWithRecoveryKey = await encryptData(recoveryKey, buff_to_base64(rawMasterKey));
        newData['masterKeyEncryptedWithRecoveryKey'] = masterKeyEncryptedWithRecoveryKey;

        let recoveryKeyEncryptedWithMasterKey = await encryptData(masterKey, buff_to_base64(rawRecoveryKey));
        newData['recoveryKeyEncryptedWithMasterKey'] = recoveryKeyEncryptedWithMasterKey;

        const testMessageEncryptedWithRecoveryKey = await encryptData(recoveryKey, "test");
        newData['testMessageEncryptedWithRecoveryKey'] = testMessageEncryptedWithRecoveryKey;
    }

    // Create new KEK
    let newSalt = window.crypto.getRandomValues(new Uint8Array(16));
    newData['salt'] = buff_to_base64(newSalt);
    const keyMaterial = await createKeyMaterial(newPassword1.value);
    // no change in iterations (but possible here)
    let newIterations = iterations;
    newData['iterations'] = newIterations;
    const KEK = await deriveKEK(keyMaterial, newSalt, newIterations);

    // Encrypt test message with new KEK
    const testMessageEncryptedWithKEK = await encryptData(KEK, "test");
    newData['testMessageEncryptedWithKEK'] = testMessageEncryptedWithKEK;

    // Encrypt rawMasterKey with new KEK
    const masterKeyEncryptedWithKEK = await encryptData(KEK, buff_to_base64(rawMasterKey));
    newData['masterKeyEncryptedWithKEK'] = masterKeyEncryptedWithKEK;

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
        throw "Error saving parameters";
    }

    return true;
}
