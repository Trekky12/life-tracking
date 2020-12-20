'use strict';

const exercisesSelected = document.querySelector('#workoutExerciseSelection .content');

document.addEventListener('click', function (event) {
    let plus = event.target.closest('.exercise .plus');
    let minus = event.target.closest('.minus');

    let headline = event.target.closest('.exercise .headline');

    let exercise = event.target.closest('.exercise');

    let add_set = event.target.closest('.add_set');
    let remove_set = event.target.closest('.remove_set');

    let add_day = event.target.closest('#add_workout_day');

    let add_superset = event.target.closest('#add_superset');


    let workoutElements = exercisesSelected.querySelectorAll('[data-type="workout-element"]');
    //let id = exercisesSelected.childElementCount;
    let nextID = workoutElements.length;

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
        loadSelectedMuscles();
    }

    if (plus) {
        event.preventDefault();
        //console.log(exercisesSelected.childElementCount);

        let new_exercise = exercise.cloneNode(true);
        new_exercise.classList.add("selected");

        new_exercise.querySelector('.plus').classList.add('hidden');
        new_exercise.querySelector('.minus').classList.remove('hidden');
        new_exercise.querySelector('.handle').classList.remove('hidden');

        new_exercise.querySelector('.sets').classList.remove('hidden');

        let input_id = document.createElement("input");
        input_id.type = 'hidden';
        input_id.name = 'exercises[' + nextID + '][id]';
        input_id.value = exercise.dataset.id;

        new_exercise.appendChild(input_id);

        let inputs = new_exercise.querySelectorAll('input');
        inputs.forEach(function (input, idx) {
            if (!input.name.includes("dummy")) {
                input.setAttribute('name', input.name.replace(/exercises\[[^\]]*\]/, 'exercises[' + nextID + ']'));
                input.removeAttribute('disabled');
            }
        });

        exercisesSelected.appendChild(new_exercise);
        loadSelectedMuscles();
    }

    if (headline) {
        event.preventDefault();
        event.target.parentElement.classList.toggle('active');
    }

    if (add_set) {
        console.log("add set");
        let setsList = exercise.querySelector('.sets .set-list');
        let set_dummy = exercise.querySelector('.sets .set-dummy');
        let sets = exercise.querySelectorAll('.sets .set');

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
        let sets = exercise.querySelectorAll('.sets .set:not(.set-dummy)');
        if (sets.length > 0) {
            let last_set = sets[sets.length - 1];
            last_set.remove();
        }
    }

    if (add_day) {
        event.preventDefault();

        let workout_day = document.createElement("div");
        workout_day.classList.add("workout_day_split");
        workout_day.dataset.type = "workout-element";
        workout_day.dataset.category = "day";

        let workout_day_content = document.createElement("div");
        workout_day_content.classList.add("content");

        let input_type = document.createElement("input");
        input_type.type = 'hidden';
        input_type.name = 'exercises[' + nextID + '][type]';
        input_type.value = 'day';

        let input_notice = document.createElement("input");
        input_notice.type = 'text';
        input_notice.name = 'exercises[' + nextID + '][notice]';
        input_notice.required = true;

        let div_icons = document.createElement("div");
        div_icons.classList.add("icons");

        let i_minus = document.createElement("i");
        i_minus.classList.add("minus");
        i_minus.classList.add("fas");
        i_minus.classList.add("fa-minus");

        let i_handle = document.createElement("i");
        i_handle.classList.add("handle");
        i_handle.classList.add("fas");
        i_handle.classList.add("fa-arrows-alt");

        div_icons.appendChild(i_minus);
        div_icons.appendChild(i_handle);

        workout_day_content.appendChild(input_type);
        workout_day_content.appendChild(input_notice);

        workout_day.appendChild(workout_day_content);
        workout_day.appendChild(div_icons);

        exercisesSelected.appendChild(workout_day);
    }

    if (add_superset) {
        event.preventDefault();

        let workout_superset = document.createElement("div");
        workout_superset.classList.add("workout_superset");
        workout_superset.dataset.type = "workout-element";

        let workout_superset_content = document.createElement("div");
        workout_superset_content.classList.add("content");

        let headline_superset = document.createElement("h2");
        headline_superset.innerHTML = lang.workouts_superset;

        let input_type = document.createElement("input");
        input_type.type = 'hidden';
        input_type.name = 'exercises[' + nextID + '][type]';
        input_type.value = 'superset';

        let div_exercises = document.createElement("div");
        div_exercises.classList.add("exercises");
        div_exercises.dataset.type = "superset";

        let div_icons = document.createElement("div");
        div_icons.classList.add("icons");

        let i_minus = document.createElement("i");
        i_minus.classList.add("minus");
        i_minus.classList.add("fas");
        i_minus.classList.add("fa-minus");

        let i_handle = document.createElement("i");
        i_handle.classList.add("handle");
        i_handle.classList.add("fas");
        i_handle.classList.add("fa-arrows-alt");

        div_icons.appendChild(i_minus);
        div_icons.appendChild(i_handle);

        workout_superset_content.appendChild(headline_superset);
        workout_superset_content.appendChild(input_type);
        workout_superset_content.appendChild(div_exercises);

        workout_superset.appendChild(workout_superset_content);
        workout_superset.appendChild(div_icons);

        exercisesSelected.appendChild(workout_superset);

        createSortable(div_exercises);

    }
});

const workoutSupersets = document.querySelectorAll('.workout_superset .exercises');
workoutSupersets.forEach(function (item, idx) {
    createSortable(item);
});

createSortable(exercisesSelected);

function createSortable(element) {
    new Sortable(element, {
        group: {
            name: "exercise"
        },
        swapThreshold: 0.5,
        fallbackOnBody: true,
        //draggable: ".exercise.selected",
        handle: ".handle",
        dataIdAttr: 'data-id',
        onUpdate: function (evt) {
            updateFields();
        },
        onAdd: function (evt) {
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
    // change input field array key
    // @see https://stackoverflow.com/a/47948276
    let workoutElements = exercisesSelected.querySelectorAll('[data-type="workout-element"]');
    workoutElements.forEach(function (item, idx) {
        let fields = item.querySelectorAll('input');
        fields.forEach(function (field) {
            field.setAttribute('name', field.name.replace(/exercises\[[^\]]*\]/, 'exercises[' + idx + ']'));
        });
    });
}


const usedMusclesWrapper = document.querySelector('#usedMusclesWrapper');

function loadSelectedMuscles() {

    if (usedMusclesWrapper) {
        let exercise_ids = [];
        let exercises = exercisesSelected.querySelectorAll('.exercise');
        exercises.forEach(function (item, idx) {
            exercise_ids.push(item.dataset.id);
        });

        let data = {'exercises': exercise_ids};

        getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(jsObject.workouts_exercises_selected_muscles, {
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
            if (data.status !== 'error') {
                usedMusclesWrapper.innerHTML = data['data'];
            }
        }).catch(function (error) {
            console.log(error);
        });
    }
}