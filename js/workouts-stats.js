'use strict';


const workoutCharts = document.querySelectorAll(".workoutChart");
workoutCharts.forEach(function (workoutChart) {

    let datasets = [];
    let data = JSON.parse(workoutChart.dataset.values);

    data.forEach(function (set, idx) {
        datasets.push(
                {
                    label: lang.workouts_set + ' ' + (idx + 1),
                    data: set,
                    spanGaps: true,
                    fill: false,
                    borderColor: randomColor({
                        hue: 'blue',
                        luminosity: 'bright'
                    })
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
                xAxes: [
                    {
                        type: "time",
                        time: {
                            unit: 'day'
                        }
                    }
                ],
                yAxes: [
                    {
                        ticks: {
                            stepSize: 1
                        }
                    }
                ]
            }
        }
    });
});