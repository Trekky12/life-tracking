'use strict';

// location range selection
var datepickerRange = document.getElementById('dateRange');
var datepickerStart = document.getElementById('inputStart');
var datepickerEnd = document.getElementById('inputEnd');
if (datepickerRange && datepickerStart && datepickerEnd) {
    flatpickr(datepickerRange, {
        "altInput": true,
        "altFormat": i18n.dateformatTwig.date,
        "dateFormat": "Y-m-d",
        "locale": i18n.template, 
        "mode": "range",
        "defaultDate": [datepickerStart.value, datepickerEnd.value],
        "onChange": function (selectedDates) {
            const dateArr = selectedDates.map(date => this.formatDate(date, "Y-m-d"));

            // clear
            datepickerStart.value = '';
            datepickerEnd.value = '';

            // one value selected --> start = end
            if (dateArr.length > 0) {
                datepickerStart.value = dateArr[0];
                datepickerEnd.value = dateArr[0];
            }
            // two values selected --> adjust end
            if (dateArr.length > 1) {
                datepickerEnd.value = dateArr[1];
            }

        }
    });
}