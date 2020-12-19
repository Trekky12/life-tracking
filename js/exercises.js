'use strict';

const exercisesAvailable = document.querySelector('#exercises_available');
const exercisesList = document.querySelector('#exercises_available_list');
const loadingIconExercises = document.querySelector('#loadingIconExercises');
const loadMoreExercises = document.querySelector('#loadMoreExercises');
const filterBodyparts = document.getElementById('filterBodyParts');
const filterSearchExercises = document.getElementById('filterSearchExercises');

const isEdit = exercisesAvailable.dataset.edit === "1";

document.addEventListener("DOMContentLoaded", function () {
    loadMoreExercisesFunctions();
    getExercises();
});

function getExercises(reset = false) {
    if (exercisesList !== null) {

        let start = reset ? 0 : exercisesList.querySelectorAll('.exercise').length;
        let count = 10;
        let bodypart = filterBodyparts.value;
        let query = filterSearchExercises.value;

        loadingIconExercises.classList.remove("hidden");
        loadMoreExercises.classList.add("hidden");

        let url = jsObject.workouts_exercises_get + '?count=' + count + '&start=' + start + '&bodypart=' + bodypart + '&query=' + query;

        if (!isEdit) {
            url += '&full=1';
        }

        return fetch(url, {
            method: 'GET',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if(reset){
                exercisesList.innerHTML = '';
            }
            if (data.status !== 'error') {

                loadingIconExercises.classList.add("hidden");

                let totalCount = parseInt(data.count);
                if (totalCount > 0) {
                    if (start + count < totalCount) {
                        loadMoreExercises.classList.remove("hidden");
                    }
                    exercisesList.insertAdjacentHTML('beforeend', data["data"]);
                } else {
                    let nothing_found = document.createElement('p');
                    nothing_found.innerHTML = lang.nothing_found;
                    exercisesList.innerHTML = '';
                    
                    exercisesList.appendChild(nothing_found);
                }
            }
        }).catch(function (error) {
            console.log(error);
        });
    }
    return emptyPromise();
}

function loadMoreExercisesFunctions() {
    if (loadMoreExercises !== null) {
        loadMoreExercises.addEventListener('click', function (e) {
            getExercises();
        });

        let offset = 100;

        if (isEdit) {
            // Detect when scrolled to bottom.
            exercisesAvailable.addEventListener('scroll', function () {
                if ((exercisesAvailable.scrollTop > 0 && (exercisesAvailable.scrollTop + exercisesAvailable.clientHeight + offset >= exercisesAvailable.scrollHeight))) {
                    if (!loadMoreExercises.classList.contains('hidden')) {
                        getExercises();
                    }
                }
            });
        } else {
            document.addEventListener('scroll', function () {
                let body = document.body;
                let html = document.documentElement;

                if ((html.scrollTop > 0 && (html.scrollTop + html.clientHeight + offset >= html.scrollHeight)) || (body.scrollTop > 0 && (body.scrollTop + body.clientHeight + offset >= body.scrollHeight))) {
                    if (!loadMoreExercises.classList.contains('hidden')) {
                        getExercises();
                    }
                }
            });
        }
    }
}


filterBodyparts.addEventListener('change', function (event) {
    event.preventDefault();
    exercisesList.innerHTML = '';
    getExercises();
});

filterSearchExercises.addEventListener('keyup', function (event) {
    event.preventDefault();
    getExercises(true);
});