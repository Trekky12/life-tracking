'use strict';

const splitbillsForm = document.getElementById('splitbillsBillsForm');
const buttons = document.querySelectorAll('.splitbill_btn');

if (splitbillsForm) {

    let inputs_paid = splitbillsForm.querySelectorAll('input.balance_paid');
    let inputs_spend = splitbillsForm.querySelectorAll('input.balance_spend');
    let input_total = splitbillsForm.querySelector('#inputValue');
    
    let remaining_paid = splitbillsForm.querySelector('#remaining_paid');
    let remaining_spend = splitbillsForm.querySelector('#remaining_spend');

    input_total.addEventListener('change', function (e) {
        inputs_paid.forEach(function (input) {
            input.value = 0;
        });
        inputs_spend.forEach(function (input) {
            input.value = 0;
        });
        calculateRemaining(inputs_paid, remaining_paid);
        calculateRemaining(inputs_spend, remaining_spend);
    });

    buttons.forEach(function (item, idx) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            let type = item.dataset.type;
            let id = item.dataset.id;

            let totalValue = parseFloat(input_total.value);

            if (totalValue > 0) {

                switch (type) {
                    case 'paid_same':
                        equal_splitting(inputs_paid, totalValue);
                        break;
                    case 'paid_person':
                        inputs_paid.forEach(function (input) {
                            input.value = 0;
                        });
                        splitbillsForm.querySelector('input[name="balance[' + id + '][paid]"]').value = totalValue;
                        break;
                    case 'spend_same':
                        equal_splitting(inputs_spend, totalValue);
                        break;
                    case 'spend_person':
                        inputs_spend.forEach(function (input) {
                            input.value = 0;
                        });
                        splitbillsForm.querySelector('input[name="balance[' + id + '][spend]"]').value = totalValue;
                        break;
                }
                calculateRemaining(inputs_paid, remaining_paid);
                calculateRemaining(inputs_spend, remaining_spend);
            }
        });
    });

    splitbillsForm.addEventListener('submit', function (e) {
        let sum_paid = getSum(inputs_paid);
        let sum_spend = getSum(inputs_spend);
        let totalValue = getTotal();

        if (totalValue !== sum_paid || totalValue !== sum_spend) {
            e.preventDefault();
            console.log(totalValue);
            console.log(sum_paid);
            console.log(sum_spend);
            alert(lang.splitbills_numbers_wrong);
        }
    });

    function getSum(inputs) {
        let sum = 0.0;
        inputs.forEach(function (input) {
            let value = Number.parseFloat(input.value);
            if (value) {
                sum += value;
            }
        });
        return sum.toFixed(2);
    }
    
    function getTotal(){
        return parseFloat(input_total.value).toFixed(2);
    }

    // calculate remaining
    inputs_paid.forEach(function (input) {
        input.addEventListener('input', function (e) {
            calculateRemaining(inputs_paid, remaining_paid);
        });
    });
    inputs_spend.forEach(function (input) {
        input.addEventListener('input', function (e) {
            calculateRemaining(inputs_spend, remaining_spend);
        });
    });
    
    function calculateRemaining(inputs, remaining){
        let sum = getSum(inputs);
        let totalValue = getTotal();
        remaining.innerHTML = (totalValue - sum).toFixed(2);
    }
}

function equal_splitting(nodeList, total) {
    // there are only 2 decimal places possible so
    // if there are more than 2 decimal places
    // somebody needs to pay "more"
    // so create an array from the NodeList 
    // and shuffle the array 
    // therefore the person who needs to pay more
    // is not always the last one
    let sorted = [].slice.call(nodeList).sort(function () {
        return 0.5 - Math.random();
    });

    let remaining = sorted.length;
    sorted.forEach(function (input) {
        let value = (total / remaining).toFixed(2);
        input.value = value;
        remaining--;
        total -= value;
    });
}
