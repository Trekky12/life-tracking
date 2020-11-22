'use strict';

document.addEventListener('click', function (event) {
    let add_set = event.target.closest('.add_set');
    let remove_set = event.target.closest('.remove_set');

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
});

const sessionExercises = document.querySelector('#sessionExercises');

document.addEventListener('click', function (event) {
    let minus = event.target.closest('.minus');

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
            exercises_day.forEach(function(exercise){
                exercise.remove()
            });
        } else {
            element.remove();
        }
    }
});

const addExerciseBtn = document.querySelector('#addExercise');
const addExerciseSelect = document.querySelector('#addExerciseToSession');
const addExerciseSetNr = document.querySelector('#setCount');
addExerciseBtn.addEventListener('click', function (event) {
    let exercise = addExerciseSelect.value;
    let sets = addExerciseSetNr.value;
    let exercise_idx = sessionExercises.childElementCount;

    return fetch(jsObject.workouts_exercises_data + '?exercise=' + exercise + '&sets=' + sets + '&count=' + exercise_idx, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if (data.status !== 'error') {
            sessionExercises.insertAdjacentHTML('beforeend', data["data"]);
        }
    }).catch(function (error) {
        console.log(error);
    });

});
