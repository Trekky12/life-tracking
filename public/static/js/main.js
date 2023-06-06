"use strict";window.onunload=function(){},moment.locale(i18n.template),initialize();const loadingWindowOverlay=document.getElementById("loading-overlay");function getCSRFToken(){return tokens.length>1?new Promise((function(e,t){e(tokens.pop())})):getNewTokens(tokens.pop())}function getNewTokens(e){return fetchWithTimeout(jsObject.csrf_tokens_url,{method:"POST",credentials:"same-origin",headers:{Accept:"application/json","Content-Type":"application/json"},body:JSON.stringify({csrf_name:e.csrf_name,csrf_value:e.csrf_value})}).then((function(e){return e.json()})).then((function(e){tokens=e})).then((function(){return tokens.pop()})).catch((function(t){throw tokens.push(e),console.log(t),"No CRSF Tokens available"}))}function deleteObject(e,t){let n=lang.really_delete;if("default"!==t&&(n=t),!confirm(n))return loadingWindowOverlay.classList.add("hidden"),!1;getCSRFToken(!0).then((function(t){return fetch(e,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){allowedReload=!0,"redirect"in e?window.location.href=e.redirect:window.location.reload(!0)})).catch((function(t){console.log(t),loadingWindowOverlay.classList.add("hidden"),document.body.classList.contains("offline")&&saveDataWhenOffline(e,"DELETE")}))}function setCookie(e,t,n,o){n=n||365;var a=new Date;a.setDate(a.getDate()+n);var i=[e+"="+t,"expires="+a.toUTCString(),"path="+(o||"/"),"SameSite=Lax"];document.cookie=i.join(";")}function getCookie(e,t){for(var n=e+"=",o=decodeURIComponent(document.cookie).split(";"),a=0;a<o.length;a++){for(var i=o[a];""==i.charAt(0);)i=i.substring(1);if(i.indexOf(n)>=0)return i.substring(i.indexOf(n)+n.length,i.length)}return t||""}function initialize(){isTouchEnabled()?document.body.classList.add("is-touch-enabled"):document.body.classList.add("no-touch-enabled");let e=document.querySelector("#go-back-btn");null!==e&&e.addEventListener("click",(function(){loadingWindowOverlay.classList.remove("hidden"),window.history.back()}));let t=document.querySelector("#cancel");if(null!==t&&t.addEventListener("click",(function(e){e.preventDefault(),loadingWindowOverlay.classList.remove("hidden"),window.history.back()})),document.body.classList.contains("login")){if(!("indexedDB"in window))return;window.indexedDB.deleteDatabase("lifeTrackingData")}document.addEventListener("click",(async function(e){let t=e.target.closest("a"),n=e.target.closest('[type="submit"]'),o=t&&!t.getAttribute("href").startsWith("#")&&"_blank"!=t.getAttribute("target")&&!t.classList.contains("no-loading")&&t.href.includes(window.location.hostname);if((o||n&&!n.classList.contains("no-loading"))&&loadingWindowOverlay.classList.remove("hidden"),o&&(e.preventDefault(),await storeQueryParams(),window.location.href=t.getAttribute("href")),n)for(const e of n.closest("form").querySelectorAll("[required]"))e.reportValidity()||loadingWindowOverlay.classList.add("hidden");let a=e.target.closest(".btn-delete");if(a){e.preventDefault();let t=a.dataset.url;if(t){deleteObject(t,a.dataset.confirm?a.dataset.confirm:"default")}else a.parentNode.remove()}else;}));let n=document.querySelector("#financesRecurringForm #dateSelect");null!==n&&n.addEventListener("change",(function(e){document.querySelector("#financesRecurringForm input[name=last_run]").value=""}));let o=document.querySelector("#checkboxCommon");o&&o.addEventListener("change",(function(e){document.querySelector("#commonValue").classList.toggle("hidden");var t=document.querySelector("#inputValue").value;t&&(e.target.checked?(document.querySelector("#inputCommonValue").value=t,document.querySelector("#inputValue").value=t/2):(document.querySelector("#inputValue").value=document.querySelector("#inputCommonValue").value,document.querySelector("#inputCommonValue").value=""))}))}function fetchWithTimeout(e,t,n=3e3){if(document.body.classList.contains("offline"))return new Promise((function(e,t){t("Offline")}));const o=new AbortController,a=o.signal;var i,l=[],c=new Promise((function(e,t){i=setTimeout((function(){o.abort(),t("Timeout")}),n)}));l.push(c);var r=fetch(e,{signal:a,...t}).then((function(e){return clearTimeout(i),e}));return l.push(r),Promise.race(l)}function freeze(){window.scrollY;document.body.style.overflow="hidden"}function unfreeze(){document.body.style.overflow="",window.onscroll=null}function isMobile(){return isVisible(document.getElementById("mobile-header-icons"))}function isTouchEnabled(){return"ontouchstart"in window||navigator.maxTouchPoints>0||navigator.msMaxTouchPoints>0}function isVisible(e){return"none"!==getDisplay(e)}function getDisplay(e){return e.currentStyle?e.currentStyle.display:getComputedStyle(e,null).display}async function storeQueryParams(){try{var e=await getCSRFToken(!0);e.path=window.location.pathname,e.params=window.location.search;const t=await fetchWithTimeout(jsObject.store_query_params,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(e)});return await t.json()}catch(e){console.log(e)}}function addRipple(e){let t=e.target.closest(".button");t&&createRipple(t,e);let n=e.target.closest("a.tabbar-tab");n&&createRipple(n,e)}function createRipple(e,t){const n=e.getBoundingClientRect(),o=document.createElement("span"),a=Math.max(e.clientWidth,e.clientHeight),i=a/2;o.style.width=o.style.height=`${a}px`,t?(o.style.left=t.clientX-n.left-i+"px",o.style.top=t.clientY-n.top-i+"px"):(o.style.left=n.left/2-i+"px",o.style.top=n.top/2-i+"px"),o.classList.add("ripple-circle");const l=e.getElementsByClassName("ripple-circle")[0];l&&l.remove(),e.appendChild(o)}flatpickr("#dateSelect",{altInput:!0,altFormat:i18n.dateformatTwig.date,altInputClass:"datepicker dateSelect",dateFormat:"Y-m-d",locale:i18n.template,onReady:function(e,t,n){n.altInput&&(n.__defaultValue=n.input.defaultValue,n.altInput.defaultValue=n.altInput.value,n.input.form.addEventListener("reset",(function(e){n.setDate(n.__defaultValue)})))}}),flatpickr("#dateSelectEnd",{altInput:!0,altFormat:i18n.dateformatTwig.date,altInputClass:"datepicker dateSelectEnd",dateFormat:"Y-m-d",locale:i18n.template,onReady:function(e,t,n){n.altInput&&(n.__defaultValue=n.input.defaultValue,n.altInput.defaultValue=n.altInput.value,n.input.form.addEventListener("reset",(function(e){n.setDate(n.__defaultValue)})))}}),document.addEventListener("click",(function(e){let t=e.target.closest(".btn-get-address");if(t){let n=t.dataset.lat,o=t.dataset.lng;n&&o&&(e.preventDefault(),fetch(jsObject.get_address_url+"?lat="+n+"&lng="+o,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){if("success"===e.status){var t="";e.data.police&&(t+=e.data.police+"\n"),e.data.road&&(t+=e.data.road+" "),e.data.house_number&&(t+=e.data.house_number),(e.data.road||e.data.house_number)&&(t+="\n"),e.data.postcode&&(t+=e.data.postcode+" "),e.data.city&&(t+=e.data.city),alert(t)}})).catch((function(e){console.log(e)})))}e.target.closest("span.closebtn")&&(e.preventDefault(),e.target.parentElement.classList.add("hidden"))})),isTouchEnabled()?document.addEventListener("touchstart",(async function(e){const t=e.touches[0];console.log(t),addRipple(t)})):document.addEventListener("mousedown",(async function(e){addRipple(e)}));