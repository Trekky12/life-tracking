'use strict';

// @see https://stackoverflow.com/a/2638357
window.onunload = function(){}; 

moment.locale(i18n.template);

initialize();

initCharts();

const loadingWindowOverlay = document.getElementById('loading-overlay');

function getCSRFToken() {
    // take available token
    if (tokens.length > 1) {
        return new Promise(function (resolve, reject) {
            resolve(tokens.pop());
        });
    }

    // get new tokens
    var last_token = tokens.pop();
    return getNewTokens(last_token);
}


function getNewTokens(token) {
    return fetchWithTimeout(jsObject.csrf_tokens_url, {
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
        tokens.push(token);
        console.log(error);
        loadingWindowOverlay.classList.add("hidden");
        throw "No CRSF Tokens available";
    });
}

function deleteObject(url, custom_confirm_text) {

    let confirm_text = lang.really_delete;
    if (custom_confirm_text !== "default") {
        confirm_text = custom_confirm_text;
    }

    if (!confirm(confirm_text)) {
        loadingWindowOverlay.classList.add("hidden");
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
        return response.json();
    }).then(function (data) {
        allowedReload = true;
        if ("redirect" in data) {
            window.location.href = data["redirect"];
        } else {
            window.location.reload(true);
        }
    }).catch(function (error) {
        console.log(error);
        if (document.body.classList.contains('offline')) {
            saveDataWhenOffline(url, 'DELETE');
        }
    });
}

function setCookie(name, value, expiryDays, path) {
    expiryDays = expiryDays || 365;

    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiryDays);

    var cookie = [
        name + '=' + value,
        'expires=' + exdate.toUTCString(),
        'path=' + path || '/'
    ];
    document.cookie = cookie.join(';');
}


function getCookie(cname, fallback) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == '') {
            c = c.substring(1);
        }
        if (c.indexOf(name) >= 0) {
            return c.substring(c.indexOf(name) + name.length, c.length);
        }
    }
    return fallback || "";
}



function initialize() {
    let backbtn = document.querySelector('#go-back-btn');
    if (backbtn !== null) {
        backbtn.addEventListener('click', function () {
            loadingWindowOverlay.classList.remove("hidden");
            window.history.back();
        });
    }

    let cancelbtn = document.querySelector('#cancel');
    if (cancelbtn !== null) {
        cancelbtn.addEventListener('click', function (e) {
            e.preventDefault();
            loadingWindowOverlay.classList.remove("hidden");
            window.history.back();
        });
    }
    
    if (document.body.classList.contains("login")) {
        // Delete IndexedDBs
        if (!('indexedDB' in window)) {
            return;
        }
        window.indexedDB.deleteDatabase('lifeTrackingData');
    }

    /**
     * Delete
     * https://elliotekj.com/2016/11/05/jquery-to-pure-js-event-listeners-on-dynamically-created-elements/
     */
    document.addEventListener('click', async function (event) {

        let link = event.target.closest('a');
        let submit = event.target.closest('[type="submit"]');

        let is_internal_link = (link && link.getAttribute("href") != '#' && link.getAttribute("target") != '_blank' && !link.classList.contains("no-loading") && link["href"].includes(window.location.hostname));

        if (is_internal_link || (submit && !submit.classList.contains("no-loading"))) {
            loadingWindowOverlay.classList.remove("hidden");
        }

        if (is_internal_link) {
            event.preventDefault();
            await storeQueryParams();
            window.location.href = link.getAttribute("href");
        }

        // https://stackoverflow.com/a/50901269
        let deleteBtn = event.target.closest('.btn-delete');
        if (deleteBtn) {
            event.preventDefault();
            let url = deleteBtn.dataset.url;
            if (url) {
                let confirm = deleteBtn.dataset.confirm ? deleteBtn.dataset.confirm : "default";
                deleteObject(url, confirm);
            } else {
                deleteBtn.parentNode.remove();
            }
            return;
        }
    });

    /**
     * Alert
     */
    let closebtn = document.querySelectorAll('span.closebtn');
    closebtn.forEach(function (item, idx) {
        item.addEventListener('click', function (event) {
            event.target.parentElement.classList.add("hidden");
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
                    y: {
                        ticks: {
                            min: 0
                        }
                    }
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
        //financeDetailChart.before(fdChart.generateLegend());
    }


    let stepsSummaryChart = document.querySelector("#stepsSummaryChart");
    if (stepsSummaryChart) {
        new Chart(stepsSummaryChart, {
            data: {
                labels: JSON.parse(stepsSummaryChart.dataset.labels),
                datasets: [
                    {
                        label: stepsSummaryChart.dataset.label,
                        data: JSON.parse(stepsSummaryChart.dataset.values),
                        backgroundColor: '#1e88e5'
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

}

// date Select on boards, finances, car control
flatpickr('#dateSelect', {
    "altInput": true,
    "altFormat": i18n.dateformatTwig.date,
    "dateFormat": "Y-m-d",
    "locale": i18n.template,
    // reset to default value
    // @see https://github.com/flatpickr/flatpickr/issues/816#issuecomment-338687240
    onReady: function (dateObj, dateStr, instance) {
        if (!instance.altInput)
            return;
        instance.__defaultValue = instance.input.defaultValue;
        instance.altInput.defaultValue = instance.altInput.value;
        instance.input.form.addEventListener('reset', function (e) {
            instance.setDate(instance.__defaultValue);
        });
    }
});
flatpickr('#dateSelectEnd', {
    "altInput": true,
    "altFormat": i18n.dateformatTwig.date,
    "dateFormat": "Y-m-d",
    "locale": i18n.template,
    onReady: function (dateObj, dateStr, instance) {
        if (!instance.altInput)
            return;
        instance.__defaultValue = instance.input.defaultValue;
        instance.altInput.defaultValue = instance.altInput.value;
        instance.input.form.addEventListener('reset', function (e) {
            instance.setDate(instance.__defaultValue);
        });
    }
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


// https://javascript.info/fetch-abort
// https://itnext.io/how-you-can-abort-fetch-request-on-a-flight-830a639b9b92
// https://developers.google.com/web/updates/2017/09/abortable-fetch
// https://dev.to/stereobooster/fetch-with-a-timeout-3d6
function fetchWithTimeout(url, options, timeout = 3000) {
    if (document.body.classList.contains("offline")) {
        return new Promise(function (resolve, reject) {
            reject('Offline');
        });
    }

    const abortController = new AbortController();
    const abortSignal = abortController.signal;

    var timeoutId;
    var promises = [];
    var cacheWhenTimedOutPromise = new Promise(function (resolve, reject) {
        timeoutId = setTimeout(function () {
            //console.log('timeout');
            abortController.abort();
            reject('Timeout');
        }, timeout);
    });
    promises.push(cacheWhenTimedOutPromise);

    var networkPromise = fetch(url, { signal: abortSignal, ...options }).then(function (response) {
        //console.log('fetch success');
        clearTimeout(timeoutId);
        return response;
    });

    promises.push(networkPromise);

    return Promise.race(promises);
}

// https://stackoverflow.com/a/29188066
function freeze() {
    var top = window.scrollY;

    document.body.style.overflow = 'hidden';

    /*window.onscroll = function () {
        window.scroll(0, top);
    }*/
}

function unfreeze() {
    document.body.style.overflow = '';
    window.onscroll = null;
}

function isMobile() {
    return isVisible(document.getElementById('mobile-header-icons'));
}

function isVisible(element) {
    return getDisplay(element) !== 'none';
}

function getDisplay(element) {
    return element.currentStyle ? element.currentStyle.display : getComputedStyle(element, null).display;
}

// Store query params
async function storeQueryParams() {
    try {
        const token = await getCSRFToken(true);
        var body = token;
        body["path"] = window.location.pathname;
        body["params"] = window.location.search;
        const response = await fetch(jsObject.store_query_params, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(body)
        });
        return await response.json();
    } catch (error) {
        console.log(error);
    }
}