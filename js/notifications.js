'use strict';


const categoriesUserElements = document.querySelectorAll('#notifications_categories_list_user input.set_notifications_category_user');
const notificationsList = document.querySelector('#notifications');
const loadingIcon = document.querySelector('#loadingIconNotifications');
const loadMore = document.querySelector('#loadMoreNotifications');


document.addEventListener("DOMContentLoaded", function () {
    categoriesUserElements.forEach(function (item, idx) {
        item.addEventListener('click', function () {
            let val = parseInt(item.value);
            if (item.checked) {
                return setCategoryUser(1, val).then(function (data) {
                    console.log(data);
                });
            } else {
                return setCategoryUser(0, val).then(function (data) {
                    console.log(data);
                });
            }
        });
    });

    loadMoreFunctions();
    getNotifications();
});


function setCategoryUser(type, category) {
    let data = {"category": category, "type": type};

    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.notifications_clients_set_category_user, {
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

function getNotifications() {
    if (notificationsList !== null) {

        let start = notificationsList.childElementCount;
        let count = 10;

        loadingIcon.classList.remove("hidden");
        loadMore.classList.add("hidden");

        let data = {"count": count, "start": start};

        return getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(jsObject.notifications_get, {
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

                loadingIcon.classList.add("hidden");

                let totalCount = parseInt(data.count);
                if (start + count < totalCount) {
                    loadMore.classList.remove("hidden");
                }

                setNotificationCount(data.unseen);

                data.data.forEach(function (item, idx) {

                    let div = document.createElement("div");
                    div.classList = 'notification';
                    div.innerHtml = item.message;
                    div.dataset.id = item.id;

                    if (item.seen) {
                        div.classList = div.classList + ' seen';
                    }

                    let header = document.createElement("div");
                    header.classList = 'notification-header';

                    let hTitle = document.createElement("h2");

                    if (item.link) {
                        let aTitle = document.createElement("a");
                        aTitle.href = item.link;
                        aTitle.innerHTML = item.title;

                        hTitle.appendChild(aTitle);
                    } else {
                        hTitle.innerHTML = item.title;
                    }

                    header.appendChild(hTitle);

                    if (item.category) {
                        if (data.categories[item.category].internal !== 1) {
                            let spanCategory = document.createElement("span");
                            spanCategory.innerHTML = lang.category + ": " + data.categories[item.category].name;

                            header.appendChild(spanCategory);
                        }
                    }

                    let divMessage = document.createElement("div");
                    divMessage.classList = 'notification-content';

                    let pMessage = document.createElement("p");
                    if (item.link) {
                        let aMessage = document.createElement("a");
                        aMessage.href = item.link;
                        aMessage.innerHTML = item.message;

                        pMessage.appendChild(aMessage);
                    } else {
                        pMessage.innerHTML = item.message;
                    }

                    let divDate = document.createElement("div");
                    divDate.classList = "createdOn";
                    divDate.innerHTML = moment(item.createdOn).format(i18n.dateformatJS.datetime);

                    divMessage.appendChild(pMessage);
                    divMessage.appendChild(divDate);

                    div.appendChild(header);
                    div.appendChild(divMessage);

                    notificationsList.appendChild(div);
                });



            }
        }).catch(function (error) {
            console.log(error);
        });
    }
    return emptyPromise();
}


function loadMoreFunctions() {
    if (loadMore !== null) {
        loadMore.addEventListener('click', function (e) {
            getNotifications();
        });
        // Detect when scrolled to bottom.
        document.addEventListener('scroll', function () {
            let body = document.body;
            let html = document.documentElement;
            let offset = 100;

            if ((html.scrollTop > 0 && (html.scrollTop + html.clientHeight + offset >= html.scrollHeight)) || (body.scrollTop > 0 && (body.scrollTop + body.clientHeight + offset >= body.scrollHeight))) {
                if (!loadMore.classList.contains('hidden')) {
                    getNotifications();
                }
            }
        });
    }
}