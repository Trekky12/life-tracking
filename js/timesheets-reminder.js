'use strict';

const addMessageButton = document.querySelector('button#add-message');
const messagesWrapper = document.querySelector('#messages-wrapper');

addMessageButton.addEventListener('click', function (event) {
        event.preventDefault();

        let nextID = messagesWrapper.querySelectorAll('.reminder-message').length;

        let div_message = document.createElement("div");
        div_message.classList.add('reminder-message');
        
        let input_message = document.createElement("input");
        input_message.type = 'text';
        input_message.name = 'messages['+ nextID +'][message]';
        input_message.classList.add('form-control');

        div_message.appendChild(input_message);

        let a_delete = document.createElement("a");
        a_delete["href"] = "#";
        a_delete.className = 'remove-reminder-message'
        a_delete.innerHTML = document.getElementById('iconTrash').innerHTML;

        div_message.appendChild(a_delete);

        messagesWrapper.appendChild(div_message);
});

/**
 * TODO: Delete messages and update fields to resort
 */

document.addEventListener('click', async function (event) {
    let remove = event.target.closest('.remove-reminder-message');

    if (remove) {
        event.preventDefault();

        let element = remove.closest('.reminder-message');
        element.remove();

        updateFields()
    }

});

function updateFields() {
    // change input field array key
    // @see https://stackoverflow.com/a/47948276
    let messages = messagesWrapper.querySelectorAll('.reminder-message');
    messages.forEach(function (item, idx) {
        let fields = item.querySelectorAll('input, textarea, select');
        fields.forEach(function (field) {
            field.setAttribute('name', field.name.replace(/messages\[[^\]]*\]/, 'messages[' + idx + ']'));
        });
    });
}