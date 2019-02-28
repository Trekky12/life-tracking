"use strict";function handleNetworkChange(e){navigator.onLine?(document.body.classList.remove("offline"),document.getElementById("offline-alert").classList.add("hidden"),setFormFields(!1)):(document.body.classList.add("offline"),document.getElementById("offline-alert").classList.remove("hidden"),setFormFields(!0))}function setFormFields(e){document.querySelectorAll('form input, form select, form button[type="submit"]').forEach(function(t,n){e?t.setAttribute("disabled",!0):t.removeAttribute("disabled")})}function initialize(){return"PushManager"in window?"showNotification"in ServiceWorkerRegistration.prototype?"denied"===Notification.permission?(console.warn("Notifications are denied by the user"),void updateButton("incompatible")):(null!==pushButton&&pushButton.addEventListener("click",function(){isSubscribed?unsubscribeUser():subscribeUser()}),navigator.serviceWorker.ready.then(function(e){return e.pushManager.getSubscription()}).then(function(e){if(updateButton("disabled"),!e)throw redirect(),"No Subscription returned";return updateSubscriptionOnServer(e,"PUT")}).then(function(e){return getUnreadNotifications(e).then(function(){return e})}).then(function(e){return getNotifications(e).then(function(){return e})}).then(function(e){return getCategorySubscriptions(e).then(function(){return e})}).then(function(e){loadMoreFunctions(e)}).then(function(){updateButton("enabled")}).catch(function(e){console.error("Error when updating the subscription",e)}),void navigator.serviceWorker.addEventListener("message",function(e){if(console.log("Received a message from service worker"),1==e.data.type){console.log(e.data.type);setNotificationCount(parseInt(menuProfile.dataset.badge)+1)}})):(console.warn("Notifications are not supported by this browser"),void updateButton("incompatible")):(console.warn("Push notifications are not supported by this browser"),void updateButton("incompatible"))}function subscribeUser(){const e=urlB64ToUint8Array(jsObject.applicationServerPublicKey);updateButton("computing"),navigator.serviceWorker.ready.then(function(t){return console.log("Subscribing.."),t.pushManager.subscribe({userVisibleOnly:!0,applicationServerKey:e})}).then(function(e){return console.log("User is subscribed."),updateSubscriptionOnServer(e,"POST")}).then(function(e){return getCategorySubscriptions(e)}).then(function(){updateButton("enabled")}).catch(function(e){"denied"===Notification.permission?(console.warn("Notifications are denied by the user."),updateButton("incompatible")):(console.error("Impossible to subscribe to push notifications",e),updateButton("disabled"))})}function unsubscribeUser(){navigator.serviceWorker.ready.then(function(e){return e.pushManager.getSubscription()}).then(function(e){if(!e)throw updateButton("disabled"),"No Subscription returned";return updateSubscriptionOnServer(e,"DELETE")}).then(function(e){e.unsubscribe(),console.log("User is unsubscribed.")}).then(function(){categoriesList.classList.add("hidden"),updateButton("disabled")}).catch(function(e){console.error("Error unsubscribing",e),updateButton("disabled")})}function updateSubscriptionOnServer(e,t="POST"){const n=e.getKey("p256dh"),o=e.getKey("auth"),i=(PushManager.supportedContentEncodings||["aesgcm"])[0];let r={endpoint:e.endpoint,publicKey:n?btoa(String.fromCharCode.apply(null,new Uint8Array(n))):null,authToken:o?btoa(String.fromCharCode.apply(null,new Uint8Array(o))):null,contentEncoding:i};return fetch(jsObject.notifications_subscribe,{method:t,credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(r)}).then(function(){return e}).catch(function(e){console.error(e)})}function urlB64ToUint8Array(e){const t=(e+"=".repeat((4-e.length%4)%4)).replace(/\-/g,"+").replace(/_/g,"/"),n=window.atob(t),o=new Uint8Array(n.length);for(let e=0;e<n.length;++e)o[e]=n.charCodeAt(e);return o}function updateButton(e){if(null!==pushButton)switch(e){case"enabled":pushButton.disabled=!1,pushButton.textContent=lang.disable_notifications,isSubscribed=!0;break;case"disabled":pushButton.disabled=!1,pushButton.textContent=lang.enable_notifications,isSubscribed=!1;break;case"computing":pushButton.disabled=!0,pushButton.textContent=lang.loading;break;case"incompatible":pushButton.disabled=!0,pushButton.textContent=lang.no_notifications_possible;break;default:console.error("Unhandled push button state",e)}}function getCategorySubscriptions(e){if(null!==categoriesList){let t=e.endpoint,n={endpoint:t};return loadingIcon.classList.remove("hidden"),getCSRFToken().then(function(e){return n.csrf_name=e.csrf_name,n.csrf_value=e.csrf_value,fetch(jsObject.notifications_clients_categories,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(n)})}).then(function(e){return e.json()}).then(function(e){"error"!==e.status&&(loadingIcon.classList.add("hidden"),categoriesElements.forEach(function(n,o){let i=parseInt(n.value);-1!==e.data.indexOf(i)?n.setAttribute("checked",!0):n.removeAttribute("checked"),n.addEventListener("click",function(){return n.checked?setCategorySubscriptions(t,1,i).then(function(e){console.log(e)}):setCategorySubscriptions(t,0,i).then(function(e){console.log(e)})})}),categoriesList.classList.remove("hidden"))}).catch(function(e){console.log(e)})}return emptyPromise()}function setCategorySubscriptions(e,t,n){let o={endpoint:e,category:n,type:t};return getCSRFToken().then(function(e){return o.csrf_name=e.csrf_name,o.csrf_value=e.csrf_value,fetch(jsObject.notifications_clients_set_category,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(o)})}).then(function(e){return e.json()}).then(function(e){return e}).catch(function(e){console.log(e)})}function getNotifications(e){if(null!==notificationsList){let t=notificationsList.childElementCount,n=e.endpoint;loadingIcon.classList.remove("hidden"),loadMore.classList.add("hidden");let o={endpoint:n,count:10,start:t};return getCSRFToken().then(function(e){return o.csrf_name=e.csrf_name,o.csrf_value=e.csrf_value,fetch(jsObject.notifications_get,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(o)})}).then(function(e){return e.json()}).then(function(e){if("error"!==e.status){loadingIcon.classList.add("hidden");let n=parseInt(e.count);t+10<n&&loadMore.classList.remove("hidden"),setNotificationCount(e.unseen),e.data.forEach(function(t,n){let o=document.createElement("div");o.classList="notification",o.innerHtml=t.message,o.dataset.id=t.id,t.seen&&(o.classList=o.classList+" seen");let i=document.createElement("div");i.classList="notification-header";let r=document.createElement("h2");r.innerHTML=t.title;let s=document.createElement("span");s.innerHTML=lang.category+": "+e.categories[t.category].name,i.appendChild(r),i.appendChild(s);let c=document.createElement("div");c.classList="notification-content";let a=document.createElement("p");a.innerHTML=t.message;let u=document.createElement("div");u.classList="createdOn",u.innerHTML=moment(t.createdOn).format(i18n.dateformatJSFull),c.appendChild(a),c.appendChild(u),o.appendChild(i),o.appendChild(c),notificationsList.appendChild(o)})}}).catch(function(e){console.log(e)})}return emptyPromise()}function redirect(){null!==notificationsList&&(window.location=jsObject.notifications_clients_manage)}function getUnreadNotifications(e){let t={endpoint:e.endpoint};return getCSRFToken().then(function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.notifications_get_unread,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})}).then(function(e){return e.json()}).then(function(e){if("error"!==e.status){let t=parseInt(e.data);t>0&&(menuProfile.classList.add("has-Notification"),menuProfile.dataset.badge=t)}}).catch(function(e){console.log(e)})}function loadMoreFunctions(e){null!==loadMore&&(loadMore.addEventListener("click",function(t){getNotifications(e)}),document.addEventListener("scroll",function(){let t=document.body,n=document.documentElement;(n.scrollTop>0&&n.scrollTop+n.clientHeight>=n.scrollHeight||t.scrollTop>0&&t.scrollTop+t.clientHeight>=t.scrollHeight)&&(loadMore.classList.contains("hidden")||getNotifications(e))}))}function emptyPromise(e=null){return new Promise(t=>{t(e)})}function setNotificationCount(e){let t=parseInt(e);menuProfile.dataset.badge=t,t>0?menuProfile.classList.add("has-Notification"):menuProfile.classList.remove("has-Notification")}window.addEventListener("online",handleNetworkChange),window.addEventListener("offline",handleNetworkChange),handleNetworkChange();const pushButton=document.querySelector("#enable_notifications"),categoriesList=document.querySelector("#notifications_categories_list"),categoriesElements=document.querySelectorAll("#notifications_categories_list input.set_notifications_category"),notificationsList=document.querySelector("#notifications"),loadingIcon=document.querySelector("#loadingIcon"),loadMore=document.querySelector("#loadMore"),menuProfile=document.querySelector("#menu-primary .profile");let isSubscribed=!1;"serviceWorker"in navigator&&window.addEventListener("load",function(){navigator.serviceWorker.register("/sw.js?v=20190227").then(function(e){console.log("Service worker successfully registered on scope",e.scope),initialize()}).catch(function(e){console.error("Service Worker Error",e)})});