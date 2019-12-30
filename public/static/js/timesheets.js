"use strict";const comeButtons=document.querySelectorAll(".timesheet-fast-come-btn"),leaveButtons=document.querySelectorAll(".timesheet-fast-leave-btn"),timesheetLatField=document.querySelector("input#geoLat"),timesheetLngField=document.querySelector("input#geoLng"),timesheetAccField=document.querySelector("input#geoAcc"),alertSuccess=document.querySelector("#alertSuccess"),alertError=document.querySelector("#alertError"),alertErrorDetail=alertError.querySelector("#alertErrorDetail");function send(e,t){alertError.classList.add("hidden"),alertSuccess.classList.add("hidden"),alertErrorDetail.innerHTML="";let r=e.dataset.url,n={};return n[t+"_lat"]=timesheetLatField.value,n[t+"_lng"]=timesheetLngField.value,n[t+"_acc"]=timesheetAccField.value,getCSRFToken().then((function(e){return n.csrf_name=e.csrf_name,n.csrf_value=e.csrf_value,fetchWithTimeout(r,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(n)})})).then((function(e){return e.json()})).then((function(t){if("success"===t.status){if(alertSuccess.classList.remove("hidden"),1===t.data){e.closest(".grid").querySelectorAll(".card").forEach((function(e,t){e.classList.toggle("inactive")}))}}else alertErrorDetail.innerHTML=t.message,alertError.classList.remove("hidden")})).catch((function(e){console.log(e),alertErrorDetail.innerHTML=lang.request_error,alertError.classList.remove("hidden")}))}comeButtons.forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault(),send(e,"start")}))})),leaveButtons.forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault(),send(e,"end")}))}));