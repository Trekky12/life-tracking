"use strict";function getCSRFToken(){if(tokens.length<=2){return getNewTokens(tokens.pop())}if(tokens.length>1)return new Promise(function(e,t){e(tokens.pop())})}function getNewTokens(e){return fetch(jsObject.csrf_tokens_url,{method:"POST",credentials:"same-origin",headers:{Accept:"application/json","Content-Type":"application/json"},body:JSON.stringify({csrf_name:e.csrf_name,csrf_value:e.csrf_value})}).then(function(e){return e.json()}).then(function(e){tokens=e}).then(function(){return tokens.pop()}).catch(function(e){console.log(e)})}function deleteObject(e,t){let n=lang.really_delete;if("board"===t&&(n=lang.really_delete_board),"stack"===t&&(n=lang.really_delete_stack),"card"===t&&(n=lang.really_delete_card),"label"===t&&(n=n=lang.really_delete_label),!confirm(n))return!1;getCSRFToken(!0).then(function(t){return fetch(e,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})}).then(function(e){allowedReload=!0,window.location.reload()}).catch(function(e){console.log(e)})}function initialize(){let e=document.querySelector("#go-back-btn");null!==e&&e.addEventListener("click",function(){window.history.back()});let t=document.querySelector("#cancel");null!==t&&t.addEventListener("click",function(e){e.preventDefault(),window.history.back()}),document.addEventListener("click",function(e){let t=e.target.closest(".btn-delete");if(t){e.preventDefault();let n=t.dataset.url;if(n){deleteObject(n,t.dataset.type?t.dataset.type:"default")}else t.parentNode.remove()}});document.querySelectorAll("span.closebtn").forEach(function(e,t){e.addEventListener("click",function(e){e.target.parentElement.style.display="none"})});let n=document.querySelector("#financesRecurringForm #dateSelect");null!==n&&n.addEventListener("change",function(e){document.querySelector("#financesRecurringForm input[name=last_run]").value=""});let a=document.querySelector("#checkboxCommon");a&&a.addEventListener("change",function(e){document.querySelector("#commonValue").classList.toggle("hidden");var t=document.querySelector("#inputValue").value;t&&(e.target.checked?(document.querySelector("#inputCommonValue").value=t,document.querySelector("#inputValue").value=t/2):(document.querySelector("#inputValue").value=document.querySelector("#inputCommonValue").value,document.querySelector("#inputCommonValue").value=""))});document.querySelectorAll("input.carServiceType").forEach(function(e,t){e.addEventListener("change",function(e){document.querySelector("#carServiceFuel").classList.toggle("hidden"),document.querySelector("#carServiceService").classList.toggle("hidden")})});document.querySelectorAll(".set_calculation_date").forEach(function(e,t){e.addEventListener("click",function(t){t.preventDefault();let n=0;e.checked&&("1"===e.dataset.type&&(n=1),"2"===e.dataset.type&&(n=2)),getCSRFToken(!0).then(function(e){var t=e;return t.state=n,fetch(jsObject.set_mileage_type,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})}).then(function(e){allowedReload=!0,window.location.reload()}).catch(function(e){console.log(e)})})});let l=document.querySelector("#logviewer");if(l){l.scrollTop=l.scrollHeight;document.querySelectorAll('.log-filter input[type="checkbox"]').forEach(function(e,t){e.addEventListener("change",function(t){let n=e.dataset.type;document.querySelectorAll("#logviewer .log-entry."+n).forEach(function(e,t){e.classList.toggle("hidden")}),l.scrollTop=l.scrollHeight})})}}function initCharts(){let e=document.querySelector("#financeSummaryChart");e&&new Chart(e,{data:{labels:JSON.parse(e.dataset.labels),datasets:[{label:e.dataset.label1,data:JSON.parse(e.dataset.values1),backgroundColor:"#FF0000"},{label:e.dataset.label2,data:JSON.parse(e.dataset.values2),backgroundColor:"#008800"}]},type:"bar",options:{responsive:!0,maintainAspectRatio:!1,scales:{yAxes:[{ticks:{min:0}}]}}});var t=randomColor({count:100,hue:"blue",luminosity:"bright"});let n=document.querySelector("#financeDetailChart");if(n){var a=new Chart(n,{data:{labels:JSON.parse(n.dataset.labels),datasets:[{backgroundColor:t,data:JSON.parse(n.dataset.values),label:"test"}]},type:"pie",options:{responsive:!0,maintainAspectRatio:!1,legend:{position:"top",display:!1},tooltips:{callbacks:{title:function(e,t){return t.labels[e[0].index]},label:function(e,t){return t.datasets[e.datasetIndex].data[e.index].toFixed(2)+" "+i18n.currency}}},legendCallback:function(e){let t=document.createElement("ul");t.id="chart-legend";return e.legend.legendItems.forEach(function(e,n){let l=document.createElement("li");l.innerHTML=e.text;let o=document.createElement("span");o.classList="legend-item",o.style="background-color:"+e.fillStyle+";",l.insertBefore(o,l.firstChild),l.setAttribute("title",e.text),l.addEventListener("click",function(e){e.target.closest("li").classList.toggle("excluded");var t=n,l=a.chart,o=l.legend.legendItems[t];l.data.datasets[0]._meta[l.id].data[t].hidden=!o.hidden||null,l.update()}),t.appendChild(l)}),t}}});n.before(a.generateLegend())}}var tokens=[{csrf_name:jsObject.csrf_name,csrf_value:jsObject.csrf_value}];moment.locale(i18n.template),initialize(),initCharts(),flatpickr("#dateSelect",{altInput:!0,altFormat:i18n.twig,dateFormat:"Y-m-d",locale:i18n.template}),flatpickr("#dateSelectEnd",{altInput:!0,altFormat:i18n.twig,dateFormat:"Y-m-d",locale:i18n.template}),document.addEventListener("click",function(e){let t=e.target.closest(".btn-get-address");if(t){let n=t.dataset.lat,a=t.dataset.lng;n&&a&&(e.preventDefault(),fetch(jsObject.get_address_url+"?lat="+n+"&lng="+a,{method:"GET",credentials:"same-origin"}).then(function(e){return e.json()}).then(function(e){if("success"===e.status){var t="";e.data.police&&(t+=e.data.police+"\n"),e.data.road&&(t+=e.data.road+" "),e.data.house_number&&(t+=e.data.house_number),(e.data.road||e.data.house_number)&&(t+="\n"),e.data.postcode&&(t+=e.data.postcode+" "),e.data.city&&(t+=e.data.city),alert(t)}}).catch(function(e){console.log(e)}))}});const menuButton=document.getElementById("menu-toggle"),navigation=document.getElementById("site-navigation"),menuList=navigation.getElementsByTagName("ul")[0],body=document.getElementsByTagName("BODY")[0],boardsSidebar=document.getElementById("sidebar"),initialHeaderHeight=document.getElementById("masthead").offsetHeight;menuButton.addEventListener("click",function(e){navigation.classList.contains("toggled")?(menuButton.setAttribute("aria-expanded","false"),menuList.setAttribute("aria-expanded","false"),boardsSidebar&&(boardsSidebar.style.paddingTop=initialHeaderHeight+"px")):(menuButton.setAttribute("aria-expanded","true"),menuList.setAttribute("aria-expanded","true")),navigation.classList.toggle("toggled"),menuButton.classList.toggle("open"),body.classList.toggle("mobile-navigation-open")});