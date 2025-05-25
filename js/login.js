'use strict';

if (document.body.classList.contains("login")) {
    // Delete IndexedDBs
    if (('indexedDB' in window)) {
        console.log("delete database");
        window.indexedDB.deleteDatabase('lifeTrackingData');
    }
}