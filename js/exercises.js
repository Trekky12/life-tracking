'use strict';

const exercisesAvailable = document.querySelector('#exercises_available');
const exercisesList = document.querySelector('#exercises_available_list');
const loadingIconExercises = document.querySelector('#loadingIconExercises');
const loadMoreExercises = document.querySelector('#loadMoreExercises');
const filterBodyparts = document.getElementById('filterBodyParts');

const isEdit = exercisesAvailable.dataset.edit === "1";

document.addEventListener("DOMContentLoaded", function () {
    loadMoreExercisesFunctions();
    getExercises();
});

function getExercises() {
    if (exercisesList !== null) {

        let start = exercisesList.querySelectorAll('.exercise').length;
        let count = 10;
        let bodypart = filterBodyparts.value;

        loadingIconExercises.classList.remove("hidden");
        loadMoreExercises.classList.add("hidden");

        let url = jsObject.workouts_exercises_get + '?count=' + count + '&start=' + start + '&bodypart=' + bodypart;

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
            if (data.status !== 'error') {

                loadingIconExercises.classList.add("hidden");

                let totalCount = parseInt(data.count);
                if (start + count < totalCount) {
                    loadMoreExercises.classList.remove("hidden");
                }
                exercisesList.insertAdjacentHTML('beforeend', data["data"]);
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