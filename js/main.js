'use strict';

// @see https://stackoverflow.com/a/2638357
window.onunload = function () { };

moment.locale(i18n.template);

initialize();

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
        throw "No CRSF Tokens available";
    });
}

function createConfirmDialog(message, callback) {
    var confirmModal = document.createElement('div');
    confirmModal.id = 'confirm-modal';
    confirmModal.classList.add('modal');

    var modalInner = document.createElement('div');
    modalInner.classList.add('modal-inner');

    var form = document.createElement('form');

    var modalContent = document.createElement('div');
    modalContent.classList.add('modal-content');
    var messageParagraph = document.createElement('p');
    messageParagraph.textContent = message;
    modalContent.appendChild(messageParagraph);

    var modalFooter = document.createElement('div');
    modalFooter.classList.add('modal-footer');

    var buttonsDiv = document.createElement('div');
    buttonsDiv.classList.add('buttons');

    var confirmButton = document.createElement('button');
    confirmButton.type = 'button';
    confirmButton.classList.add('button');
    confirmButton.textContent = lang.yes;

    var cancelButton = document.createElement('button');
    cancelButton.classList.add('button', 'button-text', 'cancel');
    cancelButton.type = 'button';
    cancelButton.textContent = lang.no;

    buttonsDiv.appendChild(confirmButton);
    buttonsDiv.appendChild(cancelButton);

    modalFooter.appendChild(buttonsDiv);

    form.appendChild(modalContent);
    form.appendChild(modalFooter);

    modalInner.appendChild(form);

    confirmModal.appendChild(modalInner);

    document.body.appendChild(confirmModal);

    confirmModal.style.display = "block";

    confirmButton.onclick = function () {
        confirmModal.remove();
        callback(true);
    };

    cancelButton.onclick = function () {
        confirmModal.remove();
        callback(false);
    };
}

// resolve acts as a callback
// When the user interacts with the confirm dialog and the callback function 
// within createConfirmDialog is called with either true or false, 
// it resolves the promise with that value.
function confirmDialog(message) {
    return new Promise((resolve, reject) => {
        createConfirmDialog(message, resolve);
    });
}

async function deleteObject(url, custom_confirm_text) {

    let confirm_text = lang.really_delete;
    if (custom_confirm_text !== "default") {
        confirm_text = custom_confirm_text;
    }

    if (!await confirmDialog(confirm_text)) {
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
        loadingWindowOverlay.classList.add("hidden");
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
        'path=' + (path || '/'),
        'SameSite=Lax'
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

    if (isTouchEnabled()) {
        document.body.classList.add("is-touch-enabled");
    } else {
        document.body.classList.add("no-touch-enabled");
    }

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

        let is_internal_link = (link && !link.getAttribute("href").startsWith('#') && link.getAttribute("target") != '_blank' && !link.classList.contains("no-loading") && link["href"].includes(window.location.hostname));

        if (is_internal_link || (submit && !submit.classList.contains("no-loading"))) {
            loadingWindowOverlay.classList.remove("hidden");
        }

        if (is_internal_link) {
            event.preventDefault();
            await storeQueryParams();
            window.location.href = link.getAttribute("href");
        }

        // Remove loading spinner if not all required form fields are filled
        if (submit) {
            for (const el of submit.closest('form').querySelectorAll("[required]")) {
                if (!el.reportValidity()) {
                    loadingWindowOverlay.classList.add("hidden");
                }
            }
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

// date Select on boards, finances, car control
flatpickr('#dateSelect', {
    "altInput": true,
    "altFormat": i18n.dateformatTwig.date,
    "altInputClass": "datepicker dateSelect",
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
    "altInputClass": "datepicker dateSelectEnd",
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



document.addEventListener('click', function (event) {
    // https://stackoverflow.com/a/50901269

    /**
     * Get Adress of marker
     */
    let addressBtn = event.target.closest('.btn-get-address');
    if (addressBtn) {
        let lat = addressBtn.dataset.lat;
        let lng = addressBtn.dataset.lng;
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

    /**
     * Close Alert
     */
    let closebtn = event.target.closest('span.closebtn');
    if (closebtn) {
        event.preventDefault();
        event.target.parentElement.classList.add("hidden");
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

function isTouchEnabled() {
    return ('ontouchstart' in window) ||
        (navigator.maxTouchPoints > 0) ||
        (navigator.msMaxTouchPoints > 0);
}

function isVisible(element) {
    return getDisplay(element) !== 'none';
}

function isVisibleOnPage(selector) {
    let elements = Array.from(document.querySelectorAll(selector));
    for (const element of elements) {
        if (isVisible(element)) {
            return true;
        }
    }
    return false;
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
        const response = await fetchWithTimeout(jsObject.store_query_params, {
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

/**
 * Add ripple effect to buttons
 */
if (!isTouchEnabled()) {
    document.addEventListener('mousedown', async function (event) {
        addRipple(event);
    });
} else {
    document.addEventListener('touchstart', async function (event) {
        const firstTouch = event.touches[0];
        addRipple(firstTouch);
    });
}

function addRipple(event) {
    let rippleBtn = event.target.closest(".button");
    if (rippleBtn) {
        createRipple(rippleBtn, event);
    }
    let rippleTab = event.target.closest("a.tabbar-tab");
    if (rippleTab) {
        createRipple(rippleTab, event);
    }
}

function createRipple(el, event) {
    const rect = el.getBoundingClientRect();

    const circle = document.createElement("span");
    const diameter = Math.max(el.clientWidth, el.clientHeight);
    const radius = diameter / 2;

    circle.style.width = circle.style.height = `${diameter}px`;

    if (event) {
        circle.style.left = `${event.clientX - rect.left - radius}px`;
        circle.style.top = `${event.clientY - rect.top - radius}px`;
    } else {
        circle.style.left = `${rect.left / 2 - radius}px`;
        circle.style.top = `${rect.top / 2 - radius}px`;
    }
    circle.classList.add("ripple-circle");

    const ripple = el.getElementsByClassName("ripple-circle")[0];

    if (ripple) {
        ripple.remove();
    }

    el.appendChild(circle);
}