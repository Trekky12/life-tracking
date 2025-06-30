'use strict';

const stepsList = document.querySelector('#recipes_steps');
const addStepBtn = document.querySelector('#add-step-btn');
const step_dummy = document.querySelector('#templates .step-dummy');
const ingredient_dummy = document.querySelector('#templates .step-ingredient-dummy');

function createUniqueID() {
    return Math.floor(Math.random() * Date.now());
}

addStepBtn.addEventListener('click', function (event) {
    event.preventDefault();

    let all_steps = stepsList.querySelectorAll('.step');

    let step_idx = all_steps.length;

    let new_step = step_dummy.cloneNode(true);
    new_step.classList.remove("hidden");
    new_step.classList.remove("step-dummy");
    new_step.dataset.idx = step_idx;

    new_step.querySelector('.step_name').value = "Schritt " + (step_idx + 1);

    let inputs = new_step.querySelectorAll('input, textarea, select');
    inputs.forEach(function (input, idx) {
        input.setAttribute('name', input.name.replace("dummy", step_idx));
        input.removeAttribute('disabled');
    });

    let ingredients = new_step.querySelector('.step-ingredients');
    createSortableIngredients(ingredients);

    stepsList.appendChild(new_step);
});


document.addEventListener('click', function (event) {
    let add_ingredient = event.target.closest('.add-ingredient-btn');
    let step_minus = event.target.closest('.step-minus');
    let ingredient_minus = event.target.closest('.remove-ingredient');

    let step = event.target.closest('.step');


    if (add_ingredient) {
        event.preventDefault();
        console.log("add ingredient");

        let step_idx = step.dataset.idx;

        let ingredientsList = step.querySelector('.step-ingredients');
        let all_ingredients = ingredientsList.querySelectorAll('.step-ingredient');

        let ingredient_idx = all_ingredients.length;

        let new_ingredient = ingredient_dummy.cloneNode(true);
        new_ingredient.classList.remove("step-ingredient-dummy");

        let inputs = new_ingredient.querySelectorAll('input, textarea, select');
        inputs.forEach(function (input, idx) {
            input.setAttribute('name', input.name.replace("dummy_step", step_idx).replace("dummy_ingredient", ingredient_idx));
            input.removeAttribute('disabled');
        });

        ingredientsList.appendChild(new_ingredient);

        let uniqueID = createUniqueID();
        let ingredient_selector = new_ingredient.querySelector('.step-ingredient-select');
        ingredient_selector.id = ingredient_selector.id + "-" + uniqueID;
        ingredient_selector.parentElement.id = ingredient_selector.parentElement.id + "-" + uniqueID;
        ingredient_selector.previousElementSibling.id = ingredient_selector.previousElementSibling.id + "-" + uniqueID;

        createChoiceIngredients(ingredient_selector);
    }

    if (step_minus) {
        event.preventDefault();
        step_minus.parentElement.parentElement.remove();
        updateStepFields();
    }

    if (ingredient_minus) {
        event.preventDefault();
        ingredient_minus.parentElement.remove();
        updateIngredientFields(step);
    }

});

function updateStepFields() {
    // change input field array key
    // @see https://stackoverflow.com/a/47948276
    let steps = stepsList.querySelectorAll('.step:not(.step-dummy)');
    steps.forEach(function (item, idx) {

        item.dataset.position = idx;

        let fields = item.querySelectorAll('input, textarea, select');
        fields.forEach(function (field) {
            field.setAttribute('name', field.name.replace(/steps\[[^\]]*\]/, 'steps[' + idx + ']'));
        });
    });
}

function createSortableSteps(element) {
    new Sortable(element, {
        scroll: true,
        scrollSensitivity: 100,
        draggable: ".step",
        swapThreshold: 0.9,
        fallbackOnBody: true,
        handle: ".step-handle",
        ghostClass: 'step-drag',
        onUpdate: function (evt) {
            updateStepFields();
        },
        onStart: function (evt) {
            document.body.classList.add("sortable-select");
        },
        onEnd: function (evt) {
            document.body.classList.remove("sortable-select");
            evt.item.scrollIntoView();
        }
    });
}

createSortableSteps(stepsList);

function updateIngredientFields(step) {
    let ingredients = step.querySelectorAll('.step-ingredient');
    ingredients.forEach(function (item, idx) {
        let fields = item.querySelectorAll('input, textarea, select');
        fields.forEach(function (field) {
            field.setAttribute('name', field.name.replace(/\[ingredients\]\[[^\]]*\]/, '[ingredients][' + idx + ']'));
        });
    });
}

const stepsIngredients = stepsList.querySelectorAll('.step .step-ingredients');
stepsIngredients.forEach(function (item, idx) {
    createSortableIngredients(item);

    const stepIngredientsWrapper = item.querySelectorAll('.step-ingredient-select');
    stepIngredientsWrapper.forEach(function (item2, idx) {
        createChoiceIngredients(item2);
    });

});


function createSortableIngredients(step_ingredients_wrapper) {
    new Sortable(step_ingredients_wrapper, {
        draggable: ".step-ingredient",
        swapThreshold: 0.9,
        fallbackOnBody: true,
        handle: ".ingredient-handle",
        ghostClass: 'ingredient-drag',
        onUpdate: function (evt) {
            let step = step_ingredients_wrapper.closest('.step');
            updateIngredientFields(step);
        }
    });
}

async function createChoiceIngredients(element) {
    new autoComplete({
        data: {
            src: async () => {

                try {
                    let response = await fetch(jsObject.groceries_search + '?query=' + element.value + "&food=1", {
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
            destination: "#" + element.parentElement.id,
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
        selector: "#" + element.id,
        wrapper: false,
    });


    element.addEventListener("selection", function (event) {
        const feedback = event.detail;
        element.value = feedback.selection.value.name;

        let unit = element.closest('.step-ingredient').querySelector('.step-ingredient-unit');
        let id = element.closest('.step-ingredient').querySelector('.step-ingredient-id');
        if (unit.value == '') {
            unit.value = feedback.selection.value.unit;
        }
        id.value = feedback.selection.value.id;
    });

    element.addEventListener("results", function (event) {
        console.log(event.detail.results.length);
        if (event.detail.results.length == 0) {
            event.target.nextElementSibling.classList.add('hidden');
        } else {
            event.target.nextElementSibling.classList.remove('hidden');
        }
    });

    element.addEventListener("close", function (event) {
        event.target.nextElementSibling.classList.add('hidden');
    });
}
