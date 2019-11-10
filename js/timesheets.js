'use strict';

const comeButtons = document.querySelectorAll('.timesheet-fast-come-btn');
const leaveButtons = document.querySelectorAll('.timesheet-fast-leave-btn');

const timesheetLatField = document.querySelector('input#geoLat');
const timesheetLngField = document.querySelector('input#geoLng');
const timesheetAccField = document.querySelector('input#geoAcc');

const alertSuccess = document.querySelector('#alertSuccess');
const alertError = document.querySelector('#alertError');
const alertErrorDetail = alertError.querySelector('#alertErrorDetail');

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

        return fetch(url, {
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
    });
}