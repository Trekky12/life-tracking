'use strict';


const addGroceryToList = document.querySelector("#addGroceryToList");
const addGroceryToList_amount = document.querySelector("#addGroceryToList_amount");
const addGroceryToList_name = document.querySelector("#addGroceryToList_name");
const addGroceryToList_unit = document.querySelector("#addGroceryToList_unit");
const addGroceryToList_ID = document.querySelector("#addGroceryToList_ID");
const addGroceryToList_notice = document.querySelector("#addGroceryToList_notice");
const shoppingListEntries = document.querySelector(".shopping-list-entries");

const loadingIconShoppingListEntries = document.querySelector('#loadingIconShoppingListEntries');
const loadMoreShoppingListEntries = document.querySelector('#loadMoreShoppingListEntries');
const filterSearchRecipes = document.getElementById('filterSearchRecipes');

let shoppingListEntriesCount = 0;
const count = 50;

const newShoppinglistEntriesAlert = document.querySelector('#new-shoppinglist-entries-alert');

document.addEventListener("DOMContentLoaded", async function () {
    loadMoreShoppingListEntriesFunctions();
    loadShoppingListEntries();
});

async function loadShoppingListEntries(){
    loadingIconShoppingListEntries.classList.remove("hidden");
    loadMoreShoppingListEntries.classList.add("hidden");
    let data = await getShoppingListEntries();
    renderShoppingListEntries(data);
}


async function getShoppingListEntries() {
    if (shoppingListEntries !== null) {

        let start = shoppingListEntries.querySelectorAll('.shopping-list-entry').length;

        let url = jsObject.recipes_shoppinglistentries_get + '?count=' + count + '&start=' + start;

        return fetch(url, {
            method: 'GET',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(function (response) {
            return response.json();
        }).catch(function (error) {
            console.log(error);
        });
    }
    return emptyPromise();
}

function renderShoppingListEntries(data) {
    if (data.status !== 'error') {

        loadingIconShoppingListEntries.classList.add("hidden");

        let totalCount = parseInt(data.count);
        shoppingListEntriesCount = totalCount;
        if (totalCount > 0) {
            // there are more data available
            let start = shoppingListEntries.querySelectorAll('.shopping-list-entry').length;
            if ((start + count) < totalCount) {
                loadMoreShoppingListEntries.classList.remove("hidden");
            } else {
                loadMoreShoppingListEntries.classList.add("hidden");
            }

            data['data']['entries'].forEach(function (item) {
                let li = createShoppinglistEntry(item['id'], item['unit'], item['amount'], item['grocery'], item['notice'], item['done']);
                shoppingListEntries.append(li);
            });

            //shoppingListEntries.insertAdjacentHTML('beforeend', data["data"]);
        } else {
            let nothing_found = document.createElement('p');
            nothing_found.innerHTML = lang.nothing_found;
            shoppingListEntries.innerHTML = '';

            shoppingListEntries.appendChild(nothing_found);
            loadMoreShoppingListEntries.classList.add("hidden");
        }
    }
}

function loadMoreShoppingListEntriesFunctions() {
    if (loadMoreShoppingListEntries !== null) {
        loadMoreShoppingListEntries.addEventListener('click', async function (e) {
            loadShoppingListEntries();
        });

        let offset = 100;

        document.addEventListener('scroll', async function () {
            let body = document.body;
            let html = document.documentElement;

            if ((html.scrollTop > 0 && (html.scrollTop + html.clientHeight + offset >= html.scrollHeight)) || (body.scrollTop > 0 && (body.scrollTop + body.clientHeight + offset >= body.scrollHeight))) {
                if (!loadMoreShoppingListEntries.classList.contains('hidden')) {
                    loadShoppingListEntries();
                }
            }
        });

    }
}

addGroceryToList.addEventListener('click', function (event) {
    event.preventDefault();
    addEntryToList();
});
addGroceryToList_name.addEventListener('keydown', function (event) {
    if (event.keyCode === 13) {
        event.preventDefault();
        loadingWindowOverlay.classList.remove("hidden");
        addEntryToList();
    }
});

async function addEntryToList() {

    document.activeElement.blur();

    document.querySelector('#groceries-suggestion-list').classList.add('hidden');

    document.querySelectorAll('.alert.flash-message').forEach(function (alert) {
        alert.classList.add('hidden');
    });

    var data = {
        'amount': addGroceryToList_amount.value,
        'grocery_input': addGroceryToList_name.value,
        'unit': addGroceryToList_unit.value,
        'notice': addGroceryToList_notice.value,
        'id': addGroceryToList_ID.value
    };

    try {
        let token = await getCSRFToken();

        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        let response = await fetch(jsObject.recipes_shoppinglists_add_entry, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)

        });
        let result = await response.json();
        if (result['status'] === 'success') {
            let id = result["id"];

            // Grocery erfolgreich gespeichert
            let li = createShoppinglistEntry(id, data['unit'], data['amount'], result['entry']['name'], data['notice']);

            if (shoppingListEntries.querySelectorAll('li').length == 0) {
                shoppingListEntries.innerHTML = '';
            }

            shoppingListEntries.prepend(li);
            shoppingListEntriesCount = shoppingListEntriesCount + 1;

        } else {
            showToast(lang.recipes_shoppinglist_error_add, "red");
        }
    } catch (error) {
        console.log(error);
        if (document.body.classList.contains('offline')) {
            let formData = new URLSearchParams(data).toString();
            saveDataWhenOffline(jsObject.recipes_shoppinglists_add_entry, 'POST', formData);
        }
    } finally {
        loadingWindowOverlay.classList.add("hidden");
        addGroceryToList_amount.value = '';
        addGroceryToList_name.value = '';
        addGroceryToList_unit.value = '';
        addGroceryToList_notice.value = '';
        addGroceryToList_ID.value = '';

        autocompleteJS.close();
    }
}

function createShoppinglistEntry(id, unit, amount, name, notice, done) {
    let li = document.createElement("li");
    li.classList.add("shopping-list-entry");
    li.classList.add("custom-checkbox");

    let checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.id = id;
    checkbox.name = id;
    checkbox.dataset.id = id;
    checkbox.autocomplete = "off";
    checkbox.dataset.url = jsObject.recipes_shoppinglistentries_set_state;

    if (done) {
        checkbox.checked = "checked";
    }

    li.appendChild(checkbox);

    let label = document.createElement("label");
    label.htmlFor = id;

    let span_unit = amount && unit ? '<span class="unit">' + unit + '</span> ' : '';
    let span_notice = notice ? '<span class="notice"> (' + notice + ')</span> ' : '';

    label.innerHTML = '<span class="amount">' + (amount ? amount : '') + '</span> ' + span_unit + name + span_notice;

    li.append(label);

    let link = document.createElement("a");
    link.href = "#";
    link.dataset.url = jsObject.recipes_shoppinglists_delete_entry + id;
    link.classList.add("btn-delete");
    link.innerHTML = document.getElementById('iconTrash').innerHTML;

    li.append(link);

    return li;
}

const autocompleteJS = new autoComplete({
    data: {
        src: async () => {

            try {
                let response = await fetch(jsObject.groceries_search + '?query=' + addGroceryToList_name.value, {
                    method: 'GET',
                    credentials: "same-origin"
                });
                let data = await response.json();

                if (data.status !== 'error') {
                    return data.data;
                }
                return [];
            } catch (exception) {
                return [];
            }
        },
        keys: ['text'],
        cache: false,
    },
    resultsList: {
        class: 'groceries-suggestion',
        id: 'groceries-suggestion-list',
        destination: "#groceries-suggestion-wrapper",
        position: "beforeend",
        tag: "ul",
        noResults: true,
        maxResults: 10,
    },
    resultItem: {
        highlight: true
    },
    trigger: query => query.length > 0,
    threshold: 1,
    debounce: 100,
    //placeHolder: lang.searching,
    selector: "#addGroceryToList_name",
    wrapper: false,
});

addGroceryToList_name.addEventListener("selection", function (event) {
    const feedback = event.detail;
    addGroceryToList_name.value = feedback.selection.value.name;
    if (addGroceryToList_unit.value == '') {
        addGroceryToList_unit.value = feedback.selection.value.unit;
    }
    addGroceryToList_ID.value = feedback.selection.value.id;
});

addGroceryToList_name.addEventListener("results", function (event) {
    if (event.detail.results.length == 0) {
        document.querySelector('#groceries-suggestion-list').classList.add('hidden');
    } else {
        document.querySelector('#groceries-suggestion-list').classList.remove('hidden');
    }
});

addGroceryToList_name.addEventListener("close", function (event) {
    document.querySelector('#groceries-suggestion-list').classList.add('hidden');
});

/**
 * Auto Update page
 */
setInterval(async function () {
    let newData = await getShoppingListEntries();
    if (newData.status !== 'error') {
        let totalCount = parseInt(newData.count);
        if (shoppingListEntriesCount !== totalCount) {
            newShoppinglistEntriesAlert.classList.remove("hidden");
        }
    }
}, 10000);
