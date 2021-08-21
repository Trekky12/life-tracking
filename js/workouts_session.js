'use strict';

const exercisesSession = document.querySelector('#workoutExercises .content');
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

const selectWorkoutDay = document.querySelector("#workoutsDaySelect");
if (selectWorkoutDay) {
    selectWorkoutDay.addEventListener('change', function (event) {
        let id = selectWorkoutDay.value;

        let start = false;
        let workoutElements = exercisesSession.querySelectorAll('[data-type="workout-element"]');
        workoutElements.forEach(function (exercise, idx) {

            if (start) {
                setFields(exercise, false);
            } else {
                setFields(exercise, true);
            }

            if (exercise.dataset.category == "day") {
                if (exercise.dataset.id == id) {
                    start = true;
                    setFields(exercise, false);
                } else {
                    start = false;
                    setFields(exercise, true);
                }
            }
        });

    });
}

function setFields(exercise, disabled) {

    if (disabled) {
        exercise.classList.add("hidden");
    } else {
        exercise.classList.remove("hidden");
    }

    let inputs = exercise.querySelectorAll('input, textarea');
    inputs.forEach(function (input, idx) {
        if (disabled) {
            input.setAttribute('disabled', true);
        } else {
            if (input.name.indexOf("dummy") === -1) {
                input.removeAttribute('disabled');
            }
        }
    });
}