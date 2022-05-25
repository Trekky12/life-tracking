"use strict";const comeButtons=document.querySelectorAll(".timesheet-fast-come-btn"),leaveButtons=document.querySelectorAll(".timesheet-fast-leave-btn"),timesheetLatField=document.querySelector("input#geoLat"),timesheetLngField=document.querySelector("input#geoLng"),timesheetAccField=document.querySelector("input#geoAcc");function createTimesheet(e,t){let s=document.querySelector("#alertErrorTimesheetFast"),n=s.querySelector("#alertErrorDetailTimesheetFast");const c=document.querySelector("#alertSuccessTimesheetFast");s.classList.add("hidden"),c.classList.add("hidden"),n.innerHTML="";let o=e.dataset.url,r={};timesheetLatField&&(r[t+"_lat"]=timesheetLatField.value),timesheetLngField&&(r[t+"_lng"]=timesheetLngField.value),timesheetAccField&&(r[t+"_acc"]=timesheetAccField.value);let l=document.querySelector("#category-filter");if(l){let e=Array.from(l.selectedOptions).map((e=>e.value));r.category=e}return getCSRFToken().then((function(e){return r.csrf_name=e.csrf_name,r.csrf_value=e.csrf_value,fetchWithTimeout(o,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(r)})})).then((function(e){return e.json()})).then((function(t){if("success"===t.status){if(c.classList.remove("hidden"),1===t.data&&!e.classList.contains("no-toggle")){e.closest(".grid").querySelectorAll(".card").forEach((function(e,t){e.classList.toggle("inactive")}))}}else n.innerHTML=t.message,s.classList.remove("hidden")})).catch((function(e){console.log(e),n.innerHTML=lang.request_error,s.classList.remove("hidden")}))}document.addEventListener("click",(function(e){let t=e.target.closest(".timesheet-fast-come-btn"),s=e.target.closest(".timesheet-fast-leave-btn");t&&(e.preventDefault(),createTimesheet(t,"start")),s&&(e.preventDefault(),createTimesheet(s,"end"))}));const projectCategorySelects=document.querySelectorAll("select.category");projectCategorySelects.forEach((function(e,t){new Selectr(e,{searchable:!0,placeholder:lang.categories,messages:{noResults:lang.nothing_found,noOptions:lang.no_options}})}));