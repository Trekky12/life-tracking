'use strict';

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

const radioDurationCustomModification = document.getElementById('radioDurationCustom');
const radioDurationNoModification = document.getElementById('radioDurationReal');
const radioDurationRateModification = document.getElementById('radioDurationProjectRate');
const inputDurationModificationWrapper = document.getElementById('inputDurationModificationWrapper');

if (radioDurationCustomModification && radioDurationNoModification && radioDurationRateModification && inputDurationModificationWrapper) {
    radioDurationCustomModification.addEventListener('click', function (event) {

        if (radioDurationCustomModification.checked) {
            inputDurationModificationWrapper.classList.remove("hidden");
            inputDurationModificationWrapper.querySelector('input').disabled = false;
        } else {
            inputDurationModificationWrapper.classList.add("hidden");
            inputDurationModificationWrapper.querySelector('input').disabled = true;
        }
    });

    radioDurationNoModification.addEventListener('click', function (event) {
        inputDurationModificationWrapper.classList.add("hidden");
        inputDurationModificationWrapper.querySelector('input').disabled = true;
    });
    radioDurationRateModification.addEventListener('click', function (event) {
        inputDurationModificationWrapper.classList.add("hidden");
        inputDurationModificationWrapper.querySelector('input').disabled = true;
    });
}

const assignCategoriesSelector = document.getElementById("assignCategoriesSelector");
const assignCategoriesBtn = document.getElementById("assign_categories");
const removeCategoriesBtn = document.getElementById("remove_categories");

if (assignCategoriesSelector && assignCategoriesBtn && removeCategoriesBtn) {
    let assignCategories = new Selectr(assignCategoriesSelector, {
        searchable: true,
        placeholder: lang.categories,
        messages: {
            noResults: lang.nothing_found,
            noOptions: lang.no_options
        }
    });

    assignCategoriesBtn.addEventListener('click', function (event) {

        let categories = assignCategories.getValue();

        let sheets = [];

        let checkboxes = document.querySelectorAll('#timesheets_sheets_table tbody input[type="checkbox"]');
        checkboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                sheets.push(checkbox.dataset.id);
            }
        });

        setCategories({'sheets': sheets, 'categories': categories, 'type': 'assign'});
    });

    removeCategoriesBtn.addEventListener('click', function (event) {

        let categories = assignCategories.getValue();

        let sheets = [];

        let checkboxes = document.querySelectorAll('#timesheets_sheets_table tbody input[type="checkbox"]');
        checkboxes.forEach(function (checkbox) {
            if (checkbox.checked) {
                sheets.push(checkbox.dataset.id);
            }
        });

        setCategories({'sheets': sheets, 'categories': categories, 'type': 'remove'});
    });
}

document.addEventListener('click', function (event) {
    let checkAllRowsInput = event.target.closest('#checkAllRows');
    if (checkAllRowsInput) {
        let checkboxes = document.querySelectorAll('#timesheets_sheets_table tbody input[type="checkbox"]');
        checkboxes.forEach(function (checkbox) {
            if (checkAllRowsInput.checked) {
                checkbox.checked = true;
            } else {
                checkbox.checked = false;
            }
        });
    }
});


function setCategories(data) {
    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.timesheets_sheets_set_categories, {
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
        console.log(data);
        allowedReload = true;
        window.location.reload(true);
    }).catch(function (error) {
        console.log(error);
        if (document.body.classList.contains('offline')) {
            let formData = new URLSearchParams(data).toString();
            saveDataWhenOffline(jsObject.timesheets_sheets_set_categories, 'POST', formData);
        }
    });
}

const radioBudgetCount = document.getElementById('radioCategorization1');
const radioBudgetDuration = document.getElementById('radioCategorization2');
const radioBudgetDurationModified = document.getElementById('radioCategorization3');

if (radioBudgetDuration && radioBudgetDurationModified && radioBudgetCount) {

    radioBudgetDuration.addEventListener('click', function (event) {
        document.querySelectorAll('.html-duration-picker-wrapper').forEach(function (picker) {
            picker.classList.remove("hidden");
        });
        document.querySelectorAll('input.duration-input').forEach(function (input) {
            input.classList.remove("hidden");
            input.removeAttribute("disabled");
            if (!input.classList.contains("html-duration-picker")) {
                input.classList.add("html-duration-picker");
            }
            HtmlDurationPicker.refresh();
        });

        document.querySelectorAll('input.count-input').forEach(function (input) {
            input.classList.add("hidden");
            input.setAttribute("disabled", true);
        });
    });

    radioBudgetDurationModified.addEventListener('click', function (event) {
        document.querySelectorAll('.html-duration-picker-wrapper').forEach(function (picker) {
            picker.classList.remove("hidden");
        });
        document.querySelectorAll('input.duration-input').forEach(function (input) {
            input.classList.remove("hidden");
            input.removeAttribute("disabled");
            if (!input.classList.contains("html-duration-picker")) {
                input.classList.add("html-duration-picker");
            }
            HtmlDurationPicker.refresh();
        });

        document.querySelectorAll('input.count-input').forEach(function (input) {
            input.classList.add("hidden");
            input.setAttribute("disabled", true);
        });
    });
    radioBudgetCount.addEventListener('click', function (event) {
        if (radioBudgetCount.checked) {
            document.querySelectorAll('.html-duration-picker-wrapper').forEach(function (picker) {
                picker.classList.add("hidden");
            });
            document.querySelectorAll('input.duration-input').forEach(function (input) {
                input.classList.add("hidden");
                input.setAttribute("disabled", true);
            });

            document.querySelectorAll('input.count-input').forEach(function (input) {
                input.classList.remove("hidden");
                input.removeAttribute("disabled");
            });

        } else {
            document.querySelectorAll('.html-duration-picker-wrapper').forEach(function (picker) {
                picker.classList.remove("hidden");
            });
            document.querySelectorAll('input.duration-input').forEach(function (input) {
                input.classList.remove("hidden");
                input.removeAttribute("disabled");

                if (!input.classList.contains("html-duration-picker")) {
                    input.classList.add("html-duration-picker");
                }

                HtmlDurationPicker.refresh();
            });


            document.querySelectorAll('input.count-input').forEach(function (input) {
                input.classList.add("hidden");
                input.setAttribute("disabled", true);
            });
        }
    });
}


