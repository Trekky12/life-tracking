'use strict';


const logViewer = document.querySelector('#logviewer');
const loadingIconLogfile = document.querySelector('#loadingIconLogfile');
const logViewerContent = document.querySelector('#logviewer #log-content');

if (logViewer) {

    loadingIconLogfile.classList.remove("hidden");

    fetch(jsObject.logfile_get + '?days=' + logViewer.dataset.days, {
        method: 'GET',
        credentials: "same-origin"
    }).then(function (response) {
        return response.text();
    }).then(function (data) {
        loadingIconLogfile.classList.add("hidden");
        logViewerContent.innerHTML = data;
    }).then(function () {
        /**
         * Logviewer autoscroll to bottom
         */
        logViewer.scrollTop = logViewer.scrollHeight;
        let logviewer_checkboxes = document.querySelectorAll('.log-filter input[type="checkbox"]');
        logviewer_checkboxes.forEach(function (item, idx) {
            item.addEventListener('change', function (event) {
                let type = item.dataset.type;
                document.querySelectorAll('#logviewer .log-entry.' + type).forEach(function (entry, idx) {
                    entry.classList.toggle('hidden');
                });
                logViewer.scrollTop = logViewer.scrollHeight;
            });
        });
    }).catch(function (error) {
        console.log(error);
    });

}
