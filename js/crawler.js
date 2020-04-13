'use strict';

let filterCrawlerDatasets = document.getElementById('filterCrawlerDatasets');
filterCrawlerDatasets.addEventListener('change', function (event) {

    var data = {'state': filterCrawlerDatasets.value};

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
        item.addEventListener('click', function (event) {

            var data = {'state': event.target.classList.contains("is_saved") ? 0 : 1, 'dataset': event.target.dataset.id};
            
            if (event.target.classList.contains("is_saved") && !confirm(lang.really_unsave_dataset)) {
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
                    event.target.classList.toggle('is_saved');
                }
            }).catch(function (error) {
                console.log(error);
            });
        });
    });
}

add_save_dataset_function();
crawlersDataTable.on("update", function (data) {
    add_save_dataset_function();
});

