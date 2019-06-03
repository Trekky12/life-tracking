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


    let my_markers = [];

    let marker_idx = 0;
    for (marker_idx in markers) {

        let marker = markers[marker_idx];

        // ignore markers without start data
        if (marker.data.start_lat === null || marker.data.start_lng === null) {
            continue;
        }

        /**
         * Popup
         */
        let popup = "<h4>" + marker.data.name + "</h4>" + marker.data.popup;


        let options = {};
        if (marker.isCarrental) {
            options['icon'] = L.ExtraMarkers.icon({
                icon: 'fa-car',
                markerColor: 'red',
                shape: 'circle',
                prefix: 'fa'
            });
        } else if (marker.isHotel) {
            options['icon'] = L.ExtraMarkers.icon({
                icon: 'fa-bed',
                markerColor: 'blue',
                shape: 'circle',
                prefix: 'fa'
            });
        } else if (marker.isEvent) {
            options['icon'] = L.ExtraMarkers.icon({
                icon: 'fa-calendar-o',
                markerColor: 'yellow',
                shape: 'circle',
                prefix: 'fa'
            });
        } else if (marker.isPlane) {
            options['icon'] = L.ExtraMarkers.icon({
                icon: 'fa-plane',
                markerColor: 'black',
                shape: 'circle',
                prefix: 'fa'
            });
        }

        /**
         * Start Marker
         */
        let start_marker = L.marker([marker.data.start_lat, marker.data.start_lng], options);
        start_marker.bindPopup(popup);
        my_markers.push(start_marker);

        if (marker.isCarrental) {
            layerCarRentals.addLayer(start_marker);
        } else if (marker.isHotel) {
            layerHotels.addLayer(start_marker);
        } else if (marker.isEvent) {
            layerEvents.addLayer(start_marker);
        } else if (marker.isTrain) {
            layerTrains.addLayer(start_marker);
        } else if (marker.isPlane) {
            layerPlanes.addLayer(start_marker);
        } else if (marker.isCar) {
            layerCars.addLayer(start_marker);
        }

        /**
         * End Marker
         */
        if (marker.data.end_lat !== null && marker.data.end_lat !== null) {
            let end_marker = L.marker([marker.data.end_lat, marker.data.end_lng], options);
            my_markers.push(end_marker);

            let tripLine = [[marker.data.start_lat, marker.data.start_lng], [marker.data.end_lat, marker.data.end_lng]];

            if (marker.isCarrental) {
                layerCarRentals.addLayer(end_marker);
            } else if (marker.isHotel) {
                layerHotels.addLayer(end_marker);
            } else if (marker.isEvent) {
                layerEvents.addLayer(end_marker);
            } else if (marker.isTrain) {
                let trainPolyline = [];
                trainPolyline[0] = L.polyline(tripLine, {color: 'black', weight: '5'}).bindPopup(popup);
                trainPolyline[1] = L.polyline(tripLine, {color: 'black', weight: '3', dashArray: '20, 20', dashOffset: '0'}).bindPopup(popup);
                trainPolyline[2] = L.polyline(tripLine, {color: 'white', weight: '3', dashArray: '20, 20', dashOffset: '20'}).bindPopup(popup);
                layerTrains.addLayer(L.layerGroup(trainPolyline));

                // remove start marker when there is a polyline
                layerTrains.removeLayer(start_marker);
            } else if (marker.isPlane) {
                let middle = calculateMidPoint(start_marker, end_marker);
                let planeCuve = L.curve([
                    'M', [marker.data.start_lat, marker.data.start_lng],
                    'Q', middle, [marker.data.end_lat, marker.data.end_lng]
                ], {color: 'black', weight: '3', dashArray: '10, 10'}).bindPopup(popup);

                //let planePolyline = L.polyline(tripLine, {color: 'black', weight: '3'}).bindPopup(marker.popup);

                layerPlanes.addLayer(start_marker);
                layerPlanes.addLayer(planeCuve);
            } else if (marker.isCar) {
                let streetPolyline = [];
                streetPolyline[0] = L.polyline(tripLine, {color: 'gray', weight: '5'}).bindPopup(popup);
                streetPolyline[1] = L.polyline(tripLine, {color: 'white', weight: '1', dashArray: '10, 10', dashOffset: '0'}).bindPopup(popup);
                layerCars.addLayer(L.layerGroup(streetPolyline));

                // remove start marker when there is a polyline
                layerCars.removeLayer(start_marker);
            }

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

    // current location
    var lc = L.control.locate({
        strings: {
            title: lang.set_current_location,
            showPopup: false
        },
        locateOptions: {
            enableHighAccuracy: true
        }
    });
    mymap.addControl(lc);

    getMarkers(from, to);
}



/**
 * @see https://medium.com/@ryancatalani/creating-consistently-curved-lines-on-leaflet-b59bc03fa9dc
 * @returns {undefined}
 */
function calculateMidPoint(start, end) {
    let latlng1 = start.getLatLng();
    let latlng2 = end.getLatLng();

    var offsetX = latlng2.lng - latlng1.lng,
            offsetY = latlng2.lat - latlng1.lat;

    var r = Math.sqrt(Math.pow(offsetX, 2) + Math.pow(offsetY, 2)),
            theta = Math.atan2(offsetY, offsetX);

    var thetaOffset = (3.14 / 10);

    var r2 = (r / 2) / (Math.cos(thetaOffset)),
            theta2 = theta + thetaOffset;

    var midpointX = (r2 * Math.cos(theta2)) + latlng1.lng,
            midpointY = (r2 * Math.sin(theta2)) + latlng1.lat;

    return [midpointY, midpointX];
}