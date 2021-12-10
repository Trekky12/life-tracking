"use strict";moment.locale(i18n.template),initialize(),initCharts();const loadingWindowOverlay=document.getElementById("loading-overlay");function getCSRFToken(){return tokens.length>1?new Promise((function(e,t){e(tokens.pop())})):getNewTokens(tokens.pop())}function getNewTokens(e){return fetchWithTimeout(jsObject.csrf_tokens_url,{method:"POST",credentials:"same-origin",headers:{Accept:"application/json","Content-Type":"application/json"},body:JSON.stringify({csrf_name:e.csrf_name,csrf_value:e.csrf_value})}).then((function(e){return e.json()})).then((function(e){tokens=e})).then((function(){return tokens.pop()})).catch((function(t){throw tokens.push(e),console.log(t),loadingWindowOverlay.classList.add("hidden"),"No CRSF Tokens available"}))}function deleteObject(e,t){let n=lang.really_delete;if("board"===t&&(n=lang.really_delete_board),"stack"===t&&(n=lang.really_delete_stack),"card"===t&&(n=lang.really_delete_card),"label"===t&&(n=n=lang.really_delete_label),!confirm(n))return loadingWindowOverlay.classList.add("hidden"),!1;getCSRFToken(!0).then((function(t){return fetch(e,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){allowedReload=!0,"redirect"in e?window.location.href=e.redirect:window.location.reload()})).catch((function(t){console.log(t),document.body.classList.contains("offline")&&saveDataWhenOffline(e,"DELETE")}))}function setCookie(e,t,n,a){n=n||365;var o=new Date;o.setDate(o.getDate()+n);var l=[e+"="+t,"expires="+o.toUTCString(),"path="+a||"/"];document.cookie=l.join(";")}function getCookie(e,t){for(var n=e+"=",a=decodeURIComponent(document.cookie).split(";"),o=0;o<a.length;o++){for(var l=a[o];""==l.charAt(0);)l=l.substring(1);if(l.indexOf(n)>=0)return l.substring(l.indexOf(n)+n.length,l.length)}return t||""}function initialize(){let e=document.querySelector("#go-back-btn");null!==e&&e.addEventListener("click",(function(){loadingWindowOverlay.classList.remove("hidden"),window.history.back()}));let t=document.querySelector("#cancel");null!==t&&t.addEventListener("click",(function(e){e.preventDefault(),loadingWindowOverlay.classList.remove("hidden"),window.history.back()})),document.addEventListener("click",(function(e){let t=e.target.closest("a"),n=e.target.closest('[type="submit"]');(t&&"#"!=t.getAttribute("href")&&"_blank"!=t.getAttribute("target")&&!t.classList.contains("no-loading")&&t.href.includes(window.location.hostname)||n&&!n.classList.contains("no-loading"))&&loadingWindowOverlay.classList.remove("hidden");let a=e.target.closest(".btn-delete");if(a){e.preventDefault();let t=a.dataset.url;if(t){deleteObject(t,a.dataset.type?a.dataset.type:"default")}else a.parentNode.remove()}else;})),document.querySelectorAll("span.closebtn").forEach((function(e,t){e.addEventListener("click",(function(e){e.target.parentElement.classList.add("hidden")}))}));let n=document.querySelector("#financesRecurringForm #dateSelect");null!==n&&n.addEventListener("change",(function(e){document.querySelector("#financesRecurringForm input[name=last_run]").value=""}));let a=document.querySelector("#checkboxCommon");a&&a.addEventListener("change",(function(e){document.querySelector("#commonValue").classList.toggle("hidden");var t=document.querySelector("#inputValue").value;t&&(e.target.checked?(document.querySelector("#inputCommonValue").value=t,document.querySelector("#inputValue").value=t/2):(document.querySelector("#inputValue").value=document.querySelector("#inputCommonValue").value,document.querySelector("#inputCommonValue").value=""))})),document.querySelectorAll("input.carServiceType").forEach((function(e,t){e.addEventListener("change",(function(e){document.querySelector("#carServiceFuel").classList.toggle("hidden"),document.querySelector("#carServiceService").classList.toggle("hidden")}))})),document.querySelectorAll(".set_calculation_date").forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault();let n=0;e.checked&&("1"===e.dataset.type&&(n=1),"2"===e.dataset.type&&(n=2)),getCSRFToken(!0).then((function(e){var t=e;return t.state=n,fetch(jsObject.set_mileage_type,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){allowedReload=!0,window.location.reload()})).catch((function(e){console.log(e)}))}))}))}function initCharts(){let e=document.querySelector("#financeSummaryChart");e&&new Chart(e,{data:{labels:JSON.parse(e.dataset.labels),datasets:[{label:e.dataset.label1,data:JSON.parse(e.dataset.values1),backgroundColor:"#FF0000"},{label:e.dataset.label2,data:JSON.parse(e.dataset.values2),backgroundColor:"#008800"}]},type:"bar",options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{ticks:{min:0}}}}});var t=randomColor({count:100,hue:"blue",luminosity:"bright"});let n=document.querySelector("#financeDetailChart");if(n)var a=new Chart(n,{data:{labels:JSON.parse(n.dataset.labels),datasets:[{backgroundColor:t,data:JSON.parse(n.dataset.values),label:"test"}]},type:"pie",options:{responsive:!0,maintainAspectRatio:!1,legend:{position:"top",display:!1},tooltips:{callbacks:{title:function(e,t){return t.labels[e[0].index]},label:function(e,t){return t.datasets[e.datasetIndex].data[e.index].toFixed(2)+" "+i18n.currency}}},legendCallback:function(e){let t=document.createElement("ul");return t.id="chart-legend",e.legend.legendItems.forEach((function(e,n){let o=document.createElement("li");o.innerHTML=e.text;let l=document.createElement("span");l.classList="legend-item",l.style="background-color:"+e.fillStyle+";",o.insertBefore(l,o.firstChild),o.setAttribute("title",e.text),o.addEventListener("click",(function(e){e.target.closest("li").classList.toggle("excluded");var t=n,o=a.chart,l=o.legend.legendItems[t];o.data.datasets[0]._meta[o.id].data[t].hidden=!l.hidden||null,o.update()})),t.appendChild(o)})),t}}});let o=document.querySelector("#stepsSummaryChart");o&&new Chart(o,{data:{labels:JSON.parse(o.dataset.labels),datasets:[{label:o.dataset.label,data:JSON.parse(o.dataset.values),backgroundColor:"#1e88e5"}]},type:"bar",options:{responsive:!0,maintainAspectRatio:!1,scales:{y:{ticks:{min:0}}}}})}function fetchWithTimeout(e,t,n=3e3){if(document.body.classList.contains("offline"))return new Promise((function(e,t){t("Offline")}));const a=new AbortController,o=a.signal;var l,i=[],r=new Promise((function(e,t){l=setTimeout((function(){a.abort(),t("Timeout")}),n)}));i.push(r);var c=fetch(e,{signal:o,...t}).then((function(e){return clearTimeout(l),e}));return i.push(c),Promise.race(i)}function freeze(){var e=window.scrollY;document.body.style.overflow="hidden",window.onscroll=function(){window.scroll(0,e)}}function unfreeze(){document.body.style.overflow="",window.onscroll=null}function isMobile(){return isVisible(document.getElementById("mobile-header-icons"))}function isVisible(e){return"none"!==getDisplay(e)}function getDisplay(e){return e.currentStyle?e.currentStyle.display:getComputedStyle(e,null).display}flatpickr("#dateSelect",{altInput:!0,altFormat:i18n.dateformatTwig.date,dateFormat:"Y-m-d",locale:i18n.template,onReady:function(e,t,n){n.altInput&&(n.__defaultValue=n.input.defaultValue,n.altInput.defaultValue=n.altInput.value,n.input.form.addEventListener("reset",(function(e){n.setDate(n.__defaultValue)})))}}),flatpickr("#dateSelectEnd",{altInput:!0,altFormat:i18n.dateformatTwig.date,dateFormat:"Y-m-d",locale:i18n.template,onReady:function(e,t,n){n.altInput&&(n.__defaultValue=n.input.defaultValue,n.altInput.defaultValue=n.altInput.value,n.input.form.addEventListener("reset",(function(e){n.setDate(n.__defaultValue)})))}}),document.addEventListener("click",(function(e){let t=e.target.closest(".btn-get-address");if(t){let n=t.dataset.lat,a=t.dataset.lng;n&&a&&(e.preventDefault(),fetch(jsObject.get_address_url+"?lat="+n+"&lng="+a,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){if("success"===e.status){var t="";e.data.police&&(t+=e.data.police+"\n"),e.data.road&&(t+=e.data.road+" "),e.data.house_number&&(t+=e.data.house_number),(e.data.road||e.data.house_number)&&(t+="\n"),e.data.postcode&&(t+=e.data.postcode+" "),e.data.city&&(t+=e.data.city),alert(t)}})).catch((function(e){console.log(e)})))}}));