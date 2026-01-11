/**
 * Map
 */

'use strict';

const detailsModal = document.getElementById("details-modal");

var mymap = L.map('mapid').setView([default_location.lat, default_location.lng], default_location.zoom);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(mymap);


const from = document.getElementById('inputStart').value;
const to = document.getElementById('inputEnd').value;

var controlLayer = null;
var circleLayer = new L.LayerGroup();

let markers = [];
let tableCount = [];

/**
 * Layers for Control
 */
var clusterToggleLayer = new L.LayerGroup();
let layerLocation = new L.LayerGroup();
let layerFinances = new L.LayerGroup();
let layerCars = new L.LayerGroup();
let layerSplittedBills = new L.LayerGroup();
let layerTimesheets = new L.LayerGroup();
let layerTrips = new L.LayerGroup();
let layerDirections = new L.LayerGroup();

/**
 * Sub-Layer for individual markers and clusters
 */
let layerLocationMarkers = new L.LayerGroup();
let layerFinancesMarkers = new L.LayerGroup();
let layerCarsMarkers = new L.LayerGroup();
let layerSplittedBillsMarkers = new L.LayerGroup();
let layerTimesheetsMarkers = new L.LayerGroup();

// Clusters
let layerLocationClusters = L.markerClusterGroup({
    iconCreateFunction: function (cluster) {
        return createClusterIcon(cluster, "location");
    },
    maxClusterRadius: 50
});
let layerFinancesClusters = L.markerClusterGroup({
    iconCreateFunction: function (cluster) {
        return createClusterIcon(cluster, "finances");
    },
    maxClusterRadius: 50
});
let layerCarsClusters = L.markerClusterGroup({
    iconCreateFunction: function (cluster) {
        return createClusterIcon(cluster, "cars");
    },
    maxClusterRadius: 50
});
let layerSplittedBillsClusters = L.markerClusterGroup({
    iconCreateFunction: function (cluster) {
        return createClusterIcon(cluster, "splittedbills");
    },
    maxClusterRadius: 50
});
let layerTimesheetsClusters = L.markerClusterGroup({
    iconCreateFunction: function (cluster) {
        return createClusterIcon(cluster, "timesheets");
    },
    maxClusterRadius: 50
});

// when acc circle is visible (mouseover on marker) and the map is zoomed 
// the mouseout event which removes the circle is not triggered 
// so do this manually
mymap.on('zoom', function () {
    removeCircleLayer();
});

getMarkers();

async function getMarkers() {
    try {
        const response = await fetch(jsObject.location_markers + '?from=' + from + '&to=' + to, {
            method: 'GET',
            credentials: "same-origin",
        });
        const data = await response.json();
        drawMarkers(data["markers"], false);
        addRouteDetails(data["count"]);
    } catch (error) {
        console.log(error);
    }
}

function drawMarkers(markersData, hideClusters = false) {

    let directions = [];
    let markers = [];

    let marker_idx = 0;
    for (marker_idx in markersData) {

        let markerData = markersData[marker_idx];

        if (markerData.type >= 4) {
            if (markerData.data.start_lat === null && markerData.data.end_lat !== null) {
                markerData.data.start_lat = markerData.data.end_lat;
            }
            if (markerData.data.start_lng === null && !markerData.data.end_lng !== null) {
                markerData.data.start_lng = markerData.data.end_lng;
            }

            if (markerData.data.start_lat !== null && markerData.data.start_lng !== null) {
                markerData.lat = markerData.data.start_lat;
                markerData.lng = markerData.data.start_lng;
            }
        }

        // ignore markers without location data
        if (markerData.lat === null || markerData.lng === null) {
            continue;
        }

        if (markerData.type == 0) {
            directions.push([markerData.lat, markerData.lng, marker_idx]);
        }

        let start_marker = createMarker(markerData);
        markers.push(start_marker);

        if (markerData.data && markerData.data.end_lat !== null && markerData.data.end_lng !== null && markerData.data.start_lat !== markerData.data.end_lat && markerData.data.start_lng !== markerData.data.end_lng) {
            markerData.lat = markerData.data.end_lat;
            markerData.lng = markerData.data.end_lng;
            markerData.acc = markerData.data.end_acc;

            let end_marker = createMarker(markerData);



            if (markerData.type == 5) {
                if (markerData.isCarrental) {
                    end_marker.bindPopup(start_marker.getPopup());
                } else if (markerData.isTrain) {
                    let trainPolyline = [];
                    let middle = calculateMidPoint(start_marker, end_marker);
                    let points = quadraticBezierPoints(start_marker, middle, end_marker);

                    trainPolyline[0] = L.polyline(points, { color: 'black', weight: '5' }).bindPopup(start_marker.getPopup());
                    trainPolyline[1] = L.polyline(points, { color: 'black', weight: '3', dashArray: '20, 20', dashOffset: '0' }).bindPopup(start_marker.getPopup());
                    trainPolyline[2] = L.polyline(points, { color: 'white', weight: '3', dashArray: '20, 20', dashOffset: '20' }).bindPopup(start_marker.getPopup());

                    layerTrips.addLayer(L.layerGroup(trainPolyline));

                    // remove start marker when there is a polyline
                    layerTrips.removeLayer(start_marker);
                    layerTrips.removeLayer(end_marker);
                } else if (markerData.isPlane) {
                    let middle = calculateMidPoint(start_marker, end_marker);
                    let points = quadraticBezierPoints(start_marker, middle, end_marker);

                    let planePolyline = L.polyline(points, { color: 'black', weight: '3', dashArray: '10, 10' }).bindPopup(start_marker.getPopup());

                    layerTrips.addLayer(planePolyline);

                    layerTrips.removeLayer(end_marker);
                } else if (markerData.isCar) {
                    let streetPolyline = [];

                    let middle = calculateMidPoint(start_marker, end_marker);
                    let points = quadraticBezierPoints(start_marker, middle, end_marker);

                    streetPolyline[0] = L.polyline(points, { color: 'gray', weight: '5' }).bindPopup(start_marker.getPopup());
                    streetPolyline[1] = L.polyline(points, { color: 'white', weight: '1', dashArray: '10, 10', dashOffset: '0' }).bindPopup(start_marker.getPopup());

                    layerTrips.addLayer(L.layerGroup(streetPolyline));

                    // remove start marker when there is a polyline
                    layerTrips.removeLayer(start_marker);
                    layerTrips.removeLayer(end_marker);
                } else if (markerData.isShip) {
                    let middle = calculateMidPoint(start_marker, end_marker);
                    let points = quadraticBezierPoints(start_marker, middle, end_marker);

                    let shipPolyline = L.polyline(points, { color: 'blue', weight: '5', dashArray: '10, 10', dashOffset: '0' }).bindPopup(start_marker.getPopup());

                    layerTrips.addLayer(shipPolyline);

                    layerTrips.removeLayer(start_marker);
                    layerTrips.removeLayer(end_marker);
                }
            }
            markers.push(end_marker);
        }

    }

    // toggle between clusters/individual markers
    if (hideClusters) {
        layerLocation.addLayer(layerLocationMarkers);
        layerFinances.addLayer(layerFinancesMarkers);
        layerCars.addLayer(layerCarsMarkers);
        layerSplittedBills.addLayer(layerSplittedBillsMarkers);
        layerTimesheets.addLayer(layerTimesheetsMarkers);
    } else {
        layerLocation.addLayer(layerLocationClusters);
        layerFinances.addLayer(layerFinancesClusters);
        layerCars.addLayer(layerCarsClusters);
        layerSplittedBills.addLayer(layerSplittedBillsClusters);
        layerTimesheets.addLayer(layerTimesheetsClusters);
    }
    mymap.addLayer(layerLocation);
    mymap.addLayer(layerFinances);
    mymap.addLayer(layerCars);
    mymap.addLayer(layerSplittedBills);
    mymap.addLayer(layerTimesheets);
    mymap.addLayer(layerTrips);

    // fit bounds of markers
    if (markers.length > 0) {
        var group = new L.featureGroup(markers);
        mymap.fitBounds(group.getBounds());
    }

    // create directions polyline
    var polyline = L.polyline(directions);
    layerDirections.addLayer(polyline);

    // dummy layer control
    if (!hideClusters) {
        clusterToggleLayer.addTo(mymap);
    }

    // layer control
    controlLayer = L.control.layers(null, null, {
        collapsed: false
    });

    controlLayer.addOverlay(clusterToggleLayer, "<span id='toggleClustering'>" + document.getElementById('iconClustering').innerHTML + "</span>");
    controlLayer.addOverlay(layerLocation, "<span id='layerLocation'>" + document.getElementById('iconLocation').innerHTML + "</span>");
    controlLayer.addOverlay(layerFinances, "<span id='layerFinances'>" + document.getElementById('iconFinances').innerHTML + "</span>");
    controlLayer.addOverlay(layerCars, "<span id='layerCars'>" + document.getElementById('iconCars').innerHTML + "</span>");
    controlLayer.addOverlay(layerSplittedBills, "<span id='layerSplittedBills'>" + document.getElementById('iconSplittedBills').innerHTML + "</span>");
    controlLayer.addOverlay(layerTimesheets, "<span id='layerTimesheets'>" + document.getElementById('iconTimesheets').innerHTML + "</span>");
    controlLayer.addOverlay(layerTrips, "<span id='layerTrips'>" + document.getElementById('iconTrips').innerHTML + "</span>");
    controlLayer.addOverlay(layerDirections, "<span id='layerDirections'>" + document.getElementById('iconDirections').innerHTML + "</span>");

    controlLayer.addTo(mymap);

    // empty circle layer
    circleLayer.addTo(mymap);
}
/**
 * Hide directions when location history is disabled
 */
mymap.on('overlayremove', function (eventLayer) {
    if (eventLayer.layer === layerLocation) {
        controlLayer.removeLayer(layerDirections);

        // @see https://gis.stackexchange.com/a/180773
        setTimeout(function () {
            mymap.removeLayer(layerDirections);
        }, 10);
    }

    /**
     * Switch Sub-Layers
     */
    if (eventLayer.layer === clusterToggleLayer) {
        layerLocation.removeLayer(layerLocationClusters);
        layerFinances.removeLayer(layerFinancesClusters);
        layerCars.removeLayer(layerCarsClusters);
        layerSplittedBills.removeLayer(layerSplittedBillsClusters);
        layerTimesheets.removeLayer(layerTimesheetsClusters);

        layerLocation.addLayer(layerLocationMarkers);
        layerFinances.addLayer(layerFinancesMarkers);
        layerCars.addLayer(layerCarsMarkers);
        layerSplittedBills.addLayer(layerSplittedBillsMarkers);
        layerTimesheets.addLayer(layerTimesheetsMarkers);
    }
});
mymap.on('overlayadd', function (eventLayer) {
    if (eventLayer.layer === layerLocation && !mymap.hasLayer(layerDirections)) {
        controlLayer.addOverlay(layerDirections, "<span id='layerDirections'>" + document.getElementById('iconDirections').innerHTML + "</span>");
    }
    /**
     * Switch Sub-Layers
     */
    if (eventLayer.layer === clusterToggleLayer) {
        layerLocation.removeLayer(layerLocationMarkers);
        layerFinances.removeLayer(layerFinancesMarkers);
        layerCars.removeLayer(layerCarsMarkers);
        layerSplittedBills.removeLayer(layerSplittedBillsMarkers);
        layerTimesheets.removeLayer(layerTimesheetsMarkers);

        layerLocation.addLayer(layerLocationClusters);
        layerFinances.addLayer(layerFinancesClusters);
        layerCars.addLayer(layerCarsClusters);
        layerSplittedBills.addLayer(layerSplittedBillsClusters);
        layerTimesheets.addLayer(layerTimesheetsClusters);
    }
});


/**
 * Do not trigger navigation drawer
 */
mymap.on("movestart zoomstart", function (e) {
    isMapMove = true;
});
mymap.on("moveend zoomend", function (e) {
    isMapMove = false;
});

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


function createClusterIcon(cluster, c) {
    var childCount = cluster.getChildCount();
    return new L.DivIcon({ html: '<div><span>' + childCount + '</span></div>', className: 'marker-cluster ' + c, iconSize: new L.Point(40, 40) });

}

function addCircleLayer(lat, lng, radius, color) {
    let circle_options = {
        opacity: 0.5,
        radius: radius,
        weight: 5,
        color: color
    };

    let circle = L.circle([lat, lng], circle_options);

    let poly_options = {
        weight: 10,
        color: color
    };
    let poly = L.polygon([[lat, lng]], poly_options);

    circleLayer.addLayer(circle);
    circleLayer.addLayer(poly);
}

function removeCircleLayer() {
    circleLayer.clearLayers();
}

function createMarker(data) {
    // create popup
    var dateString = data.dt + '<br/>';
    var accuracyString = "";
    if (data.acc > 0) {
        accuracyString = lang.accuracy + ' : ' + data.acc + ' m<br/>';
    }
    var addressString = '<a href="#" data-lat="' + data.lat + '" data-lng="' + data.lng + '" class="btn-get-address">' + lang.address + '</a>';
    var stepsString = data.steps > 0 ? lang.steps + ': ' + data.steps + '<br/>' : '';
    var removeString = '<br/><br/><a href="#" data-url="' + jsObject.delete_marker_url + data.id + '" class="btn-delete">' + lang.delete_text + '</a>';

    let popup = "";
    if (data.popup) {
        popup = data.popup;
    } else {
        popup = dateString + accuracyString + stepsString + addressString;
    }

    let options = {};
    let circle_color = '#3388ff';

    switch (data.type) {
        case 0:
            options['icon'] = L.ExtraMarkers.icon({
                markerColor: 'blue',
                shape: 'circle',
                innerHTML: document.getElementById('iconLocation').innerHTML
            });
            popup += removeString;
            break;
        case 1:
            options['icon'] = L.ExtraMarkers.icon({
                markerColor: 'green',
                shape: 'circle',
                innerHTML: document.getElementById('iconFinances').innerHTML
            });
            circle_color = 'green';
            popup += '<br/><br/><strong>' + data.description + ' - ' + data.value + ' ' + i18n.currency + '</strong>';
            break;
        case 2:
            options['icon'] = L.ExtraMarkers.icon({
                markerColor: 'yellow',
                shape: 'circle',
                innerHTML: document.getElementById('iconCars').innerHTML
            });
            circle_color = 'yellow';

            let description = data.description == 0 ? lang.car_refuel : lang.car_service;
            popup += '<br/><br/><strong>' + description + '</strong>';
            break;
        case 3:
            options['icon'] = L.ExtraMarkers.icon({
                markerColor: 'orange',
                shape: 'circle',
                innerHTML: document.getElementById('iconSplittedBills').innerHTML
            });
            circle_color = 'orange';
            popup += '<br/><br/><strong>' + data.description + '</strong>';
            break;
        case 4:
            options['icon'] = L.ExtraMarkers.icon({
                markerColor: 'pink',
                shape: 'circle',
                innerHTML: document.getElementById('iconTimesheets').innerHTML
            });
            circle_color = 'pink';
            break;
        case 5:
            if (data.isCarrental) {
                options['icon'] = L.ExtraMarkers.icon({
                    markerColor: 'red',
                    shape: 'circle',
                    innerHTML: document.getElementById('iconCarRentals').innerHTML
                });
            } else if (data.isHotel) {
                options['icon'] = L.ExtraMarkers.icon({
                    markerColor: 'blue',
                    shape: 'circle',
                    innerHTML: document.getElementById('iconHotels').innerHTML
                });
            } else if (data.isEvent) {
                options['icon'] = L.ExtraMarkers.icon({
                    markerColor: 'yellow',
                    shape: 'circle',
                    innerHTML: document.getElementById('iconEvents').innerHTML
                });
            } else if (data.isPlane) {
                options['icon'] = L.ExtraMarkers.icon({
                    markerColor: 'black',
                    shape: 'circle',
                    innerHTML: document.getElementById('iconPlanes').innerHTML
                });
            }
            break;

    }

    // create marker object
    var marker = L.marker([data.lat, data.lng], options);

    // add accuracy circle
    if (marker.acc > 0) {
        marker.on('mouseover', function (e) {
            addCircleLayer(marker.lat, marker.lng, marker.acc, circle_color);
        });

        marker.on('mouseout', function (e) {
            removeCircleLayer();
        });

        marker.on('popupopen', function (e) {
            addCircleLayer(marker.lat, marker.lng, marker.acc, circle_color);
        });

        marker.on('popupclose', function (e) {
            removeCircleLayer();
        });

    }


    // add marker to marker group and cluster group
    switch (data.type) {
        case 0:
            layerLocationMarkers.addLayer(marker);
            layerLocationClusters.addLayer(marker);
            break;
        case 1:
            layerFinancesMarkers.addLayer(marker);
            layerFinancesClusters.addLayer(marker);
            break;
        case 2:
            layerCarsMarkers.addLayer(marker);
            layerCarsClusters.addLayer(marker);
            break;
        case 3:
            layerSplittedBillsMarkers.addLayer(marker);
            layerSplittedBillsClusters.addLayer(marker);
            break;
        case 4:
            layerTimesheetsMarkers.addLayer(marker);
            layerTimesheetsClusters.addLayer(marker);
            break;
        case 5:
            layerTrips.addLayer(marker);
            break;
    }

    marker.bindPopup(popup);

    return marker;
}

document.getElementById("show-details-modal").addEventListener('click', function (e) {
    detailsModal.classList.add('visible');
});

document.getElementById("modal-close-btn").addEventListener('click', function (e) {
    detailsModal.classList.remove('visible');
});

function addRouteDetails(countData) {
    let modalContent = detailsModal.querySelector(".modal-content");
    modalContent.innerHTML = "";

    for (const [type, values] of Object.entries(countData)) {

        // skip empty sections
        if (!values || (Array.isArray(values) && values.length === 0) || (typeof values === "object" && Object.keys(values).length === 0)) {
            continue;
        }

        const total = Object.values(values).reduce((sum, val) => sum + Number(val), 0);

        const details = document.createElement("details");
        details.classList.add("dt-wrapper");
        const summary = document.createElement("summary");
        const title = lang[`${type}_module_name`] ?? type;
        summary.textContent = `${title} (${total})`;
        details.appendChild(summary);

        const table = document.createElement("table");
        table.classList.add("table", "table-hover", "small", "dt-table");

        const thead = document.createElement("thead");
        const headRow = document.createElement("tr");

        const thDate = document.createElement("th");
        thDate.textContent = lang.date;

        const thCount = document.createElement("th");
        thCount.textContent = lang.count;

        headRow.appendChild(thDate);
        headRow.appendChild(thCount);
        thead.appendChild(headRow);
        table.appendChild(thead);

        const tbody = document.createElement("tbody");

        for (const [date, count] of Object.entries(values)) {

            const tr = document.createElement("tr");

            const tdDate = document.createElement("td");
            tdDate.textContent = date;

            const tdCount = document.createElement("td");
            tdCount.textContent = count;

            tr.appendChild(tdDate);
            tr.appendChild(tdCount);
            tbody.appendChild(tr);
        }

        table.appendChild(tbody);
        details.appendChild(table);
        modalContent.appendChild(details);
    }
}
