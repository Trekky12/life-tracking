'use strict';
importScripts('static/assets/js/sw-toolbox.js');

const cacheName = 'pwa-life-tracking-v9';
const staticAssets = [
    '/',
    '/static/style.css',
    '/static/js/main.js',
    '/static/js/navigation.js',
    '/static/js/location.js',
    '/static/js/budget.js',
    '/static/js/boards.js',
    '/static/js/tables.js',
    '/static/js/car_service.js',
    '/static/js/geolocation.js',
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
    '/static/assets/fonts/open-sans/open-sans-v15-latin-regular.woff2',
    '/static/assets/fonts/open-sans/open-sans-v15-latin-italic.woff2',
    '/static/assets/fonts/open-sans/open-sans-v15-latin-600.woff2',
    '/static/assets/fonts/fontawesome-webfont.woff2',
    '/static/assets/favicon/android-chrome-192x192.png',
    '/static/assets/css/flatpickr.min.css',
    '/static/assets/css/font-awesome.min.css',
    '/static/assets/css/leaflet.min.css',
    '/static/assets/css/normalize.min.css',
    '/static/assets/css/nouislider.min.css',
    '/static/assets/css/open-sans.css',
    '/static/assets/css/selectr.min.css',
    '/static/assets/css/simplemde.min.css'
];

self.toolbox.options.cache = {
    name: cacheName
};

toolbox.precache(staticAssets);
toolbox.router.get('/static/*', toolbox.cacheFirst);
toolbox.router.get('/*', toolbox.networkFirst, {
    networkTimeoutSeconds: 3
});


self.addEventListener('push', function (event) {
    console.log('[Service Worker] Push Received.');

    var data = event.data.json();

    console.log(data);

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
        clients.matchAll().then(clis => {
            //const client = clis.find(c => {
            //    return c.focused === true && c.visibilityState === 'visible';
            //});
            
            //console.log(client);
        
            //if (clis.length === 0) {
            //if(client !== undefined){
                // Send a message to the page to update the UI
                //console.log('Application is already open!');
                // @see https://web-push-book.gauntface.com/chapter-05/04-common-notification-patterns/
                clis.forEach(cli => {
                    cli.postMessage({
                        type: 1,
                        time: new Date().toString()
                    });
                });
            //}
            // Show notification
            //self.registration.showNotification(title, options);
        })
    );
});

self.addEventListener('notificationclick', function (event) {
    console.log('[Service Worker] Notification click Received.');

    const data = event.notification.data;

    console.log(data);

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
                    message: 'Notification clicked',
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
    /*event.waitUntil(clients.matchAll({
        type: "window"
    }).then(function (clientList) {
        for (var i = 0; i < clientList.length; i++) {
            var client = clientList[i];
            console.log(client.url.toString().startsWith(data.url));
            if (client.url.toString().startsWith(data.url) && 'focus' in client)
                return client.focus();
        }
        if (clients.openWindow)
            return clients.openWindow(data.path);
    }));
    */
    
    // Close all notifications
    self.registration.getNotifications().then(notifications => {
        console.log(notifications);
        notifications.forEach(notification => {
            notification.close();
        });
    });

});