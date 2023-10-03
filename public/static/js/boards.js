"use strict";const loadingIconBoard=document.querySelector("#loadingIconBoard"),stacksWrapper=document.querySelector(".stack-wrapper"),new_stack_element=document.querySelector("#templates .stack-dummy");let boardData=[],resultPending=!1;function renderBoard(){"error"!==boardData.status&&(stacksWrapper.innerHTML="",Object.values(boardData.stacks).forEach((function(e){let a=createStack(e);stacksWrapper.appendChild(a)})),stacksWrapper.appendChild(new_stack_element),document.querySelectorAll(".card-wrapper").forEach((function(e){createSortableCards(e)})))}function loadBoard(){return fetch(jsObject.boards_data,{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/json"}}).then((function(e){return e.json()})).catch((function(e){console.log(e)}))}document.addEventListener("DOMContentLoaded",(async function(){loadingIconBoard.classList.remove("hidden"),boardData=await loadBoard(),renderBoard(),loadingIconBoard.classList.add("hidden")})),document.addEventListener("click",(function(e){let a=e.target.closest(".stack-header");if(a){e.preventDefault();let r=a.closest(".stack").dataset.stack;if(!r)return void window.alert(lang.boards_error_open_stack);document.getElementById("loading-overlay").classList.remove("hidden");let n=getElementFromID(boardData.stacks,r),o=boardData.stacks[n];stackModal.querySelector('input[name="id"]').value=o.id,stackModal.querySelector('input[name="name"]').value=o.name,stackModal.querySelector('input[name="position"]').value=o.position;var t="<a href='#' data-url='"+jsObject.stack_archive+o.id+"' data-archive='"+o.archive+"' class='btn-archive-stack' data-id='"+o.id+"'>"+document.getElementById("iconArchive").innerHTML+"</a> \n                                    <a href='#' data-url='"+jsObject.stack_delete+o.id+"' class='btn-delete-stack'  data-id='"+o.id+"'>"+document.getElementById("iconTrash").innerHTML+"</a>";stackModal.querySelector(".edit-bar").innerHTML=t,document.getElementById("stack-add-btn").value=lang.update,openDialog(stackModal),document.getElementById("loading-overlay").classList.add("hidden")}let r=e.target.closest(".btn-archive-stack");if(r){e.preventDefault(),resultPending=!0;let a=r.dataset.url,t=0===parseInt(r.dataset.archive)?1:0,o=parseInt(r.dataset.id);if(0===t){if(!confirm(lang.boards_undo_archive))return resultPending=!1,!1}else if(!confirm(lang.boards_really_archive))return resultPending=!1,!1;let l=getElementFromID(boardData.stacks,o),c=boardData.stacks[l],d=document.querySelector('.stack-wrapper .stack[data-stack="'+o+'"');t?d.classList.add("archived"):d.classList.remove("archived"),c.archive=t,closeDialog(stackModal,!0);var n={archive:t};getCSRFToken().then((function(e){return n.csrf_name=e.csrf_name,n.csrf_value=e.csrf_value,fetch(a,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(n)})})).then((function(e){return e.json()})).catch((function(e){if(console.log(e),loadingWindowOverlay.classList.remove("hidden"),window.alert(lang.boards_error_archive),t?d.classList.remove("archived"):d.classList.add("archived"),c.archive=t,document.body.classList.contains("offline")){let e=new URLSearchParams(n).toString();saveDataWhenOffline(a,"POST",e)}})).finally((function(){resultPending=!1}))}let o=e.target.closest(".btn-archive-card");o&&setTimeout((function(){resultPending=!0;let e=o.dataset.url,a=0===parseInt(o.dataset.archive)?1:0,t=parseInt(o.dataset.stack),r=parseInt(o.dataset.id),n=getElementFromID(boardData.stacks,t),l=boardData.stacks[n],c=getElementFromID(l.cards,r),d=l.cards[c],s=document.querySelector('.stack-wrapper .stack[data-stack="'+t+'"] .board-card[data-card="'+r+'"]'),i=s.querySelector(".btn-archive-card");if(0===a){if(!confirm(lang.boards_undo_archive))return i.checked=!0,resultPending=!1,!1}else if(!confirm(lang.boards_really_archive))return i.checked=!1,resultPending=!1,!1;a?(s.classList.add("archived"),i.checked=!0,i.dataset.archive=1):(s.classList.remove("archived"),i.checked=!1,i.dataset.archive=0),d.archive=a,closeDialog(cardModal,!0);var u={archive:a};getCSRFToken().then((function(e){return u.csrf_name=e.csrf_name,u.csrf_value=e.csrf_value,fetch(jsObject.card_archive+r,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(u)})})).then((function(e){return e.json()})).catch((function(t){if(console.log(t),window.alert(lang.boards_error_archive),a?(s.classList.remove("archived"),i.checked=!1,i.dataset.archive=0):(s.classList.add("archived"),i.checked=!0,i.dataset.archive=1),d.archive=a,document.body.classList.contains("offline")){let a=new URLSearchParams(u).toString();saveDataWhenOffline(e,"POST",a)}})).finally((function(){resultPending=!1}))}),20);let l=e.target.closest(".btn-delete-stack");if(l){e.preventDefault(),resultPending=!0;let a=l.dataset.url,t=parseInt(l.dataset.id);if(!confirm(lang.boards_really_delete_stack))return resultPending=!1,!1;let r=document.querySelector('.stack-wrapper .stack[data-stack="'+t+'"');r.classList.add("hidden"),closeDialog(stackModal,!0),getCSRFToken(!0).then((function(e){return fetch(a,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})})).then((function(e){return e.json()})).then((function(e){stacksWrapper.removeChild(r);let a=getElementFromID(boardData.stacks,t);delete boardData.stacks[a]})).catch((function(e){if(console.log(e),window.alert(lang.boards_error_delete),r.classList.remove("hidden"),document.body.classList.contains("offline")){let e=new URLSearchParams(n).toString();saveDataWhenOffline(a,"POST",e)}})).finally((function(){resultPending=!1}))}let c=e.target.closest(".btn-delete-card");if(c){e.preventDefault(),resultPending=!0;let a=c.dataset.url,t=parseInt(c.dataset.stack),r=parseInt(c.dataset.id);if(!confirm(lang.boards_really_delete_card))return resultPending=!1,!1;let o=document.querySelector('.stack-wrapper .stack[data-stack="'+t+'"] .card-wrapper'),l=o.querySelector('.board-card[data-card="'+r+'"]');l.classList.add("hidden"),closeDialog(cardModal,!0),getCSRFToken(!0).then((function(e){return fetch(a,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})})).then((function(e){return e.json()})).then((function(e){o.removeChild(l);let a=getElementFromID(boardData.stacks,t),n=boardData.stacks[a],c=getElementFromID(n.cards,r);delete n.cards[c]})).catch((function(e){if(console.log(e),window.alert(lang.boards_error_delete),l.classList.remove("hidden"),document.body.classList.contains("offline")){let e=new URLSearchParams(n).toString();saveDataWhenOffline(a,"POST",e)}})).finally((function(){resultPending=!1}))}e.target.closest(".create-stack")&&(e.preventDefault(),openDialog(stackModal)),e.target.closest("#stack-close-btn")&&closeDialog(stackModal),e.target.closest("#create-label")&&(e.preventDefault(),openDialog(labelModal)),e.target.closest("#label-close-btn")&&closeDialog(labelModal);let d=e.target.closest("a.edit-label");if(d){e.preventDefault(),document.getElementById("loading-overlay").classList.remove("hidden");var s=d.dataset.label;fetch(jsObject.label_get_url+s,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){if("error"!==e.status){labelModal.querySelector('input[name="id"]').value=e.entry.id,labelModal.querySelector('input[name="name"]').value=e.entry.name,labelModal.querySelector('input[name="background_color"]').value=e.entry.background_color,labelModal.querySelector('input[name="background_color"]').parentElement.style.backgroundColor=e.entry.background_color,labelModal.querySelector('input[name="text_color"]').value=e.entry.text_color,labelModal.querySelector('input[name="text_color"]').parentElement.style.backgroundColor=e.entry.text_color;var a="<a href='#' data-url='"+jsObject.label_delete+e.entry.id+"' class='btn-delete' data-confirm='"+lang.boards_really_delete_label+"'>"+document.getElementById("iconTrash").innerHTML+"</a>";labelModal.querySelector(".edit-bar").innerHTML=a,document.getElementById("label-add-btn").value=lang.update,openDialog(labelModal)}})).then((function(){document.getElementById("loading-overlay").classList.add("hidden")})).catch((function(e){console.log(e)}))}let i=e.target.closest(".create-card");if(i){e.preventDefault();var u=i.dataset.stack;cardModal.querySelector('input[name="stack"]').value=u,openDialog(cardModal)}e.target.closest("#card-close-btn")&&(e.preventDefault(),closeDialog(cardModal));let m=e.target.closest(".board-card-content");if(m){e.preventDefault();let a=m.closest(".stack").dataset.stack,r=m.closest(".board-card").dataset.card;if(!r)return void window.alert(lang.boards_error_open_card);document.getElementById("loading-overlay").classList.remove("hidden");let n=getElementFromID(boardData.stacks,a),o=boardData.stacks[n],l=getElementFromID(o.cards,r),c=o.cards[l];if(cardModal.querySelector('input[name="id"]').value=c.id,cardModal.querySelector('input[name="title"]').value=c.title,cardModal.querySelector('input[name="position"]').value=c.position,cardModal.querySelector('input[name="stack"]').value=c.stack,cardModal.querySelector('input[name="archive"]').value=c.archive,c.date){var f=cardModal.querySelector('input[name="date"]');f.value=c.date,f._flatpickr.setDate(c.date),f.parentElement.parentElement.querySelectorAll(".show-sibling").forEach((function(e,a){e.classList.add("hidden")})),f.parentElement.classList.remove("hidden")}if(c.time){var g=cardModal.querySelector('input[name="time"]');g.value=c.time,g.parentElement.parentElement.querySelectorAll(".show-sibling").forEach((function(e,a){e.classList.add("hidden")})),g.parentElement.classList.remove("hidden")}var h=cardModal.querySelector('textarea[name="description"]');if(c.description){h.value=c.description,h.parentElement.parentElement.querySelectorAll(".show-sibling").forEach((function(e,a){e.classList.add("hidden")})),h.parentElement.classList.remove("hidden")}cardModal.querySelector("#createdBy").innerHTML=boardData.users[c.createdBy].login,cardModal.querySelector("#createdOn").innerHTML=moment(c.createdOn).format(i18n.dateformatJS.datetime),cardModal.querySelector("#changedBy").innerHTML=boardData.users[c.changedBy].login,cardModal.querySelector("#changedOn").innerHTML=moment(c.changedOn).format(i18n.dateformatJS.datetime),cardModal.querySelector(".form-group.card-dates").classList.remove("hidden");let d=cardModal.querySelector('select[name="users[]"]');cardModal.querySelectorAll(".avatar-small, .avatar-small").forEach((function(e,a){let t=parseInt(e.dataset.user);var r=d.querySelector("option[value='"+t+"']");void 0!==c.users&&-1!==c.users.indexOf(t)?(e.classList.add("selected"),r.selected=!0):(e.classList.remove("selected"),r.selected=!1)})),selector.reset(),selector.setValue(c.labels.map(String));t="<a href='#' data-url='"+jsObject.card_archive+c.id+"' data-archive='"+c.archive+"' class='btn-archive-card' data-stack='"+c.stack+"' data-id='"+c.id+"'>"+document.getElementById("iconArchive").innerHTML+"</a> \n                                    <a href='#' data-url='"+jsObject.card_delete+c.id+"' class='btn-delete-card' data-stack='"+c.stack+"' data-id='"+c.id+"'>"+document.getElementById("iconTrash").innerHTML+"</a>";cardModal.querySelector(".edit-bar").innerHTML=t,document.getElementById("card-add-btn").value=lang.update,openDialog(cardModal),document.getElementById("loading-overlay").classList.add("hidden")}}));let openedDialogData=null;const selector=new Selectr("select#card-label-list",{searchable:!1,customClass:"selectr-boards",renderOption:function(e){return["<div class='select-option-label' style='background-color:",e.dataset.backgroundColor,"; color:",e.dataset.textColor,"'><span>",e.textContent,"</span></div>"].join("")},renderSelection:function(e){return['<div class="select-label" style="background-color:',e.dataset.backgroundColor,"; color:",e.dataset.textColor,'"><span>',e.textContent.trim(),"</span></div>"].join("")},placeholder:lang.boards_labels});var editor=null;document.addEventListener("keydown",(function(e){27===e.keyCode&&(isVisible(stackModal)&&closeDialog(stackModal),isVisible(labelModal)&&closeDialog(labelModal),isVisible(cardModal)&&closeDialog(cardModal))})),document.addEventListener("change",(function(e){let a=e.target.closest('input[type="color"]');a&&(a.parentElement.style.backgroundColor=a.value)}));const stackModal=document.getElementById("stack-modal");stackModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),saveStack(stackModal,jsObject.stack_save)}));const labelModal=document.getElementById("label-modal");labelModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),saveLabel(labelModal,jsObject.label_save)}));const cardModal=document.getElementById("card-modal");cardModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),saveCard(cardModal,jsObject.card_save)})),cardModal.addEventListener("keypress",(function(e){13===e.keyCode&&(e.preventDefault(),saveCard(cardModal,jsObject.card_save))}));let avatars=document.querySelectorAll("#card-modal .avatar");avatars.forEach((function(e,a){e.addEventListener("click",(function(a){a.preventDefault();var t=e.dataset.user,r=document.querySelector("#card-modal select#users option[value='"+t+"']");r.selected?(r.selected=!1,e.classList.remove("selected")):(r.selected=!0,e.classList.add("selected"))}))}));let siblings=cardModal.querySelectorAll(".show-sibling");function openDialog(e){if(openedDialogData=formToJSON(e.querySelector("form")),freeze(),e.style.display="block",isMobile()||e.querySelector('input[type="text"]').focus(),e===cardModal){var a=cardModal.querySelector('textarea[name="description"]');editor=new EasyMDE({element:a,autosave:{enabled:!1},forceSync:!0,spellChecker:!1,promptURLs:!0,status:!1,styleSelectedText:!isMobile(),minHeight:"50px"}),""!==a.value&&editor.togglePreview()}}function closeDialog(e,a=!1){let t=formToJSON(e.querySelector("form")),r="";if(e===stackModal&&(r=lang.boards_really_close_stack),e===cardModal&&(r=lang.boards_really_close_card),e===labelModal&&(r=lang.boards_really_close_label),JSON.stringify(openedDialogData)!==JSON.stringify(t)&&!a&&!confirm(r))return!1;if(unfreeze(),e.style.display="none",e===labelModal){e.querySelectorAll(".color-wrapper").forEach((function(e,a){e.style.backgroundColor="black"}))}if(e===cardModal){editor&&(editor.toTextArea(),editor=null),cardModal.querySelectorAll(".show-sibling").forEach((function(e,a){e.classList.remove("hidden")})),cardModal.querySelectorAll(".hidden-field").forEach((function(e,a){e.classList.add("hidden")})),cardModal.querySelector('textarea[name="description"]').style.height="auto",cardModal.querySelector("#createdBy").innerHTML="",cardModal.querySelector("#createdOn").innerHTML="",cardModal.querySelector("#changedBy").innerHTML="",cardModal.querySelector("#changedOn").innerHTML="",cardModal.querySelectorAll(".form-group.card-dates").forEach((function(e,a){e.classList.add("hidden")})),cardModal.querySelector('select[name="labels[]"]').value="",cardModal.querySelector('select[name="users[]"]').value="",cardModal.querySelectorAll(".avatar-small, .avatar-small").forEach((function(e,a){e.classList.remove("selected")})),cleanURL()}document.getElementById("stack-add-btn").value=lang.add,document.getElementById("card-add-btn").value=lang.add,document.getElementById("label-add-btn").value=lang.add,e.querySelector("form").reset(),e.querySelectorAll('input[type="hidden"].reset-field').forEach((function(e){e.value=""})),e.querySelector(".edit-bar").innerHTML="",openedDialogData=null}function cleanURL(){var e=window.location.toString();if(e.indexOf("?")>0){var a=e.substring(0,e.indexOf("?"));window.history.replaceState({},document.title,a)}}function addCardtoURL(e){var a=window.location.toString(),t=a.indexOf("?")>0?"&":"?";a.indexOf("card")<=0&&window.history.replaceState({},document.title,a+t+"card="+e)}function formToJSON(e){let a={};return new FormData(e).forEach((function(e,t){if(t.endsWith("[]")){let r=t.slice(0,-2);Array.isArray(a[r])||(a[r]=[]),a[r].push(e)}else a[t]=e})),a}async function saveStack(e,a){resultPending=!0;var t=e.querySelector('input[name="id"]').value;let r=e.querySelector("form"),n=formToJSON(r),o=new URLSearchParams(new FormData(r)).toString(),l=createStack(n),c=getElementFromID(boardData.stacks,n.id),d=boardData.stacks[c],s=document.querySelector('.stack-wrapper .stack[data-stack="'+n.id+'"');if(d){let e=n;e.cards=d.cards;let a=createStack(e);document.querySelector(".stack-wrapper").replaceChild(a,s)}else{document.querySelector(".stack-wrapper").insertBefore(l,new_stack_element),createSortableCards(l.querySelector(".card-wrapper"))}closeDialog(e,!0);try{let e=await getCSRFToken(),r=n;r.csrf_name=e.csrf_name,r.csrf_value=e.csrf_value;let o=await fetch(a+t,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(r)}),s=(await o.json()).entry;d?(boardData.stacks[c]=s,boardData.stacks[c].cards=d.cards):(s.cards=[],boardData.stacks.push(s)),l.dataset.stack=s.id,l.querySelector(".create-card").dataset.stack=s.id}catch(e){if(console.log(e),window.alert(lang.boards_error_save_stack),d){let e=document.querySelector('.stack-wrapper .stack[data-stack="'+n.id+'"');document.querySelector(".stack-wrapper").replaceChild(s,e)}else document.querySelector(".stack-wrapper").removeChild(l);document.body.classList.contains("offline")&&saveDataWhenOffline(a+t,"POST",o,!1)}finally{resultPending=!1}}async function saveCard(e,a){cleanURL(),resultPending=!0;var t=e.querySelector('input[name="id"]').value;let r=e.querySelector("form"),n=formToJSON(r),o=new URLSearchParams(new FormData(r)).toString(),l=document.querySelector('.stack-wrapper .stack[data-stack="'+n.stack+'"]'),c=createCard(n),d=getElementFromID(boardData.stacks,n.stack),s=getElementFromID(boardData.stacks[d].cards,n.id),i=boardData.stacks[d].cards[s],u=l.querySelector('.board-card[data-card="'+n.id+'"]');i?l.querySelector(".card-wrapper").replaceChild(c,u):l.querySelector(".card-wrapper").appendChild(c),closeDialog(e,!0);try{let e=await getCSRFToken(),r=n;r.csrf_name=e.csrf_name,r.csrf_value=e.csrf_value;let o=await fetch(a+t,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(r)}),l=(await o.json()).entry;i?boardData.stacks[d].cards[s]=l:boardData.stacks[d].cards.push(l),c.dataset.card=l.id,c.querySelector(".btn-archive-card").dataset.id=l.id}catch(e){console.log(e),window.alert(lang.boards_error_save_card),i?l.querySelector(".card-wrapper").replaceChild(u,c):l.querySelector(".card-wrapper").removeChild(c),document.body.classList.contains("offline")&&saveDataWhenOffline(a+t,"POST",o,!1)}finally{resultPending=!1}}async function saveLabel(e,a){document.getElementById("loading-overlay").classList.remove("hidden"),cleanURL(),resultPending=!0;var t=e.querySelector('input[name="id"]').value;let r=e.querySelector("form"),n=new URLSearchParams(new FormData(r)).toString(),o=formToJSON(r);try{let e=await getCSRFToken();o.csrf_name=e.csrf_name,o.csrf_value=e.csrf_value;let r=await fetch(a+t,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(o)});await r.json();allowedReload=!0,window.location.reload(!0)}catch(e){console.log(e),document.body.classList.contains("offline")&&saveDataWhenOffline(a+t,"POST",n,!0)}finally{resultPending=!1}}siblings.forEach((function(e,a){e.addEventListener("click",(function(a){a.preventDefault(),e.classList.add("hidden"),e.parentNode.querySelectorAll(".hidden-field").forEach((function(e){e.classList.remove("hidden");let a=e.querySelector("input");a&&a.focus();let t=e.querySelector("textarea");t&&t.focus();let r=cardModal.querySelector('input[name="date"]');a===r&&r._flatpickr.open()})),editor&&(editor.codemirror.refresh(),editor.codemirror.focus())}))}));let sidebarToggle=document.getElementById("sidebar-toggle");sidebarToggle.addEventListener("click",(function(e){e.preventDefault(),isMobile()?(sidebarToggle.parentElement.classList.remove("desktop-hidden"),sidebarToggle.parentElement.classList.toggle("mobile-visible"),sidebarToggle.parentElement.classList.contains("mobile-visible")?(setCookie("sidebar_mobilevisible",1),setCookie("sidebar_desktophidden",0)):setCookie("sidebar_mobilevisible",0)):(sidebarToggle.parentElement.classList.remove("mobile-visible"),sidebarToggle.parentElement.classList.toggle("desktop-hidden"),sidebarToggle.parentElement.classList.contains("desktop-hidden")?(setCookie("sidebar_desktophidden",1),setCookie("sidebar_mobilevisible",0)):setCookie("sidebar_desktophidden",0))}));let checkBoxArchivedItems=document.getElementById("checkboxArchivedItems");async function updateBoard(){let e=await loadBoard();JSON.stringify(boardData)!==JSON.stringify(e)&&(console.log("Update Board Data"),loadingIconBoard.classList.remove("hidden"),boardData=e,renderBoard(),loadingIconBoard.classList.add("hidden"))}checkBoxArchivedItems.addEventListener("click",(function(e){checkBoxArchivedItems.checked?stacksWrapper.classList.remove("hide-archived"):stacksWrapper.classList.add("hide-archived")})),setInterval((async function(){var e=isVisible(stackModal),a=isVisible(cardModal),t=isVisible(labelModal),r=document.body.classList.contains("sortable-select");!0==!e&&!0==!a&&!0==!t&&!0==!r&&!0==!resultPending&&await updateBoard()}),1e4),window.addEventListener("beforeunload",(function(e){var a=isVisible(stackModal),t=isVisible(cardModal),r=isVisible(labelModal);allowedReload||!0!==a&&!0!==t&&!0!==r||(e.returnValue=lang.boards_really_close_page)}));const sidebar=document.getElementById("sidebar"),masthead=document.getElementById("masthead"),pageBody=document.getElementsByTagName("BODY")[0];function sidebarAdjustments(){let e=masthead.offsetHeight,a=window.scrollY;if(a<e){let t=e-a;sidebar.style.paddingTop=t+"px"}else sidebar.style.paddingTop=0}window.addEventListener("scroll",(function(){pageBody.classList.contains("navigation-drawer-toggled")&&sidebarAdjustments()}));var sortable=new Sortable(stacksWrapper,{group:{name:"stacks"},draggable:".stack",handle:isTouchEnabled()?".handle":".stack-header",dataIdAttr:"data-stack",onStart:function(e){document.body.classList.add("sortable-select"),resultPending=!0},onEnd:function(e){document.body.classList.remove("sortable-select");let a=this.toArray();var t={stack:a};getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.stack_position_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){a.forEach((function(e,a){let t=getElementFromID(boardData.stacks,e);boardData.stacks[t].position=a}))})).catch((function(e){console.log(e)})).finally((function(){resultPending=!1}))}});function getElementFromID(e,a){for(let t in e){if(e[t].id==a)return t}return null}function createStack(e){let a=document.querySelector("#templates .stack").cloneNode(!0);return e.id&&(a.dataset.stack=e.id),1==e.archive&&a.classList.add("archived"),a.querySelector(".stack-header span.title").innerHTML=e.name,a.querySelector("a.create-card").dataset.stack=e.id,e.cards&&Object.values(e.cards).forEach((function(e){let t=createCard(e);a.querySelector(".card-wrapper").appendChild(t)})),a}function createCard(e){let a=document.querySelector("#templates .board-card").cloneNode(!0);if(e.id&&(a.dataset.card=e.id),1==e.archive&&a.classList.add("archived"),a.querySelector(".card-title").innerHTML=e.title,e.description&&a.querySelector(".description").classList.remove("hidden"),e.date||e.time){let t=a.querySelector(".card-date");if(t.classList.remove("hidden"),e.date){let a=moment(e.date,"YYYY-MM-DD");moment().isSameOrAfter(a)&&t.classList.add("due"),t.innerHTML=moment(e.date,"YYYY-MM-DD").format(i18n.dateformatJS.date)}e.time&&(t.innerHTML=t.innerHTML+" "+moment(e.time,"HH:mm:ss").format("HH:mm"))}let t=a.querySelector(".check");if(t.classList.add("btn-archive-card"),t.dataset.archive=e.archive?e.archive:0,t.dataset.stack=e.stack,t.dataset.id=e.id,1==e.archive&&(t.checked=!0),e.labels){let t=a.querySelector(".card-labels .handle");e.labels.forEach((function(e){let r=boardData.labels[e],n=document.createElement("div");n.classList.add("card-label"),n.style.backgroundColor=r.background_color,n.style.color=r.text_color,a.querySelector(".card-labels").insertBefore(n,t)}))}return e.users&&e.users.forEach((function(e){boardData.users[e];a.querySelectorAll(".card-member").forEach((function(a){a.dataset.user==e&&a.classList.remove("hidden")}))})),a}function changeCardPosition(e,a){var t={card:a};return resultPending=!0,getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.card_position_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(t){let r=getElementFromID(boardData.stacks,e),n=boardData.stacks[r];a.forEach((function(e,a){let t=getElementFromID(n.cards,e);n.cards[t].position=a}))})).catch((function(e){console.log(e)})).finally((function(){resultPending=!1}))}function createSortableCards(e){new Sortable(e,{group:{name:"cards"},draggable:".board-card",handle:isTouchEnabled()?".handle":".board-card",dataIdAttr:"data-card",ghostClass:"card-placeholder",onStart:function(e){document.body.classList.add("sortable-select"),resultPending=!0},onEnd:function(e){document.body.classList.remove("sortable-select"),resultPending=!1},onUpdate:function(e){changeCardPosition(e.item.closest(".stack").dataset.stack,this.toArray())},onAdd:function(e){resultPending=!0;let a=e.from.closest(".stack").dataset.stack,t=e.to.closest(".stack").dataset.stack,r=e.item.dataset.card,n=this.toArray();var o={card:r,stack:t};getCSRFToken().then((function(e){return o.csrf_name=e.csrf_name,o.csrf_value=e.csrf_value,fetch(jsObject.card_movestack_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(o)})})).then((function(e){return e.json()})).then((function(){let e=getElementFromID(boardData.stacks,a),o=boardData.stacks[e],l=getElementFromID(o.cards,r),c=o.cards[l];o.cards=o.cards.filter((function(e){return e.id!=r}));let d=getElementFromID(boardData.stacks,t);boardData.stacks[d].cards.push(c),c.stack=t,changeCardPosition(t,n),document.querySelector('.stack-wrapper .stack[data-stack="'+t+'"] .board-card[data-card="'+r+'"]').querySelector(".btn-archive-card").dataset.stack=t})).catch((function(e){console.log(e)})).finally((function(){resultPending=!1}))}})}