"use strict";function getMarkers(a,e){return fetch(jsObject.trip_markers_url+"?from="+a+"&to="+e,{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded"}}).then(function(a){return a.json()}).then(function(a){drawMarkers(a)}).catch(function(a){console.log(a)})}function drawMarkers(a){layerTrains.clearLayers(),layerCars.clearLayers(),layerPlanes.clearLayers(),layerCarRentals.clearLayers(),layerHotels.clearLayers(),layerEvents.clearLayers();let e=[],r=0;for(r in a){let t=a[r];if(null===t.data.start_lat||null===t.data.start_lng)continue;let l="<h4>"+t.data.name+"</h4>"+t.data.popup,n={};t.isCarrental?n.icon=L.ExtraMarkers.icon({icon:"fa-car",markerColor:"red",shape:"circle",prefix:"fa"}):t.isHotel?n.icon=L.ExtraMarkers.icon({icon:"fa-bed",markerColor:"blue",shape:"circle",prefix:"fa"}):t.isEvent?n.icon=L.ExtraMarkers.icon({icon:"fa-calendar-o",markerColor:"yellow",shape:"circle",prefix:"fa"}):t.isPlane&&(n.icon=L.ExtraMarkers.icon({icon:"fa-plane",markerColor:"black",shape:"circle",prefix:"fa"}));let o=L.marker([t.data.start_lat,t.data.start_lng],n);if(o.bindPopup(l),e.push(o),t.isCarrental?layerCarRentals.addLayer(o):t.isHotel?layerHotels.addLayer(o):t.isEvent?layerEvents.addLayer(o):t.isTrain?layerTrains.addLayer(o):t.isPlane?layerPlanes.addLayer(o):t.isCar&&layerCars.addLayer(o),null!==t.data.end_lat&&null!==t.data.end_lat){let a=L.marker([t.data.end_lat,t.data.end_lng],n);e.push(a);let r=[[t.data.start_lat,t.data.start_lng],[t.data.end_lat,t.data.end_lng]];if(t.isCarrental)layerCarRentals.addLayer(a);else if(t.isHotel)layerHotels.addLayer(a);else if(t.isEvent)layerEvents.addLayer(a);else if(t.isTrain){let a=[];a[0]=L.polyline(r,{color:"black",weight:"5"}).bindPopup(l),a[1]=L.polyline(r,{color:"black",weight:"3",dashArray:"20, 20",dashOffset:"0"}).bindPopup(l),a[2]=L.polyline(r,{color:"white",weight:"3",dashArray:"20, 20",dashOffset:"20"}).bindPopup(l),layerTrains.addLayer(L.layerGroup(a)),layerTrains.removeLayer(o)}else if(t.isPlane){let e=calculateMidPoint(o,a),r=L.curve(["M",[t.data.start_lat,t.data.start_lng],"Q",e,[t.data.end_lat,t.data.end_lng]],{color:"black",weight:"3",dashArray:"10, 10"}).bindPopup(l);layerPlanes.addLayer(o),layerPlanes.addLayer(r)}else if(t.isCar){let a=[];a[0]=L.polyline(r,{color:"gray",weight:"5"}).bindPopup(l),a[1]=L.polyline(r,{color:"white",weight:"1",dashArray:"10, 10",dashOffset:"0"}).bindPopup(l),layerCars.addLayer(L.layerGroup(a)),layerCars.removeLayer(o)}}}if(e.length>0){var t=new L.featureGroup(e);mymap.fitBounds(t.getBounds())}}function initMap(){mymap=L.map("trip-map",{fullscreenControl:!0}).setView([default_location.lat,default_location.lng],default_location.zoom),L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',subdomains:["a","b","c"]}).addTo(mymap),mymap.addLayer(layerPlanes),mymap.addLayer(layerTrains),mymap.addLayer(layerCars),mymap.addLayer(layerCarRentals),mymap.addLayer(layerHotels),mymap.addLayer(layerEvents),(controlLayer=L.control.layers(null,null,{collapsed:!1})).addOverlay(layerPlanes,"<span id='layerPlanes'></span>"),controlLayer.addOverlay(layerTrains,"<span id='layerTrains'></span>"),controlLayer.addOverlay(layerCars,"<span id='layerStreets'></span>"),controlLayer.addOverlay(layerCarRentals,"<span id='layerCarrental'></span>"),controlLayer.addOverlay(layerHotels,"<span id='layerHotels'></span>"),controlLayer.addOverlay(layerEvents,"<span id='layerEvents'></span>"),controlLayer.addTo(mymap);var a=L.control.locate({strings:{title:lang.set_current_location,showPopup:!1},locateOptions:{enableHighAccuracy:!0}});mymap.addControl(a),L.easyPrint({position:"bottomleft",sizeModes:["A4Portrait","A4Landscape"],spinnerBgColor:"#1565c0"}).addTo(mymap),getMarkers(from,to)}function calculateMidPoint(a,e){let r=a.getLatLng(),t=e.getLatLng();var l=t.lng-r.lng,n=t.lat-r.lat,o=Math.sqrt(Math.pow(l,2)+Math.pow(n,2)),s=Math.atan2(n,l),i=o/2/Math.cos(.314),d=s+.314,y=i*Math.cos(d)+r.lng;return[i*Math.sin(d)+r.lat,y]}let layerTrains=new L.LayerGroup,layerCars=new L.LayerGroup,layerPlanes=new L.LayerGroup,layerCarRentals=new L.LayerGroup,layerHotels=new L.LayerGroup,layerEvents=new L.LayerGroup,controlLayer=null,mymap=null;const tripDays=document.querySelectorAll(".trip_day"),changeDayLinks=document.querySelectorAll(".change_day");null!==changeDayLinks&&changeDayLinks.forEach(function(a,e){a.addEventListener("click",function(e){e.preventDefault();let r=a.dataset.date;getMarkers(r,r).then(function(){if(r){let a=document.getElementById("trip_day_"+r);tripDays.forEach(function(a){a.classList.add("hidden")}),a.classList.remove("hidden")}else tripDays.forEach(function(a){a.classList.remove("hidden")});changeDayLinks.forEach(function(a){a.querySelector("button").classList.add("gray")}),a.querySelector("button").classList.remove("gray")})})});let descriptionLinks=document.querySelectorAll(".trip_day .description_text");descriptionLinks.forEach(function(a,e){a.addEventListener("click",function(a){a.preventDefault(),a.target.parentElement.classList.toggle("active")})});const from=document.getElementById("inputStart").value,to=document.getElementById("inputEnd").value;initMap();