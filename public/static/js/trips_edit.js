"use strict";const imageInput=document.querySelector('input[type="file"]'),deleteImage=document.querySelector("button#delete_image"),eventImage=document.getElementById("event_image"),loadingIcon=document.getElementById("loadingIconImageUpload");imageInput&&imageInput.addEventListener("change",(function(e){getCSRFToken().then((function(e){loadingIcon.classList.remove("hidden");var t=new FormData;return t.append("image",imageInput.files[0]),t.append("csrf_name",e.csrf_name),t.append("csrf_value",e.csrf_value),fetch(jsObject.trip_event_image_upload,{method:"POST",credentials:"same-origin",body:t}).then((function(e){return e.json()})).then((function(e){loadingIcon.classList.add("hidden"),"success"===e.status?(eventImage.classList.remove("hidden"),eventImage.src=e.thumbnail,deleteImage.classList.remove("hidden"),imageInput.value=""):(eventImage.classList.add("hidden"),eventImage.src="",deleteImage.classList.add("hidden"))})).catch((function(e){loadingIcon.classList.add("hidden"),console.log(e)}))}))})),deleteImage&&deleteImage.addEventListener("click",(function(e){e.preventDefault(),getCSRFToken().then((function(e){return loadingIcon.classList.remove("hidden"),fetch(jsObject.trip_event_image_delete,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)})})).then((function(e){loadingIcon.classList.add("hidden"),eventImage.classList.add("hidden"),eventImage.src="",deleteImage.classList.add("hidden")})).catch((function(e){loadingIcon.classList.add("hidden"),console.log(e)}))}));const datepickerStartEvent=document.getElementById("inputStartEvent"),datepickerEndEvent=document.getElementById("inputEndEvent");datepickerStartEvent&&datepickerEndEvent&&(flatpickr(datepickerStartEvent,{altInput:!0,altFormat:i18n.dateformatTwig.date,altInputClass:"input",dateFormat:"Y-m-d",locale:i18n.template,onValueUpdate:function(e){0==datepickerEndEvent.value.length&&datepickerEndEvent._flatpickr.setDate(e[0])}}),flatpickr(datepickerEndEvent,{altInput:!0,altFormat:i18n.dateformatTwig.date,altInputClass:"input",dateFormat:"Y-m-d",locale:i18n.template}));