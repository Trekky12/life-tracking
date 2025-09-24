'use strict';

const routeModal = document.getElementById("route-modal");
const loadingOverlay = document.getElementById('loading-overlay');

let layerTrains = new L.LayerGroup();
let layerCars = new L.LayerGroup();
let layerPlanes = new L.LayerGroup();
let layerCarRentals = new L.LayerGroup();
let layerHotels = new L.LayerGroup();
let layerEvents = new L.LayerGroup();
let layerWaypoints = new L.LayerGroup();
let layerShips = new L.LayerGroup();

let controlLayer = null;
let mymap = null;
let routeControl = null;
let my_markers = [];

const tripDays = document.querySelectorAll('.trip_day');
const changeDayLinks = document.querySelectorAll('.change_day');

const today = moment(Date.now()).format('YYYY-MM-DD');
const currentDayButton = document.querySelector('.change_day[data-date="' + today + '"]');
const newEventButton = document.querySelector('#new-event-btn');

let addEventLink = "";
if (newEventButton) {
    addEventLink = newEventButton.href;
}

if (changeDayLinks !== null) {
    changeDayLinks.forEach(function (changeDayLink, idx) {
        changeDayLink.addEventListener('click', function (e) {
            e.preventDefault();
            changeDay(changeDayLink);
        });
    });
}

function changeDay(item) {
    let date = item.dataset.date;

    fromInput.value = date;
    toInput.value = date;

    // add from/to parameters to add event link
    if (newEventButton) {
        newEventButton.href = addEventLink + item.search;
    }

    // change url     
    window.history.pushState({}, '', window.location.pathname + item.search);


    getMarkers(date, date).then(function () {
        if (date) {
            let currentDay = document.getElementById('trip_day_' + date);
            tripDays.forEach(function (el) {
                if (el.id !== 'trip_day_none') {
                    el.classList.add('hidden');
                }
            });
            currentDay.classList.remove('hidden');
        } else {
            tripDays.forEach(function (el) {
                el.classList.remove('hidden');
            });
        }
        changeDayLinks.forEach(function (el) {
            el.querySelector('button').classList.add('button-outlined');
        });
        item.querySelector('button').classList.remove('button-outlined');
    });
}

let descriptionLinks = document.querySelectorAll('.trip_day .description_text');
descriptionLinks.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        event.target.parentElement.classList.toggle('active');
    });
});

const fromInput = document.getElementById('inputStart');
const toInput = document.getElementById('inputEnd');

const routeBtn = document.getElementById('createRoute');
if (routeBtn) {
    routeBtn.addEventListener('click', function (event) {
        event.preventDefault();
        routeControl.setWaypoints([]);
        my_markers.forEach(function (item, idx) {
            if (!item.data.isPlane && !item.data.isWithoutDate) {
                addWaypoint(item);
            }
        });
        routeControl.show();
    });
}

const reloadTripBtn = document.getElementById('reloadTrip');
if (reloadTripBtn !== null) {
    reloadTripBtn.addEventListener('click', function (e) {
        e.preventDefault();
        loadingWindowOverlay.classList.remove("hidden");
        window.location.reload();
    });
}

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

function drawMarkers(data) {

    layerTrains.clearLayers();
    layerCars.clearLayers();
    layerPlanes.clearLayers();
    layerCarRentals.clearLayers();
    layerHotels.clearLayers();
    layerEvents.clearLayers();
    layerWaypoints.clearLayers();
    layerShips.clearLayers();

    my_markers = [];

    let marker_idx = 0;
    for (marker_idx in data) {

        let marker = data[marker_idx];
        console.log(marker);

        // markers with end but not start position are changed
        if (marker.data.start_lat === null && marker.data.end_lat !== null) {
            marker.data.start_lat = marker.data.end_lat;
        }
        if (marker.data.start_lng === null && !marker.data.end_lng !== null) {
            marker.data.start_lng = marker.data.end_lng;
        }

        // ignore markers without start position
        if (marker.data.start_lat === null || marker.data.start_lng === null) {
            continue;
        }

        let options = { saved: true };
        if (marker.isCarrental) {
            options['icon'] = L.ExtraMarkers.icon({
                markerColor: 'red',
                shape: 'circle',
                innerHTML: document.getElementById('iconCarRentals').innerHTML
            });
        } else if (marker.isHotel) {
            options['icon'] = L.ExtraMarkers.icon({
                markerColor: 'blue',
                shape: 'circle',
                innerHTML: document.getElementById('iconHotels').innerHTML
            });
        } else if (marker.isEvent) {
            options['icon'] = L.ExtraMarkers.icon({
                markerColor: 'yellow',
                shape: 'circle',
                innerHTML: document.getElementById('iconEvents').innerHTML
            });
        } else if (marker.isPlane) {
            options['icon'] = L.ExtraMarkers.icon({
                markerColor: 'black',
                shape: 'circle',
                innerHTML: document.getElementById('iconPlanes').innerHTML
            });
        }

        /**
         * Start Marker
         */
        let start_marker = L.marker([marker.data.start_lat, marker.data.start_lng], options);
        start_marker.data = marker;
        start_marker.name = marker.data.name;
        start_marker.address = marker.data.start_address;

        /**
         * Popup
         */
        //let popup = "<h4>" + marker.data.name + "</h4>" + marker.data.popup;
        let navigationBtn = getAddToRouteLink(start_marker);

        let popup = document.createElement("div");
        if (!marker.isWaypoint) {
            let headline = document.createElement("h4");
            headline.innerHTML = marker.data.name;

            let pInner = document.createElement("p");
            pInner.innerHTML = marker.data.popup;

            popup.appendChild(headline);
            popup.appendChild(pInner);
        }
        popup.appendChild(navigationBtn);

        if (marker.isWaypoint) {
            let deleteBtn = getDeleteWaypointLink(marker.data.id, start_marker, true);
            popup.appendChild(document.createElement("br"));
            popup.appendChild(deleteBtn);
        }

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
        } else if (marker.isWaypoint) {
            layerWaypoints.addLayer(start_marker);
        } else if (marker.isShip) {
            layerShips.addLayer(start_marker);
        }

        /**
         * End Marker
         */
        if (marker.data.end_lat !== null && marker.data.end_lng !== null && marker.data.start_lat !== marker.data.end_lat && marker.data.start_lng !== marker.data.end_lng) {
            let end_marker = L.marker([marker.data.end_lat, marker.data.end_lng], options);
            end_marker.data = marker;
            end_marker.name = marker.data.name;
            end_marker.address = marker.data.end_address;
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
                //trainPolyline[0] = L.polyline(tripLine, { color: 'black', weight: '5' }).bindPopup(popup);
                //trainPolyline[1] = L.polyline(tripLine, { color: 'black', weight: '3', dashArray: '20, 20', dashOffset: '0' }).bindPopup(popup);
                //trainPolyline[2] = L.polyline(tripLine, { color: 'white', weight: '3', dashArray: '20, 20', dashOffset: '20' }).bindPopup(popup);

                let middle = calculateMidPoint(start_marker, end_marker);
                trainPolyline[0] = L.curve([
                    'M', [marker.data.start_lat, marker.data.start_lng],
                    'Q', middle, [marker.data.end_lat, marker.data.end_lng]
                ], { color: 'black', weight: '5' }).bindPopup(popup);
                trainPolyline[1] = L.curve([
                    'M', [marker.data.start_lat, marker.data.start_lng],
                    'Q', middle, [marker.data.end_lat, marker.data.end_lng]
                ], { color: 'black', weight: '3', dashArray: '20, 20', dashOffset: '0' }).bindPopup(popup);
                trainPolyline[2] = L.curve([
                    'M', [marker.data.start_lat, marker.data.start_lng],
                    'Q', middle, [marker.data.end_lat, marker.data.end_lng]
                ], { color: 'white', weight: '3', dashArray: '20, 20', dashOffset: '20' }).bindPopup(popup);

                layerTrains.addLayer(L.layerGroup(trainPolyline));

                // remove start marker when there is a polyline
                layerTrains.removeLayer(start_marker);
            } else if (marker.isPlane) {
                let middle = calculateMidPoint(start_marker, end_marker);
                let planeCuve = L.curve([
                    'M', [marker.data.start_lat, marker.data.start_lng],
                    'Q', middle, [marker.data.end_lat, marker.data.end_lng]
                ], { color: 'black', weight: '3', dashArray: '10, 10' }).bindPopup(popup);

                //let planePolyline = L.polyline(tripLine, {color: 'black', weight: '3'}).bindPopup(marker.popup);

                layerPlanes.addLayer(start_marker);
                layerPlanes.addLayer(planeCuve);
            } else if (marker.isCar) {
                let streetPolyline = [];
                //streetPolyline[0] = L.polyline(tripLine, { color: 'gray', weight: '5' }).bindPopup(popup);
                //streetPolyline[1] = L.polyline(tripLine, { color: 'white', weight: '1', dashArray: '10, 10', dashOffset: '0' }).bindPopup(popup);

                let middle = calculateMidPoint(start_marker, end_marker);
                streetPolyline[0] = L.curve([
                    'M', [marker.data.start_lat, marker.data.start_lng],
                    'Q', middle, [marker.data.end_lat, marker.data.end_lng]
                ], { color: 'gray', weight: '5' }).bindPopup(popup);
                streetPolyline[1] = L.curve([
                    'M', [marker.data.start_lat, marker.data.start_lng],
                    'Q', middle, [marker.data.end_lat, marker.data.end_lng]
                ], { color: 'white', weight: '1', dashArray: '10, 10', dashOffset: '0' }).bindPopup(popup);

                layerCars.addLayer(L.layerGroup(streetPolyline));

                // remove start marker when there is a polyline
                layerCars.removeLayer(start_marker);
            } else if (marker.isShip) {
                let shipPolyline = [];
                //shipPolyline[0] = L.polyline(tripLine, { color: 'blue', weight: '5', dashArray: '10, 10', dashOffset: '0' }).bindPopup(popup);
                let middle = calculateMidPoint(start_marker, end_marker);
                shipPolyline[0] = L.curve([
                    'M', [marker.data.start_lat, marker.data.start_lng],
                    'Q', middle, [marker.data.end_lat, marker.data.end_lng]
                ], { color: 'blue', weight: '5', dashArray: '10, 10', dashOffset: '0' }).bindPopup(popup);

                layerShips.addLayer(L.layerGroup(shipPolyline));

                // remove start marker when there is a polyline
                layerShips.removeLayer(start_marker);
            }

        }

    }

    // fit bounds of markers
    if (my_markers.length > 0) {
        var group = new L.featureGroup(my_markers);
        mymap.fitBounds(group.getBounds());
    }

}

function getNextWaypointPos() {
    let pos = 0;
    let waypoints = routeControl.getWaypoints().length;
    let start = routeControl.getWaypoints()[0].latLng;
    let end = routeControl.getWaypoints()[waypoints - 1].latLng;
    if (start) {
        pos = routeControl.getWaypoints().length - 1;
    }
    if (start && end) {
        pos = routeControl.getWaypoints().length;
    }

    return pos;
}
function addWaypoint(marker) {
    let pos = getNextWaypointPos();
    let name = marker.name ? marker.name + " (" + marker.address + ")" : null;
    let waypoint = L.Routing.waypoint(marker.getLatLng(), name, { saved: marker.options.saved });
    routeControl.spliceWaypoints(pos, 1, waypoint);
    return pos;
}

/**
 * Fix Geocoder using Promises
 * @see https://github.com/perliedman/leaflet-control-geocoder/issues/362#issuecomment-2717417710
 */
function RoutingMachineGeocoder(geocoder) {
    /*const geocode = geocoder.geocode;
    if ('AsyncFunction' === geocode[Symbol.toStringTag]) {
        geocoder.geocode = function (query, callback, context) {
            geocode.apply(this, [query]).then(callback.bind(context));
        };
    }*/

    const reverse = geocoder.reverse;
    if ('AsyncFunction' === reverse[Symbol.toStringTag]) {
        geocoder.reverse = function (location, scale, callback, context) {
            reverse.apply(this, [location, scale]).then(callback.bind(context));
        };
    }

    return geocoder;
}

function initMap() {
    mymap = L.map('trip-map', {
        fullscreenControl: {
            pseudoFullscreen: true
        }
    }).setView([default_location.lat, default_location.lng], default_location.zoom);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(mymap);

    mymap.addLayer(layerPlanes);
    mymap.addLayer(layerTrains);
    mymap.addLayer(layerCars);
    mymap.addLayer(layerCarRentals);
    mymap.addLayer(layerHotels);
    mymap.addLayer(layerEvents);
    mymap.addLayer(layerWaypoints);
    mymap.addLayer(layerShips);

    // layer control
    controlLayer = L.control.layers(null, null, {
        collapsed: false
    });
    controlLayer.addOverlay(layerPlanes, "<span id='layerPlanes'>" + document.getElementById('iconPlanes').innerHTML + "</span>");
    controlLayer.addOverlay(layerTrains, "<span id='layerTrains'>" + document.getElementById('iconTrains').innerHTML + "</span>");
    controlLayer.addOverlay(layerShips, "<span id='layerShips'>" + document.getElementById('iconShips').innerHTML + "</span>");
    controlLayer.addOverlay(layerCars, "<span id='layerStreets'>" + document.getElementById('iconStreets').innerHTML + "</span>");
    controlLayer.addOverlay(layerCarRentals, "<span id='layerCarrental'>" + document.getElementById('iconCarRentals').innerHTML + "</span>");
    controlLayer.addOverlay(layerHotels, "<span id='layerHotels'>" + document.getElementById('iconHotels').innerHTML + "</span>");
    controlLayer.addOverlay(layerEvents, "<span id='layerEvents'>" + document.getElementById('iconEvents').innerHTML + "</span>");
    controlLayer.addTo(mymap);

    // current location
    var lc = L.control.locate({
        strings: {
            title: lang.set_current_location,
            showPopup: false
        },
        locateOptions: {
            enableHighAccuracy: true
        },
        /*createButtonCallback: function (container, options) {
            var link = L.DomUtil.create('a', 'leaflet-bar-part leaflet-bar-part-single', container);
            link.title = options.strings.title;
            link.role = 'button';
            link.href = '#';
            var icon = L.DomUtil.create(options.iconElementTag, options.icon, link);
            icon.innerHTML = document.getElementById('iconLocation').innerHTML;

            if (options.strings.text !== undefined) {
                var text = L.DomUtil.create(options.textElementTag, 'leaflet-locate-text', link);
                text.textContent = options.strings.text;
                link.classList.add('leaflet-locate-text-active');
                link.parentNode.style.display = "flex";
                if (options.icon.length > 0) {
                    icon.classList.add('leaflet-locate-icon');
                }
            }

            return { link: link, icon: icon };
        },*/
    });
    mymap.addControl(lc);

    L.easyPrint({
        position: 'bottomleft',
        sizeModes: ['A4Portrait', 'A4Landscape'],
        spinnerBgColor: '#1565c0'
    }).addTo(mymap);

    // select current day
    if (!fromInput.value && !toInput.value && currentDayButton) {
        changeDay(currentDayButton);
    } else {
        getMarkers(fromInput.value, toInput.value);

        // add from/to parameters to add event link
        if (newEventButton) {
            newEventButton.href = addEventLink + '?from=' + fromInput.value + "&to=" + toInput.value;
        }
    }

    /**
     * @see https://github.com/perliedman/leaflet-routing-machine/issues/236
     * @see http://gis.stackexchange.com/questions/193235/leaflet-routing-machine-how-to-dinamically-change-router-settings
     */
    var geoPlan = L.Routing.Plan.extend({

        createGeocoders: function () {
            var container = L.Routing.Plan.prototype.createGeocoders.call(this);

            let addButton = createButton(container, "add");
            let reverseButton = createButton(container, "reverse");
            let walkButton = createButton(container, "walk");
            let bikeButton = createButton(container, "bike");
            let carButton = createButton(container, "car", true);

            L.DomEvent.on(addButton, 'click', function () {
                this.spliceWaypoints(routeControl.getWaypoints().length, 0, null);
            }, this);

            L.DomEvent.on(reverseButton, 'click', function () {
                let waypoints = routeControl.getWaypoints().reverse();
                this.setWaypoints(waypoints);
            }, this);

            L.DomEvent.on(walkButton, 'click', function () {
                setRoutingProfile('mapbox/walking');
            }, this);

            L.DomEvent.on(bikeButton, 'click', function () {
                setRoutingProfile('mapbox/cycling');
            }, this);

            L.DomEvent.on(carButton, 'click', function () {
                setRoutingProfile('mapbox/driving');
            }, this);

            let saveButton = createButton(container, "save");
            L.DomEvent.on(saveButton, 'click', async function () {
                let name = await inputDialog(lang.trips_route_name_prompt, false);
                if (name !== false) {
                    getCSRFToken().then(function (token) {
                        let data = { 'name': name, 'start_date': fromInput.value, 'end_date': toInput.value, 'waypoints': routeControl.getWaypoints(), 'profile': routeControl.getRouter().options.profile };
                        data['csrf_name'] = token.csrf_name;
                        data['csrf_value'] = token.csrf_value;

                        return fetch(jsObject.trip_add_route, {
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
                        if (data['status'] === 'success') {
                            alert(lang.trips_route_saved_successfully);
                        } else {
                            alert(lang.trips_route_saved_error);
                        }
                    }).catch(function (error) {
                        console.log(error);
                        alert(lang.trips_route_saved_error);
                    });
                }
            }, this);

            let loadButton = createButton(container, "load");
            L.DomEvent.on(loadButton, 'click', function () {

                freeze();
                loadingOverlay.classList.remove('hidden');

                fetch(jsObject.trip_list_routes, {
                    method: 'GET',
                    credentials: "same-origin",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    }
                }).then(function (response) {
                    return response.json();
                }).then(function (data) {
                    let table = routeModal.querySelector("table");
                    let tbody = table.querySelector("tbody");
                    tbody.innerHTML = "";
                    data.forEach(function (val) {

                        let tr = document.createElement("tr");

                        let td_name = document.createElement("td");
                        td_name.innerHTML = val['name'];
                        tr.appendChild(td_name);

                        let td_start_date = document.createElement("td");
                        td_start_date.innerHTML = val['start_date'];
                        tr.appendChild(td_start_date);

                        let td_route = document.createElement("td");
                        let a_route = document.createElement("a");
                        a_route["href"] = "#";
                        a_route.dataset.route = val['id'];
                        a_route.classList.add("btn-route");
                        let span_route = document.createElement("span");
                        span_route.innerHTML = document.getElementById('iconRoute').innerHTML;
                        a_route.appendChild(span_route);
                        td_route.appendChild(a_route);
                        tr.appendChild(td_route);

                        table.addEventListener('click', function (event) {
                            let closest = event.target.closest('.btn-route');
                            if (closest) {
                                event.preventDefault();
                                let route = closest.dataset.route;
                                // get waypoints!
                                return fetch(jsObject.trip_route_waypoints + "?route=" + route, {
                                    method: 'GET',
                                    credentials: "same-origin"
                                }).then(function (response) {
                                    return response.json();
                                }).then(function (data) {
                                    if (data["profile"]) {
                                        setRoutingProfile(data["profile"]);
                                    }
                                    routeControl.setWaypoints(data["waypoints"]);
                                    routeModal.classList.remove('visible');

                                    // select the day
                                    if (data["start_date"]) {
                                        let dayButton = document.querySelector('.change_day[data-date="' + data["start_date"] + '"]');
                                        changeDay(dayButton);
                                    }

                                }).catch(function (error) {
                                    console.log(error);
                                });
                            }
                        });

                        let td_delete = document.createElement("td");
                        let a_delete = document.createElement("a");
                        a_delete["href"] = "#";
                        a_delete.dataset.url = val['delete'];
                        a_delete.classList.add("btn-delete");
                        let span_delete = document.createElement("span");
                        span_delete.innerHTML = document.getElementById('iconTrash').innerHTML;
                        a_delete.appendChild(span_delete);
                        td_delete.appendChild(a_delete);
                        tr.appendChild(td_delete);

                        tbody.appendChild(tr);
                    });

                    var routesTables = new JSTable(table, {
                        perPage: 10,
                        labels: tableLabels,
                        layout: {
                            top: null,
                            bottom: "{pager}"
                        },
                        columns: [
                            {
                                select: 0,
                                sortable: true,
                            },
                            {
                                select: [1],
                                render: function (cell, idx) {
                                    let data = cell.innerHTML;
                                    return data ? moment(data).format(i18n.dateformatJS.date) : "";
                                }
                            },
                            {
                                select: [2, 3],
                                sortable: false,
                                searchable: false
                            }
                        ]
                    });

                }).catch(function (error) {
                    console.log(error);
                }).finally(function () {
                    routeModal.classList.add('visible');
                    loadingOverlay.classList.add('hidden');
                    unfreeze();
                });
            }, this);

            return container;
        }
    });


    let plan = new geoPlan(
        [],
        {
            //geocoder: new L.Control.Geocoder.Nominatim(),
            //geocoder: new L.Control.Geocoder.LatLng(),
            geocoder: RoutingMachineGeocoder(new L.Control.Geocoder.Mapbox({
                reverseQueryParams: {
                    language: i18n.routing
                },
                apiKey: mapbox_token
            })),
            createMarker: function (i, wp) {
                // has already saved marker
                if (wp.options.saved) {
                    return null;
                }
                let options = {
                    saved: false,
                    draggable: this.draggableWaypoints
                };
                let marker = L.marker(wp.latLng, options);
                let popup = document.createElement("div");
                let navigationBtn = getAddToRouteLink(marker);
                let saveBtn = saveWaypointLink(marker, wp);
                let deleteBtn = getDeleteWaypointLink(null, marker, false);
                popup.appendChild(navigationBtn);
                popup.appendChild(document.createElement("br"));
                popup.appendChild(saveBtn);
                popup.appendChild(document.createElement("br"));
                popup.appendChild(deleteBtn);
                marker.bindPopup(popup);
                return marker;
            },
            routeWhileDragging: false,
            reverseWaypoints: false,
            addWaypoints: true,
            language: i18n.routing,
            draggableWaypoints: true
        });

    routeControl = L.Routing.control({
        waypoints: [],
        autoRoute: true,
        //        router: new L.Routing.OSRMv1({
        //            profile: 'driving',
        //            suppressDemoServerWarning: true,
        //            urlParameters: {
        //            }
        //        }),
        router: L.Routing.mapbox(mapbox_token, {
            profile: "mapbox/driving",
            routingOptions: {
                alternatives: false,
                steps: false
            },
            language: i18n.routing
        }),
        show: false,
        collapsible: true,
        collapseBtn: function (itinerary) {
            var collapseBtn = L.DomUtil.create('span', itinerary.options.collapseBtnClass);
            collapseBtn.innerHTML = document.getElementById('iconRoute').innerHTML;
            L.DomEvent.on(collapseBtn, 'click', itinerary._toggle, itinerary);
            itinerary._container.insertBefore(collapseBtn, itinerary._container.firstChild);
        },
        showAlternatives: false,
        routeWhileDragging: false,
        plan: plan,
        attachResultsToContainer: true
    }).addTo(mymap);

    routeControl.on('routingerror', function (e) {
        if (e.error.target.status == 429) {
            alert(lang.routing_error_too_many_requests);
        } else {
            alert(lang.routing_error);
        }
    });

    mymap.on('contextmenu', function (e) {
        // add new marker to route
        let marker = L.marker(e.latlng, {});
        addWaypoint(marker);
        routeControl.show();
    });

    mymap.on("movestart zoomstart", function (e) {
        isMapMove = true;
    });
    mymap.on("moveend zoomend", function (e) {
        isMapMove = false;
    });

    mymap.on("fullscreenchange", function (e) {
        if (mymap.isFullscreen()) {
            isMapMove = false;
        } else {
            isMapMove = true;
        }
    });
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

function createButton(container, type, active = false) {
    var btn = L.DomUtil.create('button', '', container);
    btn.setAttribute('type', 'button');
    btn.title = type;
    btn.classList.add("leaflet-routing-btn");
    btn.classList.add(type);
    switch (type) {
        case "walk":
            btn.innerHTML = document.getElementById('iconWalking').innerHTML;
            break;
        case "car":
            btn.innerHTML = document.getElementById('iconCarRentals').innerHTML;
            break;
        case "bike":
            btn.innerHTML = document.getElementById('iconBiking').innerHTML;
            break;
        case "load":
            btn.innerHTML = document.getElementById('iconMaps').innerHTML;
            break;
        case "save":
            btn.innerHTML = document.getElementById('iconSave').innerHTML;
            break;
        case "add":
            btn.innerHTML = document.getElementById('iconAdd').innerHTML;
            break;
        case "reverse":
            btn.innerHTML = document.getElementById('iconReverse').innerHTML;
            break;
    }

    if (active) {
        btn.classList.add('active');
    }
    return btn;
}


tripDays.forEach(function (day) {
    new Sortable(day, {
        draggable: ".trip_event",
        handle: ".trip-icon",
        ghostClass: 'trip_event-placeholder',
        dataIdAttr: 'data-event',
        onUpdate: function (evt) {
            var data = { 'events': this.toArray() };

            getCSRFToken().then(function (token) {
                data['csrf_name'] = token.csrf_name;
                data['csrf_value'] = token.csrf_value;

                return fetch(jsObject.trip_event_position_url, {
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
                // fetch markers so that are correct sorted for routing
                getMarkers(fromInput.value, toInput.value);
            }).catch(function (error) {
                console.log(error);
            });
        }
    });
});

function getAddToRouteLink(marker) {
    let navigationBtn = document.createElement("a");
    navigationBtn.classList.add("navigation-btn");
    navigationBtn.innerHTML = lang.routing_add_to_route;

    navigationBtn.addEventListener("click", function () {
        addWaypoint(marker);
        routeControl.show();
    });

    return navigationBtn;
}

function saveWaypointLink(marker, waypoint) {
    let saveBtn = document.createElement("a");
    saveBtn.classList.add("navigation-btn");
    saveBtn.innerHTML = lang.trips_waypoint_save;

    saveBtn.addEventListener("click", function () {

        // Create new Waypoint
        getCSRFToken().then(function (token) {
            let data = { 'start_lat': marker.getLatLng().lat, 'start_lng': marker.getLatLng().lng, 'type': 'WAYPOINT', 'start_date': fromInput.value, 'end_date': toInput.value };

            if (waypoint.name) {
                data['start_address'] = waypoint.name;
            }

            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(jsObject.trip_add_waypoint, {
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

            // create new saved marker
            let waypoint_marker = L.marker(marker.getLatLng(), { saved: true });
            let popup = document.createElement("div");
            let navigationBtn = getAddToRouteLink(waypoint_marker);
            let deleteBtn = getDeleteWaypointLink(data['id'], waypoint_marker, true);
            popup.appendChild(navigationBtn);
            popup.appendChild(document.createElement("br"));
            popup.appendChild(deleteBtn);
            waypoint_marker.bindPopup(popup);
            layerWaypoints.addLayer(waypoint_marker);

            // delete automatic waypoint marker
            mymap.removeLayer(marker);

        }).catch(function (error) {
            console.log(error);
        });

    });

    return saveBtn;
}

function getDeleteWaypointLink(id, marker, isSaved = false) {
    let deleteBtn = document.createElement("a");
    deleteBtn.classList.add("navigation-btn");
    deleteBtn.innerHTML = lang.delete_text;
    deleteBtn.addEventListener("click", function () {
        // Delete waypoint
        if (isSaved) {
            getCSRFToken().then(function (token) {
                let data = {};
                data['csrf_name'] = token.csrf_name;
                data['csrf_value'] = token.csrf_value;

                return fetch(jsObject.trip_delete_waypoint + '?id=' + id, {
                    method: 'DELETE',
                    credentials: "same-origin",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)

                });
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                mymap.removeLayer(marker);
                my_markers.splice(my_markers.indexOf(marker), 1);
            }).catch(function (error) {
                console.log(error);
            });
        }
        // remove from route
        let waypoint_pos = null;
        let waypoint_idx = 0;
        routeControl.getWaypoints().forEach(function (wp) {
            if (wp.latLng && wp.latLng.lat === marker.getLatLng().lat && wp.latLng.lng === marker.getLatLng().lng) {
                waypoint_pos = waypoint_idx;
            }
            waypoint_idx++;
        });
        if (waypoint_pos !== null) {
            routeControl.spliceWaypoints(waypoint_pos, 1);
        }

    });
    return deleteBtn;
}

document.getElementById("modal-close-btn").addEventListener('click', function (e) {
    routeModal.classList.remove('visible');
});

function setRoutingProfile(profile) {
    routeControl.getRouter().options.profile = profile;
    routeControl.route();
    //routeControl.setWaypoints(routeControl.getWaypoints());

    let walkButton = document.querySelector('.leaflet-routing-geocoders .leaflet-routing-btn.walk');
    let bikeButton = document.querySelector('.leaflet-routing-geocoders .leaflet-routing-btn.bike');
    let carButton = document.querySelector('.leaflet-routing-geocoders .leaflet-routing-btn.car');

    switch (profile) {
        case 'mapbox/walking':
            walkButton.classList.add('active');
            bikeButton.classList.remove('active');
            carButton.classList.remove('active');
            break;
        case 'mapbox/cycling':
            walkButton.classList.remove('active');
            bikeButton.classList.add('active');
            carButton.classList.remove('active');
            break;
        case 'mapbox/driving':
            walkButton.classList.remove('active');
            bikeButton.classList.remove('active');
            carButton.classList.add('active');
            break;
    }
}