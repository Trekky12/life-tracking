'use strict';


const budgetSelect = document.querySelectorAll('select.category');
budgetSelect.forEach(function (item, idx) {
    get_recurring_costs(item);
    add_selectr(item);
});


var template = document.getElementById('budgetTemplate').innerHTML;
Mustache.parse(template);


document.getElementById('add_budget').addEventListener('click', function (e) {
    e.preventDefault();


    var index = document.querySelectorAll('.budget-entry').length;

    var rendered = Mustache.render(template, {index: index});

    let budget = document.querySelector('#budgetForm .budget-entry.remaining');
    budget.insertAdjacentHTML('beforebegin', rendered);

    add_selectr('#category_' + index);
});

document.addEventListener('click', function (event) {
    let closest = event.target.closest('.btn-delete');
    if (closest) {
        let url = closest.dataset.url;
        if (!url) {
            event.preventDefault();
            closest.parentNode.remove();
        }
    }
});


document.addEventListener('change', function (event) {
    let closest = event.target.closest('select.category');
    if (closest) {
        //category_costs
        get_recurring_costs(closest);

        // Set default description based on category
        let description = closest.closest('.budget-entry').querySelector('input.description');

        if (description.value.length === 0) {
            description.value = closest.options[closest.selectedIndex].text;
        }
    }
});


['change', 'keyup'].forEach(function (evt) {
    document.addEventListener(evt, function (event) {
        let closest = event.target.closest('input.value');
        if (closest) {
            let remaining = document.getElementById('remaining_budget');
            var income = parseFloat(remaining.dataset.income);
            var sum = 0;

            let values = document.querySelectorAll('input.value');
            values.forEach(function (item, idx) {
                if (item.value) {
                    sum += parseFloat(item.value);
                }
            });
            remaining.innerHTML = income - sum;
        }
    });
});


function add_selectr(element) {

    new Selectr(element, {
        searchable: false,
        placeholder: lang.categories
        /*renderOption: function (option) {
            var template = [
                "<div class='select-option-label'><span>",
                option.textContent,
                "</span></div>"
            ];
            return template.join('');
        }, renderSelection: function (option) {
            var template = ['<div class="select-label"><span>', option.textContent.trim(), '</span></div>'];
            return template.join('');
        }*/
    });
}

function get_recurring_costs(select) {

    var category_costs = select.closest('.budget-entry').querySelector('.category_costs');
    category_costs.innerHTML = '<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>';
    var colOfSelectedOpt = select.selectedOptions;
    var values = [];
    for (var i = 0; i < colOfSelectedOpt.length; i++) {
        values.push(colOfSelectedOpt[i].value);
    }

    fetch(jsObject.get_category_costs + '?category[]=' + values.join('&category[]='), {
        method: 'GET',
        credentials: "same-origin"
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        var sum = '';
        if (data['value'] > 0) {
            sum = data['value'] + ' ' + i18n.currency;
        } else {
            sum = '-';
        }
        category_costs.innerHTML = sum;
    }).catch(function (error) {
        console.log(error);
    });

}