'use strict';

const recipesList = document.querySelector('#recipes_list');
const loadingIconRecipes = document.querySelector('#loadingIconRecipes');
const filterSearchRecipes = document.getElementById('filterSearchRecipes');
const noticeModal = document.getElementById("notice-modal");
const noticeModalClose = document.getElementById("modal-close-btn");

document.addEventListener('click', async function (event) {
    let minus = event.target.closest('.minus');
    let add_notice = event.target.closest('.create-notice');

    if (minus) {
        event.preventDefault();
        let element = minus.parentElement.parentElement;
        let id = element.dataset.id;

        let data = { "mealplan_recipe_id": id };
        try {
            let token = await getCSRFToken(true);
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            let response = await fetch(jsObject.recipes_mealplan_remove_recipe, {
                method: 'DELETE',
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });

            let result = await response.json();
            if (result["is_deleted"]) {
                element.remove();
            }
        } catch (error) {
            console.log(error);
        }

    }

    if (add_notice) {
        event.preventDefault();

        let target = add_notice.parentElement.querySelector('.recipes-target');
        let date = target.dataset.date;

        noticeModal.querySelector("input[name='date']").value = date;

        freeze();
        noticeModal.showModal();

    }
});

const recipesTargets = document.querySelectorAll('.recipes-target');
recipesTargets.forEach(function (item, idx) {
    createSortable(item, false);
});

function createSortable(element, isSource) {
    if (document.querySelector('.mealplan-list-settings')) {
        let options = {
            group: {
                name: "recipes",
            },
            swapThreshold: 0.5,
            fallbackOnBody: true,
            handle: ".handle",
            dataIdAttr: 'data-id',
            onUpdate: function (evt) {
                move_recipe(evt);
            },
            onAdd: function (evt) {
                move_recipe(evt);
            }
        };

        if (isSource) {
            options["group"]["pull"] = "clone";
            options["group"]["put"] = false;
        }
        new Sortable(element, options);
    }
}

function move_recipe(evt) {
    let date = evt.to.dataset.date;

    let recipe = evt.item.dataset.recipe;
    let id = evt.item.dataset.id;

    createRecipeEntry(evt.item, recipe, date, evt.newDraggableIndex, id, null);

}

async function createRecipeEntry(target, recipe, date, position, id, notice) {

    var data = { 'recipe': recipe, 'date': date, 'position': position, 'id': id, 'notice': notice };

    try {
        const token = await getCSRFToken();
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;
        const response = await fetch(jsObject.recipes_mealplan_move_recipe, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        const result = await response.json();
        if (result['status'] === 'success') {
            let result_id = result["id"];
            target.dataset.id = result_id;
            target.querySelector('.minus').classList.remove("hidden");
        } else {
            target.remove();
        }
    } catch (error) {
        console.log(error);
        if (isOffline(error)) {
            let formData = new URLSearchParams(data).toString();
            saveDataWhenOffline(jsObject.recipes_mealplan_move_recipe, 'POST', formData);
        }
    }
}

async function getRecipes() {
    let start = 0;
    let count = 5;
    let query = filterSearchRecipes ? filterSearchRecipes.value : '';
    let type = 'mealplan';
    let url = jsObject.recipes_get_mealplan + '?type=' + type + '&count=' + count + '&start=' + start + '&query=' + query;

    loadingIconRecipes.classList.remove("hidden");

    try {
        const response = await fetch(url, {
            method: 'GET',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            }
        });
        const data = await response.json();
        recipesList.innerHTML = '';
        if (data.status !== 'error') {
            loadingIconRecipes.classList.add("hidden");
            let totalCount = parseInt(data.count);
            if (totalCount > 0) {
                recipesList.insertAdjacentHTML('beforeend', data["data"]);
                createSortable(recipesList, true);
            } else {
                let nothing_found = document.createElement('p');
                nothing_found.innerHTML = lang.nothing_found;
                recipesList.innerHTML = '';

                recipesList.appendChild(nothing_found);
            }
        }
    } catch (error) {
        console.log(error);
    }
}

getRecipes();
if (filterSearchRecipes) {
    filterSearchRecipes.addEventListener('keyup', function (event) {
        event.preventDefault();
        getRecipes();
    });
}


if (noticeModalClose) {
    noticeModalClose.addEventListener('click', function (e) {
        noticeModal.close();
        noticeModal.querySelector('form').reset();
        unfreeze();
    });
}
noticeModal.querySelector("form").addEventListener('submit', function (e) {
    e.preventDefault();
    document.getElementById('loading-overlay').classList.remove('hidden');

    let notice = noticeModal.querySelector('input[name="notice"]').value;
    let date = noticeModal.querySelector('input[name="date"]').value;

    let target = document.querySelector(".recipes-target[data-date='" + date + "']");
    let notice_dummy = document.querySelector("#templates .mealplan-recipe");
    let notice_entry = notice_dummy.cloneNode(true);

    notice_entry.querySelector('h3.title').innerHTML = notice;

    target.appendChild(notice_entry);

    createRecipeEntry(notice_entry, null, date, 999, null, notice).then(function () {

        document.getElementById('loading-overlay').classList.add('hidden');
        unfreeze();
        noticeModal.querySelector('form').reset();
        noticeModal.close();
    });

});