'use strict';

function add_confirm_transaction_function() {
    let financeTransactionIcon = document.querySelectorAll('span.confirm_transaction ');
    financeTransactionIcon.forEach(function (item, idx) {
        item.addEventListener('click', function (event) {

            var data = {'state': item.classList.contains("is_confirmed") ? 0 : 1, 'transaction': item.dataset.id};

            getCSRFToken().then(function (token) {
                data['csrf_name'] = token.csrf_name;
                data['csrf_value'] = token.csrf_value;

                return fetch(jsObject.finances_transaction_confirm, {
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
                allowedReload = true;
                window.location.reload();
            }).catch(function (error) {
                console.log(error);
                if (isOffline(error)) {
                    let formData = new URLSearchParams(data).toString();
                    saveDataWhenOffline(jsObject.finances_transaction_confirm, 'POST', formData);
                }
            });
        });
    });
}

add_confirm_transaction_function();
financeTransactionTable.on("update", function (data) {
    add_confirm_transaction_function();
});
