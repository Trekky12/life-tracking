'use strict';

const exercisesSession = document.querySelector('#workoutExercises .workout-selection-content');
const workoutElements = exercisesSession.querySelectorAll('[data-type="workout-element"]');

const selectWorkoutDay = document.querySelector("#workoutsDaySelect");
const addExerciseBtn = document.querySelector('#addExercise');

let exercises = Array.from(workoutElements);

addExerciseBtn.addEventListener('click', function (event) {
    let exercise = document.querySelector('#addExerciseToSession select').value;
    let sets = document.querySelector('#setCount').value;
    let repeat = document.querySelector('#setRepeat').value;
    let weight = document.querySelector('#setWeight').value;
    let view = exercisesSession.dataset.view;

    let nextID = exercisesSession.querySelectorAll('[data-type="workout-element"]').length;

    return fetch(jsObject.workouts_exercises_data + '?exercise=' + exercise + '&sets=' + sets + '&repeat=' + repeat + '&weight=' + weight + '&count=' + nextID + '&view=' + view, {
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

            // select the last inserted element and add to exercises list
            let newEl = exercisesSession.lastElementChild.querySelector('[data-type="workout-element"]');
            exercises.push(newEl);

            showToast(lang.workouts_exercise_added, "blue");
        }
    }).catch(function (error) {
        console.log(error);
    });

});


if (selectWorkoutDay) {
    selectWorkoutDay.addEventListener('change', async function (event) {
        let id = selectWorkoutDay.value;

        let start = false;
        exercises = [];
        workoutElements.forEach(function (exercise, idx) {

            if (start) {
                setFields(exercise, false);
                exercises.push(exercise);
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

        if (exercisesSession.dataset.view == "create") {
            selectWorkoutDay.setAttribute("disabled", true);

            setNextUnfinishedWorkoutElementActive();

            let day = exercisesSession.querySelector('.workout_day_split:not(.hidden)');
            await saveExercise(day);
        }
    });
} else {
    if (exercisesSession.dataset.view == "create") {
        setNextUnfinishedWorkoutElementActive();
    }
}

function setFields(exercise, disabled) {

    if (disabled) {
        exercise.classList.add("hidden");
    } else {
        exercise.classList.remove("hidden");
    }

    let inputs = exercise.querySelectorAll('input, textarea, select');
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


function setNextUnfinishedWorkoutElementActive(currentIdx = null) {
    let next;
    if (currentIdx !== null) {
        next = exercises.find(el =>
            Number(el.dataset.idx) > currentIdx && el.dataset.finished == "0"
        );
    }

    if (!next) {
        next = exercises.find(el => el.dataset.finished == "0");
    }

    if (next) {
        next.querySelectorAll('.view-exercise').forEach(function (view) {
            view.classList.add("active");
        });
        next.scrollIntoView({ block: "start", smooth: "smooth" });
    }

}

document.addEventListener('click', async function (event) {
    let saveExerciseBtn = event.target.closest('button.save_exercise');

    let skipExerciseBtn = event.target.closest('button.skip_exercise');

    let exerciseHeader = event.target.closest('[data-type="workout-element"][data-finished="0"]');


    if (saveExerciseBtn) {
        let exercise = saveExerciseBtn.closest('[data-type="workout-element"]');
        let currentIdx = Number(exercise.dataset.idx);

        await saveExercise(exercise);

        exercise.dataset.finished = 1;
        exercise.querySelectorAll('.view-exercise').forEach(function (view) {
            view.classList.remove("active");
        });
        setNextUnfinishedWorkoutElementActive(currentIdx);
        return;
    }

    if (skipExerciseBtn) {
        let exercise = skipExerciseBtn.closest('[data-type="workout-element"]');
        let currentIdx = Number(exercise.dataset.idx);
        exercise.querySelectorAll('.view-exercise').forEach(function (view) {
            view.classList.remove("active");
        });
        setNextUnfinishedWorkoutElementActive(currentIdx);
        return;
    }

    if (exerciseHeader && exercisesSession.dataset.view == "create") {
        // hide others
        exercises.forEach(function (exercise) {
            exercise.querySelectorAll('.view-exercise').forEach(function (view) {
                view.classList.remove('active');
            });
        });
        // show this
        exerciseHeader.querySelectorAll('.view-exercise').forEach(function (view) {
            view.classList.add('active');
        });
        exerciseHeader.scrollIntoView({ block: "start", smooth: "smooth" });
        return;
    }
});


async function saveExercise(exercise) {
    let form = exercise.closest('form');
    let formData = new FormData(form);

    let token = await getCSRFToken()
    formData.append('csrf_name', token.csrf_name);
    formData.append('csrf_value', token.csrf_value);

    let response = await fetch(jsObject.workouts_sessions_exercise_save, {
        method: "POST",
        credentials: "same-origin",
        body: formData,
    });
    let result = await response.json();

    if (result.status !== "success") {
        console.error(`Unable to save exercise`);
        throw "Error saving exercise";
    }
    return result;
}