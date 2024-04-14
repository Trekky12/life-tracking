'use strict';

function add_save_dataset_function() {
    let crawlerFavorite = document.querySelectorAll('span.save_crawler_dataset');
    crawlerFavorite.forEach(function (item, idx) {
        item.addEventListener('click', async function (event) {

            var data = {'state': item.classList.contains("is_saved") ? 0 : 1, 'dataset': item.dataset.id};

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
                allowedReload = true;
                window.location.reload();
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
crawlersDataSavedTable.on("update", function (data) {
    add_save_dataset_function();
});
