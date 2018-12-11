/**
 * Map
 */

'use strict';

var mymap = L.map('mapid').setView([default_location.lat, default_location.lng], default_location.zoom);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    subdomains: ['a', 'b', 'c']
}).addTo(mymap);

getMarkers();

function getMarkers() {
    let from = document.getElementById('inputStart').value;
    let to = document.getElementById('inputEnd').value;
    fetch(jsObject.marker_url + '?from=' + from + '&to=' + to, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        drawMap(data);
    }).catch(function (error) {
        alert(error);
    });
}

function drawMap(markers) {

    var my_latlngs = [];
    var my_markers = [];

    var marker_idx = 0;
    for (marker_idx in markers) {

        let marker = markers[marker_idx];

        if (marker.lat === null || marker.lng === null) {
            return;
        }

        var dateString = marker.dt;
        var accuracyString = "";
        if (marker.acc > 0) {
            accuracyString = '<br/>' + lang.accuracy + ' : ' + marker.acc + ' m';
        }
        var addressString = '<br/><a href="#" data-id="' + marker.id + '" class="btn-get-address">' + lang.address + '</a>';
        var removeString = '<br/><br/><a href="#" data-url="' + jsObject.delete_marker_url + marker.id + '" class="btn-delete">' + lang.delete_text + '</a>';

        var my_marker = L.marker([marker.lat, marker.lng]).bindPopup(dateString + accuracyString + addressString + removeString);
        my_marker.addTo(mymap);
        my_latlngs.push([marker.lat, marker.lng, marker_idx]);


        if (marker.acc > 0) {
            var circle = null;

            my_marker.on('mouseover', function (e) {
                circle = L.circle([marker.lat, marker.lng], {
                    opacity: 0.5,
                    radius: marker.acc
                }).addTo(mymap);
            });

            my_marker.on('mouseout', function (e) {
                mymap.removeLayer(circle);
            });

        }

        my_markers.push(my_marker);
    }

    var polyline = L.polyline(my_latlngs).addTo(mymap);

    var group = new L.featureGroup(my_markers);
    mymap.fitBounds(group.getBounds());

    return true;
}


/**
 * Get Adress of marker
 */
document.addEventListener('click', function (event) {
    // https://stackoverflow.com/a/50901269
    let closest = event.target.closest('.btn-get-address');
    if (closest) {
        let id = closest.dataset.id;
        if (id) {
            event.preventDefault();
            fetch(jsObject.get_address_url + id, {
                method: 'GET',
                credentials: "same-origin"
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data['status'] === 'success') {
                    var output = '';

                    if (data['data']['police']) {
                        output += data['data']['police'] + '\n';
                    }

                    if (data['data']['road']) {
                        output += data['data']['road'] + " ";
                    }

                    if (data['data']['house_number']) {
                        output += data['data']['house_number'];
                    }

                    if (data['data']['road'] || data['data']['house_number']) {
                        output += '\n';
                    }

                    if (data['data']['postcode']) {
                        output += data['data']['postcode'] + " ";
                    }

                    if (data['data']['city']) {
                        output += data['data']['city'];
                    }

                    alert(output);
                }
            }).catch(function (error) {
                alert(error);
            });
        }
    }
});


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

