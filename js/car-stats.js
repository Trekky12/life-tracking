'use strict';


let fuelChart = document.querySelector("#fuelChart");
if (fuelChart) {

    let datasets = [];
    let data = JSON.parse(fuelChart.dataset.values);

    for (let car_idx in data) {
        let car = data[car_idx];
        datasets.push(
            {
                label: lang.consumption + ' ' + car["name"],
                data: car["data"],
                fill: false,
                borderColor: randomColor({
                    hue: 'blue',
                    luminosity: 'bright'
                })
            });
    }

    new Chart(fuelChart, {
        data: {
            datasets: datasets,
            labels: JSON.parse(fuelChart.dataset.labels),
        },
        type: 'line',
        options: {
            scales: {
                x: {
                    type: "time",
                    time: {
                        unit: 'day',
                        displayFormats: {
                            day: i18n.dateformatJS.date
                        }
                    }
                }
            }
        }
    });
}


/**
    * Set km/year calculation base
    */
let setCalculationDate = document.querySelectorAll('.set_calculation_date');
setCalculationDate.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();

        let state = 0;
        if (item.checked) {
            if (item.dataset.type === "1") {
                state = 1;
            }
            if (item.dataset.type === "2") {
                state = 2;
            }
        }
        getCSRFToken(true).then(function (token) {

            var data = token;
            data["state"] = state;

            return fetch(jsObject.set_mileage_type, {
                method: 'POST',
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });
        }).then(function (response) {
            allowedReload = true;
            window.location.reload(true);
        }).catch(function (error) {
            console.log(error);
        });

    });
});