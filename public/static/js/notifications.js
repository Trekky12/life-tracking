"use strict";function setCategoryUser(e,t){let n={category:t,type:e};return getCSRFToken().then(function(e){return n.csrf_name=e.csrf_name,n.csrf_value=e.csrf_value,fetch(jsObject.notifications_clients_set_category_user,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(n)})}).then(function(e){return e.json()}).then(function(e){return e}).catch(function(e){console.log(e)})}function getNotifications(){if(null!==notificationsList){let e=notificationsList.childElementCount;loadingIcon.classList.remove("hidden"),loadMore.classList.add("hidden");let t={count:10,start:e};return getCSRFToken().then(function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.notifications_get,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})}).then(function(e){return e.json()}).then(function(t){if("error"!==t.status){loadingIcon.classList.add("hidden");let n=parseInt(t.count);e+10<n&&loadMore.classList.remove("hidden"),setNotificationCount(t.unseen),t.data.forEach(function(e,n){let o=document.createElement("div");o.classList="notification",o.innerHtml=e.message,o.dataset.id=e.id,e.seen&&(o.classList=o.classList+" seen");let i=document.createElement("div");i.classList="notification-header";let c=document.createElement("h2");if(e.link){let t=document.createElement("a");t.href=e.link,t.innerHTML=e.title,c.appendChild(t)}else c.innerHTML=e.title;if(i.appendChild(c),e.category&&1!==t.categories[e.category].internal){let n=document.createElement("span");n.innerHTML=lang.category+": "+t.categories[e.category].name,i.appendChild(n)}let s=document.createElement("div");s.classList="notification-content";let a=document.createElement("p");if(e.link){let t=document.createElement("a");t.href=e.link,t.innerHTML=e.message,a.appendChild(t)}else a.innerHTML=e.message;let r=document.createElement("div");r.classList="createdOn",r.innerHTML=moment(e.createdOn).format(i18n.dateformatJS.datetime),s.appendChild(a),s.appendChild(r),o.appendChild(i),o.appendChild(s),notificationsList.appendChild(o)})}}).catch(function(e){console.log(e)})}return emptyPromise()}function loadMoreFunctions(){null!==loadMore&&(loadMore.addEventListener("click",function(e){getNotifications()}),document.addEventListener("scroll",function(){let e=document.body,t=document.documentElement;(t.scrollTop>0&&t.scrollTop+t.clientHeight+100>=t.scrollHeight||e.scrollTop>0&&e.scrollTop+e.clientHeight+100>=e.scrollHeight)&&(loadMore.classList.contains("hidden")||getNotifications())}))}const categoriesUserElements=document.querySelectorAll("#notifications_categories_list_user input.set_notifications_category_user"),notificationsList=document.querySelector("#notifications"),loadingIcon=document.querySelector("#loadingIconNotifications"),loadMore=document.querySelector("#loadMoreNotifications");document.addEventListener("DOMContentLoaded",function(){categoriesUserElements.forEach(function(e,t){e.addEventListener("click",function(){let t=parseInt(e.value);return e.checked?setCategoryUser(1,t).then(function(e){console.log(e)}):setCategoryUser(0,t).then(function(e){console.log(e)})})}),loadMoreFunctions(),getNotifications()});