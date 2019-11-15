"use strict";function handleNetworkChange(e){setOffline(!navigator.onLine)}function setOffline(e){if(e){document.body.classList.add("offline"),document.getElementById("offline-alert").classList.remove("hidden"),setFormFieldsDisabled(!0);document.querySelectorAll(".alert.hide-offline").forEach(function(e,t){e.classList.add("hidden")})}else document.body.classList.remove("offline"),document.getElementById("offline-alert").classList.add("hidden"),setFormFieldsDisabled(!1),isCached&&window.location.reload(),syncSubscription()}function setFormFieldsDisabled(e){document.querySelectorAll('form input, form select, form button[type="submit"]').forEach(function(t,n){t.classList.contains("disabled")||(e?t.setAttribute("disabled",!0):t.removeAttribute("disabled"))})}function initServiceWorker(){"serviceWorker"in navigator?(navigator.serviceWorker.addEventListener("message",function(e){console.log("Received a message from service worker"),1===e.data.type?(console.log("Push Notification received"),console.log(e.data.type),setNotificationCount()):2===e.data.type?console.log("Push Notification Click"):3===e.data.type?(console.log("Loaded content from cache instead of network!"),localStorage.setItem("isCached",!0)):4===e.data.type?console.log("Push Notification dismissed"):alert(e.data.type)}),navigator.serviceWorker.register("/sw.js").then(function(e){return console.log("Service worker successfully registered on scope",e.scope),"PushManager"in window&&"showNotification"in ServiceWorkerRegistration.prototype?"denied"===Notification.permission?(console.warn("Push notifications are denied by the user"),void notificationsDisabled("incompatible")):(null!==pushButton&&pushButton.addEventListener("click",function(){isSubscribed?unsubscribeUser():subscribeUser()}),void syncSubscription()):(console.warn("Push notifications are not supported by this browser"),void notificationsDisabled("incompatible"))}).catch(function(e){console.error("Service Worker Error",e),notificationsDisabled("incompatible")})):notificationsDisabled("incompatible")}function syncSubscription(){navigator.serviceWorker.ready.then(function(e){return e.getNotifications().then(e=>{e.forEach(e=>{e.close()})}),e}).then(function(e){return e.pushManager.getSubscription()}).then(function(e){if(!e)throw notificationsDisabled("disabled"),"No Push Subscription returned";return updateSubscriptionOnServer(e,"PUT").then(function(t){return e}).catch(function(){throw notificationsDisabled("disabled"),"No Push Subscription on server"})}).then(function(e){return updateButton("enabled"),e}).then(function(e){return getCategorySubscriptions(e).then(function(){return e})}).catch(function(e){console.error("Error when updating the subscription",e)}).finally(function(){hideLoadingShowButton()})}function subscribeUser(){const e=urlB64ToUint8Array(jsObject.applicationServerPublicKey);updateButton("computing"),navigator.serviceWorker.ready.then(function(t){return console.log("Subscribing.."),t.pushManager.subscribe({userVisibleOnly:!0,applicationServerKey:e})}).then(function(e){return console.log("User is subscribed."),updateSubscriptionOnServer(e,"POST").then(function(){return e})}).then(function(e){return getCategorySubscriptions(e)}).then(function(){updateButton("enabled")}).catch(function(e){"denied"===Notification.permission?(console.warn("Notifications are denied by the user."),updateButton("incompatible")):(console.error("Impossible to subscribe to push notifications",e),updateButton("disabled"))})}function unsubscribeUser(){navigator.serviceWorker.ready.then(function(e){return e.pushManager.getSubscription()}).then(function(e){if(!e)throw updateButton("disabled"),"No Subscription returned";return updateSubscriptionOnServer(e,"DELETE").then(function(){return e})}).then(function(e){e.unsubscribe(),console.log("User is unsubscribed.")}).then(function(){categoriesList.classList.add("hidden"),updateButton("disabled")}).catch(function(e){console.error("Error unsubscribing",e),updateButton("disabled")})}function updateSubscriptionOnServer(e,t="POST"){const n=e.getKey("p256dh"),o=e.getKey("auth"),i=(PushManager.supportedContentEncodings||["aesgcm"])[0];let s={endpoint:e.endpoint,publicKey:n?btoa(String.fromCharCode.apply(null,new Uint8Array(n))):null,authToken:o?btoa(String.fromCharCode.apply(null,new Uint8Array(o))):null,contentEncoding:i};return getCSRFToken().then(function(e){return s.csrf_name=e.csrf_name,s.csrf_value=e.csrf_value,fetch(jsObject.notifications_subscribe,{method:t,credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(s)}).then(function(e){return e.json()}).then(function(e){if("success"!=e.status)throw"Error updating subscription";return e})}).catch(function(e){console.error("Error unsubscribing",e)})}function urlB64ToUint8Array(e){const t=(e+"=".repeat((4-e.length%4)%4)).replace(/\-/g,"+").replace(/_/g,"/"),n=window.atob(t),o=new Uint8Array(n.length);for(let e=0;e<n.length;++e)o[e]=n.charCodeAt(e);return o}function updateButton(e){if(null!==pushButton)switch(e){case"enabled":pushButton.disabled=!1,pushButton.textContent=lang.disable_push_notifications,isSubscribed=!0;break;case"disabled":pushButton.disabled=!1,pushButton.textContent=lang.enable_push_notifications,isSubscribed=!1;break;case"computing":pushButton.disabled=!0,pushButton.textContent=lang.loading;break;case"incompatible":pushButton.disabled=!0,pushButton.textContent=lang.no_push_notifications_possible;break;default:console.error("Unhandled push button state",e)}}function getCategorySubscriptions(e){if(null!==categoriesList){let t=e.endpoint,n={endpoint:t};return loadingIconManage.classList.remove("hidden"),getCSRFToken().then(function(e){return n.csrf_name=e.csrf_name,n.csrf_value=e.csrf_value,fetch(jsObject.notifications_clients_categories,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(n)})}).then(function(e){return e.json()}).then(function(e){"error"!==e.status&&(loadingIconManage.classList.add("hidden"),categoriesElements.forEach(function(n,o){let i=parseInt(n.value);-1!==e.data.indexOf(i)?n.setAttribute("checked",!0):n.removeAttribute("checked"),n.addEventListener("click",function(){return n.checked?setCategorySubscriptions(t,1,i).then(function(e){console.log(e)}):setCategorySubscriptions(t,0,i).then(function(e){console.log(e)})})}),categoriesList.classList.remove("hidden"))}).catch(function(e){console.log(e)})}return emptyPromise()}function setCategorySubscriptions(e,t,n){let o={endpoint:e,category:n,type:t};return getCSRFToken().then(function(e){return o.csrf_name=e.csrf_name,o.csrf_value=e.csrf_value,fetch(jsObject.notifications_clients_set_category,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(o)})}).then(function(e){return e.json()}).then(function(e){return e}).catch(function(e){console.log(e)})}function setCategoryUser(e,t){let n={category:t,type:e};return getCSRFToken().then(function(e){return n.csrf_name=e.csrf_name,n.csrf_value=e.csrf_value,fetch(jsObject.notifications_clients_set_category_user,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(n)})}).then(function(e){return e.json()}).then(function(e){return e}).catch(function(e){console.log(e)})}function getNotifications(){if(null!==notificationsList){let e=notificationsList.childElementCount;loadingIcon.classList.remove("hidden"),loadMore.classList.add("hidden");let t={count:10,start:e};return getCSRFToken().then(function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.notifications_get,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})}).then(function(e){return e.json()}).then(function(t){if("error"!==t.status){loadingIcon.classList.add("hidden");let n=parseInt(t.count);e+10<n&&loadMore.classList.remove("hidden"),setNotificationCount(t.unseen),t.data.forEach(function(e,n){let o=document.createElement("div");o.classList="notification",o.innerHtml=e.message,o.dataset.id=e.id,e.seen&&(o.classList=o.classList+" seen");let i=document.createElement("div");i.classList="notification-header";let s=document.createElement("h2");if(s.innerHTML=e.title,i.appendChild(s),e.category){let n=document.createElement("span");n.innerHTML=lang.category+": "+t.categories[e.category].name,i.appendChild(n)}let r=document.createElement("div");r.classList="notification-content";let c=document.createElement("p");c.innerHTML=e.message;let a=document.createElement("div");a.classList="createdOn",a.innerHTML=moment(e.createdOn).format(i18n.dateformatJS.datetime),r.appendChild(c),r.appendChild(a),o.appendChild(i),o.appendChild(r),notificationsList.appendChild(o)})}}).catch(function(e){console.log(e)})}return emptyPromise()}function redirect(){null!==notificationsList&&(window.location=jsObject.notifications_clients_manage)}function hideLoadingShowButton(){null!==pushButton&&pushButton.classList.remove("hidden"),null!==loadingIconManage&&loadingIconManage.classList.add("hidden")}function getUnreadNotifications(){let e={};return getCSRFToken().then(function(t){return e.csrf_name=t.csrf_name,e.csrf_value=t.csrf_value,fetch(jsObject.notifications_get_unread,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})}).then(function(e){return e.json()}).then(function(e){"error"!==e.status&&setNotificationCount(e.data)}).catch(function(e){console.log(e)})}function loadMoreFunctions(){null!==loadMore&&(loadMore.addEventListener("click",function(e){getNotifications()}),document.addEventListener("scroll",function(){let e=document.body,t=document.documentElement;(t.scrollTop>0&&t.scrollTop+t.clientHeight+100>=t.scrollHeight||e.scrollTop>0&&e.scrollTop+e.clientHeight+100>=e.scrollHeight)&&(loadMore.classList.contains("hidden")||getNotifications())}))}function emptyPromise(e=null){return new Promise(t=>{t(e)})}function setNotificationCount(e){badges.forEach(function(t,n){let o=parseInt(e);void 0===e&&(o=parseInt(t.dataset.badge)+1),t.dataset.badge=o,o>0?t.classList.add("has-Notification"):t.classList.remove("has-Notification")})}function notificationsDisabled(e){updateButton(e),hideLoadingShowButton()}const pushButton=document.querySelector("#enable_notifications"),categoriesList=document.querySelector("#notifications_categories_list"),categoriesElements=document.querySelectorAll("#notifications_categories_list input.set_notifications_category"),categoriesUserElements=document.querySelectorAll("#notifications_categories_list_user input.set_notifications_category_user"),notificationsList=document.querySelector("#notifications"),loadingIcon=document.querySelector("#loadingIconNotifications"),loadingIconManage=document.querySelector("#loadingIconManageNotifications"),loadMore=document.querySelector("#loadMore"),badges=document.querySelectorAll(".header-inner .badge"),bell=document.querySelector("#iconBell");let isSubscribed=!1;var isCached=!1;window.addEventListener("online",handleNetworkChange),window.addEventListener("offline",handleNetworkChange),document.addEventListener("DOMContentLoaded",function(){let e=Math.round(document.querySelector("meta[name='timestamp']").getAttribute("content")),t=Math.round(Date.now()/1e3);(localStorage.getItem("isCached")||e+10<=t)&&(localStorage.removeItem("isCached"),console.log("this is cached!"),setOffline(!0),isCached=!0),categoriesUserElements.forEach(function(e,t){e.addEventListener("click",function(){let t=parseInt(e.value);return e.checked?setCategoryUser(1,t).then(function(e){console.log(e)}):setCategoryUser(0,t).then(function(e){console.log(e)})})}),loadMoreFunctions(),getNotifications().then(function(){return getUnreadNotifications()}).then(function(){return initServiceWorker()})});