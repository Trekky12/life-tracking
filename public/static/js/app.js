"use strict";function initialize(){if(null!==pushButton){if(!("PushManager"in window))return console.warn("Push notifications are not supported by this browser"),void updateButton("incompatible");if(!("showNotification"in ServiceWorkerRegistration.prototype))return console.warn("Notifications are not supported by this browser"),void updateButton("incompatible");if("denied"===Notification.permission)return console.warn("Notifications are denied by the user"),pushButton.disabled=!0,void updateButton("incompatible");pushButton.addEventListener("click",function(){isSubscribed?unsubscribeUser():subscribeUser()}),navigator.serviceWorker.ready.then(function(e){return e.pushManager.getSubscription()}).then(function(e){updateButton("disabled"),e&&updateSubscriptionOnServer(e,"PUT").then(function(){updateButton("enabled")})}).catch(function(e){console.error("Error when updating the subscription",e)})}}function subscribeUser(){const e=urlB64ToUint8Array(jsObject.applicationServerPublicKey);updateButton("computing"),navigator.serviceWorker.ready.then(function(n){return console.log("Subscribing.."),n.pushManager.subscribe({userVisibleOnly:!0,applicationServerKey:e})}).then(function(e){console.log("User is subscribed."),updateSubscriptionOnServer(e,"POST").then(function(){updateButton("enabled")})}).catch(function(e){"denied"===Notification.permission?(console.warn("Notifications are denied by the user."),updateButton("incompatible")):(console.error("Impossible to subscribe to push notifications",e),updateButton("disabled"))})}function unsubscribeUser(){navigator.serviceWorker.ready.then(function(e){return e.pushManager.getSubscription()}).then(function(e){if(e)return updateSubscriptionOnServer(e,"DELETE"),e;updateButton("disabled")}).then(function(e){e.unsubscribe(),console.log("User is unsubscribed.")}).then(function(){updateButton("disabled")}).catch(function(e){console.error("Error unsubscribing",e),updateButton("disabled")})}function updateSubscriptionOnServer(e,n="POST"){const t=e.getKey("p256dh"),i=e.getKey("auth"),o=(PushManager.supportedContentEncodings||["aesgcm"])[0];return fetch(jsObject.notifications_subscribe,{method:n,credentials:"same-origin",body:JSON.stringify({endpoint:e.endpoint,publicKey:t?btoa(String.fromCharCode.apply(null,new Uint8Array(t))):null,authToken:i?btoa(String.fromCharCode.apply(null,new Uint8Array(i))):null,contentEncoding:o})}).catch(function(e){console.error(e)})}function urlB64ToUint8Array(e){const n=(e+"=".repeat((4-e.length%4)%4)).replace(/\-/g,"+").replace(/_/g,"/"),t=window.atob(n),i=new Uint8Array(t.length);for(let e=0;e<t.length;++e)i[e]=t.charCodeAt(e);return i}function updateButton(e){if(null!==pushButton)switch(e){case"enabled":pushButton.disabled=!1,pushButton.textContent=lang.disable_notifications,isSubscribed=!0;break;case"disabled":pushButton.disabled=!1,pushButton.textContent=lang.enable_notifications,isSubscribed=!1;break;case"computing":pushButton.disabled=!0,pushButton.textContent=lang.loading;break;case"incompatible":pushButton.disabled=!0,pushButton.textContent=lang.no_notifications_possible;break;default:console.error("Unhandled push button state",e)}}const pushButton=document.querySelector(".js-push-btn");let isSubscribed=!1;"serviceWorker"in navigator&&window.addEventListener("load",function(){navigator.serviceWorker.register("/sw.js?v=20181122").then(function(e){console.log("Service worker successfully registered on scope",e.scope),initialize()}).catch(function(e){console.error("Service Worker Error",e)})});