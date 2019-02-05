/**
 * based on https://developers.google.com/web/fundamentals/codelabs/push-notifications/
 * https://github.com/Minishlink/web-push-php-example/blob/master/src/app.js
 */
'use strict';

window.addEventListener("online", handleNetworkChange);
window.addEventListener("offline", handleNetworkChange);
handleNetworkChange();

function handleNetworkChange(event) {
    if (navigator.onLine) {
        document.body.classList.remove("offline");
        document.getElementById("offline-alert").classList.add("hidden");
        setFormFields(false);
    } else {
        document.body.classList.add("offline");
        document.getElementById("offline-alert").classList.remove("hidden");
        setFormFields(true);
    }
}

function setFormFields(value) {
    let fields = document.querySelectorAll('form input, form select, form button[type="submit"]');
    fields.forEach(function (item, idx) {
        if (value) {
            item.setAttribute("disabled", true);
        } else {
            item.removeAttribute("disabled");
        }
    });
}


const pushButton = document.querySelector('.js-push-btn');

let isSubscribed = false;

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js?v=20190131').then(function (registration) {
            console.log('Service worker successfully registered on scope', registration.scope);

            initialize();

        }).catch(function (error) {
            console.error('Service Worker Error', error);
        });
    });
}

function initialize() {

    // this is not the page for activating push
    if (pushButton === null) {
        return;
    }

    if (!('PushManager' in window)) {
        console.warn('Push notifications are not supported by this browser');
        updateButton('incompatible');
        return;
    }

    if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
        console.warn('Notifications are not supported by this browser');
        updateButton('incompatible');
        return;
    }

    if (Notification.permission === 'denied') {
        console.warn('Notifications are denied by the user');
        pushButton.disabled = true;
        updateButton('incompatible');
        return;
    }

    pushButton.addEventListener('click', function () {
        if (isSubscribed) {
            unsubscribeUser();
        } else {
            subscribeUser();
        }
    });


    // Keep server in sync of subscription
    navigator.serviceWorker.ready.then(function (serviceWorkerRegistration) {
        return serviceWorkerRegistration.pushManager.getSubscription();
    }).then(function (subscription) {
        updateButton('disabled');
        if (!subscription) {
            return;
        }
        updateSubscriptionOnServer(subscription, 'PUT').then(function(){
            updateButton('enabled');
        });
    }).catch(function (e) {
        console.error('Error when updating the subscription', e);
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
        updateSubscriptionOnServer(subscription, 'POST').then(function () {
            updateButton('enabled');
        });
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
            return;
        }

        updateSubscriptionOnServer(subscription, 'DELETE');
        return subscription;
    }).then(function (subscription) {
        subscription.unsubscribe();
        console.log('User is unsubscribed.');
    }).then(function () {
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

    return fetch(jsObject.notifications_subscribe, {
        method,
        credentials: "same-origin",
        body: JSON.stringify({
            endpoint: subscription.endpoint,
            publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
            authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
            contentEncoding: contentEncoding
        })
    }).catch(function (error) {
        console.error(error);
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
            pushButton.textContent = lang.disable_notifications;
            isSubscribed = true;
            break;
        case 'disabled':
            pushButton.disabled = false;
            pushButton.textContent = lang.enable_notifications;
            isSubscribed = false;
            break;
        case 'computing':
            pushButton.disabled = true;
            pushButton.textContent = lang.loading;
            break;
        case 'incompatible':
            pushButton.disabled = true;
            pushButton.textContent = lang.no_notifications_possible;
            break;
        default:
            console.error('Unhandled push button state', state);
            break;
    }
}

