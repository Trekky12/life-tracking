'use strict';

/**
 * @see https://github.com/TarekRaafat/autoComplete.js/issues/91
 * @see https://codepen.io/everytimeicob/pen/NWRjajL
 */

function generateOption(selection) {
    const option = document.createElement('option');
    option.value = selection.id;
    option.selected = true;

    return option;
}

function generateChip(selection) {
    const chip = document.createElement('div');
    chip.classList.add('chip');
    chip.classList.add(`chip-${selection.id}`);
    chip.innerText = selection.login;
    chip.dataset.id = selection.id;

    return chip;
}

function generateIcon(selection) {
    const span = document.createElement('span');
    span.classList.add('remove');
    span.innerHTML = '&times;';

    return span;
}

const users = document.getElementById('users');
const input = document.getElementById('users_query');
const chips = document.getElementById('users_chips');

new autoComplete({
    data: {
        src: async () => {

            let module = users.dataset.module;

            var colOfSelectedOpt = users.selectedOptions;
            var values = [];
            for (var i = 0; i < colOfSelectedOpt.length; i++) {
                values.push(colOfSelectedOpt[i].value);
            }

            return fetch(jsObject.usersearch + '?query=' + input.value + '&module=' + module + '&users[]=' + values.join('&users[]='), {
                method: 'GET',
                credentials: "same-origin"
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data.status !== 'error') {
                    return data.data;
                }
                return [];
            }).catch(_ => []);
        },
        keys: ['login'],
        cache: false,
    },
    resultsList: {
        class: 'users-collection',
        id: 'users-collection-list',
        destination: "#users_collection-wrapper",
        position: "beforeend",
        tag: "ul",
        noResults: true,
        maxResults: 10,
    },
    resultItem: {
        highlight: true
    },
    trigger: query => query.length > 0,
    threshold: 1,
    debounce: 500,
    placeHolder: lang.searching,
    selector: "#users_query",
    wrapper: false,
});

document.querySelector("#users_query").addEventListener("selection", function (event) {
    const feedback = event.detail;
    const chip = generateChip(feedback.selection.value);

    chip.appendChild(generateIcon(feedback.selection.value));

    chips.appendChild(chip);

    users.add(generateOption(feedback.selection.value));

    input.value = '';
});

document.querySelector("#users_query").addEventListener("results", function (event) {
    if (event.detail.results.length == 0) {
        document.querySelector('.users-collection').classList.remove('hidden');
        document.querySelector('#users-collection-list').classList.add('hidden');
    } else {
        document.querySelector('.users-collection').classList.add('hidden');
        document.querySelector('#users-collection-list').classList.remove('hidden');
    }
});

document.querySelector("#users_query").addEventListener("close", function (event) {
    document.querySelector('.users-collection').classList.add('hidden');
});


chips.addEventListener('click', function (event) {
    if (event.target.tagName === 'SPAN' && event.target.classList.contains('remove')) {
        let chip = event.target.closest('.chip');
        let user_id = chip.dataset.id;

        Array.from(users.options).forEach(option => {
            if (option.value === user_id) {
                option.remove();
                chip.remove();
            }
        });
    }
});