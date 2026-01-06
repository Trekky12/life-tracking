/**
 * Map
 */

'use strict';

var mymap = L.map('mapid').setView([default_location.lat, default_location.lng], default_location.zoom);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
}).addTo(mymap);

var greenIcon = new L.Icon({
    iconUrl: '/static/assets/images/leaflet-custom/marker-icon-green.png',
    shadowUrl: '/static/assets/images/leaflet/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

var yellowIcon = new L.Icon({
    iconUrl: '/static/assets/images/leaflet-custom/marker-icon-yellow.png',
    shadowUrl: '/static/assets/images/leaflet/marker-shadow.png',
    iconSize: [25, 41],
    iconAnchor: [12, 41],
    popupAnchor: [1, -34],
    shadowSize: [41, 41]
});

const from = document.getElementById('inputStart').value;
const to = document.getElementById('inputEnd').value;

var controlLayer = null;
var circleLayer = new L.LayerGroup();

let markers = [];

/**
 * Layers for Control
 */
var clusterToggleLayer = new L.LayerGroup();
let layerLocation = new L.LayerGroup();
let layerFinances = new L.LayerGroup();
let layerCars = new L.LayerGroup();
let layerDirections = new L.LayerGroup();

/**
 * Sub-Layer for individual markers and clusters
 */
let layerLocationMarkers = new L.LayerGroup();
let layerFinancesMarkers = new L.LayerGroup();
let layerCarsMarkers = new L.LayerGroup();
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


// when acc circle is visible (mouseover on marker) and the map is zoomed 
// the mouseout event which removes the circle is not triggered 
// so do this manually
mymap.on('zoom', function () {
    removeCircleLayer();
});

getMarkers();

function getMarkers() {
    return fetch(jsObject.marker_url + '?from=' + from + '&to=' + to, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        markers = data;
        drawMarkers(data, false);
    }).catch(function (error) {
        console.log(error);
    });
}

function drawMarkers(markers, hideClusters = false) {

    let my_latlngs = [];
    let my_markers = [];

    let marker_idx = 0;
    for (marker_idx in markers) {

        let marker = markers[marker_idx];

        let type = marker.type;

        // ignore markers without location data
        if (marker.lat === null || marker.lng === null) {
            continue;
        }

        // create popup
        var dateString = marker.dt + '<br/>';
        var accuracyString = "";
        if (marker.acc > 0) {
            accuracyString = lang.accuracy + ' : ' + marker.acc + ' m<br/>';
        }
        var addressString = '<a href="#" data-lat="' + marker.lat + '" data-lng="' + marker.lng + '" class="btn-get-address">' + lang.address + '</a>';
        var stepsString = marker.steps > 0 ? lang.steps + ': ' + marker.steps + '<br/>' : '';
        var removeString = '<br/><br/><a href="#" data-url="' + jsObject.delete_marker_url + marker.id + '" class="btn-delete">' + lang.delete_text + '</a>';

        let popup = dateString + accuracyString + stepsString + addressString;

        let options = {};
        let circle_color = '#3388ff';

        switch (type) {
            case 0:
                popup += removeString;
                break;
            case 1:
                options['icon'] = greenIcon;
                circle_color = 'green';
                popup += '<br/><br/><strong>' + marker.description + ' - ' + marker.value + ' ' + i18n.currency + '</strong>';
                break;
            case 2:
                options['icon'] = yellowIcon;
                circle_color = 'yellow';

                let description = marker.description == 0 ? lang.car_refuel : lang.car_service;
                popup += '<br/><br/><strong>' + description + '</strong>';
                break;

        }

        // create marker object
        var my_marker = L.marker([marker.lat, marker.lng], options).bindPopup(popup);

        // add accuracy circle
        if (marker.acc > 0) {
            my_marker.on('mouseover', function (e) {
                addCircleLayer(marker.lat, marker.lng, marker.acc, circle_color);
            });

            my_marker.on('mouseout', function (e) {
                removeCircleLayer();
            });

            my_marker.on('popupopen', function (e) {
                addCircleLayer(marker.lat, marker.lng, marker.acc, circle_color);
            });

            my_marker.on('popupclose', function (e) {
                removeCircleLayer();
            });

        }

        // add marker to marker group and cluster group
        switch (type) {
            case 0:
                layerLocationMarkers.addLayer(my_marker);
                layerLocationClusters.addLayer(my_marker);
                my_latlngs.push([marker.lat, marker.lng, marker_idx]);
                break;
            case 1:
                layerFinancesMarkers.addLayer(my_marker);
                layerFinancesClusters.addLayer(my_marker);
                break;
            case 2:
                layerCarsMarkers.addLayer(my_marker);
                layerCarsClusters.addLayer(my_marker);
                break;
        }

        my_markers.push(my_marker);
    }

    // toggle between clusters/individual markers
    if (hideClusters) {
        layerLocation.addLayer(layerLocationMarkers);
        layerFinances.addLayer(layerFinancesMarkers);
        layerCars.addLayer(layerCarsMarkers);
    } else {
        layerLocation.addLayer(layerLocationClusters);
        layerFinances.addLayer(layerFinancesClusters);
        layerCars.addLayer(layerCarsClusters);
    }
    mymap.addLayer(layerLocation);
    mymap.addLayer(layerFinances);
    mymap.addLayer(layerCars);

    // fit bounds of markers
    if (my_markers.length > 0) {
        var group = new L.featureGroup(my_markers);
        mymap.fitBounds(group.getBounds());
    }

    // create directions polyline
    var polyline = L.polyline(my_latlngs);
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

        layerLocation.addLayer(layerLocationMarkers);
        layerFinances.addLayer(layerFinancesMarkers);
        layerCars.addLayer(layerCarsMarkers);
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

        layerLocation.addLayer(layerLocationClusters);
        layerFinances.addLayer(layerFinancesClusters);
        layerCars.addLayer(layerCarsClusters);
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
    return new L.DivIcon({html: '<div><span>' + childCount + '</span></div>', className: 'marker-cluster ' + c, iconSize: new L.Point(40, 40)});

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