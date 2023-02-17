"use strict";const addGroceryToList=document.querySelector("#addGroceryToList"),addGroceryToList_amount=document.querySelector("#addGroceryToList_amount"),addGroceryToList_name=document.querySelector("#addGroceryToList_name"),addGroceryToList_unit=document.querySelector("#addGroceryToList_unit"),addGroceryToList_ID=document.querySelector("#addGroceryToList_ID"),addGroceryToList_notice=document.querySelector("#addGroceryToList_notice"),shoppingListEntries=document.querySelector(".shopping-list-entries"),loadingIconShoppingListEntries=document.querySelector("#loadingIconShoppingListEntries"),loadMoreShoppingListEntries=document.querySelector("#loadMoreShoppingListEntries"),filterSearchRecipes=document.getElementById("filterSearchRecipes");let shoppingListEntriesCount=0;const count=50,newShoppinglistEntriesAlert=document.querySelector("#new-shoppinglist-entries-alert");async function loadShoppingListEntries(){loadingIconShoppingListEntries.classList.remove("hidden"),loadMoreShoppingListEntries.classList.add("hidden"),renderShoppingListEntries(await getShoppingListEntries())}async function getShoppingListEntries(){if(null!==shoppingListEntries){let e=shoppingListEntries.querySelectorAll(".shopping-list-entry").length,t=jsObject.recipes_shoppinglistentries_get+"?count=50&start="+e;return fetch(t,{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/json"}}).then((function(e){return e.json()})).catch((function(e){console.log(e)}))}return emptyPromise()}function renderShoppingListEntries(e){if("error"!==e.status){loadingIconShoppingListEntries.classList.add("hidden");let t=parseInt(e.count);if(shoppingListEntriesCount=t,t>0){shoppingListEntries.querySelectorAll(".shopping-list-entry").length+50<t?loadMoreShoppingListEntries.classList.remove("hidden"):loadMoreShoppingListEntries.classList.add("hidden"),e.data.entries.forEach((function(e){let t=createShoppinglistEntry(e.id,e.unit,e.amount,e.grocery,e.notice,e.done);shoppingListEntries.append(t)}))}else{let e=document.createElement("p");e.innerHTML=lang.nothing_found,shoppingListEntries.innerHTML="",shoppingListEntries.appendChild(e),loadMoreShoppingListEntries.classList.add("hidden")}}}function loadMoreShoppingListEntriesFunctions(){if(null!==loadMoreShoppingListEntries){loadMoreShoppingListEntries.addEventListener("click",(async function(e){loadShoppingListEntries()}));let e=100;document.addEventListener("scroll",(async function(){let t=document.body,n=document.documentElement;(n.scrollTop>0&&n.scrollTop+n.clientHeight+e>=n.scrollHeight||t.scrollTop>0&&t.scrollTop+t.clientHeight+e>=t.scrollHeight)&&(loadMoreShoppingListEntries.classList.contains("hidden")||loadShoppingListEntries())}))}}async function addEntryToList(){document.querySelector("#groceries-suggestion-list").classList.add("hidden"),document.querySelectorAll(".alert.flash-message").forEach((function(e){e.classList.add("hidden")}));var e={amount:addGroceryToList_amount.value,grocery_input:addGroceryToList_name.value,unit:addGroceryToList_unit.value,notice:addGroceryToList_notice.value,id:addGroceryToList_ID.value};try{let t=await getCSRFToken();e.csrf_name=t.csrf_name,e.csrf_value=t.csrf_value;let n=await fetch(jsObject.recipes_shoppinglists_add_entry,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)}),i=await n.json();if("success"===i.status){let t=createShoppinglistEntry(i.id,e.unit,e.amount,i.entry.name,e.notice);0==shoppingListEntries.querySelectorAll("li").length&&(shoppingListEntries.innerHTML=""),shoppingListEntries.prepend(t),shoppingListEntriesCount+=1}else showToast(lang.recipes_shoppinglist_error_add,"red")}catch(t){if(console.log(t),document.body.classList.contains("offline")){let t=new URLSearchParams(e).toString();saveDataWhenOffline(jsObject.recipes_shoppinglists_add_entry,"POST",t)}}finally{loadingWindowOverlay.classList.add("hidden"),addGroceryToList_amount.value="",addGroceryToList_name.value="",addGroceryToList_unit.value="",addGroceryToList_notice.value="",addGroceryToList_ID.value="",autocompleteJS.close()}}function createShoppinglistEntry(e,t,n,i,s,o){let r=document.createElement("li");r.classList.add("shopping-list-entry"),r.classList.add("custom-checkbox");let a=document.createElement("input");a.type="checkbox",a.id=e,a.name=e,a.dataset.id=e,a.autocomplete="off",a.dataset.url=jsObject.recipes_shoppinglistentries_set_state,o&&(a.checked="checked"),r.appendChild(a);let d=document.createElement("label");d.htmlFor=e;let c=n&&t?'<span class="unit">'+t+"</span> ":"",l=s?'<span class="notice"> ('+s+")</span> ":"";d.innerHTML='<span class="amount">'+(n||"")+"</span> "+c+i+l,r.append(d);let p=document.createElement("a");return p.href="#",p.dataset.url=jsObject.recipes_shoppinglists_delete_entry+e,p.classList.add("btn-delete"),p.innerHTML=document.getElementById("iconTrash").innerHTML,r.append(p),r}document.addEventListener("DOMContentLoaded",(async function(){loadMoreShoppingListEntriesFunctions(),loadShoppingListEntries()})),addGroceryToList.addEventListener("click",(function(e){e.preventDefault(),addEntryToList()})),addGroceryToList_name.addEventListener("keydown",(function(e){13===e.keyCode&&(e.preventDefault(),loadingWindowOverlay.classList.remove("hidden"),addEntryToList())}));const autocompleteJS=new autoComplete({data:{src:async()=>{try{let e=await fetch(jsObject.groceries_search+"?query="+addGroceryToList_name.value,{method:"GET",credentials:"same-origin"}),t=await e.json();return"error"!==t.status?t.data:[]}catch(e){return[]}},keys:["text"],cache:!1},resultsList:{class:"groceries-suggestion",id:"groceries-suggestion-list",destination:"#groceries-suggestion-wrapper",position:"beforeend",tag:"ul",noResults:!0,maxResults:10},resultItem:{highlight:!0},trigger:e=>e.length>0,threshold:1,debounce:100,selector:"#addGroceryToList_name",wrapper:!1});addGroceryToList_name.addEventListener("selection",(function(e){const t=e.detail;addGroceryToList_name.value=t.selection.value.name,""==addGroceryToList_unit.value&&(addGroceryToList_unit.value=t.selection.value.unit),addGroceryToList_ID.value=t.selection.value.id})),addGroceryToList_name.addEventListener("results",(function(e){0==e.detail.results.length?document.querySelector("#groceries-suggestion-list").classList.add("hidden"):document.querySelector("#groceries-suggestion-list").classList.remove("hidden")})),addGroceryToList_name.addEventListener("close",(function(e){document.querySelector("#groceries-suggestion-list").classList.add("hidden")})),setInterval((async function(){let e=await getShoppingListEntries();if("error"!==e.status){let t=parseInt(e.count);shoppingListEntriesCount!==t&&newShoppinglistEntriesAlert.classList.remove("hidden")}}),1e4);