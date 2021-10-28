"use strict";const projectCategorySelects=document.querySelectorAll("select.category");projectCategorySelects.forEach((function(e,t){new Selectr(e,{searchable:!0,placeholder:lang.categories,messages:{noResults:lang.nothing_found,noOptions:lang.no_options}})}));const dateTimePickerStart=document.querySelector("#datetimePickerStart"),dateTimePickerEnd=document.querySelector("#datetimePickerEnd");dateTimePickerStart&&dateTimePickerEnd&&(flatpickr(dateTimePickerStart,{altInput:!0,altFormat:i18n.dateformatTwig.datetimeShort,dateFormat:"Y-m-d H:i",locale:i18n.template,enableTime:!0,time_24hr:!0,minuteIncrement:1,onValueUpdate:function(e){dateTimePickerEnd._flatpickr.setDate(e[0])}}),flatpickr(dateTimePickerEnd,{altInput:!0,altFormat:i18n.dateformatTwig.datetimeShort,dateFormat:"Y-m-d H:i",locale:i18n.template,enableTime:!0,time_24hr:!0,minuteIncrement:1}));const radioDurationCustomModification=document.getElementById("radioDurationCustom"),radioDurationNoModification=document.getElementById("radioDurationReal"),radioDurationRateModification=document.getElementById("radioDurationProjectRate"),inputDurationModificationWrapper=document.getElementById("inputDurationModificationWrapper");radioDurationCustomModification&&radioDurationNoModification&&radioDurationRateModification&&inputDurationModificationWrapper&&(radioDurationCustomModification.addEventListener("click",(function(e){radioDurationCustomModification.checked?(inputDurationModificationWrapper.classList.remove("hidden"),inputDurationModificationWrapper.querySelector("input").disabled=!1):(inputDurationModificationWrapper.classList.add("hidden"),inputDurationModificationWrapper.querySelector("input").disabled=!0)})),radioDurationNoModification.addEventListener("click",(function(e){inputDurationModificationWrapper.classList.add("hidden"),inputDurationModificationWrapper.querySelector("input").disabled=!0})),radioDurationRateModification.addEventListener("click",(function(e){inputDurationModificationWrapper.classList.add("hidden"),inputDurationModificationWrapper.querySelector("input").disabled=!0})));const assignCategoriesSelector=document.getElementById("assignCategoriesSelector"),assignCategoriesBtn=document.getElementById("assign_categories"),removeCategoriesBtn=document.getElementById("remove_categories");if(assignCategoriesSelector&&assignCategoriesBtn&&removeCategoriesBtn){let e=new Selectr(assignCategoriesSelector,{searchable:!0,placeholder:lang.categories,messages:{noResults:lang.nothing_found,noOptions:lang.no_options}});assignCategoriesBtn.addEventListener("click",(function(t){let i=e.getValue(),o=[];document.querySelectorAll('#timesheets_sheets_table tbody input[type="checkbox"]').forEach((function(e){e.checked&&o.push(e.dataset.id)})),setCategories({sheets:o,categories:i,type:"assign"})})),removeCategoriesBtn.addEventListener("click",(function(t){let i=e.getValue(),o=[];document.querySelectorAll('#timesheets_sheets_table tbody input[type="checkbox"]').forEach((function(e){e.checked&&o.push(e.dataset.id)})),setCategories({sheets:o,categories:i,type:"remove"})}))}function setCategories(e){return getCSRFToken().then((function(t){return e.csrf_name=t.csrf_name,e.csrf_value=t.csrf_value,fetch(jsObject.timesheets_sheets_set_categories,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})})).then((function(e){return e.json()})).then((function(e){console.log(e),allowedReload=!0,window.location.reload()})).catch((function(t){if(console.log(t),document.body.classList.contains("offline")){let t=new URLSearchParams(e).toString();saveDataWhenOffline(jsObject.timesheets_sheets_set_categories,"POST",t)}}))}document.addEventListener("click",(function(e){let t=e.target.closest("#checkAllRows");if(t){document.querySelectorAll('#timesheets_sheets_table tbody input[type="checkbox"]').forEach((function(e){t.checked?e.checked=!0:e.checked=!1}))}}));const radioBudgetCount=document.getElementById("radioCategorization1"),radioBudgetDuration=document.getElementById("radioCategorization2"),radioBudgetDurationModified=document.getElementById("radioCategorization3");radioBudgetDuration&&radioBudgetDurationModified&&radioBudgetCount&&(radioBudgetDuration.addEventListener("click",(function(e){document.querySelectorAll(".html-duration-picker-wrapper").forEach((function(e){e.classList.remove("hidden")})),document.querySelectorAll("input.duration-input").forEach((function(e){e.classList.remove("hidden"),e.removeAttribute("disabled"),e.classList.contains("html-duration-picker")||e.classList.add("html-duration-picker"),HtmlDurationPicker.refresh()})),document.querySelectorAll("input.count-input").forEach((function(e){e.classList.add("hidden"),e.setAttribute("disabled",!0)}))})),radioBudgetDurationModified.addEventListener("click",(function(e){document.querySelectorAll(".html-duration-picker-wrapper").forEach((function(e){e.classList.remove("hidden")})),document.querySelectorAll("input.duration-input").forEach((function(e){e.classList.remove("hidden"),e.removeAttribute("disabled"),e.classList.contains("html-duration-picker")||e.classList.add("html-duration-picker"),HtmlDurationPicker.refresh()})),document.querySelectorAll("input.count-input").forEach((function(e){e.classList.add("hidden"),e.setAttribute("disabled",!0)}))})),radioBudgetCount.addEventListener("click",(function(e){radioBudgetCount.checked?(document.querySelectorAll(".html-duration-picker-wrapper").forEach((function(e){e.classList.add("hidden")})),document.querySelectorAll("input.duration-input").forEach((function(e){e.classList.add("hidden"),e.setAttribute("disabled",!0)})),document.querySelectorAll("input.count-input").forEach((function(e){e.classList.remove("hidden"),e.removeAttribute("disabled")}))):(document.querySelectorAll(".html-duration-picker-wrapper").forEach((function(e){e.classList.remove("hidden")})),document.querySelectorAll("input.duration-input").forEach((function(e){e.classList.remove("hidden"),e.removeAttribute("disabled"),e.classList.contains("html-duration-picker")||e.classList.add("html-duration-picker"),HtmlDurationPicker.refresh()})),document.querySelectorAll("input.count-input").forEach((function(e){e.classList.add("hidden"),e.setAttribute("disabled",!0)})))})));