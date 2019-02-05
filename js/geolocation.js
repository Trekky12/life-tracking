'use strict';

const latField = document.querySelector('input[name="lat"]');
const lngField = document.querySelector('input[name="lng"]');
const accField = document.querySelector('input[name="acc"]');
const idField = document.querySelector('input[name="id"]');
const updateLoc = document.querySelector('#update-location');
const deleteLoc = document.querySelector('#delete-location');
const map = document.querySelector('#geo-map');

const geoOptions = {
    enableHighAccuracy: true,
    timeout: 1 * 60 * 1000,
    maximumAge: 0
};

let my_map = null;
let lastAccuracy = 9999999;
let my_marker = null;
let timeout = null;

drawMap();

/**
 * Add Geolocation
 */
function getLocation() {
    if (navigator.geolocation) {

        navigator.geolocation.getCurrentPosition(locationRetrieved, locationError, geoOptions);

    } else {
        console.log("Geolocation is not supported by this browser.");
    }
}


if ((latField !== null && lngField !== null && accField !== null && idField === null) && (document.querySelector('#financeForm') !== null || document.querySelector('#gasolineForm') !== null)) {
    if (latField.value.length === 0 && lngField.value.length === 0 && accField.value.length === 0) {
        getLocation();
    }
}

if (updateLoc !== null) {
    updateLoc.addEventListener('click', function (e) {
        e.preventDefault();
        clearTimeout(timeout);
        lastAccuracy = 9999999;
        getLocation();
        //drawMap();
    });
}

if (deleteLoc !== null) {
    deleteLoc.addEventListener('click', function (e) {
        e.preventDefault();
        removeMap();
    });
}

function locationRetrieved(position) {
    console.log(position);

    if (position.coords.accuracy < lastAccuracy) {

        latField.value = position.coords.latitude;
        lngField.value = position.coords.longitude;
        accField.value = position.coords.accuracy;

        lastAccuracy = position.coords.accuracy;

        drawMap();
    }
    if (position.coords.accuracy > 50) {
        console.log("Accuracy not exact");
        timeout = setTimeout(function () {
            getLocation();
        }, 5000);

    } else {
        console.log("Accuracy reached");
    }
}

function locationError(error) {
    switch (error.code) {
        case error.PERMISSION_DENIED:
            console.log("User denied the request for Geolocation.");
            break;
        case error.POSITION_UNAVAILABLE:
            console.log("Location information is unavailable.");
            break;
        case error.TIMEOUT:
            console.log("The request to get user location timed out.");
            break;
        case error.UNKNOWN_ERROR:
            console.log("An unknown error occurred.");
            break;
    }
}

function drawMap() {

    if (map !== null) {

        let lat = latField.value;
        let lng = lngField.value;
        let acc = accField.value;

        if (lat.length === 0 || lng.length === 0) {
            return;
        }

        if (my_map === null) {

            /**
             * Init Map
             */

            map.style.height = '300px';
            map.classList.add("visible");
            deleteLoc.classList.remove("hidden");

            my_map = L.map('geo-map').setView([default_location.lat, default_location.lng], default_location.zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                subdomains: ['a', 'b', 'c']
            }).addTo(my_map);

        }
        
        if (my_marker !== null) {
            my_map.removeLayer(my_marker)
        }
        
        /**
         * Init Marker
         */
        my_marker = L.marker([lat, lng], {draggable: true});

        my_marker.on('drag', function (e) {
            let marker = e.target;
            let position = marker.getLatLng();
            latField.value = position.lat;
            lngField.value = position.lng;
            accField.value = 0;

            my_marker._popup.setContent('<a href="#" data-lat="' + position.lat + '" data-lng="' + position.lng + '" class="btn-get-address">' + lang.address + '</a>');

            clearTimeout(timeout);
            //mymap.panTo(new L.LatLng(position.lat, position.lng));
        });
        
        my_marker.addTo(my_map);

        my_map.on('click', function (e) {
            my_marker.setLatLng(e.latlng);
            my_marker.off('mouseover');
            my_marker.fire('drag');
        });

        /**
         * set Location of marker
         */
        //my_marker.setLatLng([lat, lng]);

        let accuracyString = "";
        if (acc > 0) {
            accuracyString = '' + lang.accuracy + ': ' + acc + ' m<br/>';
        }
        let addressString = '<a href="#" data-lat="' + lat + '" data-lng="' + lng + '" class="btn-get-address" id="marker-popup">' + lang.address + '</a>';

        my_marker.bindPopup(accuracyString + addressString);

        if (acc > 0) {
            let circle = null;

            my_marker.off('mouseover');
            
            my_marker.on('mouseover', function (event) {
                circle = L.circle(event.target.getLatLng(), {
                    opacity: 0.5,
                    radius: acc
                }).addTo(my_map);
            });

            my_marker.on('mouseout', function (e) {
                my_map.removeLayer(circle);
            });

            my_marker.on('dragstart', function () {
                my_marker.off('mouseover');
            });
        }

        let group = new L.featureGroup([my_marker]);
        my_map.fitBounds(group.getBounds());

    }
}

function removeMap() {
    map.style.height = '0px';
    map.classList.remove("visible");
    deleteLoc.classList.add("hidden");
    my_map.off();
    my_map.remove();
    my_map = null;

    latField.value = "";
    lngField.value = "";
    accField.value = "";
    
    clearTimeout(timeout);
}

