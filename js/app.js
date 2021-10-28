/**
 * based on https://developers.google.com/web/fundamentals/codelabs/push-notifications/
 * https://github.com/Minishlink/web-push-php-example/blob/master/src/app.js
 */
'use strict';

const pushButton = document.querySelector('#enable_notifications');
const categoriesList = document.querySelector('#notifications_categories_list');
const categoriesElements = document.querySelectorAll('#notifications_categories_list input.set_notifications_category');
const loadingIconManage = document.querySelector('#loadingIconManageNotifications');
const iftttUrlWrapper = document.querySelector('#ifttt_url_wrapper');

const badges = document.querySelectorAll('.header-inner .badge');
//const bell = document.querySelector('#iconBell');

const offlineAlert = document.getElementById("offline-alert");
const toast = document.getElementById('toastmessage');
const offlineElementsAlert = document.getElementById("offline-entries-alert");
const appLoadingWindowOverlay = document.getElementById('loading-overlay');

let isSubscribed = false;
var isCached = false;

window.addEventListener("online", handleNetworkChange);
window.addEventListener("offline", handleNetworkChange);
//handleNetworkChange();

function handleNetworkChange(event) {
    setOffline(!navigator.onLine);
}

function setOffline(offline) {
    if (offline) {
        document.body.classList.add("offline");
        offlineAlert.classList.remove("hidden");
        setFormFieldsDisabled(true);

        let alerts = document.querySelectorAll('.alert.hide-offline');
        alerts.forEach(function (item, idx) {
            item.classList.add("hidden");
        });

        // set notifications bell disabled
        //bell.classList.add('disabled');

    } else {
        document.body.classList.remove("offline");
        offlineAlert.classList.add("hidden");
        setFormFieldsDisabled(false);

        // the page is from cache but now we are online, so reload
        if (isCached) {
            window.location.reload();
        }

        // init notitications
        //bell.classList.remove('disabled');
        syncSubscription();
    }
}

function setFormFieldsDisabled(value) {

    //check for support
    if (!('indexedDB' in window)) {
        console.log('This browser doesn\'t support IndexedDB');

        let fields = document.querySelectorAll('form input, form select, form button[type="submit"]');
        fields.forEach(function (item, idx) {
            if (!item.classList.contains("disabled")) {
                if (value) {
                    item.setAttribute("disabled", true);
                } else {
                    item.removeAttribute("disabled");
                }
            }
        });
        return;
    }

    saveFormDataWhenOffline();

}

function getIndexedDB() {
    let openRequest = indexedDB.open('lifeTrackingData', 1);

    openRequest.onupgradeneeded = function () {
        let db = openRequest.result;
        if (!db.objectStoreNames.contains('forms')) {
            db.createObjectStore('forms', {keyPath: 'id', autoIncrement: true});
        }
    }

    return openRequest;
}

function saveFormDataWhenOffline() {
    let openRequest = getIndexedDB();
    openRequest.onsuccess = function () {
        let db = openRequest.result;

        let forms = document.querySelectorAll('form');
        forms.forEach(function (item, idx) {
            if (item.method === 'post') {
                item.addEventListener('submit', function (e) {
                    e.preventDefault();

                    //@see https://stackoverflow.com/a/48950600
                    let form = new FormData(item);
                    // append time of real request if field is not visible
                    if (!form.has('time')) {
                        form.append('time', new Date().toLocaleTimeString());
                    }
                    let formData = new URLSearchParams(form).toString();

                    let transaction = db.transaction('forms', 'readwrite');
                    let forms = transaction.objectStore('forms');
                    let request = forms.add({'action': item.action, 'type': 'POST', 'data': formData});

                    request.onsuccess = function () {
                        console.log("saved locally");
                        appLoadingWindowOverlay.classList.add("hidden");
                        showToast(lang.entry_saved_locally, "green");
                        offlineElementsAlert.classList.remove("hidden");
                        item.reset();
                    }
                    request.onerror = function () {
                        console.log("Error", request.error);
                        appLoadingWindowOverlay.classList.add("hidden");
                        showToast(lang.entry_saved_locally_error, "red");
                    }
                });
            }
        });
    };
}

function saveDataWhenOffline(url, type = 'POST', data = null) {
    let openRequest = getIndexedDB();
    openRequest.onsuccess = function () {
        let db = openRequest.result;

        let transaction = db.transaction('forms', 'readwrite');
        let forms = transaction.objectStore('forms');
        let request = forms.add({'action': url, 'type': type, 'data': data});

        request.onsuccess = function () {
            console.log("saved locally");
            appLoadingWindowOverlay.classList.add("hidden");
            showToast(lang.entry_saved_locally, "green");
            offlineElementsAlert.classList.remove("hidden");
            allowedReload = true;
            window.location.reload();
        }
        request.onerror = function () {
            console.log("Error", request.error);
            appLoadingWindowOverlay.classList.add("hidden");
            showToast(lang.entry_saved_locally_error, "red");
        }
    };
}

initServiceWorker();

// set offline mode when current page is cached
// reset the info in the localStorage and save the 
// info in a local variable 
document.addEventListener("DOMContentLoaded", function () {
    let timestamp = Math.round(document.querySelector("meta[name='timestamp']").getAttribute("content"));
    let currentTime = Math.round(Date.now() / 1000);
    let offset = 15;
    if (localStorage.getItem('isCached') || (timestamp + offset <= currentTime)) {
        localStorage.removeItem('isCached');
        console.log('this is cached!');
        console.log(timestamp);
        console.log(currentTime);
        setOffline(true);
        isCached = true;
    }

    // check for offline entries
    if (!('indexedDB' in window)) {
        return;
    }

    let openRequest = getIndexedDB();
    openRequest.onsuccess = function () {
        let db = openRequest.result;
        let transaction = db.transaction('forms', 'readwrite');
        let forms = transaction.objectStore('forms');
        let request = forms.getAll();

        request.onsuccess = function () {
            if (request.result.length <= 0) {
                return;
            }
            offlineElementsAlert.classList.remove("hidden");

            // submit offline entries if we are online!
            if (!isCached) {
                let promises = [];
                let success = 0;
                let failed = 0;

                request.result.forEach(function (form, idx) {
                    //@see https://stackoverflow.com/a/38362312
                    promises.push(
                            getCSRFToken().then(function (token) {
                        let data = new URLSearchParams(form.data);
                        data.set("csrf_name", token.csrf_name);
                        data.set("csrf_value", token.csrf_value);

                        return fetch(form.action, {
                            method: form.type,
                            credentials: "same-origin",
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: data
                        });
                    }).then(function (response) {
                        return new Promise(function (resolve, reject) {
                            if (!response.ok) {
                                reject("error, wrong response");
                            } else {
                                let deleteRequest = db.transaction('forms', 'readwrite').objectStore('forms').delete(form.id);
                                deleteRequest.onsuccess = function (event) {
                                    success++;
                                    resolve();
                                };
                                deleteRequest.onerror = function (err) {
                                    failed++;
                                    reject(err);
                                };
                            }
                        });
                    })
                            );
                });

                Promise.all(promises).then(function (response) {
                    let color = "orange";
                    if (success > 0 && failed <= 0) {
                        offlineElementsAlert.classList.add("hidden");
                        color = "green";
                    }
                    if (success <= 0 && failed > 0) {
                        color = "red";
                    }
                    showToast(lang.locally_saved_entries_submitted + " " + lang.locally_saved_entries_submitted_success + ": " + success + ", " + lang.locally_saved_entries_submitted_error + ": " + failed, color);
                });
            }
        };
    };
});

function showToast(message, color) {
    toast.classList.add("show");
    toast.innerHTML = message;
    toast.classList.add(color);

    setTimeout(function () {
        toast.classList.remove("show");
        toast.classList.remove(color);
    }, 3000);
}

function initServiceWorker() {
    if ('serviceWorker' in navigator) {

        navigator.serviceWorker.addEventListener('message', function (event) {
            console.log('Received a message from service worker');
            //alert('received message from sw');
            //alert(event.data.type);
            if (event.data.type === 1) {
                console.log("Push Notification received");
                console.log(event.data.type);
                setNotificationCount();
            } else if (event.data.type === 2) {
                console.log("Push Notification Click");
            } else if (event.data.type === 3) {
                console.log("Loaded content from cache instead of network!");
                // after loading the response from cache the cache is loaded
                // afterwards possible variables are no longer available
                // so save the info that the page is from cache in the localStorage
                localStorage.setItem('isCached', true);
            } else if (event.data.type === 4) {
                console.log("Push Notification dismissed");
            } else {
                alert(event.data.type);
            }
        });
        navigator.serviceWorker.register('/sw.js').then(function (registration) {
            console.log('Service worker successfully registered on scope', registration.scope);
            // only on notifications pages
            /*if (pushButton === null && notificationsList === null) {
             return;
             }*/

            if (!('PushManager' in window)) {
                console.warn('Push notifications are not supported by this browser');
                notificationsDisabled('incompatible');
                return;
            }

            if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
                console.warn('Push notifications are not supported by this browser');
                notificationsDisabled('incompatible');
                return;
            }

            if (Notification.permission === 'denied') {
                console.warn('Push notifications are denied by the user');
                notificationsDisabled('incompatible');
                return;
            }

            if (pushButton !== null) {
                pushButton.addEventListener('click', function () {
                    if (isSubscribed) {
                        unsubscribeUser();
                    } else {
                        subscribeUser();
                    }
                });
            }

            syncSubscription();
        }).catch(function (error) {
            console.error('Service Worker Error', error);
            notificationsDisabled('incompatible');
        });
    } else {
        notificationsDisabled('incompatible');
    }

    // Load Entries of Client
    if (iftttUrlWrapper !== null) {
        let iftttUrl = iftttUrlWrapper.querySelector('input[name="ifttt_url"]');
        if (iftttUrl.value !== '') {
            getCategorySubscriptions(iftttUrl.value)
        }

        let removeBtn = iftttUrlWrapper.querySelector('button#ifttt_url_remove');
        removeBtn.addEventListener('click', function (event) {
            let ifttt_url = iftttUrlWrapper.querySelector('input[name="ifttt_url"]').value;

            let data = {
                endpoint: ifttt_url,
                type: "ifttt"
            };
            return _sendSubscriptionRequest(data, 'DELETE').then(function (result) {
                console.log(result);
            }).then(function (subscription) {
                window.location.reload();
            });
        });
    }

}

function syncSubscription() {
// Keep server in sync of subscription
    navigator.serviceWorker.ready.then(function (serviceWorkerRegistration) {

// close existing notifications
        serviceWorkerRegistration.getNotifications().then(notifications => {
            notifications.forEach(notification => {
                notification.close();
            });
        });
        return serviceWorkerRegistration;
    }).then(function (serviceWorkerRegistration) {
// get subscription
        return serviceWorkerRegistration.pushManager.getSubscription();
    }).then(function (subscription) {
//updateButton('disabled');

        if (!subscription) {
            notificationsDisabled('disabled');
            throw "No Push Subscription returned";
        }
        return updateSubscriptionOnServer(subscription, 'PUT').then(function (data) {
            return subscription;
        }).catch(function () {
            notificationsDisabled('disabled');
            throw "No Push Subscription on server";
        });
    }).then(function (subscription) {
        updateButton('enabled');
        //bell.classList.remove('disabled');
        return subscription;
    }).then(function (subscription) {
        return getCategorySubscriptions(subscription.endpoint).then(function () {
            return subscription;
        });
    }).catch(function (e) {
        console.error('Error when updating the subscription', e);
    }).finally(function () {
        hideLoadingShowButton();
    });
}

function subscribeUser() {
    const applicationServerKey = urlB64ToUint8Array(jsObject.applicationServerPublicKey);
    updateButton('computing');
    navigator.serviceWorker.ready.then(function (serviceWorkerRegistration) {
        console.log('Subscribing..');
        return serviceWorkerRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        });
    }).then(function (subscription) {
        console.log('User is subscribed.');
        return updateSubscriptionOnServer(subscription, 'POST').then(function () {
            return subscription;
        });
    }).then(function (subscription) {
        return getCategorySubscriptions(subscription.endpoint);
    }).then(function () {
        updateButton('enabled');
    }).catch(function (e) {
        if (Notification.permission === 'denied') {
            console.warn('Notifications are denied by the user.');
            updateButton('incompatible');
        } else {
            console.error('Impossible to subscribe to push notifications', e);
            updateButton('disabled');
        }
    });
}

function unsubscribeUser() {
    navigator.serviceWorker.ready.then(function (serviceWorkerRegistration) {
        return serviceWorkerRegistration.pushManager.getSubscription();
    }).then(function (subscription) {

        if (!subscription) {
            updateButton('disabled');
            throw "No Subscription returned";
        }

        return updateSubscriptionOnServer(subscription, 'DELETE').then(function () {
            return subscription;
        });
    }).then(function (subscription) {
        subscription.unsubscribe();
        console.log('User is unsubscribed.');
    }).then(function () {
        categoriesList.classList.add("hidden");
        updateButton('disabled');
    }).catch(function (error) {
        console.error('Error unsubscribing', error);
        updateButton('disabled');
    });
}

function updateSubscriptionOnServer(subscription, method = 'POST') {

    const key = subscription.getKey('p256dh');
    const token = subscription.getKey('auth');
    const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
    let data = {
        endpoint: subscription.endpoint,
        publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
        authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
        contentEncoding: contentEncoding
    };
    return _sendSubscriptionRequest(data, method);
}

function _sendSubscriptionRequest(data, method) {
    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;
        return fetch(jsObject.notifications_subscribe, {
            method,
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (data.status != "success") {
                throw "Error updating subscription";
            }
            return data;
        });
    }).catch(function (error) {
        console.error('Error unsubscribing', error);
    });
}

function urlB64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function updateButton(state) {
    if (pushButton === null) {
        return;
    }
    switch (state) {
        case 'enabled':
            pushButton.disabled = false;
            pushButton.textContent = lang.disable_push_notifications;
            isSubscribed = true;
            //bell.classList.remove('disabled');
            break;
        case 'disabled':
            pushButton.disabled = false;
            pushButton.textContent = lang.enable_push_notifications;
            isSubscribed = false;
            //bell.classList.add('disabled');
            break;
        case 'computing':
            pushButton.disabled = true;
            pushButton.textContent = lang.loading;
            break;
        case 'incompatible':
            pushButton.disabled = true;
            pushButton.textContent = lang.no_push_notifications_possible;
            //bell.classList.add('disabled');

            iftttUrlWrapper.classList.remove("hidden");

            let saveBtn = iftttUrlWrapper.querySelector('button#ifttt_url_save');
            saveBtn.addEventListener('click', function (event) {
                let ifttt_input = iftttUrlWrapper.querySelector('input[name="ifttt_url"]');
                let ifttt_url = ifttt_input.value;

                let data = {
                    endpoint: ifttt_url,
                    type: "ifttt"
                };
                return _sendSubscriptionRequest(data, 'POST').then(function (result) {
                    pushButton.classList.add("hidden");
                    ifttt_input.type = "hidden";
                    saveBtn.classList.add("hidden");
                    iftttUrlWrapper.querySelector('button#ifttt_url_remove').classList.remove("hidden");
                    iftttUrlWrapper.querySelector('p#ifttt_enable').classList.add("hidden");
                    iftttUrlWrapper.querySelector('p#ifttt_enabled').classList.remove("hidden");
                }).then(function () {
                    return getCategorySubscriptions(ifttt_url);
                });
            });

            break;
        default:
            console.error('Unhandled push button state', state);
            break;
    }
}


function getCategorySubscriptions(endpoint) {
    if (categoriesList !== null) {
        let data = {"endpoint": endpoint};
        loadingIconManage.classList.remove("hidden");
        return getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;
            return fetch(jsObject.notifications_clients_categories, {
                method: 'POST',
                credentials: "same-origin",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (data.status !== 'error') {

                loadingIconManage.classList.add("hidden");
                categoriesElements.forEach(function (item, idx) {
                    let val = item.value;
                    if (data.data.indexOf(val) !== -1) {
                        item.setAttribute("checked", true);
                    } else {
                        item.removeAttribute("checked");
                    }

                    item.addEventListener('click', function () {
                        if (item.checked) {
                            return setCategorySubscriptions(endpoint, 1, val).then(function (data) {
                                console.log(data);
                            });
                        } else {
                            return setCategorySubscriptions(endpoint, 0, val).then(function (data) {
                                console.log(data);
                            });
                        }
                    });
                });
                categoriesList.classList.remove("hidden");
            }
        }).catch(function (error) {
            console.log(error);
        });
    }

    return emptyPromise();
}

function setCategorySubscriptions(endpoint, type, category) {
    let data = {"endpoint": endpoint, "category": category, "type": type};
    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;
        return fetch(jsObject.notifications_clients_set_category, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        return data;
    }).catch(function (error) {
        console.log(error);
    });
}

function redirect() {
    if (document.querySelector('#notifications') !== null) {
        window.location = jsObject.notifications_clients_manage;
    }
}

function hideLoadingShowButton() {
    if (pushButton !== null) {
        pushButton.classList.remove("hidden");
    }
    if (loadingIconManage !== null) {
        loadingIconManage.classList.add("hidden");
    }
}

function emptyPromise(val = null) {
    return new Promise((resolve) => {
        resolve(val);
    });
}

function setNotificationCount(count) {
    badges.forEach(function (badge, idx) {
        let unseenCount = parseInt(count);
        if (count === undefined) {
            unseenCount = parseInt(badge.dataset.badge) + 1;
        }

        badge.dataset.badge = unseenCount;
        if (unseenCount > 0) {
            badge.classList.add("has-Notification");
        } else {
            badge.classList.remove("has-Notification");
        }
    });
}

function notificationsDisabled(state) {
    updateButton(state);
    //bell.classList.add('disabled');
    //bell.classList.remove('active');
    //redirect();
    hideLoadingShowButton();
}