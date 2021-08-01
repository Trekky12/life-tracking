'use strict';

const recipesList = document.querySelector('#recipes_list');
const loadingIconRecipes = document.querySelector('#loadingIconRecipes');
const filterSearchRecipes = document.getElementById('filterSearchRecipes');

document.addEventListener('click', function (event) {
    let minus = event.target.closest('.minus');

    if (minus) {
        event.preventDefault();
        let element = minus.parentElement.parentElement;
        let id = element.dataset.id;

        let data = {"mealplan_recipe_id": id};
        getCSRFToken(true).then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(jsObject.recipes_mealplan_remove_recipe, {
                method: 'DELETE',
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(data)
            });
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (data["is_deleted"]) {
                element.remove();
            }
        }).catch(function (error) {
            console.log(error);
        });

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

    var data = {'recipe': recipe, 'date': date, 'position': evt.newDraggableIndex, 'id': id};

    getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.recipes_mealplan_move_recipe, {
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
            let id = data["id"];
            evt.item.dataset.id = id;
            evt.item.querySelector('.minus').classList.remove("hidden");
        } else {
            evt.item.remove();
        }
    }).catch(function (error) {
        console.log(error);
    });
}

function getRecipes() {
    let start = 0;
    let count = 5;
    let query = filterSearchRecipes ? filterSearchRecipes.value : '';
    let type = 'mealplan';
    let url = jsObject.recipes_get_mealplan + '?type=' + type + '&count=' + count + '&start=' + start + '&query=' + query;

    loadingIconRecipes.classList.remove("hidden");

    return fetch(url, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
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
    }).catch(function (error) {
        console.log(error);
    });
}

getRecipes();
if (filterSearchRecipes) {
    filterSearchRecipes.addEventListener('keyup', function (event) {
        event.preventDefault();
        getRecipes();
    });
}