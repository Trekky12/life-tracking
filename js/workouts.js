'use strict';

const exercisesSelected = document.querySelector('#exercises_selected .content');

document.addEventListener('click', function (event) {
    let plus = event.target.closest('.exercise .plus');
    let minus = event.target.closest('.exercise .minus');

    let headline = event.target.closest('.exercise .headline');

    let exercise = event.target.closest('.exercise');

    let add_set = event.target.closest('.add_set');
    let remove_set = event.target.closest('.remove_set');

    if (minus) {
        event.preventDefault();
        exercise.remove();
    }

    if (plus) {
        event.preventDefault();
        //console.log(exercisesSelected.childElementCount);

        let id = exercisesSelected.childElementCount;

        let new_exercise = exercise.cloneNode(true);
        new_exercise.classList.add("selected");

        new_exercise.querySelector('.plus').classList.add('hidden');
        new_exercise.querySelector('.minus').classList.remove('hidden');
        new_exercise.querySelector('.handle').classList.remove('hidden');

        new_exercise.querySelector('.sets').classList.remove('hidden');

        let input_id = document.createElement("input");
        input_id.type = 'hidden';
        input_id.name = 'exercises[' + id + '][id]';
        input_id.value = exercise.dataset.id;

        new_exercise.appendChild(input_id);

        let inputs = new_exercise.querySelectorAll('.sets input');
        inputs.forEach(function (input, idx) {
            if(!input.name.includes("dummy")){
                input.setAttribute('name', input.name.replace(/exercises\[[^\]]*\]/, 'exercises[' + id + ']'));
                input.removeAttribute('disabled');
            }
        });

        exercisesSelected.appendChild(new_exercise);
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
});


new Sortable(exercisesSelected, {
    group: {
        name: "exercise"
    },
    draggable: ".exercise.selected",
    handle: ".handle",
    dataIdAttr: 'data-id',
    onUpdate: function (evt) {
        var data = {'widgets': this.toArray()};

        // change input field array key
        // @see https://stackoverflow.com/a/47948276

        let exercises = exercisesSelected.querySelectorAll('.exercise');
        exercises.forEach(function (item, idx) {
            let fields = item.querySelectorAll('input');
            fields.forEach(function (field) {
                field.setAttribute('name', field.name.replace(/exercises\[[^\]]*\]/, 'exercises[' + idx + ']'));
            });
        });
    }
});