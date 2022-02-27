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

            let stack_dummy = document.querySelector("#templates .stack");
            let stack = stack_dummy.cloneNode(true);

            stack.dataset.stack = stack_data.id;
            if (stack_data.archive == 1) {
                stack.classList.add("archived");
            }

            stack.querySelector('.stack-header span.title').innerHTML = stack_data.name;
            stack.querySelector('a#create-card').dataset.stack = stack_data.id;

            Object.values(stack_data.cards).forEach(function (card_data) {
                let card_dummy = document.querySelector("#templates .board-card");
                let card = card_dummy.cloneNode(true);

                card.dataset.card = card_data.id;
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
                card_checkbox.classList.add("btn-archive");
                card_checkbox.dataset.url = jsObject.card_archive + card_data.id;
                card_checkbox.dataset.archive = card_data.archive;

                let card_handle = card.querySelector('.card-labels .handle');

                card_data.labels.forEach(function (card_label) {
                    let label = boardData.labels[card_label];

                    let label_div = document.createElement("div");
                    label_div.classList.add("card-label");
                    label_div.style.backgroundColor = label.background_color;
                    label_div.style.color = label.text_color;

                    card.querySelector('.card-labels').insertBefore(label_div, card_handle);
                });

                card_data.users.forEach(function (card_user) {
                    let user = boardData.users[card_user];
                    card.querySelectorAll('.card-member').forEach(function (card_member) {
                        if (card_member.dataset.user == card_user) {
                            card_member.classList.remove("hidden");
                        }
                    });
                });

                stack.querySelector('.card-wrapper').appendChild(card);
            });

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
                    changeCardPosition(this.toArray());
                },
                // Moved card to new stack
                onAdd: function (evt) {
                    var stack = evt.to.closest('.stack').dataset.stack;
                    var card = evt.item.dataset.card;
                    let newPosition = evt.newIndex;

                    let cardsOnNewStack = this.toArray();

                    var data = { 'card': card, 'stack': stack };

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
                        return changeCardPosition(cardsOnNewStack);
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
        document.getElementById('loading-overlay').classList.remove('hidden');

        let stack_id = stack_header.closest('.stack').dataset.stack;
        let stack = getElementFromID(boardData.stacks, stack_id);

        stackModal.querySelector('input[name="id"]').value = stack.id;
        stackModal.querySelector('input[name="name"]').value = stack.name;
        stackModal.querySelector('input[name="position"]').value = stack.position;

        var edit_bar = "<a href='#' data-url='" + jsObject.stack_archive + stack.id + "' data-archive='" + stack.archive + "' class='btn-archive'>" + document.getElementById('iconArchive').innerHTML + "</a> \n\
                                    <a href='#' data-url='" + jsObject.stack_delete + stack.id + "' class='btn-delete' data-type='stack'>" + document.getElementById('iconTrash').innerHTML + "</a>";

        stackModal.querySelector(".edit-bar").innerHTML = edit_bar;

        document.getElementById('stack-add-btn').value = lang.update;
        openDialog(stackModal);

        document.getElementById('loading-overlay').classList.add('hidden');
    }

    let btn_archive = event.target.closest('.btn-archive');
    if (btn_archive) {
        btn_archive.parentElement.style.backgroundColor = btn_archive.value;
        event.preventDefault();
        var url = btn_archive.dataset.url;
        var is_archived = parseInt(btn_archive.dataset.archive);

        if (is_archived === 1) {
            if (!confirm(lang.undo_archive)) {
                return false;
            }
        } else {
            if (!confirm(lang.really_archive)) {
                return false;
            }
        }
        var data = { 'archive': is_archived === 0 ? 1 : 0 };

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
        }).then(function (data) {
            allowedReload = true;
            window.location.reload(true);
        }).catch(function (error) {
            console.log(error);
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
        document.getElementById('loading-overlay').classList.remove('hidden');

        let stack_id = board_card.closest('.stack').dataset.stack;
        let card_id = board_card.closest('.board-card').dataset.card;
        let stack = getElementFromID(boardData.stacks, stack_id);
        let card = getElementFromID(stack.cards, card_id);

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


        cardModal.querySelector('#createdBy').innerHTML = card.createdBy;
        cardModal.querySelector('#createdOn').innerHTML = moment(card.createdOn).format(i18n.dateformatJS.datetime);
        cardModal.querySelector('#changedBy').innerHTML = card.changedBy;
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


        var edit_bar = "<a href='#' data-url='" + jsObject.card_archive + card.id + "' data-archive='" + card.archive + "' class='btn-archive'>" + document.getElementById('iconArchive').innerHTML + "</a> \n\
                                    <a href='#' data-url='" + jsObject.card_delete + card.id + "' class='btn-delete' data-type='card'>" + document.getElementById('iconTrash').innerHTML + "</a>";

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
    placeholder: lang.labels
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
    save(stackModal, jsObject.stack_save);
});

/**
 * ==================================================
 *              Add/edit labels
 * ==================================================
 */

const labelModal = document.getElementById("label-modal");

labelModal.querySelector("form").addEventListener('submit', function (e) {
    e.preventDefault();
    save(labelModal, jsObject.label_save);
});


/**
 * ==================================================
 *              Add/edit Cards
 * ==================================================
 */

const cardModal = document.getElementById("card-modal");

cardModal.querySelector("form").addEventListener('submit', function (e) {
    e.preventDefault();
    save(cardModal, jsObject.card_save);
});

/**
 * With SimpleMDE the dialog form is not submitted on Enter 
 * so this is a ugly hack to submit the form on enter
 */
cardModal.addEventListener('keypress', function (event) {
    if (event.keyCode === 13) {
        event.preventDefault();
        save(cardModal, jsObject.card_save);
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
function closeDialog(element) {

    let new_data = formToJSON(element.querySelector('form'));

    let confirm_text = lang.really_close;

    if (element === stackModal) {
        confirm_text = lang.really_close_stack;
    }
    if (element === cardModal) {
        confirm_text = lang.really_close_card;
    }
    if (element === labelModal) {
        confirm_text = lang.really_close_label;
    }

    if (JSON.stringify(openedDialogData) !== JSON.stringify(new_data)) {
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

function save(dialog, url) {
    document.getElementById('loading-overlay').classList.remove('hidden');
    cleanURL();
    var id = dialog.querySelector('input[name="id"]').value;

    let form = dialog.querySelector('form');

    getCSRFToken().then(function (token) {
        let data = formToJSON(form);
        data["csrf_name"] = token.csrf_name;
        data["csrf_value"] = token.csrf_value;

        return fetch(url + id, {
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
        window.location.reload(true);
    }).catch(function (error) {
        console.log(error);
        if (document.body.classList.contains('offline')) {
            let formData = new URLSearchParams(new FormData(form)).toString();
            saveDataWhenOffline(url + id, 'POST', formData);
        }
    });



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

    var data = { 'state': checkBoxArchivedItems.checked ? 1 : 0 };

    getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.set_archive, {
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
        window.location.reload(true);
    }).catch(function (error) {
        console.log(error);
    });


    return;
});

/**
 * Auto Update page
 */
setInterval(async function () {
    var isOpenStack = isVisible(stackModal);
    var isOpenCard = isVisible(cardModal);
    var isOpenLabel = isVisible(labelModal);

    if (!isOpenStack === true && !isOpenCard === true && !isOpenLabel === true) {
        await updateBoard();
    }
}, 10000);

async function updateBoard(){
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
        event.returnValue = lang.really_close_page;
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
        var data = { 'stack': this.toArray() };

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
            return updateBoard();
        }).catch(function (error) {
            console.log(error);
        });

    }
});


function getElementFromID(data, element_id) {
    for (let id in data) {
        let element_data = data[id];
        if (element_data.id == element_id) {
            return element_data;
        }
    }
    return null;
}

function changeCardPosition(cards) {
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
        return updateBoard();
    }).catch(function (error) {
        console.log(error);
    });
}