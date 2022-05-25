'use strict';

const requestWidgets = document.querySelectorAll('.request_widget');
requestWidgets.forEach(function (item) {
    load(item);

    var reload = item.dataset.reload;

    if (reload > 0) {
        setInterval(function () {
            load(item);
        }, reload * 1000);
    }
});

function load(item) {
    let id = item.dataset.id;

    return fetch(jsObject.frontpage_widget_request + id, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        item.innerHTML = data["data"];
    }).catch(function (error) {
        console.log(error);
    });
}

function createEFATable(efa_data) {

    var departuresTable = document.createElement("table");
    departuresTable.classList.add("small", "table");
    departuresTable.border = '0';

    var departures = efa_data.departureList;

    for (var d in departures) {
        var departureRow = createEFADataRow(departures[d]);
        departuresTable.appendChild(departureRow);
    }
    return departuresTable;

}

function createEFADataRow(data) {

    var row = document.createElement("tr");

    var line = document.createElement("td");
    line.className = "departures__departure__line";
    line.innerHTML = '<span class="departures__departure__line__number xsmall">' + data.servingLine.number + '</span>';
    row.appendChild(line);

    var destination = document.createElement("td");
    destination.innerHTML = '<span class="departures__departure__direction small">' + data.servingLine.direction + '</span>';
    row.appendChild(destination);

    var departureTime = new Date;
    var calculatedDelay = 0;
    var dateTime = departureTime = new Date(data.dateTime.year, data.dateTime.month - 1, data.dateTime.day, data.dateTime.hour, data.dateTime.minute, 0);

    if (data.realDateTime) {
        var realDateTime = new Date(data.realDateTime.year, data.realDateTime.month - 1, data.realDateTime.day, data.realDateTime.hour, data.realDateTime.minute, 0);
        departureTime = realDateTime;
        calculatedDelay = (realDateTime - dateTime) / 1000 / 60;
    } else {
        departureTime = dateTime;
    }

    var hour = departureTime.getHours();

    var minute = departureTime.getMinutes();
    if (minute < 10) {
        minute = "0" + minute;
    }

    var delay = '';
    if (data.servingLine.delay > 0) {
        delay = data.servingLine.delay;
    } else if (calculatedDelay !== 0) {
        delay = calculatedDelay;
    }

    var departureCell = document.createElement("td");
    departureCell.className = "departures__departure";
    departureCell.innerHTML = '<span class="departures__departure__time-relative small bright">' + hour + ':' + minute + '</span>';
    row.appendChild(departureCell);

    var delayCell = document.createElement("td");
    delayCell.className = "departures__delay";
    if (delay > 0) {
        delayCell.innerHTML = '<span class="departures__delay__time xsmall">+ ' + delay + '</span>';
    }
    row.appendChild(delayCell);

    return row;
}

