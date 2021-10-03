'use strict';


const budgetSelect = document.querySelectorAll('.budget-entry select.category');
budgetSelect.forEach(function (item, idx) {
    get_recurring_costs(item);
    add_selectr(item);
});


const budget_dummy = document.querySelector('#budgetTemplate .budget-entry-dummy');


document.getElementById('add_budget').addEventListener('click', function (e) {
    e.preventDefault();

    var index = document.querySelectorAll('.budget-entry').length;

    let new_budget_entry = budget_dummy.cloneNode(true);
    new_budget_entry.classList.remove("hidden");
    new_budget_entry.classList.remove("budget-entry-dummy");
    new_budget_entry.classList.add("budget-entry");
    new_budget_entry.dataset.idx = index;

    let inputs = new_budget_entry.querySelectorAll('input, textarea, select');
    inputs.forEach(function (input, idx) {
        input.setAttribute('name', input.name.replace("DUMMY", index));
        input.removeAttribute('disabled');
        
        input.setAttribute('id', input.id.replace("DUMMY", index));
    });
    
    let labels = new_budget_entry.querySelectorAll('label');
    labels.forEach(function (label, idx) {
        label.setAttribute('for', label.getAttribute('for').replace("DUMMY", index));
    });


    let budgetForm = document.querySelector('#budgetForm');
    let budget_remaining = budgetForm.querySelector('.budget-entry.remaining');
    budgetForm.insertBefore(new_budget_entry, budget_remaining);


    add_selectr('#category_' + index);
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
        placeholder: lang.categories,
        messages: {
            noResults: lang.nothing_found,
            noOptions: lang.no_options
        }
    });
}

function get_recurring_costs(select) {

    var category_costs = select.closest('.budget-entry').querySelector('.category_costs');
    category_costs.innerHTML = document.getElementById('loading-overlay').innerHTML;
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