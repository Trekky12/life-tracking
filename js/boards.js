'use strict';
// get initial tokens
/*getCSRFToken().then(function (token) {
 console.log('new tokens generated');
 });
 */

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
            setDialogOpen(stackModal, false);
        }
        if (isVisible(labelModal)) {
            setDialogOpen(labelModal, false);
        }
        if (isVisible(cardModal)) {
            setDialogOpen(cardModal, false);
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

let stackHeaders = document.querySelectorAll('.stack-header');
stackHeaders.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        document.getElementById('loading-overlay').classList.remove('hidden');
        var stack = item.dataset.stack;

        fetch(jsObject.stack_get_url + stack, {
            method: 'GET',
            credentials: "same-origin"
        }).then(function (response) {
            return response.json();
        }).then(function (data) {
            if (data.status !== 'error') {

                stackModal.querySelector('input[name="id"]').value = data.entry.id;
                stackModal.querySelector('input[name="name"]').value = data.entry.name;
                stackModal.querySelector('input[name="position"]').value = data.entry.position;

                var edit_bar = "<a href='#' data-url='" + jsObject.stack_archive + data.entry.id + "' data-archive='" + data.entry.archive + "' class='btn-archive'><i class='fa fa-archive' aria-hidden='true'></i></a> \n\
                                    <a href='#' data-url='" + jsObject.stack_delete + data.entry.id + "' class='btn-delete' data-type='stack'><i class='fa fa-trash' aria-hidden='true'></i></a>";

                stackModal.querySelector(".edit-bar").innerHTML = edit_bar;

                document.getElementById('stack-add-btn').value = lang.update;
                setDialogOpen(stackModal, true);
            }
        }).then(function () {
            document.getElementById('loading-overlay').classList.add('hidden');
        }).catch(function (error) {
            console.log(error);
        });

    });
});

document.getElementById("create-stack").addEventListener("click", function (event) {
    event.preventDefault();
    setDialogOpen(stackModal, true);
});

stackModal.querySelector("form").addEventListener('submit', function (e) {
    e.preventDefault();
    save(stackModal, jsObject.stack_save);
});

document.getElementById("stack-close-btn").addEventListener('click', function (e) {
    setDialogOpen(stackModal, false);
});



/**
 * ==================================================
 *              Add/edit labels
 * ==================================================
 */

const labelModal = document.getElementById("label-modal");

document.getElementById("create-label").addEventListener("click", function (event) {
    event.preventDefault();
    setDialogOpen(labelModal, true);
});

labelModal.querySelector("form").addEventListener('submit', function (e) {
    e.preventDefault();
    save(labelModal, jsObject.label_save);
});

document.getElementById("label-close-btn").addEventListener('click', function (e) {
    setDialogOpen(labelModal, false);
});

let labels = document.querySelectorAll('a.edit-label');
labels.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        document.getElementById('loading-overlay').classList.remove('hidden');
        var label = item.dataset.label;

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

                var edit_bar = "<a href='#' data-url='" + jsObject.label_delete + data.entry.id + "' class='btn-delete' data-type='label'><i class='fa fa-trash' aria-hidden='true'></i></a>";

                labelModal.querySelector(".edit-bar").innerHTML = edit_bar;

                document.getElementById('label-add-btn').value = lang.update;

                setDialogOpen(labelModal, true);

            }
        }).then(function () {
            document.getElementById('loading-overlay').classList.add('hidden');
        }).catch(function (error) {
            console.log(error);
        });
    });
});


/**
 * ==================================================
 *              Add/edit Cards
 * ==================================================
 */

const cardModal = document.getElementById("card-modal");

let create_card_link = document.querySelectorAll('.create-card');
create_card_link.forEach(function (item, idx) {
    item.addEventListener("click", function (event) {
        event.preventDefault();
        var stack_id = this.dataset.stack;
        cardModal.querySelector('input[name="stack"]').value = stack_id;
        setDialogOpen(cardModal, true);
    });
});

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

document.getElementById("card-close-btn").addEventListener('click', function (e) {
    setDialogOpen(cardModal, false);
});


let cards = document.querySelectorAll('.board-card');
cards.forEach(function (item, idx) {
    item.addEventListener('click', function (event) {
        event.preventDefault();
        var card = item.dataset.card;
        loadAndOpenCard(card);
    });
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

function loadAndOpenCard(card) {
    document.getElementById('loading-overlay').classList.remove('hidden');
    //addCardtoURL(card);

    fetch(jsObject.card_get_url + card, {
        method: 'GET',
        credentials: "same-origin"
    }).then(function (response) {
        return response.json();
    }).then(function (data) {
        if (data.status !== 'error') {
            cardModal.querySelector('input[name="id"]').value = data.entry.id;
            cardModal.querySelector('input[name="title"]').value = data.entry.title;
            cardModal.querySelector('input[name="position"]').value = data.entry.position;
            cardModal.querySelector('input[name="stack"]').value = data.entry.stack;
            cardModal.querySelector('input[name="archive"]').value = data.entry.archive;

            if (data.entry.date) {
                var datefield = cardModal.querySelector('input[name="date"]');
                datefield.value = data.entry.date;

                // update flatpickr
                datefield._flatpickr.setDate(data.entry.date);

                let siblings = datefield.parentElement.parentElement.querySelectorAll('.show-sibling');
                siblings.forEach(function (sibling, idx) {
                    sibling.classList.add('hidden');
                });
                datefield.parentElement.classList.remove('hidden');
            }
            if (data.entry.time) {
                var timefield = cardModal.querySelector('input[name="time"]');
                timefield.value = data.entry.time;

                let siblings = timefield.parentElement.parentElement.querySelectorAll('.show-sibling');
                siblings.forEach(function (sibling, idx) {
                    sibling.classList.add('hidden');
                });
                timefield.parentElement.classList.remove('hidden');
            }

            var descrfield = cardModal.querySelector('textarea[name="description"]');
            if (data.entry.description) {
                descrfield.value = data.entry.description;

                let siblings = descrfield.parentElement.parentElement.querySelectorAll('.show-sibling');
                siblings.forEach(function (sibling, idx) {
                    sibling.classList.add('hidden');
                });
                descrfield.parentElement.classList.remove('hidden');
            }


            cardModal.querySelector('#createdBy').innerHTML = data.entry.createdBy;
            cardModal.querySelector('#createdOn').innerHTML = moment(data.entry.createdOn).format(i18n.dateformatJS.datetime);
            cardModal.querySelector('#changedBy').innerHTML = data.entry.changedBy;
            cardModal.querySelector('#changedOn').innerHTML = moment(data.entry.changedOn).format(i18n.dateformatJS.datetime);
            cardModal.querySelector('.form-group.card-dates').classList.remove('hidden');

            let usersSelect = cardModal.querySelector('select[name="users[]"]');
            let avatars = cardModal.querySelectorAll('.avatar-small, .avatar-small');
            avatars.forEach(function (avatar, idx) {
                let user_id = parseInt(avatar.dataset.user);
                var option = usersSelect.querySelector("option[value='" + user_id + "']");

                if (data.entry.users.indexOf(user_id) !== -1) {
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
            selector.setValue(data.entry.labels.map(String));


            var edit_bar = "<a href='#' data-url='" + jsObject.card_archive + data.entry.id + "' data-archive='" + data.entry.archive + "' class='btn-archive'><i class='fa fa-archive' aria-hidden='true'></i></a> \n\
                                    <a href='#' data-url='" + jsObject.card_delete + data.entry.id + "' class='btn-delete' data-type='card'><i class='fa fa-trash' aria-hidden='true'></i></a>";

            cardModal.querySelector(".edit-bar").innerHTML = edit_bar;

            document.getElementById('card-add-btn').value = lang.update;
            setDialogOpen(cardModal, true);
        } else {
            cleanURL();
        }
    }).then(function () {
        document.getElementById('loading-overlay').classList.add('hidden');
    }).catch(function (error) {
        console.log(error);
    });

}

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


/**
 * Currently not in use
 */
document.getElementById('addComment').addEventListener('click', function (event) {
    event.preventDefault();
    var card = cardModal.querySelector('input[name="id"]').value;
    var comment = cardModal.querySelector('textarea[name="comment"]').value;

    var data = {'card': card, 'comment': comment};

    getCSRFToken().then(function (token) {
        data['csrf_name'] = token.csrf_name;
        data['csrf_value'] = token.csrf_value;

        return fetch(jsObject.comment_save, {
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
    });

});

/**
 * Open card by GET parameter
 */
var res = window.location.href.match(/(?:\?card=([0-9]*))/);
if (res !== null && res.length > 1) {
    var card = res[1];
    loadAndOpenCard(card);
}


function setDialogOpen(element, state) {
    if (state) {

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

    } else {
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

        if (!confirm(confirm_text)) {
            return false;
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
        element.querySelector('input[type="hidden"].reset-field').value = "";
        element.querySelector(".edit-bar").innerHTML = "";
    }
}

// https://stackoverflow.com/a/29188066
function freeze() {
    var top = window.scrollY;

    document.body.style.overflow = 'hidden';

    window.onscroll = function () {
        window.scroll(0, top);
    }
}

function unfreeze() {
    document.body.style.overflow = '';
    window.onscroll = null;
}

function isMobile() {
    return isVisible(document.getElementById('mobile-header-icons'));
}

function isVisible(element) {
    return getDisplay(element) !== 'none';
}

function getDisplay(element) {
    return element.currentStyle ? element.currentStyle.display : getComputedStyle(element, null).display;
}


function setCookie(name, value, expiryDays, path) {
    expiryDays = expiryDays || 365;

    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiryDays);

    var cookie = [
        name + '=' + value,
        'expires=' + exdate.toUTCString(),
        'path=' + path || '/'
    ];
    document.cookie = cookie.join(';');
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
                output[ arrayKey ] = [];
            }
            output[arrayKey].push(value);

        } else {
            output[ key ] = value;
        }
    });
    return output;
}

function save(dialog, url) {
    document.getElementById('loading-overlay').classList.remove('hidden');
    cleanURL();
    var id = dialog.querySelector('input[name="id"]').value;

    var data = formToJSON(dialog.querySelector('form'));

    getCSRFToken().then(function (token) {
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
        window.location.reload();
    }).catch(function (error) {
        console.log(error);
    });



}

// archive / undo archive
document.addEventListener('click', function (event) {
    let closest = event.target.closest('.btn-archive');
    if (closest) {
        closest.parentElement.style.backgroundColor = closest.value;
        event.preventDefault();
        var url = closest.dataset.url;
        var is_archived = parseInt(closest.dataset.archive);

        if (is_archived === 1) {
            if (!confirm(lang.undo_archive)) {
                return false;
            }
        } else {
            if (!confirm(lang.really_archive)) {
                return false;
            }
        }
        var data = {'archive': is_archived === 0 ? 1 : 0};

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
            window.location.reload();
        }).catch(function (error) {
            console.log(error);
        });

    }
});


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

    var data = {'state': checkBoxArchivedItems.checked ? 1 : 0};

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
        window.location.reload();
    }).catch(function (error) {
        console.log(error);
    });


    return;
});

/**
 * Auto Update page
 */
setInterval(function () {
    var isOpenStack = isVisible(stackModal);
    var isOpenCard = isVisible(cardModal);
    var isOpenLabel = isVisible(labelModal);

    if (!isOpenStack === true && !isOpenCard === true && !isOpenLabel === true) {
        window.location.reload();
    }
}, 30000);


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
const header = document.getElementById('masthead');
const pageBody = document.getElementsByTagName("BODY")[0];

function sidebarAdjustments() {

    let headerHeight = header.offsetHeight;
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

var stacks = document.querySelector('.stack-wrapper');
var sortable = new Sortable(stacks, {
    group: {
        name: "stacks",
    },
    draggable: ".stack",
    handle: ".stack-header",
    dataIdAttr: 'data-stack',
    filter: '.stack-dummy',
    onEnd: function (evt) {
        var data = {'stack': this.toArray()};

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
        }).catch(function (error) {
            console.log(error);
        });

    }
});

var movableCards = document.querySelectorAll('.card-wrapper');
movableCards.forEach(function (card) {
    new Sortable(card, {
        group: {
            name: "cards"
        },
        draggable: ".board-card",
        dataIdAttr: 'data-card',
        ghostClass: 'card-placeholder',
        onUpdate: function (evt) {
            var data = {'card': this.toArray()};

            getCSRFToken().then(function (token) {
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
            }).catch(function (error) {
                console.log(error);
            });
        },
        // Moved card to new stack
        onAdd: function (evt) {
            var stack = evt.to.dataset.stack;
            var card = evt.item.dataset.card;

            var data = {'card': card, 'stack': stack};

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
            }).catch(function (error) {
                console.log(error);
            });
        }
    });
});