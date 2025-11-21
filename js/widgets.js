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

    let url = jsObject.frontpage_widget_request + id;
    let params = []
    if (widget || options) {
        url += '?';
        if (widget) {
            params.push('widget=' + widget);
        }
        if (options) {
            params.push('options=' + options);
        }
        url += params.join('&');
    }

    return fetch(url, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json',
            'sw-cache': 'none'
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        item.innerHTML = data["data"];
    }).catch(function (error) {
        console.log(error);
    });
}


document.addEventListener('click', async function (event) {
    let btn_archive_card = event.target.closest('.btn-archive-card');
    if (btn_archive_card) {
        //event.preventDefault();

        setTimeout(async function () {
            let url = btn_archive_card.dataset.url;
            let archive = parseInt(btn_archive_card.dataset.archive) === 0 ? 1 : 0;

            if (archive === 0) {
                if (!await confirmDialog(lang.boards_undo_archive)) {
                    btn_archive_card.checked = true;
                    return false;
                }
            } else {
                if (!await confirmDialog(lang.boards_really_archive)) {
                    btn_archive_card.checked = false;
                    return false;
                }
            }

            var data = { 'archive': archive };

            getCSRFToken().then(function (token) {
                data['csrf_name'] = token.csrf_name;
                data['csrf_value'] = token.csrf_value;

                return fetch(url, {
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
                //allowedReload = true;
                //window.location.reload();
                btn_archive_card.dataset.archive = archive;
            }).catch(function (error) {
                console.log(error);
            });
        }, 20);

    }
});