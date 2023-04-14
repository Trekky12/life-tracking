"use strict";const timesheetNoticeWrapper=document.querySelector("#timesheetNoticeWrapper"),loadingIconTimesheetNotice=document.querySelector("#loadingIconTimesheetNotice"),timesheetNoticeForm=document.querySelector("#timesheetNoticeForm"),alertError=document.querySelector("#alertError"),alertErrorDetail=alertError.querySelector("#alertErrorDetail"),projectID=parseInt(timesheetNoticeWrapper.dataset.project);let aesKey,KEK;async function checkPassword(){if(aesKey=await getAESKeyFromStore(),aesKey)try{let e=await getEncryptionParameters();await decryptTestMessageAndKEK(aesKey,e.data.test,e.data.KEK)}catch(e){alertErrorDetail.innerHTML=lang.decrypt_error,alertError.classList.remove("hidden");let t=await getStore();await t.delete(projectID);return void loadingIconTimesheetNotice.classList.add("hidden")}else{let e=window.prompt(lang.timesheets_notice_password);try{await createAndStoreAESKey(e),aesKey=await getAESKeyFromStore()}catch(e){return alertErrorDetail.innerHTML=lang.decrypt_error,alertError.classList.remove("hidden"),void loadingIconTimesheetNotice.classList.add("hidden")}}let e=Array.from(timesheetNoticeWrapper.querySelectorAll(".timesheet-notice"));for(const t of e){let e,r=parseInt(t.dataset.sheet);try{e=await getNotice(r)}catch(e){return alertErrorDetail.innerHTML=lang.encrypt_error,alertError.classList.remove("hidden"),void document.getElementById("loading-overlay").classList.add("hidden")}if(e)if(IsJsonString(e)){e=JSON.parse(e);for(const[r,a]of Object.entries(e)){let a=e[r],o=t.querySelector('[data-name="'+r+'"]');o&&(!o.tagName||"textarea"!==o.tagName.toLowerCase()&&"input"!==o.tagName.toLowerCase()&&"select"!==o.tagName.toLowerCase()?a?o.innerHTML=a.replace(/(?:\r\n|\r|\n)/g,"<br>"):o.parentElement.dataset.empty=1:o.value=a)}}else{let r=t.querySelector('[data-default="1"]');r&&r.tagName&&("textarea"===r.tagName.toLowerCase()||"input"===r.tagName.toLowerCase()||"select"===r.tagName.toLowerCase())?r.value=e:(e?r.innerHTML=e.replace(/(?:\r\n|\r|\n)/g,"<br>"):r.parentElement.dataset.empty=1,t.querySelectorAll('[data-default="0"]').forEach((function(e){e.parentElement.dataset.empty=1})))}else t.closest(".timesheet-notice-wrapper").dataset.empty=1}loadingIconTimesheetNotice.classList.add("hidden"),timesheetNoticeWrapper.classList.remove("hidden")}async function getNotice(e){if(!KEK)throw console.error("KEK missing"),"KEK missing";let t=await fetch(jsObject.timesheets_sheets_notice_data+"?sheet="+e,{method:"GET",credentials:"same-origin"}),r=await t.json();if("error"!==r.status&&r.entry){let e,t=r.entry.notice,a=r.entry.CEK;try{const t=await decryptData(KEK,a),r=base64_to_buf(t);e=await createKey(r)}catch(e){throw console.error(`Unable to decrypt CEK - ${e}`),e}if(t)try{return await decryptData(e,t)}catch(e){throw console.error(`Unable to decrypt notice - ${e}`),e}}}async function getEncryptionParameters(){let e={},t=await getCSRFToken();e.csrf_name=t.csrf_name,e.csrf_value=t.csrf_value;let r=await fetch(jsObject.timesheets_notice_params,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)}),a=await r.json();if("success"!==a.status)throw"Unable to retrieve parameters";return a}async function decryptTestMessageAndKEK(e,t,r){try{if("test"!==await decryptData(e,t))throw"Wrong message!"}catch(e){throw console.error(`Unable to decrypt test message - ${e}`),e}try{const t=await decryptData(e,r),a=base64_to_buf(t);KEK=await createKey(a)}catch(e){throw console.error(`Unable to decrypt KEK - ${e}`),e}return 0}async function createAndStoreAESKey(e){let t=await getEncryptionParameters();const r=t.data.iterations,a=base64_to_buf(t.data.salt),o=await createKeyMaterial(e),i=await deriveAESKey(o,a,r);return await decryptTestMessageAndKEK(i,t.data.test,t.data.KEK),(await getStore()).add({project:projectID,key:i}),0}async function getStore(){if("indexedDB"in window){let e=indexedDB.open("lifeTrackingData",2);return new Promise((function(t,r){e.onsuccess=function(){var r=e.result.transaction("keys","readwrite").objectStore("keys");t(r)}}))}}async function getAESKeyFromStore(){let e=await getStore();return await new Promise((function(t,r){let a=e.get(projectID);a.onsuccess=function(e){a.result&&t(a.result.key),t(null)}}))}function IsJsonString(e){try{JSON.parse(e)}catch(e){return!1}return!0}window.crypto&&window.crypto.subtle||(alertErrorDetail.innerHTML=lang.decrypt_error,alertError.classList.remove("hidden"),timesheetNoticeForm&&(timesheetNoticeForm.querySelectorAll("textarea, select, input").forEach((function(e){e.disabled=!0})),timesheetNoticeForm.querySelector('button[type="submit"]').classList.add("hidden"))),checkPassword(),timesheetNoticeForm&&timesheetNoticeForm.addEventListener("submit",(async function(e){if(e.preventDefault(),!KEK)return alertErrorDetail.innerHTML=lang.encrypt_error,alertError.classList.remove("hidden"),void document.getElementById("loading-overlay").classList.add("hidden");alertError.classList.add("hidden"),alertErrorDetail.innerHTML="",document.getElementById("loading-overlay").classList.remove("hidden");let t={};const r=window.crypto.getRandomValues(new Uint8Array(32)),a=await createKey(r);t.CEK=await encryptData(KEK,buff_to_base64(r));let o=Array.from(timesheetNoticeForm.querySelectorAll('input[type="text"], textarea, select'));if(o.length>1){let e={};for(const t of o)t.tagName&&"select"===t.tagName.toLowerCase()?t.selectedIndex>=0&&(e[t.name]=t.options[t.selectedIndex].text):e[t.name]=t.value;t.notice=await encryptData(a,JSON.stringify(e))}else t.notice=await encryptData(a,o[0].value);try{let e=await getCSRFToken();t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value;let r=await fetch(timesheetNoticeForm.action,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)});"success"===(await r.json()).status?(allowedReload=!0,window.location.reload(!0)):(document.getElementById("loading-overlay").classList.add("hidden"),alertErrorDetail.innerHTML=t.message,alertError.classList.remove("hidden"))}catch(e){if(console.log(e),document.body.classList.contains("offline")){let e=new URLSearchParams(t).toString();saveDataWhenOffline(timesheetNoticeForm.action,timesheetNoticeForm.method,e)}else document.getElementById("loading-overlay").classList.add("hidden"),alertErrorDetail.innerHTML=e,alertError.classList.remove("hidden")}}));let checkboxHideEmptySheets=document.getElementById("checkboxHideEmptySheets");checkboxHideEmptySheets&&checkboxHideEmptySheets.addEventListener("click",(function(e){document.querySelectorAll('.timesheet-notice-wrapper[data-empty="1"]').forEach((function(e){checkboxHideEmptySheets.checked?e.classList.add("hidden"):e.classList.remove("hidden")}))}));let checkboxHideEmptyNoticeFields=document.getElementById("checkboxHideEmptyNoticeFields");checkboxHideEmptyNoticeFields&&checkboxHideEmptyNoticeFields.addEventListener("click",(function(e){document.querySelectorAll('.timesheet-notice-field[data-empty="1"]').forEach((function(e){checkboxHideEmptyNoticeFields.checked?e.classList.add("hidden"):e.classList.remove("hidden")}))}));