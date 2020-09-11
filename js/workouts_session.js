'use strict';

const addSets = document.querySelectorAll('.add_set');

addSets.forEach(function(set){
    set.addEventListener('click', function (event) {
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
    });
});

const removeSets = document.querySelectorAll('.remove_set');

removeSets.forEach(function(set){
    set.addEventListener('click', function (event) {
        let exercise = event.target.closest('.exercise');
        let sets = exercise.querySelectorAll('.set:not(.set-dummy)');
        if (sets.length > 0) {
            let last_set = sets[sets.length - 1];
            last_set.remove();
        }
    });
});