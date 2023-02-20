'use strict';

const comeButtons = document.querySelectorAll('.timesheet-fast-come-btn');
const leaveButtons = document.querySelectorAll('.timesheet-fast-leave-btn');

const timesheetLatField = document.querySelector('input#geoLat');
const timesheetLngField = document.querySelector('input#geoLng');
const timesheetAccField = document.querySelector('input#geoAcc');

document.addEventListener('click', function (event) {
    let comeBtn = event.target.closest('.timesheet-fast-come-btn');
    let leaveBtn = event.target.closest('.timesheet-fast-leave-btn');

    if (comeBtn) {
        event.preventDefault();
        createTimesheet(comeBtn, "start");
    }
    if(leaveBtn){
        event.preventDefault();
        createTimesheet(leaveBtn, "end");
    }
});


function createTimesheet(button, type) {
    let alertError = document.querySelector('#alertErrorTimesheetFast');
    let alertErrorDetail = alertError.querySelector('#alertErrorDetailTimesheetFast');
    const alertSuccess = document.querySelector('#alertSuccessTimesheetFast');

    alertError.classList.add("hidden");
    alertSuccess.classList.add("hidden");
    alertErrorDetail.innerHTML = "";

    let url = button.dataset.url;

    let data = {};
    if (timesheetLatField) {
        data[type + "_lat"] = timesheetLatField.value;
    }
    if (timesheetLngField) {
        data[type + "_lng"] = timesheetLngField.value;
    }
    if (timesheetAccField) {
        data[type + "_acc"] = timesheetAccField.value;
    }

    let categoryFilter = document.querySelector("#category-filter");
    if (categoryFilter) {
        let timesheetCategories = Array.from(categoryFilter.selectedOptions).map(v => v.value);
        data["category"] = timesheetCategories;
    }

    let customer = document.querySelector("select#customer");
    if (customer) {
        data["customer"] = customer.value;
    }

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

            if (data['data'] === 1 && !button.classList.contains("no-toggle")) {
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
        searchable: true,
        placeholder: lang.categories,
        messages: {
            noResults: lang.nothing_found,
            noOptions: lang.no_options
        }
    });
});