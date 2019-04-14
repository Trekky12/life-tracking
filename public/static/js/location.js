"use strict";function getMarkers(){var e=[];hideLocation&&e.push("location"),hideFinances&&e.push("finances"),hideCars&&e.push("cars"),fetch(jsObject.marker_url+"?from="+from+"&to="+to+"&hide[]="+e.join("&hide[]="),{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded"}}).then(function(e){return e.json()}).then(function(e){drawMarkers(e)}).catch(function(e){console.log(e)})}function drawMarkers(e){var t=[],a=[],n=0;for(n in e){let s=e[n],m=s.type;if(null===s.lat||null===s.lng)continue;var o=s.dt+"<br/>",r="";s.acc>0&&(r=lang.accuracy+" : "+s.acc+" m<br/>");var c='<a href="#" data-lat="'+s.lat+'" data-lng="'+s.lng+'" class="btn-get-address">'+lang.address+"</a>",i='<br/><br/><a href="#" data-url="'+jsObject.delete_marker_url+s.id+'" class="btn-delete">'+lang.delete_text+"</a>";let u=o+r+c,p={},g={opacity:.5,radius:s.acc};if(0===m&&(u+=i),1===m&&(p.icon=greenIcon,g.color="green",u+="<br/><br/><strong>"+s.description+" - "+s.value+" "+i18n.currency+"</strong>"),2===m){p.icon=yellowIcon,g.color="yellow";u+="<br/><br/><strong>"+(0==s.description?lang.car_refuel:lang.car_service)+"</strong>"}var l=L.marker([s.lat,s.lng],p).bindPopup(u);if(s.acc>0){var d=null;l.on("mouseover",function(e){d=L.circle([s.lat,s.lng],g).addTo(mymap)}),l.on("mouseout",function(e){mymap.removeLayer(d)})}0===m&&t.push([s.lat,s.lng,n]),a.push(l),l.addTo(mymap)}L.polyline(t).addTo(mymap);if(a.length>0){var s=new L.featureGroup(a);mymap.fitBounds(s.getBounds())}return!0}var mymap=L.map("mapid").setView([default_location.lat,default_location.lng],default_location.zoom);L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',subdomains:["a","b","c"]}).addTo(mymap);var greenIcon=new L.Icon({iconUrl:"/static/assets/images/marker-icon-green.png",shadowUrl:"/static/assets/images/marker-shadow.png",iconSize:[25,41],iconAnchor:[12,41],popupAnchor:[1,-34],shadowSize:[41,41]}),yellowIcon=new L.Icon({iconUrl:"/static/assets/images/marker-icon-yellow.png",shadowUrl:"/static/assets/images/marker-shadow.png",iconSize:[25,41],iconAnchor:[12,41],popupAnchor:[1,-34],shadowSize:[41,41]});const from=document.getElementById("inputStart").value,to=document.getElementById("inputEnd").value,hideLocation=document.getElementById("hideLocation").checked,hideFinances=document.getElementById("hideFinances").checked,hideCars=document.getElementById("hideCars").checked;getMarkers();let locationFilter=document.getElementById("show-filter");null!==locationFilter&&locationFilter.addEventListener("click",function(e){e.preventDefault();let t=document.getElementById("search-form");t.style.height=t.scrollHeight+"px",t.classList.toggle("collapsed"),locationFilter.classList.toggle("hiddenSearch")});var datepickerRange=document.getElementById("dateRange"),datepickerStart=document.getElementById("inputStart"),datepickerEnd=document.getElementById("inputEnd");datepickerRange&&flatpickr(datepickerRange,{altInput:!0,altFormat:i18n.twig,dateFormat:"Y-m-d",locale:i18n.template,mode:"range",defaultDate:[datepickerStart.value,datepickerEnd.value],onChange:function(e){const t=e.map(e=>this.formatDate(e,"Y-m-d"));t.length>0&&(datepickerStart.value=t[0],datepickerEnd.value=t[0]),t.length>1&&(datepickerEnd.value=t[1])}});