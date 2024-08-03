'use strict';

const latField = document.querySelector('input#geoLat');
const lngField = document.querySelector('input#geoLng');
const accField = document.querySelector('input#geoAcc');
const idField = document.querySelector('input[name="id"]');

const mapContainers = document.querySelectorAll('.geo-map');
const updateLocButtons = document.querySelectorAll('.update-location');
const deleteLocButtons = document.querySelectorAll('.delete-location');
const getAdressButtons = document.querySelectorAll('.set_address');

const geoOptions = {
    enableHighAccuracy: true,
    timeout: 1 * 60 * 1000,
    maximumAge: 0
};

let map = [];
let map_marker = [];

let lastAccuracy = 9999999;
let timeout = null;

/**
 * Init maps
 */
mapContainers.forEach(function (mapContainer, idx) {
    map[idx] = null;
    map_marker[idx] = null;
    drawMap(mapContainer, idx);
});

/**
 * Add Geolocation to first map
 * @param {type} index of map
 */
function getLocation(index) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            locationRetrieved(position, index);
        }, locationError, geoOptions);
    } else {
        console.log("Geolocation is not supported by this browser.");
    }
}

// automatically get location on new entries
// and set first index
if (latField !== null &&
    lngField !== null &&
    accField !== null &&
    idField === null &&
    latField.value.length === 0 &&
    lngField.value.length === 0 &&
    accField.value.length === 0) {
    getLocation(0);
} else {
    // automatically store location on all other pages
    getLastLocationTime().then(function (lastTime) {
        let currentTime = Math.round(Date.now() / 1000);

        if (currentTime >= lastTime + 5 * 60) {
            getLocation(-1);
        }
    }).catch(function (error) {
        console.log(error);
    });

}

if (updateLocButtons !== null) {
    updateLocButtons.forEach(function (updateLoc, idx) {
        updateLoc.addEventListener('click', function (e) {
            e.preventDefault();
            clearTimeout(timeout);
            lastAccuracy = 9999999;
            // we assume the index of the button is the same like the index of the map
            getLocation(idx);
        });
    });
}

if (deleteLocButtons !== null) {
    deleteLocButtons.forEach(function (deleteLoc, idx) {
        deleteLoc.addEventListener('click', function (e) {
            e.preventDefault();
            let mapContainer = deleteLoc.parentNode.parentNode.querySelector('.geo-map');
            removeMap(deleteLoc, mapContainer, idx);
        });
    });
}

if (getAdressButtons !== null) {
    getAdressButtons.forEach(function (addressButton, idx) {
        addressButton.addEventListener('click', function (e) {
            e.preventDefault();

            let address = addressButton.previousElementSibling.value;
            let lat = addressButton.parentNode.querySelector('input.geo-lat');
            let lng = addressButton.parentNode.querySelector('input.geo-lng');

            let mapContainer = addressButton.nextElementSibling;

            if (address) {
                fetch(jsObject.get_location_of_address + '?address=' + address, {
                    method: 'GET',
                    credentials: "same-origin"
                }).then(function (response) {
                    return response.json();
                }).then(function (data) {
                    console.log(data);
                    if (data.status == "success") {
                        let result = data.data;

                        if (result.length > 0) {
                            let place = result[0];

                            lat.value = place.lat;
                            lng.value = place.lon;
                            // we assume the index of the button is the same like the index of the map
                            drawMap(mapContainer, idx);
                        } else {
                            alert(lang.nothing_found);
                        }
                    } else {
                        alert(lang.nothing_found);
                    }
                });
            }
        });
    });
}

function locationRetrieved(position, index) {
    console.log(position);

    if (position.coords.accuracy < lastAccuracy && index >= 0) {

        // default
        let latElement = latField;
        let lngElement = lngField;
        let accElement = accField;

        let mapContainer = mapContainers[index];

        // map is available, so take closest map
        if (mapContainer) {
            latElement = mapContainer.parentNode.querySelector('.geo-lat');
            lngElement = mapContainer.parentNode.querySelector('.geo-lng');
            accElement = mapContainer.parentNode.querySelector('.geo-acc');
        }

        latElement.value = position.coords.latitude;
        lngElement.value = position.coords.longitude;
        accElement.value = position.coords.accuracy;

        lastAccuracy = position.coords.accuracy;

        // draw map if map is available, otherwise only save the position
        if (mapContainer) {
            drawMap(mapContainer, index);
        }
    }

    if (position.coords.accuracy > 50) {
        console.log("Accuracy not exact");
        timeout = setTimeout(function () {
            getLocation(index);
        }, 5000);

    } else {
        console.log("Accuracy reached");
        // Store location
        // but first check last store
        getLastLocationTime().then(function (lastTime) {
            let currentTime = Math.round(Date.now() / 1000);

            if (currentTime >= lastTime + 5 * 60) {
                storeLocation(position);
            }
        }).catch(function (error) {
            console.log(error);
        });

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

function drawMap(mapContainer, index) {

    if (mapContainer !== null) {

        let latElement = mapContainer.parentNode.querySelector('.geo-lat');
        let lngElement = mapContainer.parentNode.querySelector('.geo-lng');
        let accElement = mapContainer.parentNode.querySelector('.geo-acc');
        let mapBtns = mapContainer.parentNode.querySelector('.map-btn');
        let deleteLoc = mapContainer.parentNode.querySelector('.delete-location');
        let draggableElement = mapContainer.parentNode.querySelector('.geo-draggable');

        if (latElement === null || lngElement === null) {
            return;
        }

        let lat = latElement.value;
        let lng = lngElement.value;
        let acc = accElement ? accElement.value : 0;
        let isMovable = draggableElement ? draggableElement.value !== '0' : true;

        if (lat.length === 0 || lng.length === 0) {
            return;
        }

        /**
         * Init Map
         */
        if (map[index] === null) {
            mapContainer.style.height = '300px';
            mapContainer.classList.add("visible");
            mapBtns.classList.add("map-visible");
            if (deleteLoc) {
                deleteLoc.classList.remove("hidden");
            }

            map[index] = L.map(mapContainer).setView([default_location.lat, default_location.lng], default_location.zoom);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                subdomains: ['a', 'b', 'c']
            }).addTo(map[index]);
        }

        if (map_marker[index] !== null) {
            map[index].removeLayer(map_marker[index]);
        }

        /**
         * Init Marker
         */
        map_marker[index] = L.marker([lat, lng], { draggable: isMovable });
        map_marker[index].addTo(map[index]);

        if (isMovable) {
            map_marker[index].on('drag', function (e) {
                let marker = e.target;
                let position = marker.getLatLng();
                latElement.value = position.lat;
                lngElement.value = position.lng;
                if (accElement) {
                    accElement.value = 0;
                }

                marker._popup.setContent('<a href="#" data-lat="' + position.lat + '" data-lng="' + position.lng + '" class="btn-get-address">' + lang.address + '</a>');

                clearTimeout(timeout);
            });

            map[index].on('click', function (e) {
                map_marker[index].setLatLng(e.latlng);
                map_marker[index].off('mouseover');
                map_marker[index].off('popupopen');
                map_marker[index].fire('drag');
            });
        }

        let accuracyString = "";
        if (acc > 0) {
            accuracyString = '' + lang.accuracy + ': ' + acc + ' m<br/>';
        }
        let addressString = '<a href="#" data-lat="' + lat + '" data-lng="' + lng + '" class="btn-get-address" id="marker-popup">' + lang.address + '</a>';

        map_marker[index].bindPopup(accuracyString + addressString);

        if (acc > 0) {
            let circle = null;

            map_marker[index].off('mouseover');
            map_marker[index].off('popupopen');

            map_marker[index].on('mouseover', function (event) {
                circle = L.circle(event.target.getLatLng(), {
                    opacity: 0.5,
                    radius: acc
                }).addTo(map[index]);
            });

            map_marker[index].on('mouseout', function (e) {
                if (map[index].hasLayer(circle)) {
                    map[index].removeLayer(circle);
                }
            });

            map_marker[index].on('dragstart', function () {
                map_marker[index].off('mouseover');
                map_marker[index].off('popupopen');
            });

            map_marker[index].on('popupopen', function (event) {
                circle = L.circle(event.target.getLatLng(), {
                    opacity: 0.5,
                    radius: acc
                }).addTo(map[index]);
            });

            map_marker[index].on('popupclose', function (e) {
                if (map[index].hasLayer(circle)) {
                    map[index].removeLayer(circle);
                }
            });
        }

        let group = new L.featureGroup([map_marker[index]]);
        map[index].fitBounds(group.getBounds());

    }
}

function removeMap(deleteLoc, mapContainer, index) {

    mapContainer.style.height = '0px';
    mapContainer.classList.remove("visible");
    deleteLoc.classList.add("hidden");
    map[index].off();
    map[index].remove();
    map[index] = null;

    let latElement = mapContainer.parentNode.querySelector('.geo-lat');
    let lngElement = mapContainer.parentNode.querySelector('.geo-lng');
    let accElement = mapContainer.parentNode.querySelector('.geo-acc');
    latElement.value = "";
    lngElement.value = "";
    accElement.value = "";

    let mapBtns = mapContainer.parentNode.querySelector('.map-btn');
    mapBtns.classList.remove("map-visible");

    clearTimeout(timeout);
}

function getLastLocationTime() {
    return fetch(jsObject.location_last, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if (data.status == "success") {
            return data.ts;
        }
        throw "Error";
    });
}

function storeLocation(position) {

    let data = { "gps_loc": position.coords.latitude + "," + position.coords.longitude, "gps_acc": position.coords.accuracy };

    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.location_record, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        console.log(data);
    }).catch(function (error) {
        console.log(error);
    });

}