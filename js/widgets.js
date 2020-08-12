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
        item.innerHTML = '';
        switch (data["type"]) {
            case 'efa':
                item.appendChild(createEFATable(data["result"]));
                break;
            case 'currentweather':
                item.appendChild(processWeather(data["result"]));
                break;
            case 'weatherforecast':
                item.appendChild(processWeatherForecast(data["result"]));
                break;
        }

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

function processWeather(data) {
    if (!data || !data.main || typeof data.main.temp === "undefined") {
        // Did not receive usable new data.
        // Maybe this needs a better check?
        return;
    }

    var temperature = roundValue(data.main.temp);
    var weatherType = getIcon(data.weather[0].icon);
    var description = data.weather[0]["description"];

    var wrapper = document.createElement("div");

    var large = document.createElement("div");
    large.className = "large light";

    var weatherIcon = document.createElement("span");
    weatherIcon.className = "wi weathericon " + weatherType;
    large.appendChild(weatherIcon);

    var temperatureD = document.createElement("span");
    temperatureD.className = "bright";
    temperatureD.innerHTML = " " + temperature + "&deg;C";
    large.appendChild(temperatureD);

    wrapper.appendChild(large);

    var descriptionD = document.createElement("div");
    descriptionD.className = "description";
    descriptionD.innerHTML = description;

    wrapper.appendChild(descriptionD);

    return wrapper;
}

function getIcon(type) {

    var iconTable = {
        "01d": "wi-day-sunny",
        "02d": "wi-day-cloudy",
        "03d": "wi-cloudy",
        "04d": "wi-cloudy-windy",
        "09d": "wi-showers",
        "10d": "wi-rain",
        "11d": "wi-thunderstorm",
        "13d": "wi-snow",
        "50d": "wi-fog",
        "01n": "wi-night-clear",
        "02n": "wi-night-cloudy",
        "03n": "wi-night-cloudy",
        "04n": "wi-night-cloudy",
        "09n": "wi-night-showers",
        "10n": "wi-night-rain",
        "11n": "wi-night-thunderstorm",
        "13n": "wi-night-snow",
        "50n": "wi-night-alt-cloudy-windy"
    };

    return iconTable[type];

}

function roundValue(value) {
    return parseFloat(value).toFixed(1);
}

function processWeatherForecast(data) {
    var forecasts = [];
    for (var i = 0, count = data.list.length; i < count; i++) {

        var forecast = data.list[i];
        var date = new Date(forecast.dt * 1000);

        forecasts.push({
            day: date.getDate() + '.' + date.getMonth() + '.',
            weekday: moment(date).format('ddd'),
            icon: getIcon(forecast.weather[0].icon),
            description: forecast.weather[0].description,
            maxTemp: roundValue(forecast.temp.max),
            minTemp: roundValue(forecast.temp.min),
            rain: roundValue(forecast.rain)
        });
    }
    var table = document.createElement("table");
    table.className = "small";

    var row = document.createElement("tr");
    table.appendChild(row);

    var dayHead = document.createElement("th");
    row.appendChild(dayHead);

    var descHead = document.createElement("th");
    row.appendChild(descHead);

    var maxHead = document.createElement("th");
    maxHead.innerHTML = "max";
    row.appendChild(maxHead);

    var minHead = document.createElement("th");
    minHead.innerHTML = "min";
    row.appendChild(minHead);

    for (var f in forecasts) {
        var forecast = forecasts[f];

        var row = document.createElement("tr");
        table.appendChild(row);

        var dayCell = document.createElement("td");
        dayCell.className = "day";
        dayCell.innerHTML = forecast.weekday;
        row.appendChild(dayCell);

        var iconCell = document.createElement("td");
        iconCell.className = "bright weather-icon";
        row.appendChild(iconCell);

        var icon = document.createElement("span");
        icon.className = "wi weathericon " + forecast.icon;
        iconCell.appendChild(icon);

        var description = document.createElement("span");
        description.className = "description";
        description.innerHTML = forecast.description;
        iconCell.appendChild(description);


        var maxTempCell = document.createElement("td");
        maxTempCell.innerHTML = forecast.maxTemp + "&deg;C";
        maxTempCell.className = "align-right bright max-temp";
        row.appendChild(maxTempCell);

        var minTempCell = document.createElement("td");
        minTempCell.innerHTML = forecast.minTemp + "&deg;C";
        minTempCell.className = "align-right min-temp";
        row.appendChild(minTempCell);

    }

    return table;
}