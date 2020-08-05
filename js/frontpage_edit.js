'use strict';


new Sortable(document.querySelector('.grid'), {
    group: {
        name: "widget"
    },
    draggable: ".card",
    handle: "h3",
    dataIdAttr: 'data-widget',
    onUpdate: function (evt) {
        var data = {'widgets': this.toArray()};

        getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(jsObject.frontpage_widget_position, {
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

        }).catch(function (error) {
            console.log(error);
        });
    }
});


const widgetModal = document.getElementById("widget-modal");
const modalCloseBtn = document.getElementById("modal-close-btn");

modalCloseBtn.addEventListener('click', function (e) {
    widgetModal.style.display = 'none';
});

const addWidgetBtn = document.getElementById("add-widget");
const addWidgetBtnModal = document.getElementById("add-widget-modal");

widgetModal.querySelector("form").addEventListener('submit', function (e) {
    e.preventDefault();
    document.getElementById('loading-overlay').classList.remove('hidden');

    let widget = addWidgetBtn.parentElement.querySelector('select').value;

    let options = {};
    new FormData(widgetModal.querySelector("form")).forEach(function (value, key) {
        options[ key ] = value;
    });

    saveWidget(widget, options).then(function () {
        allowedReload = true;
        window.location.reload();
    });
});


addWidgetBtn.addEventListener("click", function (event) {
    event.preventDefault();
    document.getElementById('loading-overlay').classList.remove('hidden');

    let widget = addWidgetBtn.parentElement.querySelector('select').value;
    let widgetModalContent = widgetModal.querySelector('.modal-content');

    widgetModalContent.innerHTML = '';

    fetch(jsObject.frontpage_widget_option + '?widget=' + widget, {
        method: 'GET',
        credentials: "same-origin"
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if (data.status !== 'error') {
            // show modal
            if (data.entry) {

                data.entry.forEach(function (element) {

                    let group = document.createElement("div");
                    group.classList.add("form-group");

                    let label = document.createElement("label");
                    label.innerHTML = element.label;

                    group.appendChild(label);

                    if (element.type == "select") {

                        let select = document.createElement("select");
                        select.classList.add("form-control");
                        select.name = element.name;

                        Object.keys(element.data).forEach(function (k) {
                            let option = document.createElement("option");
                            option.value = k;
                            option.innerHTML = element.data[k]["name"];
                            select.appendChild(option);
                        });
                        group.appendChild(select);
                    }else if (element.type == "input") {

                        let input = document.createElement("input");
                        input.classList.add("form-control");
                        input.name = element.name;
                        input.value = element.data;

                        group.appendChild(input);
                    }
                    

                    widgetModalContent.appendChild(group);

                });
                widgetModal.style.display = 'block';
            } else {
                // create new widget
                return saveWidget(widget);
            }
        }
    }).then(function (response) {
        if (response !== undefined) {
            allowedReload = true;
            window.location.reload();
        } else {
            document.getElementById('loading-overlay').classList.add('hidden');
        }
    }).catch(function (error) {
        console.log(error);
    });
});

function saveWidget(type, options = {}){
    document.getElementById('loading-overlay').classList.remove('hidden');

    return getCSRFToken().then(function (token) {
        let data = {"name": type, "options": options, "csrf_name": token.csrf_name, "csrf_value": token.csrf_value};

        return fetch(jsObject.frontpage_widget_option_save, {
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