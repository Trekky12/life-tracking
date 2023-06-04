'use strict';

const splitbillsForm = document.getElementById('splitbillsBillsForm');
const splittbillsButtons = document.querySelectorAll('.splitbill_btn');
const calcButton = document.getElementById('calculateExchangeRate');

if (splitbillsForm) {

    let inputs_paid = splitbillsForm.querySelectorAll('input.balance_paid');
    let inputs_spend = splitbillsForm.querySelectorAll('input.balance_spend');
    let input_total = splitbillsForm.querySelector('#inputValue');

    let remaining_paid = splitbillsForm.querySelector('#remaining_paid');
    let remaining_spend = splitbillsForm.querySelector('#remaining_spend');

    let inputs_paid_foreign = splitbillsForm.querySelectorAll('input.balance_paid_foreign');
    let inputs_spend_foreign = splitbillsForm.querySelectorAll('input.balance_spend_foreign');
    let input_total_foreign = splitbillsForm.querySelector('#inputValueForeign');

    let exchange_rate = splitbillsForm.querySelector('#inputRate');
    let exchange_fee = splitbillsForm.querySelector('#inputFee');

    input_total.addEventListener('input', function (e) {
        inputs_paid.forEach(function (input) {
            input.value = 0;
        });
        inputs_spend.forEach(function (input) {
            input.value = 0;
        });
        calculateRemaining(inputs_paid, remaining_paid);
        calculateRemaining(inputs_spend, remaining_spend);

        splittbillsButtons.forEach(function (btn) {
            btn.classList.add("button-outlined");
        });
    });

    splittbillsButtons.forEach(function (item, idx) {
        item.addEventListener('click', function (e) {
            e.preventDefault();

            let category = item.dataset.category;
            let type = item.dataset.type;
            let id = item.dataset.id;

            let totalValue = parseFloat(input_total.value);
            let totalValueForeign = input_total_foreign ? parseFloat(input_total_foreign.value) : totalValue;

            if (totalValue > 0) {

                let userlist = item.parentNode.nextElementSibling;
                userlist.classList.add("hidden");

                splittbillsButtons.forEach(function (btn) {
                    if (btn.dataset.category == category) {
                        btn.classList.add("button-outlined");
                    }
                });
                item.classList.remove("button-outlined");


                if (category == "paid") {
                    splitbillsForm.querySelector('input[name="paid_by"]').value = (type == "person") ? id : type;
                }
                if (category == "spend") {
                    splitbillsForm.querySelector('input[name="spend_by"]').value = (type == "person") ? id : type;
                }

                if (type == "same") {
                    if (category == "paid") {
                        equal_splitting(inputs_paid, totalValue);

                        inputs_paid.forEach(function (input, idx) {
                            if (inputs_paid_foreign[idx]) {
                                inputs_paid_foreign[idx].value = (input.value * getExchangeRateWithFee()).toFixed(2);
                            }
                        });
                    } else if (category == "spend") {
                        equal_splitting(inputs_spend, totalValue);

                        inputs_spend.forEach(function (input, idx) {
                            if (inputs_spend_foreign[idx]) {
                                inputs_spend_foreign[idx].value = (input.value * getExchangeRateWithFee()).toFixed(2);
                            }
                        });
                    }

                } else if (type == 'person') {
                    if (category == "paid") {
                        inputs_paid.forEach(function (input) {
                            input.value = 0;
                        });
                        let input_user_paid = splitbillsForm.querySelector('input[name="balance[' + id + '][paid]"]');
                        input_user_paid.value = totalValue;

                        inputs_paid_foreign.forEach(function (input) {
                            input.value = 0;
                        });
                        let input_user_paid_foreign = splitbillsForm.querySelector('input[name="balance[' + id + '][paid_foreign]"]');
                        if (input_user_paid_foreign) {
                            input_user_paid_foreign.value = totalValueForeign;
                        }
                    } else if (category == "spend") {
                        inputs_spend.forEach(function (input) {
                            input.value = 0;
                        });
                        let input_user_spend = splitbillsForm.querySelector('input[name="balance[' + id + '][spend]"]');
                        input_user_spend.value = totalValue;


                        inputs_spend_foreign.forEach(function (input) {
                            input.value = 0;
                        });
                        let input_user_spend_foreign = splitbillsForm.querySelector('input[name="balance[' + id + '][spend_foreign]"]');
                        if (input_user_spend_foreign) {
                            input_user_spend_foreign.value = totalValueForeign;
                        }
                    }
                } else if (type == "individual") {
                    userlist.classList.remove("hidden");
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
            document.getElementById('loading-overlay').classList.add('hidden');
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

    function getTotal() {
        if (input_total.value == "") {
            return 0;
        }
        return parseFloat(input_total.value).toFixed(2);
    }

    // calculate remaining
    inputs_paid.forEach(function (input, idx) {
        input.addEventListener('input', function (e) {
            calculateRemaining(inputs_paid, remaining_paid);
        });
    });
    inputs_spend.forEach(function (input, idx) {
        input.addEventListener('input', function (e) {
            calculateRemaining(inputs_spend, remaining_spend);
        });
    });

    function calculateRemaining(inputs, remaining) {
        let sum = getSum(inputs);
        let totalValue = getTotal();
        remaining.innerHTML = (totalValue - sum).toFixed(2);
    }


    /**
     * calculate foreign to local currency
     */
    if (exchange_rate) {
        exchange_rate.addEventListener('input', function (e) {
            calculateValueInLocalCurrency();
        });
    }

    if (exchange_fee) {
        exchange_fee.addEventListener('input', function (e) {
            calculateValueInLocalCurrency();
        });
    }

    // convert values
    if (input_total_foreign) {
        input_total_foreign.addEventListener('input', function (e) {
            input_total.value = getValueInLocalCurrency();
            calculateRemaining(inputs_paid, remaining_paid);
            calculateRemaining(inputs_spend, remaining_spend);
        });
        inputs_paid_foreign.forEach(function (input, idx) {
            input.addEventListener('input', function (e) {
                inputs_paid[idx].value = (input.value / getExchangeRateWithFee()).toFixed(2);
                calculateRemaining(inputs_paid, remaining_paid);
                calculateRemaining(inputs_spend, remaining_spend);
            });
        });
        inputs_spend_foreign.forEach(function (input, idx) {
            input.addEventListener('input', function (e) {
                inputs_spend[idx].value = (input.value / getExchangeRateWithFee()).toFixed(2);
                calculateRemaining(inputs_paid, remaining_paid);
                calculateRemaining(inputs_spend, remaining_spend);
            });
        });
    }

    function getValueInLocalCurrency() {
        let value = (input_total_foreign.value / exchange_rate.value);
        value = value + value * (parseFloat(exchange_fee.value) / 100);
        return value.toFixed(2)
    }

    function getExchangeRateWithFee() {
        return parseFloat(input_total_foreign.value) / parseFloat(input_total.value);
    }

    function calculateValueInLocalCurrency() {
        input_total.value = getValueInLocalCurrency();

        inputs_paid_foreign.forEach(function (input, idx) {
            input.value = 0;
        });
        inputs_spend_foreign.forEach(function (input, idx) {
            input.value = 0;
        });
        inputs_paid.forEach(function (input, idx) {
            input.value = 0;
        });
        inputs_spend.forEach(function (input, idx) {
            input.value = 0;
        });
        calculateRemaining(inputs_paid, remaining_paid);
        calculateRemaining(inputs_spend, remaining_spend);
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
