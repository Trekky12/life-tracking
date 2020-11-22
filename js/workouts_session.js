'use strict';

document.addEventListener('click', function (event) {
    let add_set = event.target.closest('.add_set');
    let remove_set = event.target.closest('.remove_set');

    let minus = event.target.closest('.minus');

    if (add_set) {
        let exercise = event.target.closest('.exercise');
        let setsList = exercise.querySelector('.set-list');
        let set_dummy = exercise.querySelector('.set-dummy');
        let sets = exercise.querySelectorAll('.set');

        let set_id = sets.length;

        let new_set = set_dummy.cloneNode(true);
        new_set.classList.remove("hidden");
        new_set.classList.remove("set-dummy");

        let set_nr = new_set.querySelector('.set-nr');
        set_nr.innerHTML = set_id;

        let inputs = new_set.querySelectorAll('input');
        inputs.forEach(function (input, idx) {
            input.setAttribute('name', input.name.replace("dummy", set_id - 1));
            input.removeAttribute('disabled');
        });

        setsList.appendChild(new_set);
    }

    if (remove_set) {
        let exercise = event.target.closest('.exercise');
        let sets = exercise.querySelectorAll('.set:not(.set-dummy)');
        if (sets.length > 0) {
            let last_set = sets[sets.length - 1];
            last_set.remove();
        }
    }

    if (minus) {
        event.preventDefault();
        let element = minus.parentElement.parentElement;
        let cat = element.dataset.category;

        if (cat === "day") {
            // get all "childs"
            let exercises_day = [];
            exercises_day.push(element);
            element = element.nextElementSibling;
            while (element) {
                if (element.dataset.category === "day")
                    break;
                exercises_day.push(element);
                element = element.nextElementSibling;
            }
            exercises_day.forEach(function (exercise) {
                exercise.remove()
            });
        } else {
            element.remove();
        }
    }
});


const exercisesSession = document.querySelector('#sessionExercises');
const addExerciseBtn = document.querySelector('#addExercise');
const addExerciseSelect = document.querySelector('#addExerciseToSession');
const addExerciseSetNr = document.querySelector('#setCount');
addExerciseBtn.addEventListener('click', function (event) {
    let exercise = addExerciseSelect.value;
    let sets = addExerciseSetNr.value;

    let workoutElements = exercisesSession.querySelectorAll('[data-type="workout-element"]');
    let nextID = workoutElements.length;

    return fetch(jsObject.workouts_exercises_data + '?exercise=' + exercise + '&sets=' + sets + '&count=' + nextID, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if (data.status !== 'error') {
            exercisesSession.insertAdjacentHTML('beforeend', data["data"]);
        }
    }).catch(function (error) {
        console.log(error);
    });

});

createSortable(exercisesSession);

const workoutSupersets = document.querySelectorAll('.workout_superset_child');
workoutSupersets.forEach(function (item, idx) {
    createSortable(item);
});

function createSortable(element) {
    new Sortable(element, {
        group: {
            name: "exercise"
        },
        swapThreshold: 0.5,
        fallbackOnBody: true,
        handle: ".handle",
        dataIdAttr: 'data-id',
        onUpdate: function (evt) {
            console.log("update");
            updateFields();
        },
        onAdd: function (evt) {
            console.log("add");
            let targetType = evt.to.dataset.type;
            let exercise = evt.item;
            let input = exercise.querySelector('input[name*="is_child"]');
            if (targetType === "main") {
                input.value = 0;
            } else {
                input.value = 1;
            }
            updateFields();
        }
    });
}

function updateFields() {
    let workoutElements = exercisesSession.querySelectorAll('[data-type="workout-element"]');
    console.log(workoutElements);
    workoutElements.forEach(function (item, idx) {
        let fields = item.querySelectorAll('input');
        fields.forEach(function (field) {
            field.setAttribute('name', field.name.replace(/exercises\[[^\]]*\]/, 'exercises[' + idx + ']'));
        });
    });
}
