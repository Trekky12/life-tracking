'use strict';

document.addEventListener('click', async function (event) {
    let check_entry = event.target.closest('.shopping-list-entry input[type="checkbox"]');

    if (check_entry) {
        let data = { 'state': check_entry.checked ? 1 : 0, 'dataset': check_entry.dataset.id }

        try {
            let url = check_entry.dataset.url;
            let token = await getCSRFToken();

            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            let response = await fetch(url, {
                method: 'POST',
                credentials: "same-origin",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            let result = await response.json();
            if (result['status'] !== 'success') {
                check_entry.checked = !check_entry.checked;
            }
        } catch (error) {
            console.log(error);
            check_entry.checked = !check_entry.checked;
            if (document.body.classList.contains('offline')) {
                let formData = new URLSearchParams(data).toString();
                saveDataWhenOffline(jsObject.crawler_dataset_save, 'POST', formData);
            }
        }
    }
});