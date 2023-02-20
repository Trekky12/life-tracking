'use strict';

document.addEventListener('click', function (event) {
    console.log("click");
    let headline = event.target.closest('.exercise .headline');
    if (headline) {
        event.preventDefault();
        event.target.parentElement.classList.toggle('active');
    }
});