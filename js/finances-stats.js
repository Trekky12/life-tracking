'use strict'

let financeSummaryChart = document.querySelector("#financeSummaryChart");
if (financeSummaryChart) {
    new Chart(financeSummaryChart, {
        data: {
            labels: JSON.parse(financeSummaryChart.dataset.labels),
            datasets: [
                {
                    label: financeSummaryChart.dataset.label1,
                    data: JSON.parse(financeSummaryChart.dataset.values1),
                    backgroundColor: '#FF0000'
                },
                {
                    label: financeSummaryChart.dataset.label2,
                    data: JSON.parse(financeSummaryChart.dataset.values2),
                    backgroundColor: '#008800'
                }
            ]
        },
        type: 'bar',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    ticks: {
                        min: 0
                    }
                }
            }
        }
    });
}

/**
 * Custom HTML Legend
 * @see https://www.chartjs.org/docs/master/samples/legend/html.html
 */
const getOrCreateLegendList = (chart, id) => {
    const legendContainer = document.getElementById(id);
    let listContainer = legendContainer.querySelector('ul');

    if (!listContainer) {
        listContainer = document.createElement('ul');
        legendContainer.appendChild(listContainer);
    }

    return listContainer;
};

const htmlLegendPlugin = {
    id: 'htmlLegend',
    afterUpdate(chart, args, options) {
        const ul = getOrCreateLegendList(chart, options.containerID);

        // Remove old legend items
        while (ul.firstChild) {
            ul.firstChild.remove();
        }

        // Reuse the built-in legendItems generator
        const items = chart.options.plugins.legend.labels.generateLabels(chart);

        items.forEach(item => {
            const li = document.createElement('li');

            li.onclick = () => {
                const { type } = chart.config;
                if (type === 'pie' || type === 'doughnut') {
                    // Pie and doughnut charts only have a single dataset and visibility is per item
                    chart.toggleDataVisibility(item.index);
                } else {
                    chart.setDatasetVisibility(item.datasetIndex, !chart.isDatasetVisible(item.datasetIndex));
                }
                chart.update();
            };

            // Color box
            const boxSpan = document.createElement('span');
            boxSpan.style.background = item.fillStyle;
            boxSpan.style.borderColor = item.strokeStyle;
            boxSpan.style.borderWidth = item.lineWidth + 'px';

            // Text
            const textContainer = document.createElement('p');
            textContainer.style.color = item.fontColor;
            textContainer.style.textDecoration = item.hidden ? 'line-through' : '';

            const text = document.createTextNode(item.text);
            textContainer.appendChild(text);

            li.appendChild(boxSpan);
            li.appendChild(textContainer);
            ul.appendChild(li);
        });
    }
};

//var defaultColors = ['#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#3B3EAC', '#0099C6', '#DD4477', '#66AA00', '#B82E2E', '#316395', '#994499', '#22AA99', '#AAAA11', '#6633CC', '#E67300', '#8B0707', '#329262', '#5574A6', '#3B3EAC'];
var defaultColors = randomColor({
    count: 100,
    hue: 'blue',
    luminosity: 'bright'
});
let financeDetailChart = document.querySelector("#financeDetailChart");
if (financeDetailChart) {
    var fdChart = new Chart(financeDetailChart, {
        data: {
            labels: JSON.parse(financeDetailChart.dataset.labels),
            datasets: [
                {

                    backgroundColor: defaultColors,
                    data: JSON.parse(financeDetailChart.dataset.values),
                    label: 'test'
                }
            ]
        },
        type: 'pie',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                htmlLegend: {
                    containerID: 'financeDetailChartLegend',
                },
                legend: {
                    display: false
                },
                tooltip: {
                    // @see https://stackoverflow.com/a/44010778
                    callbacks: {
                        title: function (tooltipItems) {
                            return tooltipItems[0].label;
                        },
                        label: function (tooltipItem) {
                            return tooltipItem.parsed.toFixed(2) + " " + i18n.currency
                        }
                    }
                },
            }
        },
        plugins: [htmlLegendPlugin]
    });
    financeDetailChart.before(fdChart.generateLegend());
}