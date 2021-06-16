'use strict';

const comeButtons = document.querySelectorAll('.timesheet-fast-come-btn');
const leaveButtons = document.querySelectorAll('.timesheet-fast-leave-btn');

const timesheetLatField = document.querySelector('input#geoLat');
const timesheetLngField = document.querySelector('input#geoLng');
const timesheetAccField = document.querySelector('input#geoAcc');

const alertSuccess = document.querySelector('#alertSuccess');

comeButtons.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        send(item, "start");
    });
});

leaveButtons.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        send(item, "end");
    });
});

function send(button, type) {
    let alertError = document.querySelector('#alertError');
    let alertErrorDetail = alertError.querySelector('#alertErrorDetail');

    alertError.classList.add("hidden");
    alertSuccess.classList.add("hidden");
    alertErrorDetail.innerHTML = "";

    let url = button.dataset.url;

    let data = {};
    data[type + "_lat"] = timesheetLatField.value;
    data[type + "_lng"] = timesheetLngField.value;
    data[type + "_acc"] = timesheetAccField.value;

    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetchWithTimeout(url, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if (data['status'] === 'success') {
            alertSuccess.classList.remove("hidden");

            if (data['data'] === 1) {
                let grid = button.closest('.grid');
                let cards = grid.querySelectorAll('.card');

                cards.forEach(function (item, idx) {
                    item.classList.toggle("inactive");
                });
            }
        } else {
            alertErrorDetail.innerHTML = data['message'];
            alertError.classList.remove("hidden");
        }
    }).catch(function (error) {
        console.log(error);
        alertErrorDetail.innerHTML = lang.request_error;
        alertError.classList.remove("hidden");
    });
}


const projectCategorySelects = document.querySelectorAll('select.category');
projectCategorySelects.forEach(function (item, idx) {
    new Selectr(item, {
        searchable: false,
        placeholder: lang.categories,
        messages: {
            noOptions: lang.no_options
        }
    });
});


const dateTimePickerStart = document.querySelector('#datetimePickerStart');
const dateTimePickerEnd = document.querySelector('#datetimePickerEnd');

if (dateTimePickerStart && dateTimePickerEnd) {

    flatpickr(dateTimePickerStart, {
        "altInput": true,
        "altFormat": i18n.dateformatTwig.datetimeShort,
        "dateFormat": "Y-m-d H:i",
        "locale": i18n.template,
        "enableTime": true,
        "time_24hr": true,
        "minuteIncrement": 1,
        "onValueUpdate": function (selectedDates) {
            dateTimePickerEnd._flatpickr.setDate(selectedDates[0]);
        }
    });

    flatpickr(dateTimePickerEnd, {
        "altInput": true,
        "altFormat": i18n.dateformatTwig.datetimeShort,
        "dateFormat": "Y-m-d H:i",
        "locale": i18n.template,
        "enableTime": true,
        "time_24hr": true,
        "minuteIncrement": 1
    });
}

const checkboxDurationModification = document.getElementById('checkboxDurationModification');
const inputDurationModificationWrapper = document.getElementById('inputDurationModificationWrapper');

if (checkboxDurationModification && inputDurationModificationWrapper) {
    checkboxDurationModification.addEventListener('click', function (event) {

        if (checkboxDurationModification.checked) {
            inputDurationModificationWrapper.classList.remove("hidden");
            inputDurationModificationWrapper.querySelector('input').disabled = false;
        } else {
            inputDurationModificationWrapper.classList.add("hidden");
            inputDurationModificationWrapper.querySelector('input').disabled = true;
        }
    });
}


const categoryFilter = document.getElementById("category-filter");
if (categoryFilter) {
    new Selectr(categoryFilter, {
        searchable: false,
        placeholder: lang.categories,
        messages: {
            noOptions: lang.no_options
        }
    });
}