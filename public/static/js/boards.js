"use strict";const loadingIconBoard=document.querySelector("#loadingIconBoard"),stacksWrapper=document.querySelector(".stack-wrapper"),new_stack_element=document.querySelector("#templates .stack-dummy");let boardData=[],resultPending=!1;function renderBoard(){"error"!==boardData.status&&(stacksWrapper.innerHTML="",Object.values(boardData.stacks).forEach((function(e){let a=createStack(e);stacksWrapper.appendChild(a)})),stacksWrapper.appendChild(new_stack_element),document.querySelectorAll(".card-wrapper").forEach((function(e){createSortableCards(e)})))}function loadBoard(){return fetch(jsObject.boards_data,{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/json"}}).then((function(e){return e.json()})).catch((function(e){console.log(e)}))}document.addEventListener("DOMContentLoaded",(async function(){loadingIconBoard.classList.remove("hidden"),boardData=await loadBoard(),renderBoard(),loadingIconBoard.classList.add("hidden")})),document.addEventListener("click",(async function(e){let a=e.target.closest(".stack-header .stack-menu-button");if(a){e.preventDefault();let t=a.parentElement.querySelector(".stack-menu");t.style.display="block"===t.style.display?"none":"block"}else{document.querySelectorAll(".stack-menu").forEach((function(e){e.style.display="none"}))}let t=e.target.closest(".stack-header .stack-edit");if(t){e.preventDefault();let a=t.closest(".stack").dataset.stack;if(!a)return void window.alert(lang.boards_error_open_stack);document.getElementById("loading-overlay").classList.remove("hidden");let n=getElementFromID(boardData.stacks,a),c=boardData.stacks[n];stackModal.querySelector('input[name="id"]').value=c.id,stackModal.querySelector('input[name="name"]').value=c.name,stackModal.querySelector('input[name="position"]').value=c.position;var r="<a href='#' data-url='"+jsObject.stack_archive+c.id+"' data-archive='"+c.archive+"' class='btn-archive-stack' data-id='"+c.id+"'>"+document.getElementById("iconArchive").innerHTML+"</a> \n                                    <a href='#' data-url='"+jsObject.stack_delete+c.id+"' class='btn-delete-stack'  data-id='"+c.id+"'>"+document.getElementById("iconTrash").innerHTML+"</a>";stackModal.querySelector(".edit-bar").innerHTML=r,document.getElementById("stack-add-btn").value=lang.update,openDialog(stackModal),document.getElementById("loading-overlay").classList.add("hidden")}let n=e.target.closest(".btn-archive-stack");if(n){e.preventDefault(),resultPending=!0;let a=n.dataset.url,t=0===parseInt(n.dataset.archive)?1:0,r=1===parseInt(n.dataset.cards)?1:0,o=parseInt(n.dataset.id);if(0===t){if(!await confirmDialog(lang.boards_undo_archive))return resultPending=!1,!1}else if(!await confirmDialog(lang.boards_really_archive))return resultPending=!1,!1;let l=getElementFromID(boardData.stacks,o),d=boardData.stacks[l],s=document.querySelector('.stack-wrapper .stack[data-stack="'+o+'"');t?s.classList.add("archived"):s.classList.remove("archived");s.querySelectorAll(".stack-header .btn-archive-stack").forEach((function(e){e.dataset.archive=t})),d.archive=t,1==r&&(d.cards.forEach((function(e){e.archive=t})),s.querySelectorAll(".board-card").forEach((function(e){t?e.classList.add("archived"):e.classList.remove("archived");let a=e.querySelector(".btn-archive-card");a.dataset.archive=t,a.checked=1==t}))),closeDialog(stackModal,!0);var c={archive:t,cards:r};getCSRFToken().then((function(e){return c.csrf_name=e.csrf_name,c.csrf_value=e.csrf_value,fetch(a,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(c)})})).then((function(e){return e.json()})).catch((function(e){console.log(e),loadingWindowOverlay.classList.remove("hidden"),window.alert(lang.boards_error_archive);let n=0==t?1:0;t?s.classList.remove("archived"):s.classList.add("archived");if(s.querySelectorAll(".stack-header .btn-archive-stack").forEach((function(e){e.dataset.archive=n})),d.archive=n,1==r&&(d.cards.forEach((function(e){e.archive=n})),s.querySelectorAll(".board-card").forEach((function(e){t?e.classList.remove("archived"):e.classList.add("archived");let a=e.querySelector(".btn-archive-card");a.dataset.archive=n,a.checked=1==n}))),document.body.classList.contains("offline")){let e=new URLSearchParams(c).toString();saveDataWhenOffline(a,"POST",e)}})).finally((function(){resultPending=!1}))}let o=e.target.closest(".btn-archive-card");o&&setTimeout((async function(){resultPending=!0;let e=o.dataset.url,a=0===parseInt(o.dataset.archive)?1:0,t=parseInt(o.dataset.stack),r=parseInt(o.dataset.id),n=getElementFromID(boardData.stacks,t),c=boardData.stacks[n],l=getElementFromID(c.cards,r),d=c.cards[l],s=document.querySelector('.stack-wrapper .stack[data-stack="'+t+'"] .board-card[data-card="'+r+'"]'),i=s.querySelector(".btn-archive-card");if(0===a){if(!await confirmDialog(lang.boards_undo_archive))return i.checked=!0,resultPending=!1,!1}else if(!await confirmDialog(lang.boards_really_archive))return i.checked=!1,resultPending=!1,!1;a?(s.classList.add("archived"),i.checked=!0,i.dataset.archive=1):(s.classList.remove("archived"),i.checked=!1,i.dataset.archive=0),d.archive=a,closeDialog(cardModal,!0);var u={archive:a};getCSRFToken().then((function(e){return u.csrf_name=e.csrf_name,u.csrf_value=e.csrf_value,fetch(jsObject.card_archive+r,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(u)})})).then((function(e){return e.json()})).catch((function(t){if(console.log(t),window.alert(lang.boards_error_archive),a?(s.classList.remove("archived"),i.checked=!1,i.dataset.archive=0):(s.classList.add("archived"),i.checked=!0,i.dataset.archive=1),d.archive=a,document.body.classList.contains("offline")){let a=new URLSearchParams(u).toString();saveDataWhenOffline(e,"POST",a)}})).finally((function(){resultPending=!1}))}),20);let l=e.target.closest(".btn-delete-stack");if(l){e.preventDefault(),resultPending=!0;let a=l.dataset.url,t=parseInt(l.dataset.id);if(!await confirmDialog(lang.boards_really_delete_stack))return resultPending=!1,!1;let r=document.querySelector('.stack-wrapper .stack[data-stack="'+t+'"');r.classList.add("hidden"),closeDialog(stackModal,!0),getCSRFToken(!0).then((function(e){return fetch(a,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})})).then((function(e){return e.json()})).then((function(e){stacksWrapper.removeChild(r);let a=getElementFromID(boardData.stacks,t);delete boardData.stacks[a]})).catch((function(e){if(console.log(e),window.alert(lang.boards_error_delete),r.classList.remove("hidden"),document.body.classList.contains("offline")){let e=new URLSearchParams(c).toString();saveDataWhenOffline(a,"POST",e)}})).finally((function(){resultPending=!1}))}let d=e.target.closest(".btn-delete-card");if(d){e.preventDefault(),resultPending=!0;let a=d.dataset.url,t=parseInt(d.dataset.stack),r=parseInt(d.dataset.id);if(!await confirmDialog(lang.boards_really_delete_card))return resultPending=!1,!1;let n=document.querySelector('.stack-wrapper .stack[data-stack="'+t+'"] .card-wrapper'),o=n.querySelector('.board-card[data-card="'+r+'"]');o.classList.add("hidden"),closeDialog(cardModal,!0),getCSRFToken(!0).then((function(e){return fetch(a,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})})).then((function(e){return e.json()})).then((function(e){n.removeChild(o);let a=getElementFromID(boardData.stacks,t),c=boardData.stacks[a],l=getElementFromID(c.cards,r);delete c.cards[l]})).catch((function(e){if(console.log(e),window.alert(lang.boards_error_delete),o.classList.remove("hidden"),document.body.classList.contains("offline")){let e=new URLSearchParams(c).toString();saveDataWhenOffline(a,"POST",e)}})).finally((function(){resultPending=!1}))}e.target.closest(".create-stack")&&(e.preventDefault(),openDialog(stackModal,!0)),e.target.closest("#stack-close-btn")&&closeDialog(stackModal),e.target.closest("#create-label")&&(e.preventDefault(),openDialog(labelModal,!0)),e.target.closest("#label-close-btn")&&closeDialog(labelModal);let s=e.target.closest("a.edit-label");if(s){e.preventDefault(),document.getElementById("loading-overlay").classList.remove("hidden");var i=s.dataset.label;fetch(jsObject.label_get_url+i,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){if("error"!==e.status){labelModal.querySelector('input[name="id"]').value=e.entry.id,labelModal.querySelector('input[name="name"]').value=e.entry.name,labelModal.querySelector('input[name="background_color"]').value=e.entry.background_color,labelModal.querySelector('input[name="text_color"]').value=e.entry.text_color;var a="<a href='#' data-url='"+jsObject.label_delete+e.entry.id+"' class='btn-delete' data-confirm='"+lang.boards_really_delete_label+"'>"+document.getElementById("iconTrash").innerHTML+"</a>";labelModal.querySelector(".edit-bar").innerHTML=a,document.getElementById("label-add-btn").value=lang.update,openDialog(labelModal)}})).then((function(){document.getElementById("loading-overlay").classList.add("hidden")})).catch((function(e){console.log(e)}))}let u=e.target.closest(".create-card");if(u){e.preventDefault();var m=u.dataset.stack;cardModal.querySelector('input[name="stack"]').value=m,openDialog(cardModal,!0)}e.target.closest("#card-close-btn")&&(e.preventDefault(),closeDialog(cardModal));let f=e.target.closest(".board-card-content");if(f){e.preventDefault();let a=f.closest(".stack").dataset.stack,t=f.closest(".board-card").dataset.card;if(!t)return void window.alert(lang.boards_error_open_card);document.getElementById("loading-overlay").classList.remove("hidden");let n=getElementFromID(boardData.stacks,a),c=boardData.stacks[n],o=getElementFromID(c.cards,t),l=c.cards[o];if(cardModal.querySelector('input[name="id"]').value=l.id,cardModal.querySelector('input[name="title"]').value=l.title,cardModal.querySelector('input[name="position"]').value=l.position,cardModal.querySelector('input[name="stack"]').value=l.stack,cardModal.querySelector('input[name="archive"]').value=l.archive,l.date){var h=cardModal.querySelector('input[name="date"]').parentElement;h.value=l.date,h._flatpickr.setDate(l.date),h.parentElement.parentElement.querySelectorAll(".show-sibling").forEach((function(e,a){e.classList.add("hidden")})),h.parentElement.classList.remove("hidden")}if(l.time){var g=cardModal.querySelector('input[name="time"]');g.value=l.time,g.parentElement.parentElement.querySelectorAll(".show-sibling").forEach((function(e,a){e.classList.add("hidden")})),g.parentElement.classList.remove("hidden")}var b=cardModal.querySelector('textarea[name="description"]');if(l.description){b.value=l.description,b.parentElement.parentElement.querySelectorAll(".show-sibling").forEach((function(e,a){e.classList.add("hidden")})),b.parentElement.classList.remove("hidden")}cardModal.querySelector("#createdBy").innerHTML=boardData.users[l.createdBy].login,cardModal.querySelector("#createdOn").innerHTML=moment(l.createdOn).format(i18n.dateformatJS.datetime),cardModal.querySelector("#changedBy").innerHTML=boardData.users[l.changedBy].login,cardModal.querySelector("#changedOn").innerHTML=moment(l.changedOn).format(i18n.dateformatJS.datetime),cardModal.querySelector(".form-group.card-dates").classList.remove("hidden");let d=cardModal.querySelector('select[name="users[]"]');cardModal.querySelectorAll(".avatar-small, .avatar-small").forEach((function(e,a){let t=parseInt(e.dataset.user);var r=d.querySelector("option[value='"+t+"']");void 0!==l.users&&-1!==l.users.indexOf(t)?(e.classList.add("selected"),r.selected=!0):(e.classList.remove("selected"),r.selected=!1)})),selector.reset(),selector.setValue(l.labels.map(String));r="<a href='#' data-url='"+jsObject.card_archive+l.id+"' data-archive='"+l.archive+"' class='btn-archive-card' data-stack='"+l.stack+"' data-id='"+l.id+"'>"+document.getElementById("iconArchive").innerHTML+"</a> \n                                    <a href='#' data-url='"+jsObject.card_delete+l.id+"' class='btn-delete-card' data-stack='"+l.stack+"' data-id='"+l.id+"'>"+document.getElementById("iconTrash").innerHTML+"</a>";cardModal.querySelector(".edit-bar").innerHTML=r,document.getElementById("card-add-btn").value=lang.update,openDialog(cardModal),document.getElementById("loading-overlay").classList.add("hidden")}}));let openedDialogData=null;const selector=new Selectr("select#card-label-list",{searchable:!1,customClass:"selectr-boards",renderOption:function(e){return["<div class='select-option-label' style='background-color:",e.dataset.backgroundColor,"; color:",e.dataset.textColor,"'><span>",e.textContent,"</span></div>"].join("")},renderSelection:function(e){return['<div class="select-label" style="background-color:',e.dataset.backgroundColor,"; color:",e.dataset.textColor,'"><span>',e.textContent.trim(),"</span></div>"].join("")},placeholder:lang.boards_labels});var editor=null;document.addEventListener("keydown",(function(e){27===e.keyCode&&(isVisible(stackModal)&&closeDialog(stackModal),isVisible(labelModal)&&closeDialog(labelModal),isVisible(cardModal)&&closeDialog(cardModal))}));const stackModal=document.getElementById("stack-modal");stackModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),saveStack(stackModal,jsObject.stack_save)}));const labelModal=document.getElementById("label-modal");labelModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),saveLabel(labelModal,jsObject.label_save)}));const cardModal=document.getElementById("card-modal");cardModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),saveCard(cardModal,jsObject.card_save)})),cardModal.addEventListener("keypress",(function(e){13===e.keyCode&&(e.preventDefault(),saveCard(cardModal,jsObject.card_save))}));let avatars=document.querySelectorAll("#card-modal .avatar");avatars.forEach((function(e,a){e.addEventListener("click",(function(a){a.preventDefault();var t=e.dataset.user,r=document.querySelector("#card-modal select#users option[value='"+t+"']");r.selected?(r.selected=!1,e.classList.remove("selected")):(r.selected=!0,e.classList.add("selected"))}))}));let siblings=cardModal.querySelectorAll(".show-sibling");function openDialog(e,a=!1){if(openedDialogData=formToJSON(e.querySelector("form")),freeze(),e.style.display="block",!isMobile()&&a&&e.querySelector('input[type="text"]').focus(),e===cardModal){var t=cardModal.querySelector('textarea[name="description"]');editor=new EasyMDE({element:t,autosave:{enabled:!1},forceSync:!0,spellChecker:!1,promptURLs:!0,status:!1,styleSelectedText:!isMobile(),minHeight:"50px"}),""!==t.value&&editor.togglePreview()}}async function closeDialog(e,a=!1){let t=formToJSON(e.querySelector("form")),r="";if(e===stackModal&&(r=lang.boards_really_close_stack),e===cardModal&&(r=lang.boards_really_close_card),e===labelModal&&(r=lang.boards_really_close_label),JSON.stringify(openedDialogData)!==JSON.stringify(t)&&!a&&!await confirmDialog(r))return!1;if(unfreeze(),e.style.display="none",e===cardModal){editor&&(editor.toTextArea(),editor=null),cardModal.querySelectorAll(".show-sibling").forEach((function(e,a){e.classList.remove("hidden")})),cardModal.querySelectorAll(".hidden-field").forEach((function(e,a){e.classList.add("hidden")})),cardModal.querySelector('textarea[name="description"]').style.height="auto",cardModal.querySelector("#createdBy").innerHTML="",cardModal.querySelector("#createdOn").innerHTML="",cardModal.querySelector("#changedBy").innerHTML="",cardModal.querySelector("#changedOn").innerHTML="",cardModal.querySelectorAll(".form-group.card-dates").forEach((function(e,a){e.classList.add("hidden")})),cardModal.querySelector('select[name="labels[]"]').value="",cardModal.querySelector('select[name="users[]"]').value="",cardModal.querySelectorAll(".avatar-small, .avatar-small").forEach((function(e,a){e.classList.remove("selected")})),cleanURL()}document.getElementById("stack-add-btn").value=lang.add,document.getElementById("card-add-btn").value=lang.add,document.getElementById("label-add-btn").value=lang.add,e.querySelector("form").reset(),e.querySelectorAll('input[type="hidden"].reset-field').forEach((function(e){e.value=""})),e.querySelector(".edit-bar").innerHTML="",openedDialogData=null}function cleanURL(){var e=window.location.toString();if(e.indexOf("?")>0){var a=e.substring(0,e.indexOf("?"));window.history.replaceState({},document.title,a)}}function addCardtoURL(e){var a=window.location.toString(),t=a.indexOf("?")>0?"&":"?";a.indexOf("card")<=0&&window.history.replaceState({},document.title,a+t+"card="+e)}function formToJSON(e){let a={};return new FormData(e).forEach((function(e,t){if(t.endsWith("[]")){let r=t.slice(0,-2);Array.isArray(a[r])||(a[r]=[]),a[r].push(e)}else a[t]=e})),a}async function saveStack(e,a){resultPending=!0;var t=e.querySelector('input[name="id"]').value;let r=e.querySelector("form"),n=formToJSON(r),c=new URLSearchParams(new FormData(r)).toString(),o=createStack(n),l=getElementFromID(boardData.stacks,n.id),d=boardData.stacks[l],s=document.querySelector('.stack-wrapper .stack[data-stack="'+n.id+'"');if(d){let e=n;e.cards=d.cards;let a=createStack(e);document.querySelector(".stack-wrapper").replaceChild(a,s)}else{document.querySelector(".stack-wrapper").insertBefore(o,new_stack_element),createSortableCards(o.querySelector(".card-wrapper"))}closeDialog(e,!0);try{let e=await getCSRFToken(),r=n;r.csrf_name=e.csrf_name,r.csrf_value=e.csrf_value;let c=await fetch(a+t,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(r)}),s=(await c.json()).entry;d?(boardData.stacks[l]=s,boardData.stacks[l].cards=d.cards):(s.cards=[],boardData.stacks.push(s)),o.dataset.stack=s.id,o.querySelector(".create-card").dataset.stack=s.id}catch(e){if(console.log(e),window.alert(lang.boards_error_save_stack),d){let e=document.querySelector('.stack-wrapper .stack[data-stack="'+n.id+'"');document.querySelector(".stack-wrapper").replaceChild(s,e)}else document.querySelector(".stack-wrapper").removeChild(o);document.body.classList.contains("offline")&&saveDataWhenOffline(a+t,"POST",c,!1)}finally{resultPending=!1}}async function saveCard(e,a){cleanURL(),resultPending=!0;var t=e.querySelector('input[name="id"]').value;let r=e.querySelector("form"),n=formToJSON(r),c=new URLSearchParams(new FormData(r)).toString(),o=document.querySelector('.stack-wrapper .stack[data-stack="'+n.stack+'"]'),l=createCard(n),d=getElementFromID(boardData.stacks,n.stack),s=getElementFromID(boardData.stacks[d].cards,n.id),i=boardData.stacks[d].cards[s],u=o.querySelector('.board-card[data-card="'+n.id+'"]');i?o.querySelector(".card-wrapper").replaceChild(l,u):o.querySelector(".card-wrapper").appendChild(l),closeDialog(e,!0);try{let e=await getCSRFToken(),r=n;r.csrf_name=e.csrf_name,r.csrf_value=e.csrf_value;let c=await fetch(a+t,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(r)}),o=(await c.json()).entry;i?boardData.stacks[d].cards[s]=o:boardData.stacks[d].cards.push(o),l.dataset.card=o.id,l.querySelector(".btn-archive-card").dataset.id=o.id}catch(e){console.log(e),window.alert(lang.boards_error_save_card),i?o.querySelector(".card-wrapper").replaceChild(u,l):o.querySelector(".card-wrapper").removeChild(l),document.body.classList.contains("offline")&&saveDataWhenOffline(a+t,"POST",c,!1)}finally{resultPending=!1}}async function saveLabel(e,a){document.getElementById("loading-overlay").classList.remove("hidden"),cleanURL(),resultPending=!0;var t=e.querySelector('input[name="id"]').value;let r=e.querySelector("form"),n=new URLSearchParams(new FormData(r)).toString(),c=formToJSON(r);try{let e=await getCSRFToken();c.csrf_name=e.csrf_name,c.csrf_value=e.csrf_value;let r=await fetch(a+t,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(c)});await r.json();allowedReload=!0,window.location.reload(!0)}catch(e){console.log(e),document.body.classList.contains("offline")&&saveDataWhenOffline(a+t,"POST",n,!0)}finally{resultPending=!1}}siblings.forEach((function(e,a){e.addEventListener("click",(function(a){a.preventDefault(),e.classList.add("hidden"),e.parentNode.querySelectorAll(".hidden-field").forEach((function(e){e.classList.remove("hidden");let a=e.querySelector("input");a&&a.focus();let t=e.querySelector("textarea");t&&t.focus();let r=cardModal.querySelector('input[name="date"]').parentElement;a===r&&r._flatpickr.open()})),editor&&(editor.codemirror.refresh(),editor.codemirror.focus())}))}));let sidebarToggle=document.getElementById("sidebar-toggle");sidebarToggle.addEventListener("click",(function(e){e.preventDefault(),isMobile()?(sidebarToggle.parentElement.classList.remove("desktop-hidden"),sidebarToggle.parentElement.classList.toggle("mobile-visible"),sidebarToggle.parentElement.classList.contains("mobile-visible")?(setCookie("sidebar_mobilevisible",1),setCookie("sidebar_desktophidden",0)):setCookie("sidebar_mobilevisible",0)):(sidebarToggle.parentElement.classList.remove("mobile-visible"),sidebarToggle.parentElement.classList.toggle("desktop-hidden"),sidebarToggle.parentElement.classList.contains("desktop-hidden")?(setCookie("sidebar_desktophidden",1),setCookie("sidebar_mobilevisible",0)):setCookie("sidebar_desktophidden",0))}));let checkBoxArchivedItems=document.getElementById("checkboxArchivedItems");async function updateBoard(){let e=await loadBoard();JSON.stringify(boardData)!==JSON.stringify(e)&&(console.log("Update Board Data"),loadingIconBoard.classList.remove("hidden"),boardData=e,renderBoard(),loadingIconBoard.classList.add("hidden"))}checkBoxArchivedItems.addEventListener("click",(function(e){checkBoxArchivedItems.checked?stacksWrapper.classList.remove("hide-archived"):stacksWrapper.classList.add("hide-archived")})),setInterval((async function(){var e=isVisible(stackModal),a=isVisible(cardModal),t=isVisible(labelModal),r=document.body.classList.contains("sortable-select");let n=isVisibleOnPage(".stack-wrapper .stack-menu");!0==!e&&!0==!a&&!0==!t&&!0==!r&&!0==!resultPending&&!0==!n&&await updateBoard()}),3e4),window.addEventListener("beforeunload",(function(e){var a=isVisible(stackModal),t=isVisible(cardModal),r=isVisible(labelModal);allowedReload||!0!==a&&!0!==t&&!0!==r||(e.returnValue=lang.boards_really_close_page)}));const sidebar=document.getElementById("sidebar"),masthead=document.getElementById("masthead"),pageBody=document.getElementsByTagName("BODY")[0];function sidebarAdjustments(){let e=masthead.offsetHeight,a=window.scrollY;if(a<e){let t=e-a;sidebar.style.paddingTop=t+"px"}else sidebar.style.paddingTop=0}window.addEventListener("scroll",(function(){pageBody.classList.contains("navigation-drawer-toggled")&&sidebarAdjustments()}));var sortable=new Sortable(stacksWrapper,{group:{name:"stacks"},draggable:".stack",handle:isTouchEnabled()?".handle":".stack-header",dataIdAttr:"data-stack",onStart:function(e){document.body.classList.add("sortable-select"),resultPending=!0},onEnd:function(e){document.body.classList.remove("sortable-select");let a=this.toArray();var t={stack:a};getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.stack_position_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){a.forEach((function(e,a){let t=getElementFromID(boardData.stacks,e);boardData.stacks[t].position=a}))})).catch((function(e){console.log(e)})).finally((function(){resultPending=!1}))}});function getElementFromID(e,a){for(let t in e){if(e[t].id==a)return t}return null}function createStack(e){let a=document.querySelector("#templates .stack").cloneNode(!0);e.id&&(a.dataset.stack=e.id),1==e.archive&&a.classList.add("archived"),a.querySelector(".stack-header span.title").innerHTML=e.name,a.querySelector("a.create-card").dataset.stack=e.id;return a.querySelectorAll(".stack-header .btn-archive-stack").forEach((function(a){a.dataset.id=e.id,a.dataset.archive=e.archive,a.dataset.url=jsObject.stack_archive+e.id})),e.cards&&Object.values(e.cards).forEach((function(e){let t=createCard(e);a.querySelector(".card-wrapper").appendChild(t)})),a}function createCard(e){let a=document.querySelector("#templates .board-card").cloneNode(!0);if(e.id&&(a.dataset.card=e.id),1==e.archive&&a.classList.add("archived"),a.querySelector(".card-title").innerHTML=e.title,e.description&&a.querySelector(".description").classList.remove("hidden"),e.date||e.time){let t=a.querySelector(".card-date");if(t.classList.remove("hidden"),e.date){let a=moment(e.date,"YYYY-MM-DD");moment().isSameOrAfter(a)&&t.classList.add("due"),t.innerHTML=moment(e.date,"YYYY-MM-DD").format(i18n.dateformatJS.date)}e.time&&(t.innerHTML=t.innerHTML+" "+moment(e.time,"HH:mm:ss").format("HH:mm"))}let t=a.querySelector(".check");if(t.classList.add("btn-archive-card"),t.dataset.archive=e.archive?e.archive:0,t.dataset.stack=e.stack,t.dataset.id=e.id,1==e.archive&&(t.checked=!0),e.labels){let t=a.querySelector(".card-labels .handle");e.labels.forEach((function(e){let r=boardData.labels[e],n=document.createElement("div");n.classList.add("card-label"),n.style.backgroundColor=r.background_color,n.style.color=r.text_color,a.querySelector(".card-labels").insertBefore(n,t)}))}return e.users&&e.users.forEach((function(e){boardData.users[e];a.querySelectorAll(".card-member").forEach((function(a){a.dataset.user==e&&a.classList.remove("hidden")}))})),a}function changeCardPosition(e,a){var t={card:a};return resultPending=!0,getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.card_position_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(t){let r=getElementFromID(boardData.stacks,e),n=boardData.stacks[r];a.forEach((function(e,a){let t=getElementFromID(n.cards,e);n.cards[t].position=a}))})).catch((function(e){console.log(e)})).finally((function(){resultPending=!1}))}function createSortableCards(e){new Sortable(e,{group:{name:"cards"},draggable:".board-card",handle:isTouchEnabled()?".handle":".board-card",dataIdAttr:"data-card",ghostClass:"card-placeholder",onStart:function(e){document.body.classList.add("sortable-select"),resultPending=!0},onEnd:function(e){document.body.classList.remove("sortable-select"),resultPending=!1},onUpdate:function(e){changeCardPosition(e.item.closest(".stack").dataset.stack,this.toArray())},onAdd:function(e){resultPending=!0;let a=e.from.closest(".stack").dataset.stack,t=e.to.closest(".stack").dataset.stack,r=e.item.dataset.card,n=this.toArray();var c={card:r,stack:t};getCSRFToken().then((function(e){return c.csrf_name=e.csrf_name,c.csrf_value=e.csrf_value,fetch(jsObject.card_movestack_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(c)})})).then((function(e){return e.json()})).then((function(){let e=getElementFromID(boardData.stacks,a),c=boardData.stacks[e],o=getElementFromID(c.cards,r),l=c.cards[o];c.cards=c.cards.filter((function(e){return e.id!=r}));let d=getElementFromID(boardData.stacks,t);boardData.stacks[d].cards.push(l),l.stack=t,changeCardPosition(t,n),document.querySelector('.stack-wrapper .stack[data-stack="'+t+'"] .board-card[data-card="'+r+'"]').querySelector(".btn-archive-card").dataset.stack=t})).catch((function(e){console.log(e)})).finally((function(){resultPending=!1}))}})}