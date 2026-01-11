'use strict';

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

function quadraticBezierPoints(start, middle, end, steps = 10) {
    let latlng1 = start.getLatLng();
    let latlng2 = end.getLatLng();

    const points = [];
    for (let i = 0; i <= steps; i++) {
        const t = i / steps;
        const x = (1 - t) * (1 - t) * latlng1.lng +
            2 * (1 - t) * t * middle[1] +
            t * t * latlng2.lng;
        const y = (1 - t) * (1 - t) * latlng1.lat +
            2 * (1 - t) * t * middle[0] +
            t * t * latlng2.lat;
        points.push([y, x]);
    }
    return points;
}