'use strict';

const stepsList = document.querySelector('#recipes_steps');
const addStepBtn = document.querySelector('#add-step-btn');
const step_dummy = document.querySelector('#templates .step-dummy');
const ingredient_dummy = document.querySelector('#templates .step-ingredient-dummy');

addStepBtn.addEventListener('click', function (event) {
    event.preventDefault();

    let all_steps = stepsList.querySelectorAll('.step');

    let step_idx = all_steps.length;

    let new_step = step_dummy.cloneNode(true);
    new_step.classList.remove("hidden");
    new_step.classList.remove("step-dummy");
    new_step.dataset.idx = step_idx;

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

        createChoiceIngredients(new_ingredient.querySelector('.step-ingredient-select'));

        ingredientsList.appendChild(new_ingredient);
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
        draggable: ".step",
        swapThreshold: 0.9,
        fallbackOnBody: true,
        handle: ".step-handle",
        ghostClass: 'step-drag',
        onUpdate: function (evt) {
            updateStepFields();
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

function createChoiceIngredients(element) {

    let selected = element.dataset.selected ? element.dataset.selected : "";

    new Choices(element, {
        loadingText: lang.loading,
        itemSelectText: 'Press to select',
        noResultsText: lang.nothing_found,
        shouldSort: false,
        classNames: {
            containerOuter: 'choices ingredient-choices',
        },
    }).setChoices(function () {
        return fetch(jsObject.ingredients_get)
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    return data;
                });
    }).then(function (choice) {
        
        let placeholder = element.closest('.step-ingredient').querySelector('.choices__placeholder');
        placeholder.innerHTML = lang.ingredient;
        
        choice.setChoiceByValue(selected);
    });


}
