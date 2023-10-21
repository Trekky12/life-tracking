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
    
    return window.crypto.subtle.importKey("raw", rawKey, "AES-GCM", true, [
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