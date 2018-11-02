'use strict';
importScripts('static/assets/js/sw-toolbox.js'); 

const staticAssets = [
    '/',
    '/static/js/main.min.js',
    '/static/style.css',
    '/static/js/navigation.min.js',
    '/static/js/location.min.js',
    '/static/assets/js/moment-with-locales.min.js',
    '/static/assets/js/leaflet.js',
    '/static/assets/js/jquery.min.js',
    '/static/assets/js/jquery.dataTables.min.js',
    '/static/assets/js/jquery-ui.min.js',
    '/static/assets/js/dataTables.responsive.min.js',
    '/static/assets/js/Chart.min.js',
    '/static/assets/images/ui-icons_444444_256x240.png',
    '/static/assets/images/sort_desc.png',
    '/static/assets/images/sort_both.png',
    '/static/assets/fonts/open-sans/open-sans-v15-latin-regular.woff2',
    '/static/assets/fonts/open-sans/open-sans-v15-latin-italic.woff2',
    '/static/assets/fonts/open-sans/open-sans-v15-latin-600.woff2',
    '/static/assets/fonts/fontawesome-webfont.woff2',
    '/static/assets/favicon/android-chrome-192x192.png',
    '/static/assets/css/responsive.dataTables.min.css',
    '/static/assets/css/open-sans.css',
    '/static/assets/css/normalize.min.css',
    '/static/assets/css/leaflet.css',
    '/static/assets/css/jquery.dataTables.min.css',
    '/static/assets/css/jquery-ui.css',
    '/static/assets/css/font-awesome.min.css',
    '/dataTable'
];

toolbox.precache(staticAssets); 
toolbox.router.get('/static/*', toolbox.cacheFirst); 
toolbox.router.get('/*', toolbox.networkFirst, { networkTimeoutSeconds: 3});