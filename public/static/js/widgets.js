"use strict";const requestWidgets=document.querySelectorAll(".request_widget");function load(e){let t=e.dataset.id,n=e.dataset.widget,a=e.dataset.options;return fetch(jsObject.frontpage_widget_request+t+"?widget="+n+"&options="+a,{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/json","sw-cache":"none"}}).then((function(e){return e.json()})).then((function(t){e.innerHTML=t.data})).catch((function(e){console.log(e)}))}requestWidgets.forEach((function(e){load(e);var t=e.dataset.reload;t>0&&setInterval((function(){load(e)}),1e3*t)})),document.addEventListener("click",(async function(e){let t=e.target.closest(".btn-archive-card");t&&setTimeout((function(){let e=t.dataset.url,n=0===parseInt(t.dataset.archive)?1:0;if(0===n){if(!confirm(lang.boards_undo_archive))return t.checked=!0,!1}else if(!confirm(lang.boards_really_archive))return t.checked=!1,!1;var a={archive:n};getCSRFToken().then((function(t){return a.csrf_name=t.csrf_name,a.csrf_value=t.csrf_value,fetch(e,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(a)})})).then((function(e){return e.json()})).then((function(e){})).catch((function(e){console.log(e)}))}),20)}));