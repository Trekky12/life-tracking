'use strict';

const exercisesSelected = document.getElementById('exercises_selected');

document.addEventListener('click', function (event) {
    let closest = event.target.closest('.exercise');
    if (closest) {
        event.preventDefault();
        var exercise_id = closest.dataset.id;

        if (closest.classList.contains("remove")) {
            closest.remove();
        } else {

            //console.log(exercisesSelected.childElementCount);

            let exercise = closest.cloneNode(true);
            exercise.classList.add("remove");

            let input_id = document.createElement("input");
            input_id.type = 'hidden';
            input_id.name = 'exercises[' + exercise_id + '][id]';
            input_id.value = exercise_id;

            exercise.appendChild(input_id);

            let input_pos = document.createElement("input");
            input_pos.type = 'hidden';
            input_pos.name = 'exercises[' + exercise_id + '][position]';
            input_pos.value = exercisesSelected.childElementCount;

            exercise.appendChild(input_pos);

            exercisesSelected.appendChild(exercise);
        }
    }
});


new Sortable(document.getElementById('exercises_selected'), {
    group: {
        name: "exercise"
    },
    draggable: ".exercise.remove",
    //handle: "h3",
    dataIdAttr: 'data-id',
    onUpdate: function (evt) {
        var data = {'widgets': this.toArray()};

        /*getCSRFToken().then(function (token) {
         data['csrf_name'] = token.csrf_name;
         data['csrf_value'] = token.csrf_value;
         
         return fetch(jsObject.frontpage_widget_position, {
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
         
         }).catch(function (error) {
         console.log(error);
         });
         * 
         */
    }
});

