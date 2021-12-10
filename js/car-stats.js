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