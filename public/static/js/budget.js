"use strict";function add_selectr(e){new Selectr(e,{searchable:!1,placeholder:lang.categories})}function get_recurring_costs(e){var t=e.closest(".budget-entry").querySelector(".category_costs");t.innerHTML='<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>';for(var n=e.selectedOptions,c=[],r=0;r<n.length;r++)c.push(n[r].value);fetch(jsObject.get_category_costs+"?category[]="+c.join("&category[]="),{method:"GET",credentials:"same-origin"}).then(function(e){return e.json()}).then(function(e){var n="";n=e.value>0?e.value+" "+i18n.currency:"-",t.innerHTML=n}).catch(function(e){console.log(e)})}const budgetSelect=document.querySelectorAll("select.category");budgetSelect.forEach(function(e,t){get_recurring_costs(e),add_selectr(e)});var template=document.getElementById("budgetTemplate").innerHTML;Mustache.parse(template),document.getElementById("add_budget").addEventListener("click",function(e){e.preventDefault();var t=document.querySelectorAll(".budget-entry").length,n=Mustache.render(template,{index:t});document.querySelector("#budgetForm .budget-entry.remaining").insertAdjacentHTML("beforebegin",n),add_selectr("#category_"+t)}),document.addEventListener("change",function(e){let t=e.target.closest("select.category");if(t){get_recurring_costs(t);let e=t.closest(".budget-entry").querySelector("input.description");0===e.value.length&&(e.value=t.options[t.selectedIndex].text)}}),["change","keyup"].forEach(function(e){document.addEventListener(e,function(e){if(e.target.closest("input.value")){let e=document.getElementById("remaining_budget");var t=parseFloat(e.dataset.income),n=0;document.querySelectorAll("input.value").forEach(function(e,t){e.value&&(n+=parseFloat(e.value))}),e.innerHTML=t-n}})});