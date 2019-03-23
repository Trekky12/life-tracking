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