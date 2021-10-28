"use strict";const recipesList=document.querySelector("#recipes_list"),loadingIconRecipes=document.querySelector("#loadingIconRecipes"),filterSearchRecipes=document.getElementById("filterSearchRecipes"),noticeModal=document.getElementById("notice-modal"),noticeModalClose=document.getElementById("modal-close-btn");document.addEventListener("click",(function(e){let t=e.target.closest(".minus"),n=e.target.closest(".create-notice");if(t){e.preventDefault();let n=t.parentElement.parentElement,c={mealplan_recipe_id:n.dataset.id};getCSRFToken(!0).then((function(e){return c.csrf_name=e.csrf_name,c.csrf_value=e.csrf_value,fetch(jsObject.recipes_mealplan_remove_recipe,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(c)})})).then((function(e){return e.json()})).then((function(e){e.is_deleted&&n.remove()})).catch((function(e){console.log(e)}))}if(n){e.preventDefault();let t=n.parentElement.querySelector(".recipes-target").dataset.date;noticeModal.querySelector("input[name='date']").value=t,freeze(),noticeModal.classList.add("visible")}}));const recipesTargets=document.querySelectorAll(".recipes-target");function createSortable(e,t){if(document.querySelector(".mealplan-list-settings")){let n={group:{name:"recipes"},swapThreshold:.5,fallbackOnBody:!0,handle:".handle",dataIdAttr:"data-id",onUpdate:function(e){move_recipe(e)},onAdd:function(e){move_recipe(e)}};t&&(n.group.pull="clone",n.group.put=!1),new Sortable(e,n)}}function move_recipe(e){let t=e.to.dataset.date,n=e.item.dataset.recipe,c=e.item.dataset.id;createRecipeEntry(e.item,n,t,e.newDraggableIndex,c,null)}function createRecipeEntry(e,t,n,c,i,o){var r={recipe:t,date:n,position:c,id:i,notice:o};return getCSRFToken().then((function(e){return r.csrf_name=e.csrf_name,r.csrf_value=e.csrf_value,fetch(jsObject.recipes_mealplan_move_recipe,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(r)})})).then((function(e){return e.json()})).then((function(t){if("success"===t.status){let n=t.id;e.dataset.id=n,e.querySelector(".minus").classList.remove("hidden")}else e.remove()})).catch((function(e){if(console.log(e),document.body.classList.contains("offline")){let e=new URLSearchParams(r).toString();saveDataWhenOffline(jsObject.recipes_mealplan_move_recipe,"POST",e)}}))}function getRecipes(){let e=filterSearchRecipes?filterSearchRecipes.value:"",t=jsObject.recipes_get_mealplan+"?type=mealplan&count=5&start=0&query="+e;return loadingIconRecipes.classList.remove("hidden"),fetch(t,{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/json"}}).then((function(e){return e.json()})).then((function(e){if(recipesList.innerHTML="","error"!==e.status){if(loadingIconRecipes.classList.add("hidden"),parseInt(e.count)>0)recipesList.insertAdjacentHTML("beforeend",e.data),createSortable(recipesList,!0);else{let e=document.createElement("p");e.innerHTML=lang.nothing_found,recipesList.innerHTML="",recipesList.appendChild(e)}}})).catch((function(e){console.log(e)}))}recipesTargets.forEach((function(e,t){createSortable(e,!1)})),getRecipes(),filterSearchRecipes&&filterSearchRecipes.addEventListener("keyup",(function(e){e.preventDefault(),getRecipes()})),noticeModalClose&&noticeModalClose.addEventListener("click",(function(e){noticeModal.classList.remove("visible"),noticeModal.querySelector("form").reset(),unfreeze()})),noticeModal.querySelector("form").addEventListener("submit",(function(e){e.preventDefault(),document.getElementById("loading-overlay").classList.remove("hidden");let t=noticeModal.querySelector('input[name="notice"]').value,n=noticeModal.querySelector('input[name="date"]').value,c=document.querySelector(".recipes-target[data-date='"+n+"']"),i=document.querySelector("#templates .mealplan-recipe").cloneNode(!0);i.querySelector("h3.title").innerHTML=t,c.appendChild(i),createRecipeEntry(i,null,n,999,null,t).then((function(){document.getElementById("loading-overlay").classList.add("hidden"),unfreeze(),noticeModal.querySelector("form").reset(),noticeModal.classList.remove("visible")}))}));