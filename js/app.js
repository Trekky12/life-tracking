/**
 * based on https://developers.google.com/web/fundamentals/codelabs/push-notifications/
 * https://github.com/Minishlink/web-push-php-example/blob/master/src/app.js
 */
'use strict';

const pushButton = document.querySelector('#enable_notifications');
const categoriesList = document.querySelector('#notifications_categories_list');
const categoriesElements = document.querySelectorAll('#notifications_categories_list input.set_notifications_category');
const categoriesUserElements = document.querySelectorAll('#notifications_categories_list_user input.set_notifications_category_user');
const notificationsList = document.querySelector('#notifications');
const loadingIcon = document.querySelector('#loadingIconNotifications');
const loadingIconManage = document.querySelector('#loadingIconManageNotifications');
const loadMore = document.querySelector('#loadMore');
//const menuProfile = document.querySelector('#menu-primary .profile');
const badges = document.querySelectorAll('.header-inner .badge');
const bell = document.querySelector('#iconBell');

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
        document.getElementById("offline-alert").classList.remove("hidden");
        setFormFieldsDisabled(true);

        let alerts = document.querySelectorAll('.alert.hide-offline');
        alerts.forEach(function (item, idx) {
            item.classList.add("hidden");
        });

        // set notifications bell disabled
        //bell.classList.add('disabled');

    } else {
        document.body.classList.remove("offline");
        document.getElementById("offline-alert").classList.add("hidden");
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
}

// set offline mode when current page is cached
// reset the info in the localStorage and save the 
// info in a local variable 
document.addEventListener("DOMContentLoaded", function () {
    let timestamp = Math.round(document.querySelector("meta[name='timestamp']").getAttribute("content"));
    let currentTime = Math.round(Date.now() / 1000);
    let offset = 10;

    if (localStorage.getItem('isCached') || (timestamp + offset <= currentTime)) {
        localStorage.removeItem('isCached');
        console.log('this is cached!');
        setOffline(true);
        isCached = true;
    }

    categoriesUserElements.forEach(function (item, idx) {
        item.addEventListener('click', function () {
            let val = parseInt(item.value);
            if (item.checked) {
                return setCategoryUser(1, val).then(function (data) {
                    console.log(data);
                });
            } else {
                return setCategoryUser(0, val).then(function (data) {
                    console.log(data);
                });
            }
        });
    });
    
    loadMoreFunctions();
    getNotifications().then(function () {
        // init service worker after notifications are loaded
        // so that there is no request with the same CSRF tokens
        return initServiceWorker();
    });
});

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
        return getCategorySubscriptions(subscription).then(function () {
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
        return getCategorySubscriptions(subscription);
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
                    let val = parseInt(item.value);

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

function setCategoryUser(type, category) {
    let data = {"category": category, "type": type};

    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.notifications_clients_set_category_user, {
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

function getNotifications() {
    if (notificationsList !== null) {

        let start = notificationsList.childElementCount;
        let count = 10;

        loadingIcon.classList.remove("hidden");
        loadMore.classList.add("hidden");

        let data = {"count": count, "start": start};

        return getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(jsObject.notifications_get, {
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

                loadingIcon.classList.add("hidden");

                let totalCount = parseInt(data.count);
                if (start + count < totalCount) {
                    loadMore.classList.remove("hidden");
                }

                setNotificationCount(data.unseen);

                data.data.forEach(function (item, idx) {

                    let div = document.createElement("div");
                    div.classList = 'notification';
                    div.innerHtml = item.message;
                    div.dataset.id = item.id;

                    if (item.seen) {
                        div.classList = div.classList + ' seen';
                    }

                    let header = document.createElement("div");
                    header.classList = 'notification-header';

                    let hTitle = document.createElement("h2");
                    hTitle.innerHTML = item.title;

                    header.appendChild(hTitle);

                    if (item.category) {
                        let spanCategory = document.createElement("span");
                        spanCategory.innerHTML = lang.category + ": " + data.categories[item.category].name;

                        header.appendChild(spanCategory);
                    }

                    let divMessage = document.createElement("div");
                    divMessage.classList = 'notification-content';

                    let pMessage = document.createElement("p");
                    pMessage.innerHTML = item.message;

                    let divDate = document.createElement("div");
                    divDate.classList = "createdOn";
                    divDate.innerHTML = moment(item.createdOn).format(i18n.dateformatJS.datetime);

                    divMessage.appendChild(pMessage);
                    divMessage.appendChild(divDate);

                    div.appendChild(header);
                    div.appendChild(divMessage);

                    notificationsList.appendChild(div);
                });



            }
        }).catch(function (error) {
            console.log(error);
        });
    }
    return emptyPromise();
}
function redirect() {
    if (notificationsList !== null) {
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

function getUnreadNotifications() {

    let data = {};

    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.notifications_get_unread, {
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
            setNotificationCount(data.data);
        }
    }).catch(function (error) {
        console.log(error);
    });

}

function loadMoreFunctions() {
    if (loadMore !== null) {
        loadMore.addEventListener('click', function (e) {
            getNotifications();
        });
        // Detect when scrolled to bottom.
        document.addEventListener('scroll', function () {
            let body = document.body;
            let html = document.documentElement;
            let offset = 100;

            if ((html.scrollTop > 0 && (html.scrollTop + html.clientHeight + offset >= html.scrollHeight)) || (body.scrollTop > 0 && (body.scrollTop + body.clientHeight + offset >= body.scrollHeight))) {
                if (!loadMore.classList.contains('hidden')) {
                    getNotifications();
                }
            }
        });
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