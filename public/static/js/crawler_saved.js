"use strict";let crawlerFavorite=document.querySelectorAll("span.save_crawler_dataset");crawlerFavorite.forEach((function(e,t){e.addEventListener("click",(function(e){var t={state:e.target.classList.contains("is_saved")?0:1,dataset:e.target.dataset.id};if(e.target.classList.contains("is_saved")&&!confirm(lang.really_unsave_dataset))return!1;getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.crawler_dataset_save,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){allowedReload=!0,window.location.reload()})).catch((function(e){console.log(e)}))}))}));