'use strict';

const imageInput = document.querySelector('input[type="file"]');
const deleteImage = document.querySelector('button#delete_image');
const eventImage = document.getElementById('event_image');
const loadingIcon = document.getElementById('loadingIconImageUpload');


if (imageInput) {
    imageInput.addEventListener('change', function (e) {

        getCSRFToken().then(function (token) {

            loadingIcon.classList.remove('hidden');

            var data = new FormData()
            data.append('image', imageInput.files[0]);
            data.append('csrf_name', token.csrf_name);
            data.append('csrf_value', token.csrf_value);
            return fetch(jsObject.trip_event_image_upload, {
                method: 'POST',
                credentials: "same-origin",
                body: data
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                loadingIcon.classList.add('hidden');
                if (data['status'] === 'success') {
                    eventImage.classList.remove('hidden');
                    eventImage.src = data['thumbnail'];
                    deleteImage.classList.remove('hidden');
                    imageInput.value = "";
                } else {
                    eventImage.classList.add('hidden');
                    eventImage.src = "";
                    deleteImage.classList.add('hidden');
                }
            }).catch(function (error) {
                loadingIcon.classList.add('hidden');
                console.log(error);
            });
        });
    });
}

if (deleteImage) {
    deleteImage.addEventListener('click', function (e) {
        e.preventDefault();

        getCSRFToken().then(function (token) {

            loadingIcon.classList.remove('hidden');

            return fetch(jsObject.trip_event_image_delete, {
                method: 'DELETE',
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(token)
            });
        }).then(function (response) {
            loadingIcon.classList.add('hidden');
            eventImage.classList.add('hidden');
            eventImage.src = "";
            deleteImage.classList.add('hidden');
        }).catch(function (error) {
            loadingIcon.classList.add('hidden');
            console.log(error);
        });
    });
}


// set end date to same date like start date
const datepickerStartEvent = document.getElementById('inputStartEvent');
const datepickerEndEvent = document.getElementById('inputEndEvent');

if (datepickerStartEvent && datepickerEndEvent) {
    flatpickr(datepickerStartEvent, {
        "altInput": true,
        "altFormat": i18n.dateformatTwig.date,
        "dateFormat": "Y-m-d",
        "locale": i18n.template, 
        "onValueUpdate": function (selectedDates) {
            if(datepickerEndEvent.value.length == 0){
                datepickerEndEvent._flatpickr.setDate(selectedDates[0]);
            }
        }
    });
    
    flatpickr(datepickerEndEvent, {
        "altInput": true,
        "altFormat": i18n.dateformatTwig.date,
        "dateFormat": "Y-m-d",
        "locale": i18n.template
    });
}