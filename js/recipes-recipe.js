'use strict';

const stepsIngredientsSelect = document.querySelectorAll('.ingredient-select');
stepsIngredientsSelect.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        //event.preventDefault();
        let id = item.dataset.id;
        let items = document.querySelectorAll('.ingredient-select[data-id="' + id + '"]');
        items.forEach(function (el) {
            el.classList.toggle('active');
            el.checked = item.checked;
        });
    });
});

/**
 * Loading list
 */

const recipesList = document.querySelector('#recipes_list');
const loadingIconRecipes = document.querySelector('#loadingIconRecipes');
const loadMoreRecipes = document.querySelector('#loadMoreRecipes');
const filterSearchRecipes = document.getElementById('filterSearchRecipes');

document.addEventListener("DOMContentLoaded", function () {
    loadMoreRecipesFunctions();
    getRecipes();
});

function getRecipes(reset = false) {
    if (recipesList !== null) {

        let start = reset ? 0 : recipesList.querySelectorAll('.recipe').length;
        let count = 20;
        let query = filterSearchRecipes ? filterSearchRecipes.value : '';
        let type = 'list';

        loadingIconRecipes.classList.remove("hidden");
        loadMoreRecipes.classList.add("hidden");

        let url = jsObject.recipes_get + '?type=' + type + '&count=' + count + '&start=' + start + '&query=' + query;

        if (recipesList.dataset.cookbook !== undefined) {
            url = url + '&cookbook=' + recipesList.dataset.cookbook;
        }

        return fetch(url, {
            method: 'GET',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (reset) {
                recipesList.innerHTML = '';
            }
            if (data.status !== 'error') {

                loadingIconRecipes.classList.add("hidden");

                let totalCount = parseInt(data.count);
                if (totalCount > 0) {
                    // there are more data available
                    if ((start + count) < totalCount) {
                        loadMoreRecipes.classList.remove("hidden");
                    } else {
                        loadMoreRecipes.classList.add("hidden");
                    }
                    recipesList.insertAdjacentHTML('beforeend', data["data"]);
                } else {
                    let nothing_found = document.createElement('p');
                    nothing_found.innerHTML = lang.nothing_found;
                    recipesList.innerHTML = '';

                    recipesList.appendChild(nothing_found);
                    loadMoreRecipes.classList.add("hidden");
                }
            }
        }).catch(function (error) {
            console.log(error);
        });
    }
    return emptyPromise();
}

function loadMoreRecipesFunctions() {
    if (loadMoreRecipes !== null) {
        loadMoreRecipes.addEventListener('click', function (e) {
            getRecipes();
        });

        let offset = 100;

        document.addEventListener('scroll', function () {
            let body = document.body;
            let html = document.documentElement;

            if ((html.scrollTop > 0 && (html.scrollTop + html.clientHeight + offset >= html.scrollHeight)) || (body.scrollTop > 0 && (body.scrollTop + body.clientHeight + offset >= body.scrollHeight))) {
                if (!loadMoreRecipes.classList.contains('hidden')) {
                    getRecipes();
                }
            }
        });

    }
}

if (filterSearchRecipes) {
    filterSearchRecipes.addEventListener('keyup', function (event) {
        event.preventDefault();
        getRecipes(true);
    });
}

let cookmodeWakeLock = null;
const cookmodeButton = document.getElementById("cookmodeButton");

async function enableCookmode() {
    try {
        cookmodeWakeLock = await navigator.wakeLock.request("screen");
        cookmodeButton.textContent = "🕓 " + lang.recipes_cookmode_enabled;

        document.addEventListener("visibilitychange", async function () {
            if (cookmodeWakeLock !== null && document.visibilityState === "visible") {
                cookmodeWakeLock = await navigator.wakeLock.request("screen");
            }
        });
    } catch (err) {
        console.error("Error retrieving wakelock:", err.name, err.message);
        showToast(lang.recipes_cookmode_error, "red");
    }
}

function disableCookmode() {
    if (cookmodeWakeLock) {
        cookmodeWakeLock.release();
        cookmodeWakeLock = null;
        cookmodeButton.textContent = "🍳 " + lang.recipes_cookmode_enable;
    }
}

cookmodeButton.addEventListener("click", function () {
    if (cookmodeWakeLock) {
        disableCookmode();
    } else {
        enableCookmode();
    }
});
