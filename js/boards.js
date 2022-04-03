'use strict';
// get initial tokens
/*getCSRFToken().then(function (token) {
 console.log('new tokens generated');
 });
 */
/**
 * Open card by GET parameter
 */
/*var res = window.location.href.match(/(?:\?card=([0-9]*))/);
if (res !== null && res.length > 1) {
    var card = res[1];
    loadAndOpenCard(card);
}*/


const loadingIconBoard = document.querySelector('#loadingIconBoard');
const stacksWrapper = document.querySelector('.stack-wrapper');
const new_stack_element = document.querySelector('#templates .stack-dummy');

let boardData = [];

document.addEventListener("DOMContentLoaded", async function () {
    loadingIconBoard.classList.remove("hidden");
    boardData = await loadBoard();
    renderBoard();
    loadingIconBoard.classList.add("hidden");
});

function renderBoard() {

    if (boardData.status !== 'error') {

        stacksWrapper.innerHTML = "";

        Object.values(boardData.stacks).forEach(function (stack_data) {

            let stack = createStack(stack_data);

            stacksWrapper.appendChild(stack);

        });
        stacksWrapper.appendChild(new_stack_element);

        var movableCards = document.querySelectorAll('.card-wrapper');
        movableCards.forEach(function (card) {
            new Sortable(card, {
                group: {
                    name: "cards"
                },
                draggable: ".board-card",
                handle: isMobile() ? ".handle" : ".board-card",
                dataIdAttr: 'data-card',
                ghostClass: 'card-placeholder',
                onUpdate: function (evt) {
                    let stack_id = card.closest('.stack').dataset.stack;
                    changeCardPosition(stack_id, this.toArray());
                },
                // Moved card to new stack
                onAdd: function (evt) {
                    let stack_id_from = evt.from.closest('.stack').dataset.stack;
                    let stack_id_to = evt.to.closest('.stack').dataset.stack;
                    let card_id = evt.item.dataset.card;
                    //let newPosition = evt.newIndex;

                    let cardsOnNewStack = this.toArray();

                    var data = { 'card': card_id, 'stack': stack_id_to };

                    getCSRFToken().then(function (token) {
                        data['csrf_name'] = token.csrf_name;
                        data['csrf_value'] = token.csrf_value;

                        return fetch(jsObject.card_movestack_url, {
                            method: 'POST',
                            credentials: "same-origin",
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)

                        });
                    }).then(function (response) {
                        return response.json();
                    }).then(function () {
                        let stack_from_Idx = getElementFromID(boardData.stacks, stack_id_from);
                        let stack_from = boardData.stacks[stack_from_Idx];
                        let cardIdx = getElementFromID(stack_from.cards, card_id);
                        let card = stack.cards[cardIdx]

                        // remove card from old stack
                        stack_from.cards = stack_from.cards.filter(function (stack_card) {
                            return stack_card.id != card_id;
                        });

                        // add card to new stack
                        let stack_to_Idx = getElementFromID(boardData.stacks, stack_id_to);
                        let stack_to = boardData.stacks[stack_to_Idx];
                        stack_to.cards.push(card);
                        card.stack = stack_id_to;

                        changeCardPosition(stack_id_to, cardsOnNewStack);
                    }).catch(function (error) {
                        console.log(error);
                    });
                }
            });
        });
    }
}

function loadBoard() {
    return fetch(jsObject.boards_data, {
        method: 'GET',
        credentials: "same-origin",
        headers: {
            'Content-Type': 'application/json'
        }
    }).then(function (response) {
        return response.json();
    }).catch(function (error) {
        console.log(error);
    });
}

document.addEventListener('click', function (event) {
    let stack_header = event.target.closest('.stack-header');
    if (stack_header) {
        event.preventDefault();

        let stack_id = stack_header.closest('.stack').dataset.stack;

        if (!stack_id) {
            window.alert(lang.boards_error_open_stack);
            return;
        }
        document.getElementById('loading-overlay').classList.remove('hidden');

        let stackIdx = getElementFromID(boardData.stacks, stack_id);
        let stack = boardData.stacks[stackIdx];

        stackModal.querySelector('input[name="id"]').value = stack.id;
        stackModal.querySelector('input[name="name"]').value = stack.name;
        stackModal.querySelector('input[name="position"]').value = stack.position;

        var edit_bar = "<a href='#' data-url='" + jsObject.stack_archive + stack.id + "' data-archive='" + stack.archive + "' class='btn-archive-stack' data-id='" + stack.id + "'>" + document.getElementById('iconArchive').innerHTML + "</a> \n\
                                    <a href='#' data-url='" + jsObject.stack_delete + stack.id + "' class='btn-delete-stack'  data-id='" + stack.id + "'>" + document.getElementById('iconTrash').innerHTML + "</a>";

        stackModal.querySelector(".edit-bar").innerHTML = edit_bar;

        document.getElementById('stack-add-btn').value = lang.update;
        openDialog(stackModal);

        document.getElementById('loading-overlay').classList.add('hidden');
    }

    let btn_archive_stack = event.target.closest('.btn-archive-stack');
    if (btn_archive_stack) {
        event.preventDefault();
        let url = btn_archive_stack.dataset.url;
        let archive = parseInt(btn_archive_stack.dataset.archive) === 0 ? 1 : 0;
        let id = parseInt(btn_archive_stack.dataset.id);

        if (archive === 0) {
            if (!confirm(lang.boards_undo_archive)) {
                return false;
            }
        } else {
            if (!confirm(lang.boards_really_archive)) {
                return false;
            }
        }
        let stackIdx = getElementFromID(boardData.stacks, id);
        let stack = boardData.stacks[stackIdx];
        let savedStackEl = document.querySelector('.stack-wrapper .stack[data-stack="' + id + '"');

        if (archive) {
            savedStackEl.classList.add("archived");
        } else {
            savedStackEl.classList.remove("archived");
        }
        stack.archive = archive;
        closeDialog(stackModal, true);

        var data = { 'archive': archive };

        getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(url, {
                method: 'POST',
                credentials: "same-origin",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
        }).then(function (response) {
            return response.json();
        }).catch(function (error) {
            console.log(error);

            window.alert(lang.boards_error_archive);

            if (archive) {
                savedStackEl.classList.remove("archived");
            } else {
                savedStackEl.classList.add("archived");
            }
            stack.archive = archive;

            if (document.body.classList.contains('offline')) {
                let formData = new URLSearchParams(data).toString();
                saveDataWhenOffline(url, 'POST', formData);
            }
        });
    }

    let btn_archive_card = event.target.closest('.btn-archive-card');
    if (btn_archive_card) {
        event.preventDefault();
        let url = btn_archive_card.dataset.url;
        let archive = parseInt(btn_archive_card.dataset.archive) === 0 ? 1 : 0;
        let stack_id = parseInt(btn_archive_card.dataset.stack);
        let id = parseInt(btn_archive_card.dataset.id);

        if (archive === 0) {
            if (!confirm(lang.boards_undo_archive)) {
                return false;
            }
        } else {
            if (!confirm(lang.boards_really_archive)) {
                return false;
            }
        }
        let stackIdx = getElementFromID(boardData.stacks, stack_id);
        let stack = boardData.stacks[stackIdx];
        let cardIdx = getElementFromID(stack.cards, id);
        let card = stack.cards[cardIdx]

        let savedCardEl = document.querySelector('.stack-wrapper .stack[data-stack="' + stack_id + '"] .board-card[data-card="' + id + '"]');

        if (archive) {
            savedCardEl.classList.add("archived");
        } else {
            savedCardEl.classList.remove("archived");
        }
        card.archive = archive;
        closeDialog(cardModal, true);

        var data = { 'archive': archive };

        getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(url, {
                method: 'POST',
                credentials: "same-origin",
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
        }).then(function (response) {
            return response.json();
        }).catch(function (error) {
            console.log(error);

            window.alert(lang.boards_error_archive);

            if (archive) {
                savedCardEl.classList.remove("archived");
            } else {
                savedCardEl.classList.add("archived");
            }
            card.archive = archive;

            if (document.body.classList.contains('offline')) {
                let formData = new URLSearchParams(data).toString();
                saveDataWhenOffline(url, 'POST', formData);
            }
        });
    }

    let btn_delete_stack = event.target.closest('.btn-delete-stack');
    if (btn_delete_stack) {
        event.preventDefault();
        let url = btn_delete_stack.dataset.url;
        let id = parseInt(btn_delete_stack.dataset.id);

        if (!confirm(lang.boards_really_delete_stack)) {
            return false;
        }

        let savedStackEl = document.querySelector('.stack-wrapper .stack[data-stack="' + id + '"');
        savedStackEl.classList.add("hidden");

        closeDialog(stackModal, true);

        getCSRFToken(true).then(function (token) {
            return fetch(url, {
                method: 'DELETE',
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(token)
            });
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            stacksWrapper.removeChild(savedStackEl);

            let stackIdx = getElementFromID(boardData.stacks, id);
            delete boardData.stacks[stackIdx];
        }).catch(function (error) {
            console.log(error);

            window.alert(lang.boards_error_delete);
            savedStackEl.classList.remove("hidden");

            if (document.body.classList.contains('offline')) {
                let formData = new URLSearchParams(data).toString();
                saveDataWhenOffline(url, 'POST', formData);
            }
        });
    }

    let btn_delete_card = event.target.closest('.btn-delete-card');
    if (btn_delete_card) {
        event.preventDefault();
        let url = btn_delete_card.dataset.url;
        let stack_id = parseInt(btn_delete_card.dataset.stack);
        let id = parseInt(btn_delete_card.dataset.id);

        if (!confirm(lang.boards_really_delete_card)) {
            return false;
        }

        let cardWrapper = document.querySelector('.stack-wrapper .stack[data-stack="' + stack_id + '"] .card-wrapper');
        let savedCardEl = cardWrapper.querySelector('.board-card[data-card="' + id + '"]');
        savedCardEl.classList.add("hidden");

        closeDialog(cardModal, true);

        getCSRFToken(true).then(function (token) {
            return fetch(url, {
                method: 'DELETE',
                credentials: "same-origin",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(token)
            });
        }).then(function (response) {
            return response.json();
        }).then(function (response) {
            cardWrapper.removeChild(savedCardEl);

            let stackIdx = getElementFromID(boardData.stacks, stack_id);
            let stack = boardData.stacks[stackIdx];
            let cardIdx = getElementFromID(stack.cards, id);
            delete stack.cards[cardIdx];
        }).catch(function (error) {
            console.log(error);

            window.alert(lang.boards_error_delete);
            savedCardEl.classList.remove("hidden");

            if (document.body.classList.contains('offline')) {
                let formData = new URLSearchParams(data).toString();
                saveDataWhenOffline(url, 'POST', formData);
            }
        });
    }

    let stack_create_btn = event.target.closest("#create-stack")
    if (stack_create_btn) {
        event.preventDefault();
        openDialog(stackModal);
    }

    let stack_close_btn = event.target.closest("#stack-close-btn")
    if (stack_close_btn) {
        closeDialog(stackModal);
    }

    let label_create_btn = event.target.closest("#create-label")
    if (label_create_btn) {
        event.preventDefault();
        openDialog(labelModal);
    }

    let label_close_btn = event.target.closest("#label-close-btn")
    if (label_close_btn) {
        closeDialog(labelModal);
    }

    let label_edit = event.target.closest("a.edit-label");
    if (label_edit) {
        event.preventDefault();
        document.getElementById('loading-overlay').classList.remove('hidden');
        var label = label_edit.dataset.label;

        fetch(jsObject.label_get_url + label, {
            method: 'GET',
            credentials: "same-origin"
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (data.status !== 'error') {

                labelModal.querySelector('input[name="id"]').value = data.entry.id;
                labelModal.querySelector('input[name="name"]').value = data.entry.name;
                labelModal.querySelector('input[name="background_color"]').value = data.entry.background_color;
                labelModal.querySelector('input[name="background_color"]').parentElement.style.backgroundColor = data.entry.background_color;
                labelModal.querySelector('input[name="text_color"]').value = data.entry.text_color;
                labelModal.querySelector('input[name="text_color"]').parentElement.style.backgroundColor = data.entry.text_color;

                var edit_bar = "<a href='#' data-url='" + jsObject.label_delete + data.entry.id + "' class='btn-delete' data-type='label'>" + document.getElementById('iconTrash').innerHTML + "</a>";

                labelModal.querySelector(".edit-bar").innerHTML = edit_bar;

                document.getElementById('label-add-btn').value = lang.update;

                openDialog(labelModal);

            }
        }).then(function () {
            document.getElementById('loading-overlay').classList.add('hidden');
        }).catch(function (error) {
            console.log(error);
        });
    }

    let create_card_link = event.target.closest(".create-card");
    if (create_card_link) {
        event.preventDefault();
        var stack_id = create_card_link.dataset.stack;
        cardModal.querySelector('input[name="stack"]').value = stack_id;
        openDialog(cardModal);
    }

    let card_close_btn = event.target.closest("#card-close-btn")
    if (card_close_btn) {
        event.preventDefault();
        closeDialog(cardModal);
    };


    let board_card = event.target.closest(".board-card-content")
    if (board_card) {
        event.preventDefault();
        let stack_id = board_card.closest('.stack').dataset.stack;
        let card_id = board_card.closest('.board-card').dataset.card;

        if (!card_id) {
            window.alert(lang.boards_error_open_card);
            return;
        }

        document.getElementById('loading-overlay').classList.remove('hidden');

        let stackIdx = getElementFromID(boardData.stacks, stack_id);
        let stack = boardData.stacks[stackIdx];
        let cardIdx = getElementFromID(stack.cards, card_id);
        let card = stack.cards[cardIdx]

        cardModal.querySelector('input[name="id"]').value = card.id;
        cardModal.querySelector('input[name="title"]').value = card.title;
        cardModal.querySelector('input[name="position"]').value = card.position;
        cardModal.querySelector('input[name="stack"]').value = card.stack;
        cardModal.querySelector('input[name="archive"]').value = card.archive;

        if (card.date) {
            var datefield = cardModal.querySelector('input[name="date"]');
            datefield.value = card.date;

            // update flatpickr
            datefield._flatpickr.setDate(card.date);

            let siblings = datefield.parentElement.parentElement.querySelectorAll('.show-sibling');
            siblings.forEach(function (sibling, idx) {
                sibling.classList.add('hidden');
            });
            datefield.parentElement.classList.remove('hidden');
        }
        if (card.time) {
            var timefield = cardModal.querySelector('input[name="time"]');
            timefield.value = card.time;

            let siblings = timefield.parentElement.parentElement.querySelectorAll('.show-sibling');
            siblings.forEach(function (sibling, idx) {
                sibling.classList.add('hidden');
            });
            timefield.parentElement.classList.remove('hidden');
        }

        var descrfield = cardModal.querySelector('textarea[name="description"]');
        if (card.description) {
            descrfield.value = card.description;

            let siblings = descrfield.parentElement.parentElement.querySelectorAll('.show-sibling');
            siblings.forEach(function (sibling, idx) {
                sibling.classList.add('hidden');
            });
            descrfield.parentElement.classList.remove('hidden');
        }


        cardModal.querySelector('#createdBy').innerHTML = boardData.users[card.createdBy].login;
        cardModal.querySelector('#createdOn').innerHTML = moment(card.createdOn).format(i18n.dateformatJS.datetime);
        cardModal.querySelector('#changedBy').innerHTML = boardData.users[card.changedBy].login;
        cardModal.querySelector('#changedOn').innerHTML = moment(card.changedOn).format(i18n.dateformatJS.datetime);
        cardModal.querySelector('.form-group.card-dates').classList.remove('hidden');

        let usersSelect = cardModal.querySelector('select[name="users[]"]');
        let avatars = cardModal.querySelectorAll('.avatar-small, .avatar-small');
        avatars.forEach(function (avatar, idx) {
            let user_id = parseInt(avatar.dataset.user);
            var option = usersSelect.querySelector("option[value='" + user_id + "']");

            if (typeof card.users !== 'undefined' && card.users.indexOf(user_id) !== -1) {
                avatar.classList.add('selected');
                option.selected = true;
            } else {
                avatar.classList.remove('selected');
                option.selected = false;
            }
        });

        // not working!
        //var labels = cardModal.querySelector('select[name="labels[]"]');
        //labels.value = data.entry.labels;
        selector.reset();
        selector.setValue(card.labels.map(String));


        var edit_bar = "<a href='#' data-url='" + jsObject.card_archive + card.id + "' data-archive='" + card.archive + "' class='btn-archive-card' data-stack='" + card.stack + "' data-id='" + card.id + "'>" + document.getElementById('iconArchive').innerHTML + "</a> \n\
                                    <a href='#' data-url='" + jsObject.card_delete + card.id + "' class='btn-delete-card' data-stack='" + card.stack + "' data-id='" + card.id + "'>" + document.getElementById('iconTrash').innerHTML + "</a>";

        cardModal.querySelector(".edit-bar").innerHTML = edit_bar;

        document.getElementById('card-add-btn').value = lang.update;
        openDialog(cardModal);

        document.getElementById('loading-overlay').classList.add('hidden');
    }
});

let openedDialogData = null;

const selector = new Selectr("select#card-label-list", {
    searchable: false,
    customClass: 'selectr-boards',
    renderOption: function (option) {
        var template = [
            "<div class='select-option-label' style='background-color:", option.dataset.backgroundColor, "; color:", option.dataset.textColor, "'><span>",
            option.textContent,
            "</span></div>"
        ];
        return template.join('');
    }, renderSelection: function (option) {
        var template = ['<div class="select-label" style="background-color:', option.dataset.backgroundColor, '; color:', option.dataset.textColor, '"><span>', option.textContent.trim(), '</span></div>'];
        return template.join('');
    },
    placeholder: lang.boards_labels
});

var simplemde = null;

document.addEventListener('keydown', function (event) {
    if (event.keyCode === 27) {
        if (isVisible(stackModal)) {
            closeDialog(stackModal);
        }
        if (isVisible(labelModal)) {
            closeDialog(labelModal);
        }
        if (isVisible(cardModal)) {
            closeDialog(cardModal);
        }
    }
});

// replace color picker placeholder with chosen color
document.addEventListener('change', function (event) {
    let closest = event.target.closest('input[type="color"]');
    if (closest) {
        closest.parentElement.style.backgroundColor = closest.value;
    }
});


/**
 * ==================================================
 *              Add and update Stacks
 * ==================================================
 */
const stackModal = document.getElementById("stack-modal");

stackModal.querySelector("form").addEventListener('submit', function (e) {
    e.preventDefault();
    saveStack(stackModal, jsObject.stack_save);
});

/**
 * ==================================================
 *              Add/edit labels
 * ==================================================
 */

const labelModal = document.getElementById("label-modal");

labelModal.querySelector("form").addEventListener('submit', function (e) {
    e.preventDefault();
    saveLabel(labelModal, jsObject.label_save);
});


/**
 * ==================================================
 *              Add/edit Cards
 * ==================================================
 */

const cardModal = document.getElementById("card-modal");

cardModal.querySelector("form").addEventListener('submit', function (e) {
    e.preventDefault();
    saveCard(cardModal, jsObject.card_save);
});

/**
 * With SimpleMDE the dialog form is not submitted on Enter 
 * so this is a ugly hack to submit the form on enter
 */
cardModal.addEventListener('keypress', function (event) {
    if (event.keyCode === 13) {
        event.preventDefault();
        saveCard(cardModal, jsObject.card_save);
    }
});


/**
 * Select user on avatar click in hidden multi-select
 */
let avatars = document.querySelectorAll('#card-modal .avatar');
avatars.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        var user_id = item.dataset.user;
        var option = document.querySelector("#card-modal select#users option[value='" + user_id + "']");

        if (option.selected) {
            option.selected = false;
            item.classList.remove('selected');
        } else {
            option.selected = true;
            item.classList.add('selected');
        }
    });
});


// show hidden fields on card-dialog
let siblings = cardModal.querySelectorAll('.show-sibling');
siblings.forEach(function (sibling, idx) {
    sibling.addEventListener('click', function (event) {
        event.preventDefault();
        sibling.classList.add('hidden');
        let hiddenFields = sibling.parentNode.querySelectorAll('.hidden-field');
        hiddenFields.forEach(function (hidden) {
            hidden.classList.remove('hidden');
            let input = hidden.querySelector('input');
            if (input) {
                input.focus();
            }
            let textarea = hidden.querySelector('textarea');
            if (textarea) {
                textarea.focus();
            }

            // Open Datepicker
            let datefield = cardModal.querySelector('input[name="date"]');
            if (input === datefield) {
                datefield._flatpickr.open();
            }

        });

        if (simplemde) {
            // Focus SimpleMDE
            simplemde.codemirror.refresh();
            simplemde.codemirror.focus();
        }
    });
});



function openDialog(element) {
    openedDialogData = formToJSON(element.querySelector('form'));

    freeze();

    element.style.display = 'block';

    if (!isMobile()) {
        element.querySelector('input[type="text"]').focus();
    }


    if (element === cardModal) {
        // init simplemde
        var textarea = cardModal.querySelector('textarea[name="description"]');
        simplemde = new SimpleMDE({
            element: textarea,
            autosave: {
                enabled: false
            },
            forceSync: true,
            spellChecker: false,
            promptURLs: true,
            status: false,
            styleSelectedText: isMobile() ? false : true,
            minHeight: '50px'

        });
        if (textarea.value !== '') {
            simplemde.togglePreview();
        }
    }
}
function closeDialog(element, force = false) {

    let new_data = formToJSON(element.querySelector('form'));

    let confirm_text = '';

    if (element === stackModal) {
        confirm_text = lang.boards_really_close_stack;
    }
    if (element === cardModal) {
        confirm_text = lang.boards_really_close_card;
    }
    if (element === labelModal) {
        confirm_text = lang.boards_really_close_label;
    }

    if (JSON.stringify(openedDialogData) !== JSON.stringify(new_data) && !force) {
        if (!confirm(confirm_text)) {
            return false;
        }
    }

    unfreeze();

    element.style.display = 'none';

    // for labels
    if (element === labelModal) {
        let colorPickers = element.querySelectorAll('.color-wrapper');
        colorPickers.forEach(function (item, idx) {
            item.style.backgroundColor = 'black';
        });
    }

    // for cards
    if (element === cardModal) {

        if (simplemde) {
            simplemde.toTextArea();
            simplemde = null;
        }

        let siblings = cardModal.querySelectorAll('.show-sibling');
        siblings.forEach(function (sibling, idx) {
            sibling.classList.remove('hidden');
        });
        let hiddens = cardModal.querySelectorAll('.hidden-field');
        hiddens.forEach(function (hidden, idx) {
            hidden.classList.add('hidden');
        });

        cardModal.querySelector('textarea[name="description"]').style.height = "auto";

        cardModal.querySelector('#createdBy').innerHTML = "";
        cardModal.querySelector('#createdOn').innerHTML = "";
        cardModal.querySelector('#changedBy').innerHTML = "";
        cardModal.querySelector('#changedOn').innerHTML = "";

        let cardDates = cardModal.querySelectorAll('.form-group.card-dates');
        cardDates.forEach(function (cardDate, idx) {
            cardDate.classList.add('hidden');
        });

        cardModal.querySelector('select[name="labels[]"]').value = "";

        cardModal.querySelector('select[name="users[]"]').value = "";

        let avatars = cardModal.querySelectorAll('.avatar-small, .avatar-small');
        avatars.forEach(function (avatar, idx) {
            avatar.classList.remove('selected');
        });

        cleanURL();

    }

    // general
    document.getElementById('stack-add-btn').value = lang.add;
    document.getElementById('card-add-btn').value = lang.add;
    document.getElementById('label-add-btn').value = lang.add;

    element.querySelector('form').reset();

    element.querySelectorAll('input[type="hidden"].reset-field').forEach(function (field) {
        field.value = "";
    });
    element.querySelector(".edit-bar").innerHTML = "";

    openedDialogData = null;
}


function cleanURL() {
    var uri = window.location.toString();
    if (uri.indexOf("?") > 0) {
        var clean_uri = uri.substring(0, uri.indexOf("?"));
        window.history.replaceState({}, document.title, clean_uri);
    }
}

function addCardtoURL(card) {
    var uri = window.location.toString();
    var separator = uri.indexOf("?") > 0 ? "&" : "?";
    if (uri.indexOf("card") <= 0) {
        window.history.replaceState({}, document.title, uri + separator + "card=" + card);
    }
}

/**
 * @see https://stackoverflow.com/a/49826736
 */
function formToJSON(elem) {
    let output = {};
    new FormData(elem).forEach(function (value, key) {
        if (key.endsWith('[]')) {
            let arrayKey = key.slice(0, -2);
            if (!Array.isArray(output[arrayKey])) {
                output[arrayKey] = [];
            }
            output[arrayKey].push(value);

        } else {
            output[key] = value;
        }
    });
    return output;
}

async function saveStack(dialog, url) {
    //document.getElementById('loading-overlay').classList.remove('hidden');

    var id = dialog.querySelector('input[name="id"]').value;

    let form = dialog.querySelector('form');
    let formData = formToJSON(form);
    let stack = createStack(formData);

    let stackIdx = getElementFromID(boardData.stacks, formData.id);
    let savedStack = boardData.stacks[stackIdx];

    let savedStackEl = document.querySelector('.stack-wrapper .stack[data-stack="' + formData.id + '"');

    if (savedStack) {
        let newStackData = formData;
        newStackData["cards"] = savedStack.cards;
        let updatedStack = createStack(newStackData);

        document.querySelector('.stack-wrapper').replaceChild(updatedStack, savedStackEl);
    } else {
        document.querySelector('.stack-wrapper').insertBefore(stack, new_stack_element);
    }

    closeDialog(dialog, true);

    try {
        let token = await getCSRFToken();

        let data = formData;
        data["csrf_name"] = token.csrf_name;
        data["csrf_value"] = token.csrf_value;

        let response = await fetch(url + id, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        let data2 = await response.json();

        //allowedReload = true;
        //window.location.reload(true);

        let stack_data = data2["entry"];
        if (savedStack) {
            boardData.stacks[stackIdx] = stack_data;
            boardData.stacks[stackIdx].cards = savedStack.cards;
        } else {
            stack_data.cards = [];
            boardData.stacks.push(stack_data);
        }

        stack.dataset.stack = stack_data.id;
        stack.querySelector(".create-card").dataset.stack = stack_data.id;

        //document.getElementById('loading-overlay').classList.add('hidden');

    } catch (error) {
        console.log(error);
        window.alert(lang.boards_error_save_stack);

        if (savedStack) {
            let updatedStack = document.querySelector('.stack-wrapper .stack[data-stack="' + formData.id + '"');

            document.querySelector('.stack-wrapper').replaceChild(savedStackEl, updatedStack);
        } else {
            document.querySelector('.stack-wrapper').removeChild(stack);
        }

        if (document.body.classList.contains('offline')) {
            let formData = new URLSearchParams(new FormData(form)).toString();
            saveDataWhenOffline(url + id, 'POST', formData);
        }
    }
}

async function saveCard(dialog, url) {
    //document.getElementById('loading-overlay').classList.remove('hidden');
    cleanURL();
    var id = dialog.querySelector('input[name="id"]').value;

    let form = dialog.querySelector('form');
    let formData = formToJSON(form);

    let stackEl = document.querySelector('.stack-wrapper .stack[data-stack="' + formData.stack + '"]');
    let card = createCard(formData);

    let stackIdx = getElementFromID(boardData.stacks, formData.stack);
    let cardIdx = getElementFromID(boardData.stacks[stackIdx].cards, formData.id);
    let savedCard = boardData.stacks[stackIdx].cards[cardIdx];

    let savedCardEl = stackEl.querySelector('.board-card[data-card="' + formData.id + '"]');

    if (savedCard) {
        stackEl.querySelector('.card-wrapper').replaceChild(card, savedCardEl);
    } else {
        stackEl.querySelector('.card-wrapper').appendChild(card);
    }

    closeDialog(dialog, true);

    try {
        let token = await getCSRFToken();

        let data = formData;
        data["csrf_name"] = token.csrf_name;
        data["csrf_value"] = token.csrf_value;

        let response = await fetch(url + id, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        let data2 = await response.json();

        //allowedReload = true;
        //window.location.reload(true);

        let card_data = data2["entry"];
        if (savedCard) {
            boardData.stacks[stackIdx].cards[cardIdx] = card_data;
        } else {
            boardData.stacks[stackIdx].cards.push(card_data);
        }

        card.dataset.card = card_data.id;

        //closeDialog(dialog, true);
        //document.getElementById('loading-overlay').classList.add('hidden');

    } catch (error) {
        console.log(error);
        window.alert(lang.boards_error_save_card);

        if (savedCard) {
            stackEl.querySelector('.card-wrapper').replaceChild(savedCardEl, card);
        } else {
            stackEl.querySelector('.card-wrapper').removeChild(card);
        }

        if (document.body.classList.contains('offline')) {
            let formData = new URLSearchParams(new FormData(form)).toString();
            saveDataWhenOffline(url + id, 'POST', formData);
        }
    }
}

async function saveLabel(dialog, url) {
    document.getElementById('loading-overlay').classList.remove('hidden');
    cleanURL();
    var id = dialog.querySelector('input[name="id"]').value;

    let form = dialog.querySelector('form');

    let data = formToJSON(form);

    try {
        let token = await getCSRFToken();

        data["csrf_name"] = token.csrf_name;
        data["csrf_value"] = token.csrf_value;

        let response = await fetch(url + id, {
            method: 'POST',
            credentials: "same-origin",
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        let data2 = await response.json();

        allowedReload = true;
        window.location.reload(true);
    } catch (error) {
        console.log(error);
        if (document.body.classList.contains('offline')) {
            let formData = new URLSearchParams(new FormData(form)).toString();
            saveDataWhenOffline(url + id, 'POST', formData);
        }
    }
}


let sidebarToggle = document.getElementById('sidebar-toggle');
sidebarToggle.addEventListener('click', function (event) {
    event.preventDefault();
    if (isMobile()) {
        // mobile visible means desktop visible
        // default is visible so remove possible hidden class
        sidebarToggle.parentElement.classList.remove('desktop-hidden');

        // change state
        sidebarToggle.parentElement.classList.toggle('mobile-visible');

        // set cookie
        if (sidebarToggle.parentElement.classList.contains('mobile-visible')) {
            setCookie('sidebar_mobilevisible', 1);
            setCookie('sidebar_desktophidden', 0);
        } else {
            setCookie('sidebar_mobilevisible', 0);
        }

    } else {
        // desktop visible means mobile hidden
        // default is hidden so remove possible visible class
        sidebarToggle.parentElement.classList.remove('mobile-visible');

        // change state
        sidebarToggle.parentElement.classList.toggle('desktop-hidden');

        // set cookie
        if (sidebarToggle.parentElement.classList.contains('desktop-hidden')) {
            setCookie('sidebar_desktophidden', 1);
            setCookie('sidebar_mobilevisible', 0);
        } else {
            setCookie('sidebar_desktophidden', 0);
        }
    }
});


/**
 * Show archived items?
 */
let checkBoxArchivedItems = document.getElementById('checkboxArchivedItems');
checkBoxArchivedItems.addEventListener('click', function (event) {

    if (checkBoxArchivedItems.checked) {
        stacksWrapper.classList.remove("hide-archived");
    } else {
        stacksWrapper.classList.add("hide-archived");
    }
    return;
});

/**
 * Auto Update page
 */
/*setInterval(async function () {
    var isOpenStack = isVisible(stackModal);
    var isOpenCard = isVisible(cardModal);
    var isOpenLabel = isVisible(labelModal);

    if (!isOpenStack === true && !isOpenCard === true && !isOpenLabel === true) {
        await updateBoard();
    }
}, 10000);*/

async function updateBoard() {
    let newData = await loadBoard();
    if (JSON.stringify(boardData) !== JSON.stringify(newData)) {
        console.log("Update Board Data");
        loadingIconBoard.classList.remove("hidden");
        boardData = newData;
        renderBoard();
        loadingIconBoard.classList.add("hidden");
    }
}


window.addEventListener('beforeunload', function (event) {
    var isOpenStack = isVisible(stackModal);
    var isOpenCard = isVisible(cardModal);
    var isOpenLabel = isVisible(labelModal);

    if (!allowedReload && (isOpenStack === true || isOpenCard === true || isOpenLabel === true)) {
        event.returnValue = lang.boards_really_close_page;
    }
});


/**
 * Stick sidebar to top when scrolling
 */
const sidebar = document.getElementById('sidebar');
const masthead = document.getElementById('masthead');
const pageBody = document.getElementsByTagName("BODY")[0];

function sidebarAdjustments() {

    let headerHeight = masthead.offsetHeight;
    let scroll = window.scrollY;
    if (scroll < headerHeight) {
        let value = headerHeight - scroll;
        sidebar.style.paddingTop = value + 'px';
    } else {
        sidebar.style.paddingTop = 0;
    }
}

/* Do not apply sidebar adjustments on fixed header */
//sidebarAdjustments();
window.addEventListener('scroll', function () {
    if (pageBody.classList.contains("mobile-navigation-open")) {
        sidebarAdjustments();
    }
});


var sortable = new Sortable(stacksWrapper, {
    group: {
        name: "stacks",
    },
    draggable: ".stack",
    handle: isMobile() ? ".handle" : ".stack-header",
    dataIdAttr: 'data-stack',
    filter: '.stack-dummy',
    onEnd: function (evt) {
        let stacks = this.toArray();
        var data = { 'stack': stacks };

        getCSRFToken().then(function (token) {
            data['csrf_name'] = token.csrf_name;
            data['csrf_value'] = token.csrf_value;

            return fetch(jsObject.stack_position_url, {
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
            stacks.forEach(function (stack_id, position) {
                let stackIdx = getElementFromID(boardData.stacks, stack_id);
                let stack = boardData.stacks[stackIdx];
                stack.position = position;
            });
        }).catch(function (error) {
            console.log(error);
        });

    }
});


function getElementFromID(data, element_id) {
    for (let id in data) {
        let element_data = data[id];
        if (element_data.id == element_id) {
            return id;
        }
    }
    return null;
}

function createStack(stack_data) {
    let stack_dummy = document.querySelector("#templates .stack");
    let stack = stack_dummy.cloneNode(true);

    if (stack_data.id) {
        stack.dataset.stack = stack_data.id;
    }
    if (stack_data.archive == 1) {
        stack.classList.add("archived");
    }

    stack.querySelector('.stack-header span.title').innerHTML = stack_data.name;
    stack.querySelector('a#create-card').dataset.stack = stack_data.id;

    if (stack_data.cards) {
        Object.values(stack_data.cards).forEach(function (card_data) {
            let card = createCard(card_data);

            stack.querySelector('.card-wrapper').appendChild(card);
        });
    }
    return stack;
}

function createCard(card_data) {
    let card_dummy = document.querySelector("#templates .board-card");
    let card = card_dummy.cloneNode(true);

    if (card_data.id) {
        card.dataset.card = card_data.id;
    }
    if (card_data.archive == 1) {
        card.classList.add("archived");
    }
    card.querySelector('.card-title').innerHTML = card_data.title;

    if (card_data.description) {
        card.querySelector('.description').classList.remove("hidden");
    }

    if (card_data.date || card_data.time) {
        let card_date = card.querySelector('.card-date');
        card_date.classList.remove("hidden");

        if (card_data.date) {
            let date = moment(card_data.date, "YYYY-MM-DD");
            if (moment().isSameOrAfter(date)) {
                card_date.classList.add("due");
            }
            card_date.innerHTML = moment(card_data.date, "YYYY-MM-DD").format(i18n.dateformatJS.date);
        }
        if (card_data.time) {
            card_date.innerHTML = card_date.innerHTML + " " + moment(card_data.time, "HH:mm:ss").format("HH:mm");
        }

    }

    let card_checkbox = card.querySelector('.check');
    card_checkbox.classList.add("btn-archive-card");
    card_checkbox.dataset.url = jsObject.card_archive + card_data.id;
    card_checkbox.dataset.archive = card_data.archive;
    card_checkbox.dataset.stack = card_data.stack;
    card_checkbox.dataset.id = card_data.id;

    if (card_data.labels) {
        let card_handle = card.querySelector('.card-labels .handle');

        card_data.labels.forEach(function (card_label) {
            let label = boardData.labels[card_label];

            let label_div = document.createElement("div");
            label_div.classList.add("card-label");
            label_div.style.backgroundColor = label.background_color;
            label_div.style.color = label.text_color;

            card.querySelector('.card-labels').insertBefore(label_div, card_handle);
        });
    }
    if (card_data.users) {
        card_data.users.forEach(function (card_user) {
            let user = boardData.users[card_user];
            card.querySelectorAll('.card-member').forEach(function (card_member) {
                if (card_member.dataset.user == card_user) {
                    card_member.classList.remove("hidden");
                }
            });
        });
    }

    return card;
}

function changeCardPosition(stack_id, cards) {
    var data = { 'card': cards };

    return getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.card_position_url, {
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
        let stackIdx = getElementFromID(boardData.stacks, stack_id);
        let stack = boardData.stacks[stackIdx];
        cards.forEach(function (card_id, position) {
            let cardIdx = getElementFromID(stack.cards, card_id);
            let card = stack.cards[cardIdx]
            card.position = position;
        });
    }).catch(function (error) {
        console.log(error);
    });
}