/**
 * Map
 */

'use strict';

var mymap = L.map('mapid').setView([default_location.lat, default_location.lng], default_location.zoom);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    subdomains: ['a', 'b', 'c']
}).addTo(mymap);

var greenIcon = new L.Icon({
    iconUrl: '/static/assets/images/marker-icon-green.png',
    shadowUrl: '/static/assets/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var yellowIcon = new L.Icon({
    iconUrl: '/static/assets/images/marker-icon-yellow.png',
    shadowUrl: '/static/assets/images/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const from = document.getElementById('inputStart').value;
const to = document.getElementById('inputEnd').value;

getMarkers();

function getMarkers() {
    fetch(jsObject.marker_url + '?from=' + from + '&to=' + to, {
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

    var my_latlngs = [];
    var my_markers = [];


    var marker_idx = 0;
    for (marker_idx in markers) {

        let marker = markers[marker_idx];

        let type = marker.type;

        if (marker.lat === null || marker.lng === null) {
            continue;
        }

        var dateString = marker.dt + '<br/>';
        var accuracyString = "";
        if (marker.acc > 0) {
            accuracyString = lang.accuracy + ' : ' + marker.acc + ' m<br/>';
        }
        var addressString = '<a href="#" data-lat="' + marker.lat + '" data-lng="' + marker.lng + '" class="btn-get-address">' + lang.address + '</a>';
        var removeString = '<br/><br/><a href="#" data-url="' + jsObject.delete_marker_url + marker.id + '" class="btn-delete">' + lang.delete_text + '</a>';

        let popup = dateString + accuracyString + addressString;

        let options = {};
        let circle_options = {
            opacity: 0.5,
            radius: marker.acc
        };

        if (type === 0) {
            popup += removeString;
        }

        if (type === 1) {
            options['icon'] = greenIcon;
            circle_options['color'] = 'green';
            popup += '<br/><br/><strong>' + marker.description + ' - ' + marker.value + ' ' + i18n.currency + '</strong>';
        }

        if (type === 2) {
            options['icon'] = yellowIcon;
            circle_options['color'] = 'yellow';

            let description = marker.description == 0 ? lang.car_refuel : lang.car_service;
            popup += '<br/><br/><strong>' + description + '</strong>';
        }

        var my_marker = L.marker([marker.lat, marker.lng], options).bindPopup(popup);


        if (marker.acc > 0) {
            var circle = null;

            my_marker.on('mouseover', function (e) {
                circle = L.circle([marker.lat, marker.lng], circle_options).addTo(mymap);
            });

            my_marker.on('mouseout', function (e) {
                mymap.removeLayer(circle);
            });

        }
        if (type === 0) {
            my_latlngs.push([marker.lat, marker.lng, marker_idx]);
        }
        my_markers.push(my_marker);
        my_marker.addTo(mymap);
    }

    var polyline = L.polyline(my_latlngs).addTo(mymap);

    if (my_markers.length > 0) {
        var group = new L.featureGroup(my_markers);
        mymap.fitBounds(group.getBounds());
    }

    return true;
}


/**
 * Fade Filter
 */

let locationFilter = document.getElementById('show-filter');
if (locationFilter !== null) {
    locationFilter.addEventListener('click', function (e) {
        e.preventDefault();

        let searchForm = document.getElementById('search-form');

        searchForm.style.height = searchForm.scrollHeight + 'px';
        searchForm.classList.toggle('collapsed');
        locationFilter.classList.toggle('hiddenSearch');
    });
}


// location range selection
var datepickerRange = document.getElementById('dateRange');
var datepickerStart = document.getElementById('inputStart');
var datepickerEnd = document.getElementById('inputEnd');
if (datepickerRange) {
    flatpickr(datepickerRange, {
        "altInput": true,
        "altFormat": i18n.twig,
        "dateFormat": "Y-m-d",
        "locale": i18n.template,
        "mode": "range",
        "defaultDate": [datepickerStart.value, datepickerEnd.value],
        "onChange": function (selectedDates) {
            const dateArr = selectedDates.map(date => this.formatDate(date, "Y-m-d"));

            if (dateArr.length > 0) {
                datepickerStart.value = dateArr[0];
                datepickerEnd.value = dateArr[0];
            }
            if (dateArr.length > 1) {
                datepickerEnd.value = dateArr[1];
            }

        }
    });
}

