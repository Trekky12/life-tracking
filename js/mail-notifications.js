'use strict';


const mailNotificationsUserElements = document.querySelectorAll('#mail_notifications_categories_list_user input.set_mail_category_user');

document.addEventListener("DOMContentLoaded", function () {
    mailNotificationsUserElements.forEach(function (item, idx) {
        item.addEventListener('click', function () {
            let val = item.value;
            console.log(item);
            if (item.checked) {
                return setCategoryUser(1, val).then(function (data) {
                    console.log(data);
                });
            } else {
                return setCategoryUser(0, val).then(function (data) {
                    console.log(data);
                });
            }
        });
    });
});


function setCategoryUser(type, category) {
    let data = {"type": type, "category": category};

    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.mail_notifications_set_category_user, {
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
        return data;
    }).catch(function (error) {
        console.log(error);
    });
}