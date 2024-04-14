'use strict';

let filterCrawlerDatasets = document.getElementById('filterCrawlerDatasets');
filterCrawlerDatasets.addEventListener('change', function (event) {

    var data = { 'state': filterCrawlerDatasets.value };

    getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.crawler_filter, {
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
        allowedReload = true;
        window.location.reload();
    }).catch(function (error) {
        console.log(error);
    });


    return;
});

let crawlerLinks = document.querySelectorAll('#crawler_links > li > a');
crawlerLinks.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        event.target.parentElement.classList.toggle('active');
    });
});

function add_save_dataset_function() {
    let crawlerFavorite = document.querySelectorAll('span.save_crawler_dataset');
    crawlerFavorite.forEach(function (item, idx) {
        item.addEventListener('click', async function (event) {

            var data = { 'state': item.classList.contains("is_saved") ? 0 : 1, 'dataset': item.dataset.id };

            if (item.classList.contains("is_saved") && !await confirmDialog(lang.really_unsave_dataset)) {
                return false;
            }

            getCSRFToken().then(function (token) {
                data['csrf_name'] = token.csrf_name;
                data['csrf_value'] = token.csrf_value;

                return fetch(jsObject.crawler_dataset_save, {
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
                if (data['status'] === 'success') {
                    item.classList.toggle('is_saved');
                }
            }).catch(function (error) {
                console.log(error);
                if (document.body.classList.contains('offline')) {
                    let formData = new URLSearchParams(data).toString();
                    saveDataWhenOffline(jsObject.crawler_dataset_save, 'POST', formData);
                }
            });
        });
    });
}

add_save_dataset_function();
crawlersDataTable.on("update", function (data) {
    add_save_dataset_function();
});

const markAsShownBtn = document.querySelector('#mark_all_as_shown');
markAsShownBtn.addEventListener('click', async function (event) {
    event.preventDefault();
    try {
        let token = await getCSRFToken();
        let data = {};
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        let result = await fetch(jsObject.crawler_set_shown, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        let json = await result.json();
        if (json['status'] === 'done') {
            alert(lang.crawlers_mark_all_as_shown_success);
        } else {
            alert(lang.crawlers_mark_all_as_shown_error);
        }
    } catch (error) {
        console.log(error);
        alert(lang.crawlers_mark_all_as_shown_error);
    }
});