"use strict";const timesheetNoticeWrapper=document.querySelector("#timesheetNoticeWrapper"),loadingIconTimesheetNotice=document.querySelector("#loadingIconTimesheetNotice"),timesheetNoticeForm=document.querySelector("#timesheetNoticeForm"),alertError=document.querySelector("#alertError"),alertErrorDetail=alertError.querySelector("#alertErrorDetail"),projectID=parseInt(timesheetNoticeWrapper.dataset.project);let aesKey;async function checkPassword(){if(aesKey=await getAESKeyFromStore(),!aesKey){let t=window.prompt(lang.timesheets_notice_password);var e={password:t};let r=await getCSRFToken();e.csrf_name=r.csrf_name,e.csrf_value=r.csrf_value;let a=await fetch(jsObject.timesheets_sheets_check_pw,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)}),n=await a.json();if("success"===n.status){const e=base64_to_buf(n.data),r=await createKeyMaterial(t),a=await createsAESKey(r,e);(await getStore()).add({project:projectID,key:a}),aesKey=await getAESKeyFromStore()}else if("error"===n.status&&n.reason)return alertErrorDetail.innerHTML=n.reason,alertError.classList.remove("hidden"),void loadingIconTimesheetNotice.classList.add("hidden")}let t=Array.from(timesheetNoticeWrapper.querySelectorAll(".timesheet-notice"));for(const e of t){let t=parseInt(e.dataset.sheet),r=await getNotice(t);if(r){let t=e.querySelector('[data-default="1"]');if(IsJsonString(r)){r=JSON.parse(r);for(const[t,a]of Object.entries(r)){let a=r[t],n=e.querySelector('[data-name="'+t+'"]');n&&(!n.tagName||"textarea"!==n.tagName.toLowerCase()&&"input"!==n.tagName.toLowerCase()&&"select"!==n.tagName.toLowerCase()?n.innerHTML=a.replace(/(?:\r\n|\r|\n)/g,"<br>"):n.value=a)}}else t&&t.tagName&&"textarea"===t.tagName.toLowerCase()?t.value=r:t.innerHTML=r.replace(/(?:\r\n|\r|\n)/g,"<br>")}}loadingIconTimesheetNotice.classList.add("hidden"),timesheetNoticeWrapper.classList.remove("hidden")}async function getNotice(e){let t=await fetch(jsObject.timesheets_sheets_notice_data+"?sheet="+e,{method:"GET",credentials:"same-origin"}),r=await t.json();if("error"!==r.status&&r.entry){let e=r.entry.notice;if(e){if(!aesKey)return alertErrorDetail.innerHTML=lang.decrypt_error,void alertError.classList.remove("hidden");return await decryptData(e)}}}function createKeyMaterial(e){let t=new TextEncoder;return window.crypto.subtle.importKey("raw",t.encode(e),{name:"PBKDF2"},!1,["deriveBits","deriveKey"])}function createsAESKey(e,t){return window.crypto.subtle.deriveKey({name:"PBKDF2",salt:t,iterations:25e4,hash:"SHA-256"},e,{name:"AES-GCM",length:256},!0,["encrypt","decrypt"])}async function encryptData(e){try{const t=window.crypto.getRandomValues(new Uint8Array(12)),r=await window.crypto.subtle.encrypt({name:"AES-GCM",iv:t},aesKey,(new TextEncoder).encode(e)),a=new Uint8Array(r);let n=new Uint8Array(t.byteLength+a.byteLength);n.set(t,0),n.set(a,t.byteLength);return buff_to_base64(n)}catch(e){return console.log(`Error - ${e}`),alertErrorDetail.innerHTML=lang.encrypt_error,alertError.classList.remove("hidden"),""}}function buff_to_base64(e){return btoa(String.fromCharCode.apply(null,e))}function base64_to_buf(e){return Uint8Array.from(atob(e),(e=>e.charCodeAt(null)))}async function getStore(){if("indexedDB"in window){let e=indexedDB.open("lifeTrackingData",2);return new Promise((function(t,r){e.onsuccess=function(){var r=e.result.transaction("keys","readwrite").objectStore("keys");t(r)}}))}}async function getAESKeyFromStore(){let e=await getStore();return await new Promise((function(t,r){let a=e.get(projectID);a.onsuccess=function(e){a.result&&t(a.result.key),t(null)}}))}async function decryptData(e){try{const t=base64_to_buf(e),r=t.slice(0,12),a=t.slice(12),n=await window.crypto.subtle.decrypt({name:"AES-GCM",iv:r},aesKey,a);return(new TextDecoder).decode(n)}catch(e){return console.log(`Error - ${e}`),alertErrorDetail.innerHTML=lang.decrypt_error,alertError.classList.remove("hidden"),""}}function IsJsonString(e){try{JSON.parse(e)}catch(e){return!1}return!0}window.crypto&&window.crypto.subtle||(alertErrorDetail.innerHTML=lang.decrypt_error,alertError.classList.remove("hidden"),timesheetNoticeForm&&(timesheetNoticeForm.querySelectorAll("textarea, select, input").forEach((function(e){e.disabled=!0})),timesheetNoticeForm.querySelector('button[type="submit"]').classList.add("hidden"))),checkPassword(),timesheetNoticeForm&&timesheetNoticeForm.addEventListener("submit",(async function(e){e.preventDefault(),alertError.classList.add("hidden"),alertErrorDetail.innerHTML="",document.getElementById("loading-overlay").classList.remove("hidden");let t={},r=Array.from(timesheetNoticeForm.querySelectorAll('input[type="text"], textarea, select'));if(r.length>1){let e={};for(const t of r)t.tagName&&"select"===t.tagName.toLowerCase()?e[t.name]=t.options[t.selectedIndex].text:e[t.name]=t.value;t.notice=await encryptData(JSON.stringify(e))}else t.notice=await encryptData(r[0].value);getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(timesheetNoticeForm.action,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){"success"===e.status?(allowedReload=!0,window.location.reload(!0)):(document.getElementById("loading-overlay").classList.add("hidden"),alertErrorDetail.innerHTML=e.message,alertError.classList.remove("hidden"))})).catch((function(e){console.log(e),document.getElementById("loading-overlay").classList.add("hidden"),alertErrorDetail.innerHTML=e,alertError.classList.remove("hidden")}))})),document.querySelector("#wordExport").addEventListener("click",(function(e){e.preventDefault();let t=timesheetNoticeWrapper.dataset.sheetname?timesheetNoticeWrapper.dataset.sheetname:timesheetNoticeWrapper.dataset.projectname;const r=[];let a=Array.from(timesheetNoticeWrapper.querySelectorAll(".timesheet-notice-wrapper"));for(const e of a){let t=e.querySelector("#sheet_categories"),n=t?t.innerHTML:"",o=e.querySelector("#sheet_title").innerHTML;const i=new docx.Paragraph({heading:docx.HeadingLevel.HEADING_1,children:[new docx.TextRun({text:o})]}),s=new docx.Paragraph({children:[new docx.TextRun({text:n,italics:!0,size:24})],spacing:{after:200}});r.push(i),r.push(s),e.querySelectorAll('input[type="text"], textarea, select, p.notice-field').forEach((function(e){let t=[new docx.TextRun({text:e.previousElementSibling.innerHTML,underline:{}})];const a=("p"===e.tagName.toLowerCase()?e.innerHTML.replace(/<br ?\/?>/g,"\n"):e.value).split("\n").map((e=>new docx.TextRun({text:e,break:1})));t.push(...a);const n=new docx.Paragraph({children:t,spacing:{after:400}});r.push(n)})),a.length-1!==a.indexOf(e)&&r.push(new docx.Paragraph({children:[new docx.PageBreak]}))}const n=new docx.Document({styles:{default:{heading1:{run:{size:48,color:"000000",font:"Calibri"},paragraph:{spacing:{after:120}}}},paragraphStyles:[{name:"Normal",run:{size:24,font:"Calibri"}}]},sections:[{properties:{},children:r}]});docx.Packer.toBlob(n).then((e=>{saveAs(e,t+"_Export.docx")}))}));