'use strict';

// location range selection
var datepickerRange = document.getElementById('dateRange');
var datepickerStart = document.getElementById('inputStart');
var datepickerEnd = document.getElementById('inputEnd');
if (datepickerRange) {
    flatpickr(datepickerRange, {
        "altInput": true,
        "altFormat": i18n.twig,
        "dateFormat": "Y-m-d",
        "locale": i18n.template,
        "mode": "range",
        "defaultDate": [datepickerStart.value, datepickerEnd.value],
        "onChange": function (selectedDates) {
            const dateArr = selectedDates.map(date => this.formatDate(date, "Y-m-d"));

            if (dateArr.length > 0) {
                datepickerStart.value = dateArr[0];
                datepickerEnd.value = dateArr[0];
            }
            if (dateArr.length > 1) {
                datepickerEnd.value = dateArr[1];
            }

        }
    });
}