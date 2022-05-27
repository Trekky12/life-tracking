'use strict';

const requestWidgets = document.querySelectorAll('.request_widget');
requestWidgets.forEach(function (item) {
    load(item);

    var reload = item.dataset.reload;

    if (reload > 0) {
        setInterval(function () {
            load(item);
        }, reload * 1000);
    }
});

function load(item) {
    let id = item.dataset.id;
    let widget = item.dataset.widget;
    let options = item.dataset.options;

    return fetch(jsObject.frontpage_widget_request + id + '?widget=' + widget + '&options=' + options, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        item.innerHTML = data["data"];
    }).catch(function (error) {
        console.log(error);
    });
}