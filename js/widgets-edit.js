'use strict';


new Sortable(document.querySelector('.grid'), {
    group: {
        name: "widget"
    },
    draggable: ".card",
    handle: isTouchEnabled() ? ".handle" : ".card-headline",
    dataIdAttr: 'data-widget',
    onUpdate: function (evt) {
        var data = { 'widgets': this.toArray() };

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
const widgetModalContent = widgetModal.querySelector('.modal-content');
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
        options[key] = value;
    });

    saveWidget(widget, options).then(function () {
        allowedReload = true;
        window.location.reload();
    });
});


addWidgetBtn.addEventListener("click", function (event) {
    event.preventDefault();
    document.getElementById('loading-overlay').classList.remove('hidden');

    let widget_type = addWidgetBtn.parentElement.querySelector('select').value;

    widgetModalContent.innerHTML = '';

    fetch(jsObject.frontpage_widget_option + '?widget=' + widget_type, {
        method: 'GET',
        credentials: "same-origin"
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if (data.status !== 'error') {
            // show modal
            if (data.entry && data.entry.length > 0) {

                setModalContent(data.entry);

            } else {
                // create new widget
                return saveWidget(widget_type);
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
        document.getElementById('loading-overlay').classList.add('hidden');
    });
});

async function setModalContent(data, id = null) {
    data.forEach(function (element) {

        let group = document.createElement("div");
        group.classList.add("form-group");

        let label = document.createElement("label");
        label.innerHTML = element.label;

        group.appendChild(label);

        let child = createWidgetOption(element);

        if (child) {
            group.appendChild(child);
        }
        widgetModalContent.appendChild(group);

    });

    // Load dependent data
    for (const element of data) {
        if (element.dependency) {
            let dependency = widgetModalContent.querySelector('[name="' + element.dependency + '"]');

            if (dependency) {
                await populateDependentWidgetOption(element);
                dependency.addEventListener('change', async function (event) {
                    document.getElementById('loading-overlay').classList.remove('hidden');
                    await populateDependentWidgetOption(element);
                    document.getElementById('loading-overlay').classList.add('hidden');
                });
            }
        }
    }

    document.getElementById('add-widget-modal').value = lang.add;

    if (id !== null) {
        let inputID = document.createElement("input");
        inputID.type = "hidden";
        inputID.name = "id";
        inputID.value = id;
        widgetModalContent.appendChild(inputID);

        document.getElementById('add-widget-modal').value = lang.update;
    }
    widgetModal.style.display = 'block';
}

function saveWidget(type, options = {}) {
    document.getElementById('loading-overlay').classList.remove('hidden');

    return getCSRFToken().then(function (token) {
        let data = { "name": type, "options": options, "csrf_name": token.csrf_name, "csrf_value": token.csrf_value };

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
        document.getElementById('loading-overlay').classList.add('hidden');
    });

}

let widgets = document.querySelectorAll('a.btn-edit');
widgets.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        document.getElementById('loading-overlay').classList.remove('hidden');
        widgetModalContent.innerHTML = '';

        let widget_id = item.dataset.id;

        fetch(jsObject.frontpage_widget_option + widget_id, {
            method: 'GET',
            credentials: "same-origin"
        }).then(function (response) {
            return response.json();
        }).then(async function (data) {
            if (data.status !== 'error') {
                // show modal
                if (data.entry) {
                    await setModalContent(data.entry, widget_id);
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
});

function createWidgetOption(element) {
    if (element.type == "select") {

        let select = document.createElement("select");
        select.classList.add("form-control");
        select.name = element.name;

        Object.keys(element.data).forEach(function (k) {
            let option = document.createElement("option");
            option.value = k;
            option.innerHTML = element.data[k]["name"];

            if (k == element.value) {
                option.selected = true;
            }

            if (element.data[k]["url"]) {
                option.dataset.url = element.data[k]["url"];
            }

            select.appendChild(option);
        });
        return select;
    } else if (element.type == "input") {

        let input = document.createElement("input");
        input.classList.add("form-control");
        input.name = element.name;
        input.value = element.value;
        input.type = "text";

        return input;
    } else if (element.type == "number") {

        let input = document.createElement("input");
        input.classList.add("form-control");
        input.name = element.name;
        input.value = element.value;
        input.type = "number";

        return input;
    }
    return;
}

async function populateDependentWidgetOption(element) {

    let dependency = widgetModalContent.querySelector('[name="' + element.dependency + '"]');
    let url = dependency.options[dependency.selectedIndex].dataset.url;

    if (element.type == "select") {
        let target = widgetModalContent.querySelector('[name="' + element.name + '"]');
        target.innerHTML = "";

        // remove all options and replace with loaded data
        let response = await fetch(url, {
            method: 'GET',
            credentials: "same-origin"
        });
        let data = await response.json();

        if (data.status == "success") {
            Object.keys(data.data).forEach(function (k) {
                let option = document.createElement("option");
                option.value = k;
                option.innerHTML = data.data[k]["name"];

                if (k == element.value) {
                    option.selected = true;
                }

                target.appendChild(option);
            });
        }

    }
}


let widgetsHide = document.querySelectorAll('a.btn-hide');
widgetsHide.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        setHiddenState(item.dataset.id, item.dataset.state == "0" ? 1 : 0);
    });
});

async function setHiddenState(id, state) {
    const data = { 'state': state, 'id': id };

    try {
        const token = await getCSRFToken();

        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        const response = await fetch(jsObject.frontpage_widget_hide, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        allowedReload = true;
        window.location.reload();
    } catch (error) {

        console.log(error);
        if (isOffline(error)) {
            let formData = new URLSearchParams(data).toString();
            saveDataWhenOffline(jsObject.crawler_dataset_save, 'POST', formData);
        }
    }

}