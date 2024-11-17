"use strict";

const loadingIconTimesheetNotice = document.querySelector("#loadingIconTimesheetNotice");
const alertError = document.querySelector("#alertError");
const alertErrorDetail = alertError.querySelector("#alertErrorDetail");

const recoveryKeyWrapper = document.querySelector("#recoveryKeyWrapper");

const projectID = parseInt(recoveryKeyWrapper.dataset.project);

if (!window.crypto || !window.crypto.subtle) {
    alertErrorDetail.innerHTML = lang.decrypt_error;
    alertError.classList.remove("hidden");
    alertError.classList.add("danger");
    loadingIconTimesheetNotice.classList.add("hidden");
}

getRecoveryCode();

async function getRecoveryCode() {

    const parameters = await getEncryptionParameters();
    const testMessageEncryptedWithKEK = parameters.data.testMessageEncryptedWithKEK;

    if (!testMessageEncryptedWithKEK) {
        alertErrorDetail.innerHTML = lang.timesheets_no_password_set;
        alertError.classList.remove("hidden");
        loadingIconTimesheetNotice.classList.add("hidden");
        return;
    }

    const masterKey = await getMasterKeyFromStoreOrInput(projectID, parameters);

    if (masterKey === false || !parameters.data.testMessageEncryptedWithRecoveryKey) {
        alertErrorDetail.innerHTML = lang.decrypt_error;
        alertError.classList.remove("hidden");
        loadingIconTimesheetNotice.classList.add("hidden");
        return;
    }

    const recoveryKeyEncryptedWithMasterKey = parameters.data.recoveryKeyEncryptedWithMasterKey;
    try {
        const recoveryKey = await decryptData(masterKey, recoveryKeyEncryptedWithMasterKey);
        const rawRecoveryKey = base64_to_buf(recoveryKey);

        const recoveryKeyWords = bip39.entropyToMnemonic(rawRecoveryKey);

        recoveryKeyWrapper.querySelector('code').innerHTML = recoveryKeyWords;

        alertErrorDetail.innerHTML = "";
        alertError.classList.add("hidden");
        alertError.classList.remove("danger");
        loadingIconTimesheetNotice.classList.add("hidden");
        recoveryKeyWrapper.classList.remove("hidden");

    } catch (e) {
        console.error(`Unable to decrypt recoveryKey - ${e}`);
        throw e;
    }




}
