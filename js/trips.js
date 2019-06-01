'use strict';

let layerTrains = new L.LayerGroup();
let layerCars = new L.LayerGroup();
let layerPlanes = new L.LayerGroup();
let layerCarRentals = new L.LayerGroup();
let layerHotels = new L.LayerGroup();
let layerEvents = new L.LayerGroup();

let controlLayer = null;
let mymap = null;

const tripDays = document.querySelectorAll('.trip_day');
const changeDayLinks = document.querySelectorAll('.change_day');

if (changeDayLinks !== null) {
    changeDayLinks.forEach(function (changeDayLink, idx) {
        changeDayLink.addEventListener('click', function (e) {
            e.preventDefault();
            let date = changeDayLink.dataset.date;
            getMarkers(date, date).then(function () {
                if (date) {
                    let currentDay = document.getElementById('trip_day_' + date);
                    tripDays.forEach(function (el) {
                        el.classList.add('hidden');
                    });
                    currentDay.classList.remove('hidden');
                } else {
                    tripDays.forEach(function (el) {
                        el.classList.remove('hidden');
                    });
                }
                changeDayLinks.forEach(function (el) {
                    el.querySelector('button').classList.add('gray');
                });
                changeDayLink.querySelector('button').classList.remove('gray');

            });
        });
    });
}

let descriptionLinks = document.querySelectorAll('.trip_day .description_text');
descriptionLinks.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        event.target.parentElement.classList.toggle('active');
    });
});

var greenIcon = new L.Icon({
    iconUrl: '/static/assets/images/marker-icon-green.png',
    shadowUrl: '/static/assets/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const from = document.getElementById('inputStart').value;
const to = document.getElementById('inputEnd').value;

initMap();

function getMarkers(from, to) {
    return fetch(jsObject.trip_markers_url + '?from=' + from + '&to=' + to, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        drawMarkers(data);
    }).catch(function (error) {
        console.log(error);
    });
}

function drawMarkers(markers) {

    layerTrains.clearLayers();
    layerCars.clearLayers();
    layerPlanes.clearLayers();
    layerCarRentals.clearLayers();
    layerHotels.clearLayers();
    layerEvents.clearLayers();


    let my_latlngs = [];
    let my_markers = [];

    let marker_idx = 0;
    for (marker_idx in markers) {

        let marker = markers[marker_idx];

        //let type = marker.type;

        // ignore markers without location data
        if (marker.lat === null || marker.lng === null) {
            continue;
        }

        let options = {};

        if (marker.isTravel) {

            if (marker.isPlane) {
                options['icon'] = greenIcon;
            }

            let start_marker = L.marker([marker.start_lat, marker.start_lng], options);
            let end_marker = L.marker([marker.end_lat, marker.end_lng], options);

            my_markers.push(start_marker);
            my_markers.push(end_marker);

            let tripLine = [[marker.start_lat, marker.start_lng], [marker.end_lat, marker.end_lng]];

            if (marker.isTrain) {
                let trainPolyline = [];
                trainPolyline[0] = L.polyline(tripLine, {color: 'black', weight: '5'}).bindPopup(marker.popup);
                trainPolyline[1] = L.polyline(tripLine, {color: 'black', weight: '3', dashArray: '20, 20', dashOffset: '0'}).bindPopup(marker.popup);
                trainPolyline[2] = L.polyline(tripLine, {color: 'white', weight: '3', dashArray: '20, 20', dashOffset: '20'}).bindPopup(marker.popup);

                let trainLayer = L.layerGroup(trainPolyline);
                layerTrains.addLayer(trainLayer);
            } else if (marker.isPlane) {
                start_marker.bindPopup(marker.popup);
                let planePolyline = L.polyline(tripLine, {color: 'black', weight: '3'}).bindPopup(marker.popup);
                layerPlanes.addLayer(start_marker);
                layerPlanes.addLayer(planePolyline);
            } else if (marker.isCar) {
                let streetPolyline = [];
                streetPolyline[0] = L.polyline(tripLine, {color: 'gray', weight: '5'}).bindPopup(marker.popup);
                streetPolyline[1] = L.polyline(tripLine, {color: 'white', weight: '1', dashArray: '10, 10', dashOffset: '0'}).bindPopup(marker.popup);
                layerCars.addLayer(L.layerGroup(streetPolyline));
            }

        } else {
            let marker_options = options;
            if (marker.isCarrental) {
                marker_options['icon'] = L.divIcon({
                    html: '<i class="fa fa-2x fa-car"></i>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10],
                    className: 'myDivIcon'
                });
            } else if (marker.isHotel) {
                marker_options['icon'] = L.divIcon({
                    html: '<i class="fa fa-2x fa-bed"></i>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10],
                    className: 'myDivIcon'
                });
            }

            var my_marker = L.marker([marker.start_lat, marker.start_lng], marker_options);

            my_marker.bindPopup(marker.popup);

            if (marker.isCarrental) {
                layerCarRentals.addLayer(my_marker);
            } else if (marker.isHotel) {
                layerHotels.addLayer(my_marker);
            } else {
                layerEvents.addLayer(my_marker);
            }

            my_markers.push(my_marker);
        }
    }


    // fit bounds of markers
    if (my_markers.length > 0) {
        var group = new L.featureGroup(my_markers);
        mymap.fitBounds(group.getBounds());
    }

}

function initMap() {
    mymap = L.map('trip-map').setView([default_location.lat, default_location.lng], default_location.zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        subdomains: ['a', 'b', 'c']
    }).addTo(mymap);

    mymap.addLayer(layerPlanes);
    mymap.addLayer(layerTrains);
    mymap.addLayer(layerCars);
    mymap.addLayer(layerCarRentals);
    mymap.addLayer(layerHotels);
    mymap.addLayer(layerEvents);

    // layer control
    controlLayer = L.control.layers(null, null, {
        collapsed: false
    });
    controlLayer.addOverlay(layerPlanes, "<span id='layerPlanes'></span>");
    controlLayer.addOverlay(layerTrains, "<span id='layerTrains'></span>");
    controlLayer.addOverlay(layerCars, "<span id='layerStreets'></span>");
    controlLayer.addOverlay(layerCarRentals, "<span id='layerCarrental'></span>");
    controlLayer.addOverlay(layerHotels, "<span id='layerHotels'></span>");
    controlLayer.addOverlay(layerEvents, "<span id='layerEvents'></span>");
    controlLayer.addTo(mymap);

    getMarkers(from, to);
}