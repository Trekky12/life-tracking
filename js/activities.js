'use strict';

const activitiesList = document.querySelector('#activities');
const loadingIconActivities = document.querySelector('#loadingIconActivities');
const loadMoreActivities = document.querySelector('#loadMoreActivities');

document.addEventListener("DOMContentLoaded", function () {
    loadMoreActivitiesFunctions();
    getActivities();
});

function getActivities() {
    if (activitiesList !== null) {

        let start = activitiesList.querySelectorAll('.activity').length;;
        let count = 20;

        loadingIconActivities.classList.remove("hidden");
        loadMoreActivities.classList.add("hidden");

        let data = {"count": count, "start": start};

        return getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(jsObject.activities_get, {
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
            if (data.status !== 'error') {

                loadingIconActivities.classList.add("hidden");

                let totalCount = parseInt(data.count);
                if (start + count < totalCount) {
                    loadMoreActivities.classList.remove("hidden");
                }

                // get existing date headlines
                let dates = [];
                let dateDivs = activitiesList.querySelectorAll(".activity-date");
                dateDivs.forEach(function (item, idx) {
                    let date = item.dataset.date;
                    if (!dates.includes(date)) {
                        dates.push(date);
                    }
                });

                data.data.forEach(function (item, idx) {

                    // create new date headline
                    if (!dates.includes(item["date"])) {
                        let hDate = document.createElement("h2");
                        hDate.classList = 'activity-date';
                        hDate.dataset.date = item["date"];
                        hDate.innerHTML = item["date"];
                        activitiesList.appendChild(hDate);

                        dates.push(item["date"]);
                    }


                    let div = document.createElement("div");
                    div.classList = 'activity';

                    let iIcon = document.createElement("i");
                    iIcon.classList = item["icon"];
                    div.appendChild(iIcon);

                    let divContent = document.createElement("div");
                    divContent.classList.add("content");

                    let divTime = document.createElement("div");
                    divTime.classList = "time";
                    divTime.innerHTML = item["time"];
                    divContent.appendChild(divTime);

                    let pMessage = document.createElement("p");

                    if (item["link"]) {
                        let aMessage = document.createElement("a");
                        aMessage.href = item["link"];
                        aMessage.innerHTML = item["description"];

                        pMessage.appendChild(aMessage);
                    } else {
                        pMessage.innerHTML = item["description"];
                    }

                    divContent.appendChild(pMessage);

                    div.appendChild(divContent);

                    activitiesList.appendChild(div);
                });
            }
        }).catch(function (error) {
            console.log(error);
        });
    }
    return emptyPromise();
}


function loadMoreActivitiesFunctions() {
    if (loadMoreActivities !== null) {
        loadMoreActivities.addEventListener('click', function (e) {
            getActivities();
        });
        // Detect when scrolled to bottom.
        document.addEventListener('scroll', function () {
            let body = document.body;
            let html = document.documentElement;
            let offset = 100;

            if ((html.scrollTop > 0 && (html.scrollTop + html.clientHeight + offset >= html.scrollHeight)) || (body.scrollTop > 0 && (body.scrollTop + body.clientHeight + offset >= body.scrollHeight))) {
                if (!loadMoreActivities.classList.contains('hidden')) {
                    getActivities();
                }
            }
        });
    }
}