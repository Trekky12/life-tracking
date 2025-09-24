'use strict';

const selectedItems = document.querySelector('#selected_items');

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

const projectNoticefieldsSelects = document.querySelectorAll('select.noticefield');
projectNoticefieldsSelects.forEach(function (item, idx) {
    new Selectr(item, {
        searchable: true,
        placeholder: lang.timesheets_noticefield,
        messages: {
            noResults: lang.nothing_found,
            noOptions: lang.no_options
        }
    });
});

const dateTimePickerStart = document.querySelector('#datetimePickerStart');
const dateTimePickerEnd = document.querySelector('#datetimePickerEnd');
const dateTimePickerEndField = document.querySelector('#datetimePickerEndField');

if (dateTimePickerStart) {

    flatpickr(dateTimePickerStart, {
        "wrap": true,
        "altInput": true,
        "altFormat": i18n.dateformatTwig.datetimeShort,
        "altInputClass": "datepicker datetimePickerStart",
        "dateFormat": "Y-m-d H:i",
        "locale": i18n.template,
        "enableTime": true,
        "time_24hr": true,
        "minuteIncrement": 1,
        "onChange": function (selectedDates) {
            setEndDate();
        }
    });
}
if (dateTimePickerEnd) {
    flatpickr(dateTimePickerEnd, {
        "wrap": true,
        "altInput": true,
        "altFormat": i18n.dateformatTwig.datetimeShort,
        "altInputClass": "datepicker datetimePickerEnd",
        "dateFormat": "Y-m-d H:i",
        "locale": i18n.template,
        "enableTime": true,
        "time_24hr": true,
        "minuteIncrement": 1
    });
    if (dateTimePickerEndField.dataset.saved != "1") {
        setEndDate();
    }

}

function setEndDate() {
    if (!dateTimePickerStart || !dateTimePickerEnd) {
        return;
    }
    let default_duration = dateTimePickerEndField.dataset.defaultDuration;
    let selectedDate = dateTimePickerStart._flatpickr.selectedDates[0];
    if (default_duration > 0) {
        selectedDate.setSeconds(selectedDate.getSeconds() + default_duration);
    }
    dateTimePickerEnd._flatpickr.setDate(selectedDate);
}

const radioDurationCustomModification = document.getElementById('radioDurationCustom');
const radioDurationNoModification = document.getElementById('radioDurationReal');
const radioDurationRateModification = document.getElementById('radioDurationProjectRate');
const inputDurationModificationWrapper = document.getElementById('inputDurationModificationWrapper');

if (radioDurationCustomModification && radioDurationNoModification && radioDurationRateModification && inputDurationModificationWrapper) {
    radioDurationCustomModification.addEventListener('click', function (event) {

        if (radioDurationCustomModification.checked) {
            inputDurationModificationWrapper.classList.remove("hidden");
            let inputDurationCustomModification = inputDurationModificationWrapper.querySelector('input');
            inputDurationCustomModification.disabled = false;
            if (!inputDurationCustomModification.classList.contains("html-duration-picker")) {
                inputDurationCustomModification.classList.add("html-duration-picker");
            }
            HtmlDurationPicker.refresh();
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

        let sheets = getSelectedSheets();

        setCategories({ 'sheets': sheets, 'categories': categories, 'type': 'assign' });
    });

    removeCategoriesBtn.addEventListener('click', function (event) {

        let categories = assignCategories.getValue();

        let sheets = getSelectedSheets();

        setCategories({ 'sheets': sheets, 'categories': categories, 'type': 'remove' });
    });
}

function getSelectedSheets() {
    let sheets = [];
    let checkboxes = document.querySelectorAll('#timesheets_sheets_table tbody input[type="checkbox"]:checked');
    checkboxes.forEach(function (checkbox) {
        sheets.push(checkbox.dataset.id);
    });
    return sheets;
}


function setCategories(data) {

    loadingWindowOverlay.classList.remove("hidden");

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
        loadingWindowOverlay.classList.add("hidden");
    });
}

const radioBudgetCount = document.getElementById('radioCategorization1');
const radioBudgetDuration = document.getElementById('radioCategorization2');
const radioBudgetDurationModified = document.getElementById('radioCategorization3');

if (radioBudgetDuration && radioBudgetDurationModified && radioBudgetCount) {

    radioBudgetDuration.addEventListener('click', function (event) {
        document.querySelectorAll('.html-duration-picker-input-controls-wrapper').forEach(function (picker) {
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
        document.querySelectorAll('.html-duration-picker-input-controls-wrapper').forEach(function (picker) {
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
            document.querySelectorAll('.html-duration-picker-input-controls-wrapper').forEach(function (picker) {
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
            document.querySelectorAll('.html-duration-picker-input-controls-wrapper').forEach(function (picker) {
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

const applyOptionsBtn = document.querySelector('#apply_options');
const optionsSelector = document.querySelector('select#applyOptions');

if (applyOptionsBtn) {
    applyOptionsBtn.addEventListener('click', function (event) {

        let option = optionsSelector.value;

        let sheets = getSelectedSheets();

        let data = { 'sheets': sheets, 'option': option };

        loadingWindowOverlay.classList.remove("hidden");

        return getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(jsObject.timesheets_sheets_set_options, {
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
            loadingWindowOverlay.classList.add("hidden");
        });
    });
}

var calendarEl = document.getElementById('timesheets_calendar');

const eventModal = document.getElementById("event-modal");

if (calendarEl) {

    let min = calendarEl.dataset.min;
    let max = calendarEl.dataset.max;
    let hiddendays = calendarEl.dataset.hiddendays;

    let projectID = calendarEl.dataset.project;
    let editURL = calendarEl.dataset.edit;

    let from = calendarEl.dataset.from;
    let to = calendarEl.dataset.to;

    let initialView = localStorage.getItem('calendarView_' + projectID) || 'timeGridWeek';
    let savedDate = localStorage.getItem('calendarDate_' + projectID);
    let initialDate = savedDate ? new Date(savedDate) : from ? new Date(from) : new Date();

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: initialView,
        initialDate: initialDate,
        locale: i18n.template,
        events: {
            url: jsObject.timesheets_calendar_events,
        },
        headerToolbar: {
            left: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            center: 'title',
            right: 'today prev,next'
        },
        views: {
            timeGridWeek: {
                allDaySlot: false,
                nowIndicator: true,
                slotMinTime: min,
                slotMaxTime: max,
                hiddenDays: hiddendays
            },
            timeGridDay: {
                allDaySlot: false,
                nowIndicator: true,
                slotMinTime: min,
                slotMaxTime: max,
                hiddenDays: hiddendays
            }
        },
        datesSet: function (info) {
            localStorage.setItem('calendarView_' + projectID, info.view.type);
            localStorage.setItem('calendarDate_' + projectID, info.view.currentStart);

            if (info.view.type !== 'dayGridMonth') {
                calendar.setOption('eventContent', function (arg) {
                    let date = document.createElement('div');
                    date.classList.add('fc-event-time');

                    let timeText = arg.timeText;
                    if (arg.event.extendedProps.is_happened == "1") {
                        timeText = document.getElementById('iconCheck').innerHTML + arg.timeText;
                    } else {
                        timeText = arg.timeText;
                    }

                    date.innerHTML = timeText;

                    let container = document.createElement('div');
                    container.classList.add('fc-event-title-container');

                    let titleEl = document.createElement('div');
                    titleEl.classList.add('fc-event-title');

                    let title = arg.event.title;
                    if (arg.event.extendedProps.has_sheet_notice) {
                        title = document.getElementById('iconNotice').innerHTML + arg.event.title;
                    } else {
                        title = arg.event.title;
                    }

                    titleEl.innerHTML = title;

                    container.appendChild(titleEl);

                    return { domNodes: [date, container] };
                });
            } else {
                // Reset eventContent to default for other views
                calendar.setOption('eventContent', null);
            }
        },
        //eventContent: function(arg){
        //    console.log(arg);
        //},
        contentHeight: "auto",
        selectable: true,
        select: function (info) {
            loadingWindowOverlay.classList.remove("hidden");
            window.location.href = `${editURL}&start=${encodeURIComponent(info.startStr)}`;
        },
        eventClick: function (info) {
            //alert('Event: ' + info.event.title);
            console.log(info.event.extendedProps);
            let dateContent = eventModal.querySelector(".date");
            dateContent.textContent = info.event.extendedProps.date;

            let customer = eventModal.querySelector(".customer");
            let customerName = customer.querySelector(".customer-name");
            if (info.event.extendedProps.customer) {
                customerName.textContent = info.event.extendedProps.customer;
                customer.classList.remove("hidden");
            } else {
                customerName.textContent = "";
                customer.classList.add("hidden");
            }

            let customerNoticeButton = customer.querySelector(".customer-notice-btn");
            if (info.event.extendedProps.customer_notice) {
                customerNoticeButton.href = info.event.extendedProps.customer_notice;
                customerNoticeButton.classList.remove("hidden");
            } else {
                customerNoticeButton.classList.add("hidden");
            }

            let categories = eventModal.querySelector(".categories");
            let categoriesName = categories.querySelector(".categoriesName");
            if (info.event.extendedProps.categories) {
                categoriesName.textContent = info.event.extendedProps.categories;
                categories.classList.remove("hidden");
            } else {
                categoriesName.textContent = "";
                categories.classList.add("hidden");
            }

            let state = eventModal.querySelector(".state");
            if (info.event.extendedProps.is_invoiced == 0 && info.event.extendedProps.is_billed == 0 && info.event.extendedProps.is_payed == 0 && info.event.extendedProps.is_happened == 0) {
                state.classList.add("hidden");
            } else {
                state.classList.remove("hidden");

                let invoiced = eventModal.querySelector(".invoiced");
                if (info.event.extendedProps.is_invoiced == 1) {
                    invoiced.classList.remove("hidden");
                } else {
                    invoiced.classList.add("hidden");
                }

                let billed = eventModal.querySelector(".billed");
                if (info.event.extendedProps.is_billed == 1) {
                    billed.classList.remove("hidden");
                } else {
                    billed.classList.add("hidden");
                }

                let payed = eventModal.querySelector(".payed");
                if (info.event.extendedProps.is_payed == 1) {
                    payed.classList.remove("hidden");
                } else {
                    payed.classList.add("hidden");
                }

                let happened = eventModal.querySelector(".happened");
                if (info.event.extendedProps.is_happened == 1) {
                    happened.classList.remove("hidden");
                } else {
                    happened.classList.add("hidden");
                }
            }

            let series = eventModal.querySelector(".series");
            let previous = eventModal.querySelector('.previous');
            let following = eventModal.querySelector('.following');
            let following_last = eventModal.querySelector('.following-last');
            let following_delete_btn = eventModal.querySelector('.btn-deletefollowing');

            let previous_list = previous.querySelector('ul');
            previous_list.innerHTML = "";

            let following_list = following.querySelector('ul');
            following_list.innerHTML = "";

            let is_part_of_series = info.event.extendedProps.reference_sheet != null || info.event.extendedProps.series.length > 0;

            if (!is_part_of_series) {
                series.classList.add("hidden");
                series.querySelector('.count').innerHTML = "";

                previous.classList.add("hidden");
                following.classList.add("hidden");
                following_delete_btn.classList.add("hidden");

            } else {
                series.classList.remove("hidden");
                series.querySelector('.count').innerHTML = info.event.extendedProps.series.length;

                if (info.event.extendedProps.previous.length > 0) {
                    previous.classList.remove("hidden");
                    info.event.extendedProps.previous.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        previous_list.appendChild(li);
                    });
                } else {
                    previous.classList.add("hidden");
                }

                if (info.event.extendedProps.remaining.length > 0) {
                    following_last.classList.add("hidden");
                    following.classList.remove("hidden");
                    info.event.extendedProps.remaining.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        following_list.appendChild(li);
                    });

                    following_delete_btn.classList.remove("hidden");
                } else {
                    following_last.classList.remove("hidden");
                    following.classList.add("hidden");
                    following_delete_btn.classList.add("hidden");
                }
            }

            let sheetCategoryBudgetsWrapper = eventModal.querySelector(".sheet-category-budgets-wrapper");
            let sheetCategoryBudgets = sheetCategoryBudgetsWrapper.querySelector(".sheet-category-budgets");
            sheetCategoryBudgets.innerHTML = "";

            const budgets = info.event.extendedProps.categorybudgets;
            if (budgets && budgets.length > 0) {
                sheetCategoryBudgetsWrapper.classList.remove("hidden");

                budgets.forEach(budget => {
                    const budgetDiv = document.createElement("div");
                    budgetDiv.classList.add("budget-entry");

                    var h4 = document.createElement("h4");
                    var title = budget.name ? budget.name : "";

                    if (budget.start && budget.end) {
                        title += " (" + moment(budget.start).format(i18n.dateformatJS.date) + " - " + moment(budget.en).format(i18n.dateformatJS.date) + ")";
                    }
                    h4.innerHTML = title;
                    budgetDiv.appendChild(h4);

                    var categoriesDiv = document.createElement("div");
                    categoriesDiv.className = "categories-list";

                    if (budget.category_names && budget.category_names.length > 0) {
                        categoriesDiv.innerHTML = lang.categories + ": " + budget.category_names;

                        if (budget.no_category == 1) {
                            categoriesDiv.innerHTML = lang.categories + ": " + budget.category_names + ", " + lang.timesheets_project_category_budget_entries_without_category;
                        }

                    } else if (budget.no_category == 1) {
                        categoriesDiv.innerHTML = lang.timesheets_project_category_budget_entries_without_category;
                    }

                    budgetDiv.appendChild(categoriesDiv);

                    if (budget.notice) {
                        var small = document.createElement("small");
                        small.innerHTML = budget.notice;
                        budgetDiv.appendChild(small);
                    }

                    var valuesDiv = document.createElement("div");
                    valuesDiv.className = "values";

                    var span1 = document.createElement("span");
                    span1.innerHTML = "0";
                    valuesDiv.appendChild(span1);

                    var span2 = document.createElement("span");
                    span2.innerHTML = (budget.categorization !== "count") ? splitDateInterval(budget.sum, true) : budget.sum;
                    valuesDiv.appendChild(span2);

                    var span3 = document.createElement("span");
                    span3.innerHTML = (budget.categorization !== "count") ? splitDateInterval(budget.value, true) : budget.value;
                    valuesDiv.appendChild(span3);

                    budgetDiv.appendChild(valuesDiv);

                    var progressBar = document.createElement("div");
                    progressBar.className = "progress-bar";

                    var progressClass = "green";
                    if (budget.warning3 > 0 && budget.sum >= budget.warning3) {
                        progressClass = "red";
                    }
                    else if (budget.warning2 > 0 && budget.sum >= budget.warning2) {
                        progressClass = "orange";
                    }
                    else if (budget.warning1 > 0 && budget.sum >= budget.warning1) {
                        progressClass = "yellow";
                    }

                    var progress = document.createElement("div");
                    progress.className = "progress " + progressClass;
                    progress.style.width = budget.percent + "%";

                    if (parseFloat(budget.percent) >= 25) {
                        progress.innerHTML = budget.percent + "%";
                    }

                    progressBar.appendChild(progress);

                    if (budget.percent && parseFloat(budget.percent) < 25) {
                        var progressText = document.createElement("div");
                        progressText.className = "progress progress-text";
                        progressText.innerHTML = budget.percent + "%";
                        progressBar.appendChild(progressText);
                    }

                    budgetDiv.appendChild(progressBar);

                    if (budget.diff !== null && typeof budget.diff !== "undefined") {
                        var remainingDiv = document.createElement("div");
                        remainingDiv.className = "remaining";

                        remainingDiv.innerHTML = lang.remaining + ": " + ((budget.categorization !== "count") ? splitDateInterval(budget.diff, true) : budget.diff);

                        budgetDiv.appendChild(remainingDiv);
                    }

                    sheetCategoryBudgets.appendChild(budgetDiv);
                });

            } else {
                sheetCategoryBudgetsWrapper.classList.add("hidden");
            }

            let requirementsWrapperDiv = eventModal.querySelector(".requirements-wrapper");
            let requirementsDiv = requirementsWrapperDiv.querySelector(".requirements");
            requirementsDiv.innerHTML = "";

            const requirements = info.event.extendedProps.customer_requirements;
            if (requirements) {
                requirementsWrapperDiv.classList.remove("hidden");

                requirements.forEach(requirement => {
                    const requirementDiv = document.createElement("div");
                    requirementDiv.classList.add("requirement-entry");

                    var req = document.createElement("span");
                    if (requirement.is_valid) {
                        requirementDiv.classList.add("valid");
                        req.innerHTML = requirement.requirement_type_name + ": " + lang.timesheets_requirements_done;
                    } else {
                        requirementDiv.classList.add("invalid");
                        req.innerHTML = requirement.requirement_type_name + ": " + lang.timesheets_requirements_open;
                    }
                    requirementDiv.appendChild(req);

                    if (!requirement.is_valid) {
                        let aMessage = document.createElement("a");
                        aMessage.href = requirement.url;

                        let urlButton = document.createElement('button');
                        urlButton.type = 'button';
                        urlButton.classList.add('button', 'small', 'button-text');
                        urlButton.textContent = lang.add;

                        aMessage.appendChild(urlButton);

                        requirementDiv.appendChild(aMessage);
                    }

                    requirementsDiv.appendChild(requirementDiv);
                });

            } else {
                requirementsWrapperDiv.classList.add("hidden");
            }


            let sheetNoticeButton = eventModal.querySelector(".sheet-notice-btn a");
            if (info.event.extendedProps.sheet_notice) {
                sheetNoticeButton.href = info.event.extendedProps.sheet_notice;
                sheetNoticeButton.classList.remove("hidden");
            } else {
                sheetNoticeButton.classList.add("hidden");
            }

            let editButton = eventModal.querySelector(".btn-edit");
            editButton.href = info.event.extendedProps.edit;

            let deleteButtons = eventModal.querySelectorAll(".btn-delete");
            deleteButtons.forEach(deleteButton => {
                deleteButton.dataset.url = info.event.extendedProps.delete;

                if (info.event.extendedProps.has_sheet_notice) {
                    deleteButton.dataset.warning = lang.timesheets_sheets_delete_warning_notice;
                } else {
                    deleteButton.dataset.warning = "";
                }

            });

            freeze();
            eventModal.classList.add('visible');
        }
    });
    calendar.render();

    document.getElementById("modal-close-btn").addEventListener('click', function (e) {
        hideEventModal();
    });
    // Hide when clicking outside of modal
    eventModal.addEventListener('click', function (e) {
        if (e.target === eventModal) {
            hideEventModal();
        }
    });
}


const checkBoxRepeat = document.querySelector('#checkBoxRepeat');
const repeatsContent = document.querySelector(".repeats-content");

if (checkBoxRepeat && repeatsContent) {
    checkBoxRepeat.addEventListener('click', function (event) {

        if (checkBoxRepeat.checked) {
            repeatsContent.classList.remove("hidden");
        } else {
            repeatsContent.classList.add("hidden");
        }
    });

}

const dateTimePickerStartModified = document.querySelector('#datetimePickerStartModified');
if (dateTimePickerStartModified) {

    flatpickr(dateTimePickerStartModified, {
        "wrap": true,
        "altInput": true,
        "altFormat": i18n.dateformatTwig.datetimeShort,
        "altInputClass": "datepicker dateTimePickerStartModified",
        "dateFormat": "Y-m-d H:i",
        "locale": i18n.template,
        "enableTime": true,
        "time_24hr": true,
        "minuteIncrement": 1
    });
}

const checkBoxDateModified = document.querySelector('#checkBoxDateModified');
const dateModifiedContent = document.querySelector(".date-modified-content");

if (checkBoxDateModified && dateModifiedContent) {
    checkBoxDateModified.addEventListener('click', function (event) {

        if (checkBoxDateModified.checked) {
            dateModifiedContent.classList.remove("hidden");
            dateTimePickerStartModified._flatpickr.setDate("3000-01-01 00:00:00");
        } else {
            dateModifiedContent.classList.add("hidden");
            dateTimePickerStartModified._flatpickr.clear();
        }
    });
}



document.addEventListener('click', function (event) {
    let checkAllRowsInput = event.target.closest('.checkAllRows');
    if (checkAllRowsInput) {
        let checkboxes = document.querySelectorAll('#timesheets_sheets_table input[type="checkbox"]');
        checkboxes.forEach(function (checkbox) {
            if (checkAllRowsInput.checked) {
                checkbox.checked = true;
            } else {
                checkbox.checked = false;
            }
        });
        selectedItems.innerHTML = getSelectedSheets().length;
    }

    let checkbox = event.target.closest('#timesheets_sheets_table tbody input[type="checkbox"]');
    if (checkbox) {
        selectedItems.innerHTML = getSelectedSheets().length;
    }

    // Hide modal when any link on the modal is clicked
    let modalButtons = event.target.closest('#event-modal a');
    if (modalButtons) {
        hideEventModal();
    }
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' || event.keyCode === 27) {
        hideEventModal();
    }
});

function hideEventModal() {
    unfreeze();
    if (eventModal) {
        eventModal.classList.remove('visible');
    }
}

const timesheetsExportFilter = document.getElementById('timesheets-export-filter');
if (timesheetsExportFilter) {
    const timesheetsExportFilterNoticeFields = timesheetsExportFilter.querySelector('#timesheets-export-filter-noticefields');
    const timesheetsExportFilterModified = timesheetsExportFilter.querySelector('#timesheets-export-filter-modified');
    
    document.querySelectorAll('.search-filter input[name="type"]').forEach(function (input) {
        input.addEventListener('click', function (e) {
            setExportFilter(input);
        });
    });

    const checkedType = document.querySelector('.search-filter input[name="type"]:checked');
    setExportFilter(checkedType);

    function setExportFilter(input) {
        if (input.checked && input.value === 'html-overview') {
            timesheetsExportFilterNoticeFields.classList.remove('hidden');
            timesheetsExportFilterModified.classList.remove('hidden');
        }  else {
            timesheetsExportFilterNoticeFields.classList.add('hidden');
            timesheetsExportFilterModified.classList.add('hidden');
        }
    }
}

