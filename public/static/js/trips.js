"use strict";const routeModal=document.getElementById("route-modal"),loadingOverlay=document.getElementById("loading-overlay");let layerTrains=new L.LayerGroup,layerCars=new L.LayerGroup,layerPlanes=new L.LayerGroup,layerCarRentals=new L.LayerGroup,layerHotels=new L.LayerGroup,layerEvents=new L.LayerGroup,layerWaypoints=new L.LayerGroup,controlLayer=null,mymap=null,routeControl=null,my_markers=[];const tripDays=document.querySelectorAll(".trip_day"),changeDayLinks=document.querySelectorAll(".change_day"),today=moment(Date.now()).format("YYYY-MM-DD"),currentDayButton=document.querySelector('.change_day[data-date="'+today+'"]'),newEventButton=document.querySelector("#new-event-btn");let addEventLink="";function changeDay(e){let t=e.dataset.date;fromInput.value=t,toInput.value=t,newEventButton&&(newEventButton.href=addEventLink+e.search),getMarkers(t,t).then((function(){if(t){let e=document.getElementById("trip_day_"+t);tripDays.forEach((function(e){e.classList.add("hidden")})),e.classList.remove("hidden")}else tripDays.forEach((function(e){e.classList.remove("hidden")}));changeDayLinks.forEach((function(e){e.querySelector("button").classList.add("gray")})),e.querySelector("button").classList.remove("gray")}))}newEventButton&&(addEventLink=newEventButton.href),null!==changeDayLinks&&changeDayLinks.forEach((function(e,t){e.addEventListener("click",(function(t){t.preventDefault(),changeDay(e)}))}));let descriptionLinks=document.querySelectorAll(".trip_day .description_text");descriptionLinks.forEach((function(e,t){e.addEventListener("click",(function(e){e.preventDefault(),e.target.parentElement.classList.toggle("active")}))}));const fromInput=document.getElementById("inputStart"),toInput=document.getElementById("inputEnd"),routeBtn=document.getElementById("createRoute");function getMarkers(e,t){return fetch(jsObject.trip_markers_url+"?from="+e+"&to="+t,{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded"}}).then((function(e){return e.json()})).then((function(e){drawMarkers(e)})).catch((function(e){console.log(e)}))}function drawMarkers(e){layerTrains.clearLayers(),layerCars.clearLayers(),layerPlanes.clearLayers(),layerCarRentals.clearLayers(),layerHotels.clearLayers(),layerEvents.clearLayers(),layerWaypoints.clearLayers(),my_markers=[];let t=0;for(t in e){let a=e[t];if(null===a.data.start_lat||null===a.data.start_lng)continue;let n={};a.isCarrental?n.icon=L.ExtraMarkers.icon({icon:"fa-car",markerColor:"red",shape:"circle",prefix:"fa"}):a.isHotel?n.icon=L.ExtraMarkers.icon({icon:"fa-bed",markerColor:"blue",shape:"circle",prefix:"fas"}):a.isEvent?n.icon=L.ExtraMarkers.icon({icon:"fa-calendar-alt",markerColor:"yellow",shape:"circle",prefix:"fas"}):a.isPlane&&(n.icon=L.ExtraMarkers.icon({icon:"fa-plane",markerColor:"black",shape:"circle",prefix:"fas"}));let r=L.marker([a.data.start_lat,a.data.start_lng],n);r.data=a,r.name=a.data.name,r.address=a.data.start_address;let o=getAddToRouteLink(r),l=document.createElement("div");if(!a.isWaypoint){let e=document.createElement("h4");e.innerHTML=a.data.name;let t=document.createElement("p");t.innerHTML=a.data.popup,l.appendChild(e),l.appendChild(t)}if(l.appendChild(o),a.isWaypoint){let e=getDeleteWaypointLink(a.data.id,r,r.waypoint);l.appendChild(document.createElement("br")),l.appendChild(e)}if(r.bindPopup(l),my_markers.push(r),a.isCarrental?layerCarRentals.addLayer(r):a.isHotel?layerHotels.addLayer(r):a.isEvent?layerEvents.addLayer(r):a.isTrain?layerTrains.addLayer(r):a.isPlane?layerPlanes.addLayer(r):a.isCar?layerCars.addLayer(r):a.isWaypoint&&layerWaypoints.addLayer(r),null!==a.data.end_lat&&null!==a.data.end_lat){let e=L.marker([a.data.end_lat,a.data.end_lng],n);e.data=a,e.name=a.data.name,e.address=a.data.end_address,my_markers.push(e);let t=[[a.data.start_lat,a.data.start_lng],[a.data.end_lat,a.data.end_lng]];if(a.isCarrental)layerCarRentals.addLayer(e);else if(a.isHotel)layerHotels.addLayer(e);else if(a.isEvent)layerEvents.addLayer(e);else if(a.isTrain){let e=[];e[0]=L.polyline(t,{color:"black",weight:"5"}).bindPopup(l),e[1]=L.polyline(t,{color:"black",weight:"3",dashArray:"20, 20",dashOffset:"0"}).bindPopup(l),e[2]=L.polyline(t,{color:"white",weight:"3",dashArray:"20, 20",dashOffset:"20"}).bindPopup(l),layerTrains.addLayer(L.layerGroup(e)),layerTrains.removeLayer(r)}else if(a.isPlane){let t=calculateMidPoint(r,e),n=L.curve(["M",[a.data.start_lat,a.data.start_lng],"Q",t,[a.data.end_lat,a.data.end_lng]],{color:"black",weight:"3",dashArray:"10, 10"}).bindPopup(l);layerPlanes.addLayer(r),layerPlanes.addLayer(n)}else if(a.isCar){let e=[];e[0]=L.polyline(t,{color:"gray",weight:"5"}).bindPopup(l),e[1]=L.polyline(t,{color:"white",weight:"1",dashArray:"10, 10",dashOffset:"0"}).bindPopup(l),layerCars.addLayer(L.layerGroup(e)),layerCars.removeLayer(r)}}}if(my_markers.length>0){var a=new L.featureGroup(my_markers);mymap.fitBounds(a.getBounds())}}function getNextWaypointPos(){let e=0,t=routeControl.getWaypoints().length,a=routeControl.getWaypoints()[0].latLng,n=routeControl.getWaypoints()[t-1].latLng;return a&&(e=routeControl.getWaypoints().length-1),a&&n&&(e=routeControl.getWaypoints().length),e}function addWaypoint(e){let t=getNextWaypointPos(),a=e.name?e.name+" ("+e.address+")":null,n=L.Routing.waypoint(e.getLatLng(),a,{fixed:!0});e.waypoint=t,routeControl.spliceWaypoints(t,1,n)}function initMap(){mymap=L.map("trip-map",{fullscreenControl:!0}).setView([default_location.lat,default_location.lng],default_location.zoom),L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",{attribution:'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',subdomains:["a","b","c"]}).addTo(mymap),mymap.addLayer(layerPlanes),mymap.addLayer(layerTrains),mymap.addLayer(layerCars),mymap.addLayer(layerCarRentals),mymap.addLayer(layerHotels),mymap.addLayer(layerEvents),mymap.addLayer(layerWaypoints),controlLayer=L.control.layers(null,null,{collapsed:!1}),controlLayer.addOverlay(layerPlanes,"<span id='layerPlanes'></span>"),controlLayer.addOverlay(layerTrains,"<span id='layerTrains'></span>"),controlLayer.addOverlay(layerCars,"<span id='layerStreets'></span>"),controlLayer.addOverlay(layerCarRentals,"<span id='layerCarrental'></span>"),controlLayer.addOverlay(layerHotels,"<span id='layerHotels'></span>"),controlLayer.addOverlay(layerEvents,"<span id='layerEvents'></span>"),controlLayer.addTo(mymap);var e=L.control.locate({strings:{title:lang.set_current_location,showPopup:!1},locateOptions:{enableHighAccuracy:!0},icon:"fas fa-map-marker-alt"});mymap.addControl(e),L.easyPrint({position:"bottomleft",sizeModes:["A4Portrait","A4Landscape"],spinnerBgColor:"#1565c0"}).addTo(mymap),currentDayButton?changeDay(currentDayButton):getMarkers(fromInput.value,toInput.value);let t=new(L.Routing.Plan.extend({createGeocoders:function(){var e=L.Routing.Plan.prototype.createGeocoders.call(this);let t=createButton(e,"walk"),a=createButton(e,"bike"),n=createButton(e,"car",!0);L.DomEvent.on(t,"click",(function(){routeControl.getRouter().options.profile="mapbox/walking",routeControl.route(),t.classList.add("active"),a.classList.remove("active"),n.classList.remove("active")}),this),L.DomEvent.on(a,"click",(function(){routeControl.getRouter().options.profile="mapbox/cycling",routeControl.route(),t.classList.remove("active"),a.classList.add("active"),n.classList.remove("active")}),this),L.DomEvent.on(n,"click",(function(){routeControl.getRouter().options.profile="mapbox/driving",routeControl.route(),t.classList.remove("active"),a.classList.remove("active"),n.classList.add("active")}),this);let r=createButton(e,"save");L.DomEvent.on(r,"click",(function(){let e=prompt(lang.trips_route_name_prompt);null!==e&&getCSRFToken().then((function(t){let a={name:e,start_date:fromInput.value,end_date:toInput.value,waypoints:routeControl.getWaypoints()};return a.csrf_name=t.csrf_name,a.csrf_value=t.csrf_value,fetch(jsObject.trip_add_route,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(a)})})).then((function(e){return e.json()})).then((function(e){"success"===e.status?alert(lang.trips_route_saved_successfully):alert(lang.trips_route_saved_error)})).catch((function(e){console.log(e),alert(lang.trips_route_saved_error)}))}),this);let o=createButton(e,"load");return L.DomEvent.on(o,"click",(function(){freeze(),loadingOverlay.classList.remove("hidden"),fetch(jsObject.trip_list_routes,{method:"GET",credentials:"same-origin",headers:{"Content-Type":"application/x-www-form-urlencoded"}}).then((function(e){return e.json()})).then((function(e){let t=routeModal.querySelector("table"),a=t.querySelector("tbody");a.innerHTML="",e.forEach((function(e){let n=document.createElement("tr"),r=document.createElement("td");r.innerHTML=e.name,n.appendChild(r);let o=document.createElement("td");o.innerHTML=e.start_date,n.appendChild(o);let l=document.createElement("td"),i=document.createElement("a");i.href="#",i.dataset.route=e.id,i.classList.add("btn-route");let s=document.createElement("span");s.classList="fas fa-route fa-lg",i.appendChild(s),l.appendChild(i),n.appendChild(l),t.addEventListener("click",(function(e){let t=e.target.closest(".btn-route");if(t){e.preventDefault();let a=t.dataset.route;return fetch(jsObject.trip_route_waypoints+"?route="+a,{method:"GET",credentials:"same-origin"}).then((function(e){return e.json()})).then((function(e){if(routeControl.setWaypoints(e.waypoints),routeModal.classList.remove("visible"),e.start_date){changeDay(document.querySelector('.change_day[data-date="'+e.start_date+'"]'))}})).catch((function(e){console.log(e)}))}}));let d=document.createElement("td"),c=document.createElement("a");c.href="#",c.dataset.url=e.delete,c.classList.add("btn-delete");let u=document.createElement("span");u.classList="fas fa-trash fa-lg",c.appendChild(u),d.appendChild(c),n.appendChild(d),a.appendChild(n)}));new JSTable(t,{perPage:10,labels:tableLabels,layout:{top:null,bottom:"{pager}"},columns:[{select:0,sortable:!0},{select:[1],render:function(e,t){let a=e.innerHTML;return a?moment(a).format(i18n.dateformatJS.date):""}},{select:[2,3],sortable:!1,searchable:!1}]})})).catch((function(e){console.log(e)})).finally((function(){routeModal.classList.add("visible"),loadingOverlay.classList.add("hidden"),unfreeze()}))}),this),e}}))([],{geocoder:new L.Control.Geocoder.Mapbox(mapbox_token,{reverseQueryParams:{language:i18n.routing}}),createMarker:function(e,t){return t.options.fixed?null:L.marker(t.latLng,{})},routeWhileDragging:!1,reverseWaypoints:!0,addWaypoints:!0,language:i18n.routing,draggableWaypoints:!1});routeControl=L.Routing.control({waypoints:[],autoRoute:!0,router:L.Routing.mapbox(mapbox_token,{profile:"mapbox/driving",routingOptions:{alternatives:!1,steps:!1},language:i18n.routing}),show:!1,collapsible:!0,showAlternatives:!1,routeWhileDragging:!1,plan:t}).addTo(mymap),routeControl.on("routingerror",(function(e){429==e.error.target.status?alert(lang.routing_error_too_many_requests):alert(lang.routing_error)})),mymap.on("contextmenu",(function(e){getCSRFToken().then((function(t){let a={start_lat:e.latlng.lat,start_lng:e.latlng.lng,type:"WAYPOINT",start_date:fromInput.value,end_date:toInput.value};return a.csrf_name=t.csrf_name,a.csrf_value=t.csrf_value,fetch(jsObject.trip_add_waypoint,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(a)})})).then((function(e){return e.json()})).then((function(t){let a=getNextWaypointPos(),n=L.Routing.waypoint(e.latlng,null,{fixed:!0});routeControl.spliceWaypoints(a,1,n),routeControl.show();let r=L.marker(n.latLng,{}),o=document.createElement("div"),l=getAddToRouteLink(r),i=getDeleteWaypointLink(t.id,r,a);o.appendChild(l),o.appendChild(document.createElement("br")),o.appendChild(i),r.bindPopup(o),layerWaypoints.addLayer(r)})).catch((function(e){console.log(e)}))}))}function calculateMidPoint(e,t){let a=e.getLatLng(),n=t.getLatLng();var r=n.lng-a.lng,o=n.lat-a.lat,l=Math.sqrt(Math.pow(r,2)+Math.pow(o,2)),i=Math.atan2(o,r),s=l/2/Math.cos(.314),d=i+.314,c=s*Math.cos(d)+a.lng;return[s*Math.sin(d)+a.lat,c]}function createButton(e,t,a=!1){var n=L.DomUtil.create("button","",e);return n.setAttribute("type","button"),n.title=t,n.classList.add("leaflet-routing-btn"),n.classList.add(t),a&&n.classList.add("active"),n}function getAddToRouteLink(e){let t=document.createElement("a");return t.classList.add("navigation-btn"),t.innerHTML=lang.routing_add_to_route,t.addEventListener("click",(function(){addWaypoint(e),routeControl.show()})),t}function getDeleteWaypointLink(e,t,a){let n=document.createElement("a");return n.classList.add("navigation-btn"),n.innerHTML=lang.delete_text,n.addEventListener("click",(function(){getCSRFToken().then((function(t){let a={};return a.csrf_name=t.csrf_name,a.csrf_value=t.csrf_value,fetch(jsObject.trip_delete_waypoint+"?id="+e,{method:"DELETE",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(a)})})).then((function(e){return e.json()})).then((function(e){mymap.removeLayer(t),my_markers.splice(my_markers.indexOf(t),1),routeControl.spliceWaypoints(a,1)})).catch((function(e){console.log(e)}))})),n}routeBtn&&routeBtn.addEventListener("click",(function(e){e.preventDefault(),routeControl.setWaypoints([]),my_markers.forEach((function(e,t){e.data.isPlane||addWaypoint(e)})),routeControl.show()})),initMap(),tripDays.forEach((function(e){new Sortable(e,{draggable:".trip_event",handle:".icon",ghostClass:"trip_event-placeholder",dataIdAttr:"data-event",onUpdate:function(e){var t={events:this.toArray()};getCSRFToken().then((function(e){return t.csrf_name=e.csrf_name,t.csrf_value=e.csrf_value,fetch(jsObject.trip_event_position_url,{method:"POST",credentials:"same-origin",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)})})).then((function(e){return e.json()})).then((function(e){getMarkers(fromInput.value,toInput.value)})).catch((function(e){console.log(e)}))}})})),document.getElementById("modal-close-btn").addEventListener("click",(function(e){routeModal.classList.remove("visible")}));