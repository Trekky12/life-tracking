'use strict';


// csrf token array
var tokens = [{'csrf_name': jsObject.csrf_name, 'csrf_value': jsObject.csrf_value}];

moment.locale(i18n.template);

initialize();

initCharts();

function getCSRFToken() {

    // get new tokens
    if (tokens.length <= 2) {
        var last_token = tokens.pop();
        return getNewTokens(last_token);
    }

    if (tokens.length > 1) {
        return new Promise(function (resolve, reject) {
            resolve(tokens.pop());
        });
    }
}


function getNewTokens(token) {
    return fetch(jsObject.csrf_tokens_url, {
        method: 'POST',
        credentials: "same-origin",
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            'csrf_name': token.csrf_name,
            'csrf_value': token.csrf_value
        })
    }).then(function (response) {
        return response.json();
    }).then(function (json) {
        tokens = json;
    }).then(function () {
        return tokens.pop();
    }).catch(function (error) {
        console.log(error);
    });
}

function deleteObject(url, type) {

    let confirm_text = lang.really_delete;
    if (type === "board") {
        confirm_text = lang.really_delete_board;
    }
    if (type === "stack") {
        confirm_text = lang.really_delete_stack;
    }
    if (type === "card") {
        confirm_text = lang.really_delete_card;
    }
    if (type === "label") {
        confirm_text = confirm_text = lang.really_delete_label;
    }


    if (!confirm(confirm_text)) {
        return false;
    }

    getCSRFToken(true).then(function (token) {
        return fetch(url, {
            method: 'DELETE',
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(token)
        });
    }).then(function (response) {
        allowedReload = true;
        window.location.reload();
    }).catch(function (error) {
        console.log(error);
    });


}

function initialize() {
    let backbtn = document.querySelector('#go-back-btn');
    if (backbtn !== null) {
        backbtn.addEventListener('click', function () {
            window.history.back();
        });
    }

    let cancelbtn = document.querySelector('#cancel');
    if (cancelbtn !== null) {
        cancelbtn.addEventListener('click', function (e) {
            e.preventDefault();
            window.history.back();
        });
    }

    /**
     * Delete
     * https://elliotekj.com/2016/11/05/jquery-to-pure-js-event-listeners-on-dynamically-created-elements/
     */
    document.addEventListener('click', function (event) {
        // https://stackoverflow.com/a/50901269
        let closest = event.target.closest('.btn-delete');
        if (closest) {
            event.preventDefault();
            let url = closest.dataset.url;
            if (url) {
                let type = closest.dataset.type ? closest.dataset.type : "default";
                deleteObject(url, type);
            }else{
                closest.parentNode.remove();
            }
        }
    });

    /**
     * Alert
     */
    let closebtn = document.querySelectorAll('span.closebtn');
    closebtn.forEach(function (item, idx) {
        item.addEventListener('click', function (event) {
            event.target.parentElement.style.display = 'none';
        });
    });

    /**
     * Reset lastrun when startdate on recurring entries is changed
     */
    let recurring_start = document.querySelector('#financesRecurringForm #dateSelect');
    if (recurring_start !== null) {
        recurring_start.addEventListener('change', function (event) {
            document.querySelector("#financesRecurringForm input[name=last_run]").value = "";
        });
    }


    /**
     * Common finances
     */
    let checkboxCommon = document.querySelector('#checkboxCommon');
    if (checkboxCommon) {
        checkboxCommon.addEventListener('change', function (event) {
            document.querySelector('#commonValue').classList.toggle('hidden');
            var value = document.querySelector('#inputValue').value;
            if (value) {
                if (event.target.checked) {
                    // move value to common Value and the half into value
                    document.querySelector('#inputCommonValue').value = value;
                    document.querySelector('#inputValue').value = value / 2;
                } else {
                    // move commonValue to value and reset commonValue
                    document.querySelector('#inputValue').value = document.querySelector('#inputCommonValue').value;
                    document.querySelector('#inputCommonValue').value = "";
                }
            }
        });
    }


    let carServiceType = document.querySelectorAll('input.carServiceType');
    carServiceType.forEach(function (item, idx) {
        item.addEventListener('change', function (event) {
            document.querySelector("#carServiceFuel").classList.toggle('hidden');
            document.querySelector("#carServiceService").classList.toggle('hidden');
        });
    });

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
                window.location.reload();
            }).catch(function (error) {
                console.log(error);
            });

        });
    });

    /**
     * Logviewer autoscroll to bottom
     */
    let logViewer = document.querySelector('#logviewer');
    if (logViewer) {
        logViewer.scrollTop = logViewer.scrollHeight;
        let logviewer_checkboxes = document.querySelectorAll('.log-filter input[type="checkbox"]');
        logviewer_checkboxes.forEach(function (item, idx) {
            item.addEventListener('change', function (event) {
                let type = item.dataset.type;
                document.querySelectorAll('#logviewer .log-entry.' + type).forEach(function (entry, idx) {
                    entry.classList.toggle('hidden');
                });
                logViewer.scrollTop = logViewer.scrollHeight;
            });
        });
    }
}

/**
 * Charts
 */
function initCharts() {

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
                    yAxes: [{
                            ticks: {
                                min: 0
                            }
                        }]
                }
            }
        });
    }

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
                legend: {
                    position: 'top',
                    display: false
                },
                tooltips: {
                    // @see https://stackoverflow.com/a/44010778
                    callbacks: {
                        title: function (tooltipItem, data) {
                            return data['labels'][tooltipItem[0]['index']];
                        },
                        label: function (tooltipItem, chart) {
                            return chart['datasets'][tooltipItem['datasetIndex']]['data'][tooltipItem['index']].toFixed(2) + " " + i18n.currency;
                        }
                    }
                },
                // @see https://github.com/chartjs/Chart.js/issues/5049, 
                // https://github.com/chartjs/Chart.js/issues/3761,
                // https://jsfiddle.net/asimovwasright/xs15f60y/
                legendCallback: function (chart) {
                    let ul = document.createElement("ul");
                    ul.id = "chart-legend";
                    var items = chart.legend.legendItems;
                    items.forEach(function (item, idx) {
                        let li = document.createElement("li");
                        li.innerHTML = item.text;

                        let span = document.createElement('span');
                        span.classList = "legend-item";
                        span.style = "background-color:" + item.fillStyle + ";";
                        li.insertBefore(span, li.firstChild);

                        li.setAttribute("title", item.text);

                        li.addEventListener("click", function (event) {
                            event.target.closest('li').classList.toggle('excluded');

                            var index = idx;
                            var ci = fdChart.chart;
                            var meta = ci.legend.legendItems[index];
                            ci.data.datasets[0]._meta[ci.id].data[index].hidden = (!meta.hidden) ? true : null;
                            ci.update();
                        });

                        ul.appendChild(li);
                    });
                    return ul;
                }
            }
        });
        financeDetailChart.before(fdChart.generateLegend());
    }
}

// date Select on boards, finances, car control
flatpickr('#dateSelect', {
    "altInput": true,
    "altFormat": i18n.twig,
    "dateFormat": "Y-m-d",
    "locale": i18n.template
});
flatpickr('#dateSelectEnd', {
    "altInput": true,
    "altFormat": i18n.twig,
    "dateFormat": "Y-m-d",
    "locale": i18n.template
});


/**
 * Get Adress of marker
 */
document.addEventListener('click', function (event) {
    // https://stackoverflow.com/a/50901269
    let closest = event.target.closest('.btn-get-address');
    if (closest) {
        let lat = closest.dataset.lat;
        let lng = closest.dataset.lng;
        if (lat && lng) {
            event.preventDefault();
            fetch(jsObject.get_address_url + '?lat=' + lat + '&lng=' + lng, {
                method: 'GET',
                credentials: "same-origin"
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data['status'] === 'success') {
                    var output = '';

                    if (data['data']['police']) {
                        output += data['data']['police'] + '\n';
                    }

                    if (data['data']['road']) {
                        output += data['data']['road'] + " ";
                    }

                    if (data['data']['house_number']) {
                        output += data['data']['house_number'];
                    }

                    if (data['data']['road'] || data['data']['house_number']) {
                        output += '\n';
                    }

                    if (data['data']['postcode']) {
                        output += data['data']['postcode'] + " ";
                    }

                    if (data['data']['city']) {
                        output += data['data']['city'];
                    }

                    alert(output);
                }
            }).catch(function (error) {
                console.log(error);
            });
        }
    }
});

/**
 * mobile navigation
 */
const menuButton = document.getElementById('menu-toggle');
const navigation = document.getElementById('site-navigation');
const menuList = navigation.getElementsByTagName('ul')[0];
const body = document.getElementsByTagName("BODY")[0];
const boardsSidebar = document.getElementById('sidebar');
const initialHeaderHeight = document.getElementById('masthead').offsetHeight;

menuButton.addEventListener('click', function (evt) {
    if (navigation.classList.contains('toggled')) {
        menuButton.setAttribute('aria-expanded', 'false');
        menuList.setAttribute('aria-expanded', 'false');
        if (boardsSidebar) {
            boardsSidebar.style.paddingTop = initialHeaderHeight + 'px';
        }
    } else {
        menuButton.setAttribute('aria-expanded', 'true');
        menuList.setAttribute('aria-expanded', 'true');
    }
    navigation.classList.toggle("toggled");
    menuButton.classList.toggle("open");
    body.classList.toggle("mobile-navigation-open");
});