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


const pushButton = document.querySelector('#enable_notifications');
const categoriesList = document.querySelector('#notifications_categories_list');
const categoriesElements = document.querySelectorAll('#notifications_categories_list input.set_notifications_category');

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
            throw "No Subscription returned";
        }
        return updateSubscriptionOnServer(subscription, 'PUT');
    }).then(function (subscription) {
        getCategorySubscriptions(subscription);
    }).then(function () {
        updateButton('enabled');
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
        return updateSubscriptionOnServer(subscription, 'POST');
    }).then(function (subscription) {
        getCategorySubscriptions(subscription);
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

        return updateSubscriptionOnServer(subscription, 'DELETE');
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

    return fetch(jsObject.notifications_subscribe, {
        method,
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    }).then(function () {
        return subscription;
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


function getCategorySubscriptions(subscription) {
    if (categoriesList !== null) {
        let endpoint = subscription.endpoint;
        let data = {"endpoint": endpoint};

        getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            fetch(jsObject.notifications_clients_categories, {
                method: 'POST',
                credentials: "same-origin",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data.status !== 'error') {

                    categoriesElements.forEach(function (item, idx) {
                        let val = parseInt(item.value);

                        if (data.data.indexOf(val) !== -1) {
                            item.setAttribute("checked", true);
                        } else {
                            item.removeAttribute("checked");
                        }

                        item.addEventListener('click', function () {
                            if (item.checked) {
                                setCategorySubscriptions(endpoint, 1, val);
                            } else {
                                setCategorySubscriptions(endpoint, 0, val);
                            }
                        });
                    });
                    categoriesList.classList.remove("hidden");
                }
            }).catch(function (error) {
                alert(error);
            });
        });
    }
}

function setCategorySubscriptions(endpoint, type, category) {
    let data = {"endpoint": endpoint, "category": category, "type": type};

    getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        fetch(jsObject.notifications_clients_set_category, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            console.log(data);
        }).catch(function (error) {
            alert(error);
        });
    });
}
