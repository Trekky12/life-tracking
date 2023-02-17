"use strict";const stepsIngredientsSelect=document.querySelectorAll(".ingredient-select");stepsIngredientsSelect.forEach((function(e,t){e.addEventListener("click",(function(t){let c=e.dataset.id;document.querySelectorAll('.ingredient-select[data-id="'+c+'"]').forEach((function(t){t.classList.toggle("active"),t.checked=e.checked}))}))}));const recipesList=document.querySelector("#recipes_list"),loadingIconRecipes=document.querySelector("#loadingIconRecipes"),loadMoreRecipes=document.querySelector("#loadMoreRecipes"),filterSearchRecipes=document.getElementById("filterSearchRecipes");function getRecipes(e=!1){if(null!==recipesList){let t=e?0:recipesList.querySelectorAll(".recipe").length,c=20,i=filterSearchRecipes?filterSearchRecipes.value:"",n="list";loadingIconRecipes.classList.remove("hidden"),loadMoreRecipes.classList.add("hidden");let s=jsObject.recipes_get+"?type="+n+"&count="+c+"&start="+t+"&query="+i;return void 0!==recipesList.dataset.cookbook&&(s=s+"&cookbook="+recipesList.dataset.cookbook),fetch(s,{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/json"}}).then((function(e){return e.json()})).then((function(i){if(e&&(recipesList.innerHTML=""),"error"!==i.status){loadingIconRecipes.classList.add("hidden");let e=parseInt(i.count);if(e>0)t+c<e?loadMoreRecipes.classList.remove("hidden"):loadMoreRecipes.classList.add("hidden"),recipesList.insertAdjacentHTML("beforeend",i.data);else{let e=document.createElement("p");e.innerHTML=lang.nothing_found,recipesList.innerHTML="",recipesList.appendChild(e),loadMoreRecipes.classList.add("hidden")}}})).catch((function(e){console.log(e)}))}return emptyPromise()}function loadMoreRecipesFunctions(){if(null!==loadMoreRecipes){loadMoreRecipes.addEventListener("click",(function(e){getRecipes()}));let e=100;document.addEventListener("scroll",(function(){let t=document.body,c=document.documentElement;(c.scrollTop>0&&c.scrollTop+c.clientHeight+e>=c.scrollHeight||t.scrollTop>0&&t.scrollTop+t.clientHeight+e>=t.scrollHeight)&&(loadMoreRecipes.classList.contains("hidden")||getRecipes())}))}}document.addEventListener("DOMContentLoaded",(function(){loadMoreRecipesFunctions(),getRecipes()})),filterSearchRecipes&&filterSearchRecipes.addEventListener("keyup",(function(e){e.preventDefault(),getRecipes(!0)}));