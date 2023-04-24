"use strict";new Sortable(document.querySelector(".grid"),{group:{name:"widget"},draggable:".card",handle:"h3",dataIdAttr:"data-widget",onUpdate:function(e){var t={widgets:this.toArray()};getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.frontpage_widget_position,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){})).catch((function(e){console.log(e)}))}});const widgetModal=document.getElementById("widget-modal"),widgetModalContent=widgetModal.querySelector(".modal-content"),modalCloseBtn=document.getElementById("modal-close-btn");modalCloseBtn.addEventListener("click",(function(e){widgetModal.style.display="none"}));const addWidgetBtn=document.getElementById("add-widget"),addWidgetBtnModal=document.getElementById("add-widget-modal");async function setModalContent(e,t=null){e.forEach((function(e){let t=document.createElement("div");t.classList.add("form-group");let n=document.createElement("label");n.innerHTML=e.label,t.appendChild(n);let d=createWidgetOption(e);d&&t.appendChild(d),widgetModalContent.appendChild(t)}));for(const t of e)if(t.dependency){let e=widgetModalContent.querySelector('[name="'+t.dependency+'"]');e&&(await populateDependentWidgetOption(t),e.addEventListener("change",(async function(e){document.getElementById("loading-overlay").classList.remove("hidden"),await populateDependentWidgetOption(t),document.getElementById("loading-overlay").classList.add("hidden")})))}if(document.getElementById("add-widget-modal").value=lang.add,null!==t){let e=document.createElement("input");e.type="hidden",e.name="id",e.value=t,widgetModalContent.appendChild(e),document.getElementById("add-widget-modal").value=lang.update}widgetModal.style.display="block"}function saveWidget(e,t={}){return document.getElementById("loading-overlay").classList.remove("hidden"),getCSRFToken().then((function(n){let d={name:e,options:t,csrf_name:n.csrf_name,csrf_value:n.csrf_value};return fetch(jsObject.frontpage_widget_option_save,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(d)})})).then((function(e){return e.json()})).then((function(e){return e})).catch((function(e){console.log(e),document.getElementById("loading-overlay").classList.add("hidden")}))}widgetModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),document.getElementById("loading-overlay").classList.remove("hidden");let t=addWidgetBtn.parentElement.querySelector("select").value,n={};new FormData(widgetModal.querySelector("form")).forEach((function(e,t){n[t]=e})),saveWidget(t,n).then((function(){allowedReload=!0,window.location.reload()}))})),addWidgetBtn.addEventListener("click",(function(e){e.preventDefault(),document.getElementById("loading-overlay").classList.remove("hidden");let t=addWidgetBtn.parentElement.querySelector("select").value;widgetModalContent.innerHTML="",fetch(jsObject.frontpage_widget_option+"?widget="+t,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){if("error"!==e.status){if(!e.entry)return saveWidget(t);setModalContent(e.entry)}})).then((function(e){void 0!==e?(allowedReload=!0,window.location.reload()):document.getElementById("loading-overlay").classList.add("hidden")})).catch((function(e){console.log(e),document.getElementById("loading-overlay").classList.add("hidden")}))}));let widgets=document.querySelectorAll("a.btn-edit");function createWidgetOption(e){if("select"==e.type){let t=document.createElement("select");return t.classList.add("form-control"),t.name=e.name,Object.keys(e.data).forEach((function(n){let d=document.createElement("option");d.value=n,d.innerHTML=e.data[n].name,n==e.value&&(d.selected=!0),e.data[n].url&&(d.dataset.url=e.data[n].url),t.appendChild(d)})),t}if("input"==e.type){let t=document.createElement("input");return t.classList.add("form-control"),t.name=e.name,t.value=e.value,t}}async function populateDependentWidgetOption(e){let t=widgetModalContent.querySelector('[name="'+e.dependency+'"]'),n=t.options[t.selectedIndex].dataset.url;if("select"==e.type){let t=widgetModalContent.querySelector('[name="'+e.name+'"]');t.innerHTML="";let d=await fetch(n,{method:"GET",credentials:"same-origin"}),o=await d.json();"success"==o.status&&Object.keys(o.data).forEach((function(n){let d=document.createElement("option");d.value=n,d.innerHTML=o.data[n].name,n==e.value&&(d.selected=!0),t.appendChild(d)}))}}widgets.forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault(),document.getElementById("loading-overlay").classList.remove("hidden"),widgetModalContent.innerHTML="";let n=e.dataset.id;fetch(jsObject.frontpage_widget_option+n,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((async function(e){"error"!==e.status&&e.entry&&await setModalContent(e.entry,n)})).then((function(e){void 0!==e?(allowedReload=!0,window.location.reload()):document.getElementById("loading-overlay").classList.add("hidden")})).catch((function(e){console.log(e)}))}))}));