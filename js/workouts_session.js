'use strict';

const exercisesSession = document.querySelector('#workoutExerciseSelection .content');
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