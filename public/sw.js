'use strict';

/**
 * based on 
 * https://vaadin.com/tutorials/learn-pwa/turn-website-into-a-pwa
 * https://serviceworke.rs/strategy-network-or-cache_service-worker_doc.html
 * https://medium.com/web-on-the-edge/offline-posts-with-progressive-web-apps-fc2dc4ad895
 * https://serviceworke.rs/offline-fallback_service-worker_doc.html
 * https://medium.com/progressive-web-apps/pwa-create-a-new-update-available-notification-using-service-workers-18be9168d717
 */

const cacheName = 'pwa-life-tracking-v20190602';
const staticAssets = [
    '/',
    '/static/style.css',
    '/static/js/app.js',
    '/static/js/boards.js',
    '/static/js/budget.js',
    '/static/js/car_service.js',
    '/static/js/crawler.js',
    '/static/js/datefilter.js',
    '/static/js/geolocation.js',
    '/static/js/location.js',
    '/static/js/main.js',
    '/static/js/navigation.js',
    '/static/js/splitbills.js',
    '/static/js/tables.js',
    '/static/js/trips.js',
    '/static/assets/js/Chart.min.js',
    '/static/assets/js/Sortable.min.js',
    '/static/assets/js/jstable.min.js',
    '/static/assets/js/flatpickr.js',
    '/static/assets/js/leaflet.js',
    '/static/assets/js/moment-with-locales.min.js',
    '/static/assets/js/mustache.min.js',
    '/static/assets/js/nouislider.min.js',
    '/static/assets/js/randomColor.min.js',
    '/static/assets/js/selectr.min.js',
    '/static/assets/js/simplemde.min.js',
    '/static/assets/fonts/open-sans/open-sans-v15-latin-300.woff2',
    '/static/assets/fonts/open-sans/open-sans-v15-latin-600.woff2',
    '/static/assets/fonts/open-sans/open-sans-v15-latin-italic.woff2',
    '/static/assets/fonts/open-sans/open-sans-v15-latin-regular.woff2',
    '/static/assets/fonts/fontawesome-webfont.woff2',
    '/static/assets/favicon/android-chrome-192x192.png',
    '/static/assets/css/flatpickr.min.css',
    '/static/assets/css/font-awesome.min.css',
    '/static/assets/css/leaflet.min.css',
    '/static/assets/css/normalize.min.css',
    '/static/assets/css/nouislider.min.css',
    '/static/assets/css/open-sans.css',
    '/static/assets/css/selectr.min.css',
    '/static/assets/css/simplemde.min.css',
    '/static/assets/css/jstable.css',
    '/static/assets/js/i18n/de.js',
];

const NETWORK_TIMEOUT = 1000;


self.addEventListener('install', event => {
    console.log('Attempting to install service worker and cache static assets');
    
    event.waitUntil(
        caches.open(cacheName).then(cache => {
            return cache.addAll(staticAssets);
        }).then(function(){
            self.skipWaiting();
        })
    );
});

self.addEventListener('activate', event => {
    console.log('Activating new service worker...');
    const cacheWhitelist = [cacheName];

    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(function(){
            self.clients.claim()
        })
    );
});

self.addEventListener('fetch', event => {
    const req = event.request;

    if (event.request.method !== 'GET') {
        //console.log('WORKER: fetch event ignored.', event.request.method, event.request.url);
        return;
    }

    if (/.*(\/static\/).*/.test(req.url) || /.*(\/uploads\/).*/.test(req.url)) {
        return event.respondWith(cacheFirst(req));
    } else {
        //console.log('network first', req.url);
        //self.clients.matchAll().then(function (clientList) {
        //    console.log("clients", clientList);
        //});
        return event.respondWith(networkFirst(req));
    }

});

function networkFirst(req) {
    return _fromNetwork(req.clone(), NETWORK_TIMEOUT).catch(function () {

        //console.log('network error!', req);
        /**
         * No result from network so try cache instead
         */
        return _fromCache(req.clone()).then(function (result) {
            //console.log('try from cache', req);
            return _notifyCache(req).then(function () {
                return result;
            });
        }).catch(function (result) {
            //console.log('no network and nothing in cache found', req);
            return _notifyCache(req).then(function () {
                if (req.headers.get('accept').includes('text/html')) {
                    return _fromCache('/');
                }
                return Promise.reject('no-match');
            });
        });
    });
}


function cacheFirst(req) {
    return _fromCache(req).catch(function () {
        //console.log('error cachefirst');
        //console.log(req);
        return networkFirst(req);
    });
}

/**
 * Notify client that this request was from cache instead of network
 * @param {type} req
 * @returns {Promise}
 */
function _notifyCache(req) {
    if (req.headers.get('accept').includes('text/html')) {
        //console.log('notify cache');
        return _sendMessageToClients(3);
    }
    // resolve empty promise
    return Promise.resolve('no-notification-needed');
}


/**
 * @see https://serviceworke.rs/strategy-network-or-cache_service-worker_doc.html
 */
function _fromCache(request) {
    return caches.open(cacheName).then(function (cache) {
        return cache.match(request).then(function (cachedResponse) {
            return cachedResponse || Promise.reject('no-match');
        });
    });
}

/**
 * this function tries to get the data from the network
 * it returns after a response or after a timeout
 * 
 * the result of the fetch is still processed, because a promise can't be canceled
 * so the response is in cached if the fetch is successfully after the timeout
 * 
 * it is possible to implement this with a timeout wrapper Promise 
 * or with a promise race.
 * 
 * @see https://stackoverflow.com/questions/46946380/fetch-api-request-timeout
 * 
 * @param {type} request
 * @param {type} timeout
 * @returns {Promise}
 */
function _fromNetwork(request, timeout) {
    /**
     * Promice.race
     */
    var timeoutId;
    var promises = [];
    var cacheWhenTimedOutPromise = new Promise(function (resolve, reject) {
        timeoutId = setTimeout(function () {
            //console.log('timeout');
            reject('timeout');
        }, timeout);
    });
    promises.push(cacheWhenTimedOutPromise);

    var networkPromise = _fetchAndCache(request.clone()).then(function (response) {
        //console.log('fetch success');
        clearTimeout(timeoutId);
        return response;
    });

    promises.push(networkPromise);

    return Promise.race(promises);

    /**
     * Promice Timeout Wrapper
     */
//    return new Promise(function (resolve, reject) {
//        var timeoutId = setTimeout(function (t) {
//            reject();
//        }, timeout);
//        _fetchAndCache(request.clone()).then(function (response) {
//            clearTimeout(timeoutId);
//            resolve(response);
//        }, reject);
//    });
}


/**
 * fetch the request and save it into cache
 */
function _fetchAndCache(request) {
    return fetch(request.clone()).then(function (response) {
        /**
         * save new fetched result in cache (asynchron)
         */
        if (request.url.includes(self.location.hostname)) {
            caches.open(cacheName).then(function (cache) {
                cache.put(request, response);
            });
        }
        return response.clone();
    });
}

/**
 * Send a message to all clients
 * @param {type} message
 * @returns {Promise}
 */
function _sendMessageToClients(message) {
    return self.clients.matchAll().then(function (clientList) {
        clientList.forEach(function (client) {
            client.postMessage({
                type: message,
                time: new Date().toString()
            });
        });
    });
}



self.addEventListener('push', function (event) {
    console.log('[Service Worker] Push Received.');

    var data = event.data.json();

    //console.log(data);

    const title = data.title;
    const options = {
        body: data.body,
        icon: '/static/assets/favicon/android-chrome-192x192.png',
        data: data.data,
        vibrate: [100, 200, 100, 200, 100, 200, 100, 200]
    };

    // if we don't send the notification (e.g. not to focused clients) then there is a notification on desktop chrome
    // when the tab is in the background that the background tab is updated
    const notificationPromise = self.registration.showNotification(title, options);
    event.waitUntil(notificationPromise);

    //@see https://developers.google.com/web/ilt/pwa/lab-integrating-web-push#52_when_to_show_notifications
    event.waitUntil(
//            clients.matchAll().then(clis => {
//
//            // only visible clients
////            const client = clis.find(c => {
////                return c.focused === true && c.visibilityState === 'visible';
////            });
//
//            //console.log(client);
//
////            if (clis.length === 0) {
////                if (client !== undefined) {
//                    // Send a message to the page to update the UI
//                    //console.log('Application is already open!');
//                    // @see https://web-push-book.gauntface.com/chapter-05/04-common-notification-patterns/
//                    clis.forEach(cli => {
//                        cli.postMessage({
//                            type: 1,
//                            time: new Date().toString()
//                        });
//                    });
////                }
////            }
//            // Show notification
////            self.registration.showNotification(title, options);
//        })

        _sendMessageToClients(1)
    );
});

self.addEventListener('notificationclick', function (event) {
    console.log('[Service Worker] Notification click Received.');

    const data = event.notification.data;

    //console.log(data);

    event.notification.close();

    // Focus open window or open new
    // @see https://developers.google.com/web/ilt/pwa/lab-integrating-web-push#2_using_the_notifications_api
    // @see https://github.com/google-developer-training/pwa-training-labs/blob/master/push-notification-lab/
    event.waitUntil(
        clients.matchAll().then(clis => {
            const client = clis.find(c => {
                //return c.visibilityState === 'visible';
                return c.focused === true && c.visibilityState === 'visible';
            });

            clis.forEach(cli => {
                cli.postMessage({
                    type: 2,
                    time: new Date().toString()
                });
            });

            if (client !== undefined) {
                client.navigate(data.path);
                client.focus();
            } else {
                // there are no visible windows. Open one.
                clients.openWindow(data.path);
            }
        })
    );

    //@see https://developers.google.com/web/fundamentals/push-notifications/common-notification-patterns#focus_an_existing_window
//    event.waitUntil(clients.matchAll({
//        type: "window"
//    }).then(function (clientList) {
//        for (var i = 0; i < clientList.length; i++) {
//            var client = clientList[i];
//            console.log(client.url.toString().startsWith(data.url));
//            if (client.url.toString().startsWith(data.url) && 'focus' in client)
//                return client.focus();
//        }
//        if (clients.openWindow)
//            return clients.openWindow(data.path);
//    }));


    // Close all notifications
    self.registration.getNotifications().then(notifications => {
        console.log(notifications);
        notifications.forEach(notification => {
            notification.close();
        });
    });

});