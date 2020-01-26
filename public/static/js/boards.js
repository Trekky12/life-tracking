"use strict";const selector=new Selectr("select#card-label-list",{searchable:!1,customClass:"selectr-boards",renderOption:function(e){return["<div class='select-option-label' style='background-color:",e.dataset.backgroundColor,"; color:",e.dataset.textColor,"'><span>",e.textContent,"</span></div>"].join("")},renderSelection:function(e){return['<div class="select-label" style="background-color:',e.dataset.backgroundColor,"; color:",e.dataset.textColor,'"><span>',e.textContent.trim(),"</span></div>"].join("")},placeholder:lang.labels});var simplemde=null;document.addEventListener("keydown",(function(e){27===e.keyCode&&(isVisible(stackModal)&&setDialogOpen(stackModal,!1),isVisible(labelModal)&&setDialogOpen(labelModal,!1),isVisible(cardModal)&&setDialogOpen(cardModal,!1))})),document.addEventListener("change",(function(e){let t=e.target.closest('input[type="color"]');t&&(t.parentElement.style.backgroundColor=t.value)}));const stackModal=document.getElementById("stack-modal");let stackHeaders=document.querySelectorAll(".stack-header");stackHeaders.forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault(),document.getElementById("loading-overlay").classList.remove("hidden");var a=e.dataset.stack;fetch(jsObject.stack_get_url+a,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){if("error"!==e.status){stackModal.querySelector('input[name="id"]').value=e.entry.id,stackModal.querySelector('input[name="name"]').value=e.entry.name,stackModal.querySelector('input[name="position"]').value=e.entry.position;var t="<a href='#' data-url='"+jsObject.stack_archive+e.entry.id+"' data-archive='"+e.entry.archive+"' class='btn-archive'><i class='fas fa-archive' aria-hidden='true'></i></a> \n                                    <a href='#' data-url='"+jsObject.stack_delete+e.entry.id+"' class='btn-delete' data-type='stack'><i class='fas fa-trash' aria-hidden='true'></i></a>";stackModal.querySelector(".edit-bar").innerHTML=t,document.getElementById("stack-add-btn").value=lang.update,setDialogOpen(stackModal,!0)}})).then((function(){document.getElementById("loading-overlay").classList.add("hidden")})).catch((function(e){console.log(e)}))}))})),document.getElementById("create-stack").addEventListener("click",(function(e){e.preventDefault(),setDialogOpen(stackModal,!0)})),stackModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),save(stackModal,jsObject.stack_save)})),document.getElementById("stack-close-btn").addEventListener("click",(function(e){setDialogOpen(stackModal,!1)}));const labelModal=document.getElementById("label-modal");document.getElementById("create-label").addEventListener("click",(function(e){e.preventDefault(),setDialogOpen(labelModal,!0)})),labelModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),save(labelModal,jsObject.label_save)})),document.getElementById("label-close-btn").addEventListener("click",(function(e){setDialogOpen(labelModal,!1)}));let labels=document.querySelectorAll("a.edit-label");labels.forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault(),document.getElementById("loading-overlay").classList.remove("hidden");var a=e.dataset.label;fetch(jsObject.label_get_url+a,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){if("error"!==e.status){labelModal.querySelector('input[name="id"]').value=e.entry.id,labelModal.querySelector('input[name="name"]').value=e.entry.name,labelModal.querySelector('input[name="background_color"]').value=e.entry.background_color,labelModal.querySelector('input[name="background_color"]').parentElement.style.backgroundColor=e.entry.background_color,labelModal.querySelector('input[name="text_color"]').value=e.entry.text_color,labelModal.querySelector('input[name="text_color"]').parentElement.style.backgroundColor=e.entry.text_color;var t="<a href='#' data-url='"+jsObject.label_delete+e.entry.id+"' class='btn-delete' data-type='label'><i class='fas fa-trash' aria-hidden='true'></i></a>";labelModal.querySelector(".edit-bar").innerHTML=t,document.getElementById("label-add-btn").value=lang.update,setDialogOpen(labelModal,!0)}})).then((function(){document.getElementById("loading-overlay").classList.add("hidden")})).catch((function(e){console.log(e)}))}))}));const cardModal=document.getElementById("card-modal");let create_card_link=document.querySelectorAll(".create-card");create_card_link.forEach((function(e,t){e.addEventListener("click",(function(e){e.preventDefault();var t=this.dataset.stack;cardModal.querySelector('input[name="stack"]').value=t,setDialogOpen(cardModal,!0)}))})),cardModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),save(cardModal,jsObject.card_save)})),cardModal.addEventListener("keypress",(function(e){13===e.keyCode&&(e.preventDefault(),save(cardModal,jsObject.card_save))})),document.getElementById("card-close-btn").addEventListener("click",(function(e){setDialogOpen(cardModal,!1)}));let cards=document.querySelectorAll(".board-card");cards.forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault(),loadAndOpenCard(e.dataset.card)}))}));let avatars=document.querySelectorAll("#card-modal .avatar");function loadAndOpenCard(e){document.getElementById("loading-overlay").classList.remove("hidden"),fetch(jsObject.card_get_url+e,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){if("error"!==e.status){if(cardModal.querySelector('input[name="id"]').value=e.entry.id,cardModal.querySelector('input[name="title"]').value=e.entry.title,cardModal.querySelector('input[name="position"]').value=e.entry.position,cardModal.querySelector('input[name="stack"]').value=e.entry.stack,cardModal.querySelector('input[name="archive"]').value=e.entry.archive,e.entry.date){var t=cardModal.querySelector('input[name="date"]');t.value=e.entry.date,t._flatpickr.setDate(e.entry.date),t.parentElement.parentElement.querySelectorAll(".show-sibling").forEach((function(e,t){e.classList.add("hidden")})),t.parentElement.classList.remove("hidden")}if(e.entry.time){var a=cardModal.querySelector('input[name="time"]');a.value=e.entry.time,a.parentElement.parentElement.querySelectorAll(".show-sibling").forEach((function(e,t){e.classList.add("hidden")})),a.parentElement.classList.remove("hidden")}var n=cardModal.querySelector('textarea[name="description"]');if(e.entry.description){n.value=e.entry.description,n.parentElement.parentElement.querySelectorAll(".show-sibling").forEach((function(e,t){e.classList.add("hidden")})),n.parentElement.classList.remove("hidden")}cardModal.querySelector("#createdBy").innerHTML=e.entry.createdBy,cardModal.querySelector("#createdOn").innerHTML=moment(e.entry.createdOn).format(i18n.dateformatJS.datetime),cardModal.querySelector("#changedBy").innerHTML=e.entry.changedBy,cardModal.querySelector("#changedOn").innerHTML=moment(e.entry.changedOn).format(i18n.dateformatJS.datetime),cardModal.querySelector(".form-group.card-dates").classList.remove("hidden");let r=cardModal.querySelector('select[name="users[]"]');cardModal.querySelectorAll(".avatar-small, .avatar-small").forEach((function(t,a){let n=parseInt(t.dataset.user);var l=r.querySelector("option[value='"+n+"']");-1!==e.entry.users.indexOf(n)?(t.classList.add("selected"),l.selected=!0):(t.classList.remove("selected"),l.selected=!1)})),selector.reset(),selector.setValue(e.entry.labels.map(String));var l="<a href='#' data-url='"+jsObject.card_archive+e.entry.id+"' data-archive='"+e.entry.archive+"' class='btn-archive'><i class='fas fa-archive' aria-hidden='true'></i></a> \n                                    <a href='#' data-url='"+jsObject.card_delete+e.entry.id+"' class='btn-delete' data-type='card'><i class='fas fa-trash' aria-hidden='true'></i></a>";cardModal.querySelector(".edit-bar").innerHTML=l,document.getElementById("card-add-btn").value=lang.update,setDialogOpen(cardModal,!0)}else cleanURL()})).then((function(){document.getElementById("loading-overlay").classList.add("hidden")})).catch((function(e){console.log(e)}))}avatars.forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault();var a=e.dataset.user,n=document.querySelector("#card-modal select#users option[value='"+a+"']");n.selected?(n.selected=!1,e.classList.remove("selected")):(n.selected=!0,e.classList.add("selected"))}))}));let siblings=cardModal.querySelectorAll(".show-sibling");siblings.forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault(),e.classList.add("hidden"),e.parentNode.querySelectorAll(".hidden-field").forEach((function(e){e.classList.remove("hidden");let t=e.querySelector("input");t&&t.focus();let a=e.querySelector("textarea");a&&a.focus();let n=cardModal.querySelector('input[name="date"]');t===n&&n._flatpickr.open()})),simplemde&&(simplemde.codemirror.refresh(),simplemde.codemirror.focus())}))})),document.getElementById("addComment").addEventListener("click",(function(e){e.preventDefault();var t={card:cardModal.querySelector('input[name="id"]').value,comment:cardModal.querySelector('textarea[name="comment"]').value};getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.comment_save,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){allowedReload=!0,window.location.reload()})).catch((function(e){console.log(e)}))}));var res=window.location.href.match(/(?:\?card=([0-9]*))/);if(null!==res&&res.length>1){var card=res[1];loadAndOpenCard(card)}function setDialogOpen(e,t){if(t){if(freeze(),e.style.display="block",isMobile()||e.querySelector('input[type="text"]').focus(),e===cardModal){var a=cardModal.querySelector('textarea[name="description"]');simplemde=new SimpleMDE({element:a,autosave:{enabled:!1},forceSync:!0,spellChecker:!1,promptURLs:!0,status:!1,styleSelectedText:!isMobile(),minHeight:"50px"}),""!==a.value&&simplemde.togglePreview()}}else{let t=lang.really_close;if(e===stackModal&&(t=lang.really_close_stack),e===cardModal&&(t=lang.really_close_card),e===labelModal&&(t=lang.really_close_label),!confirm(t))return!1;if(unfreeze(),e.style.display="none",e===labelModal){e.querySelectorAll(".color-wrapper").forEach((function(e,t){e.style.backgroundColor="black"}))}if(e===cardModal){simplemde&&(simplemde.toTextArea(),simplemde=null),cardModal.querySelectorAll(".show-sibling").forEach((function(e,t){e.classList.remove("hidden")})),cardModal.querySelectorAll(".hidden-field").forEach((function(e,t){e.classList.add("hidden")})),cardModal.querySelector('textarea[name="description"]').style.height="auto",cardModal.querySelector("#createdBy").innerHTML="",cardModal.querySelector("#createdOn").innerHTML="",cardModal.querySelector("#changedBy").innerHTML="",cardModal.querySelector("#changedOn").innerHTML="",cardModal.querySelectorAll(".form-group.card-dates").forEach((function(e,t){e.classList.add("hidden")})),cardModal.querySelector('select[name="labels[]"]').value="",cardModal.querySelector('select[name="users[]"]').value="",cardModal.querySelectorAll(".avatar-small, .avatar-small").forEach((function(e,t){e.classList.remove("selected")})),cleanURL()}document.getElementById("stack-add-btn").value=lang.add,document.getElementById("card-add-btn").value=lang.add,document.getElementById("label-add-btn").value=lang.add,e.querySelector("form").reset(),e.querySelector('input[type="hidden"].reset-field').value="",e.querySelector(".edit-bar").innerHTML=""}}function freeze(){var e=window.scrollY;document.body.style.overflow="hidden",window.onscroll=function(){window.scroll(0,e)}}function unfreeze(){document.body.style.overflow="",window.onscroll=null}function isMobile(){return isVisible(document.getElementById("mobile-header-icons"))}function isVisible(e){return"none"!==getDisplay(e)}function getDisplay(e){return e.currentStyle?e.currentStyle.display:getComputedStyle(e,null).display}function cleanURL(){var e=window.location.toString();if(e.indexOf("?")>0){var t=e.substring(0,e.indexOf("?"));window.history.replaceState({},document.title,t)}}function addCardtoURL(e){var t=window.location.toString(),a=t.indexOf("?")>0?"&":"?";t.indexOf("card")<=0&&window.history.replaceState({},document.title,t+a+"card="+e)}function formToJSON(e){let t={};return new FormData(e).forEach((function(e,a){if(a.endsWith("[]")){let n=a.slice(0,-2);Array.isArray(t[n])||(t[n]=[]),t[n].push(e)}else t[a]=e})),t}function save(e,t){document.getElementById("loading-overlay").classList.remove("hidden"),cleanURL();var a=e.querySelector('input[name="id"]').value,n=formToJSON(e.querySelector("form"));getCSRFToken().then((function(e){return n.csrf_name=e.csrf_name,n.csrf_value=e.csrf_value,fetch(t+a,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(n)})})).then((function(e){return e.json()})).then((function(e){allowedReload=!0,window.location.reload()})).catch((function(e){console.log(e)}))}document.addEventListener("click",(function(e){let t=e.target.closest(".btn-archive");if(t){t.parentElement.style.backgroundColor=t.value,e.preventDefault();var a=t.dataset.url,n=parseInt(t.dataset.archive);if(1===n){if(!confirm(lang.undo_archive))return!1}else if(!confirm(lang.really_archive))return!1;var l={archive:0===n?1:0};getCSRFToken().then((function(e){return l.csrf_name=e.csrf_name,l.csrf_value=e.csrf_value,fetch(a,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(l)})})).then((function(e){return e.json()})).then((function(e){allowedReload=!0,window.location.reload()})).catch((function(e){console.log(e)}))}}));let sidebarToggle=document.getElementById("sidebar-toggle");sidebarToggle.addEventListener("click",(function(e){e.preventDefault(),isMobile()?(sidebarToggle.parentElement.classList.remove("desktop-hidden"),sidebarToggle.parentElement.classList.toggle("mobile-visible"),sidebarToggle.parentElement.classList.contains("mobile-visible")?(setCookie("sidebar_mobilevisible",1),setCookie("sidebar_desktophidden",0)):setCookie("sidebar_mobilevisible",0)):(sidebarToggle.parentElement.classList.remove("mobile-visible"),sidebarToggle.parentElement.classList.toggle("desktop-hidden"),sidebarToggle.parentElement.classList.contains("desktop-hidden")?(setCookie("sidebar_desktophidden",1),setCookie("sidebar_mobilevisible",0)):setCookie("sidebar_desktophidden",0))}));let checkBoxArchivedItems=document.getElementById("checkboxArchivedItems");checkBoxArchivedItems.addEventListener("click",(function(e){var t={state:checkBoxArchivedItems.checked?1:0};getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.set_archive,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){allowedReload=!0,window.location.reload()})).catch((function(e){console.log(e)}))})),setInterval((function(){var e=isVisible(stackModal),t=isVisible(cardModal),a=isVisible(labelModal);!0==!e&&!0==!t&&!0==!a&&window.location.reload()}),3e4),window.addEventListener("beforeunload",(function(e){var t=isVisible(stackModal),a=isVisible(cardModal),n=isVisible(labelModal);allowedReload||!0!==t&&!0!==a&&!0!==n||(e.returnValue=lang.really_close_page)}));const sidebar=document.getElementById("sidebar"),masthead=document.getElementById("masthead"),pageBody=document.getElementsByTagName("BODY")[0];function sidebarAdjustments(){let e=masthead.offsetHeight,t=window.scrollY;if(t<e){let a=e-t;sidebar.style.paddingTop=a+"px"}else sidebar.style.paddingTop=0}window.addEventListener("scroll",(function(){pageBody.classList.contains("mobile-navigation-open")&&sidebarAdjustments()}));var stacks=document.querySelector(".stack-wrapper"),sortable=new Sortable(stacks,{group:{name:"stacks"},draggable:".stack",handle:".stack-header",dataIdAttr:"data-stack",filter:".stack-dummy",onEnd:function(e){var t={stack:this.toArray()};getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.stack_position_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).catch((function(e){console.log(e)}))}}),movableCards=document.querySelectorAll(".card-wrapper");movableCards.forEach((function(e){new Sortable(e,{group:{name:"cards"},draggable:".board-card",dataIdAttr:"data-card",ghostClass:"card-placeholder",onUpdate:function(e){var t={card:this.toArray()};getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.card_position_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).catch((function(e){console.log(e)}))},onAdd:function(e){var t=e.to.dataset.stack,a={card:e.item.dataset.card,stack:t};getCSRFToken().then((function(e){return a.csrf_name=e.csrf_name,a.csrf_value=e.csrf_value,fetch(jsObject.card_movestack_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(a)})})).then((function(e){return e.json()})).catch((function(e){console.log(e)}))}})}));