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
