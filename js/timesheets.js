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

if (dateTimePickerStart && dateTimePickerEnd) {

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

    let savedView = localStorage.getItem('calendarView_' + projectID) || 'timeGridWeek';
    let savedDate = localStorage.getItem('calendarDate_' + projectID) || new Date();

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: savedView,
        initialDate: savedDate,
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
            localStorage.setItem('calendarDate_' + projectID, info.startStr);

            if (info.view.type !== 'dayGridMonth') {
                calendar.setOption('eventContent', function (arg) {
                    let date = document.createElement('div');
                    date.classList.add('fc-event-time');

                    let timeText = arg.timeText;
                    if (arg.event.extendedProps.is_planned == "0") {
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

                    if (arg.event.extendedProps.categories) {
                        if (arg.event.title) {
                            title += " | ";
                        }
                        title += arg.event.extendedProps.categories;
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
            let customerName = customer.querySelector(".customerName");
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
            } else {
                categoriesName.textContent = "";
                categories.classList.add("hidden");
            }

            let is_part_of_series = info.event.extendedProps.reference_sheet != null || info.event.extendedProps.series.length > 0;

            let state = eventModal.querySelector(".state");
            if (info.event.extendedProps.is_billed == 0 && info.event.extendedProps.is_payed == 0 && info.event.extendedProps.is_planned == 0 && !is_part_of_series) {
                state.classList.add("hidden");
            } else {
                state.classList.remove("hidden");

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

                let planned = eventModal.querySelector(".planned");
                if (info.event.extendedProps.is_planned == 1) {
                    planned.classList.remove("hidden");
                } else {
                    planned.classList.add("hidden");
                }

                let series = eventModal.querySelector(".series");
                let following = eventModal.querySelector('.following');

                let following_list = following.querySelector('ul');
                following_list.innerHTML = "";

                let following_delete_btn = eventModal.querySelector('.btn-deletefollowing');

                if (is_part_of_series) {
                    series.classList.remove("hidden");
                    series.querySelector('.count').innerHTML = info.event.extendedProps.series.length;

                    if (info.event.extendedProps.remaining.length > 0) {
                        following.classList.remove("hidden");
                        info.event.extendedProps.remaining.forEach(item => {
                            const li = document.createElement('li');
                            li.textContent = item;
                            following_list.appendChild(li);
                        });

                        following_delete_btn.classList.remove("hidden");
                    } else {
                        following.classList.add("hidden");
                        following_delete_btn.classList.add("hidden");
                    }

                } else {
                    series.classList.add("hidden");
                    series.querySelector('.count').innerHTML = "";

                    following.classList.add("hidden");
                    following_delete_btn.classList.add("hidden");
                }
            }

            let sheetNoticeButton = eventModal.querySelector(".sheet-notice-btn");
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

const noticeFieldsFilter = document.getElementById('noticefieldsfilter');
if (noticeFieldsFilter) {
    document.querySelectorAll('.search-filter input[name="type"]').forEach(function (input) {
        setNoticeFieldFilter(input);
        input.addEventListener('click', function (e) {
            setNoticeFieldFilter(input);
        });
    });

    function setNoticeFieldFilter(input) {
        if (input.checked && input.value === 'html-overview') {
            noticeFieldsFilter.classList.remove('hidden');
        } else {
            noticeFieldsFilter.classList.add('hidden');
        }
    }
}

