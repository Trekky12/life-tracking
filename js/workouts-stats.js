'use strict';


const workoutCharts = document.querySelectorAll(".workoutChart");
workoutCharts.forEach(function (workoutChart) {

    let datasets = [];
    let data = JSON.parse(workoutChart.dataset.values);

    let dates = JSON.parse(workoutChart.dataset.dates);

    data.forEach(function (sets, idx) {

        let color = randomColor({
            hue: 'blue',
            luminosity: 'bright'
        });
        datasets.push(
            {
                label: lang.workouts_set + ' ' + (idx + 1),
                data: sets,
                spanGaps: false,
                fill: false,
                borderColor: color,
                backgroundColor: color,
                pointRadius: 5,
                pointHoverRadius: 6
            });
    });

    let annotations = [];
    dates.forEach(function (date) {
        annotations.push({
            type: 'line',
            xMin: date,
            xMax: date,
            borderColor: '#CCCCCC',
            borderWidth: 2,
            drawTime: 'beforeDatasetsDraw'
        });
    });


    new Chart(workoutChart, {
        type: 'line',
        data: {
            datasets: datasets
        },
        options: {
            //responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: "time",
                    time: {
                        unit: 'day',
                        displayFormats: {
                            day: i18n.dateformatJS.date
                        },
                        tooltipFormat: i18n.dateformatJS.date
                    },
                    min: workoutChart.dataset.min,
                    max: workoutChart.dataset.max
                },
                y:
                {
                    ticks: {
                        stepSize: 1
                    }
                }

            },
            plugins: {
                autocolors: false,
                annotation: {
                    annotations: annotations
                }
            }
        }
    });
});