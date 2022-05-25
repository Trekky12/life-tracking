'use strict';

var sliders = document.querySelectorAll('.slider');
sliders.forEach(function (item, idx) {
    noUiSlider.create(item, {
        start: item.dataset.level,
        step: 5,
        range: {
            'min': parseInt(item.dataset.min),
            'max': parseInt(item.dataset.max)
        }
    });
    item.noUiSlider.on('change', function (values, handle) {
        item.parentNode.querySelector('.slider-value').value = parseInt(this.get());
    });
});


let carServiceType = document.querySelectorAll('input.carServiceType');
carServiceType.forEach(function (item, idx) {
    item.addEventListener('change', function (event) {
        document.querySelector("#carServiceFuel").classList.toggle('hidden');
        document.querySelector("#carServiceService").classList.toggle('hidden');
    });
});