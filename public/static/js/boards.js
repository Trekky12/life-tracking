(function ($) {

    $(document).ready(function () {


        var stackDialog, stackForm, cardDialog, cardForm;

        function save(dialog, url) {
            var id = dialog.find('input[name="id"]').val();
            $.ajax({
                url: url + id,
                method: 'POST',
                data: dialog.find('form').serialize(),
                success: function (response) {
                    window.location.reload();
                },
                error: function (data) {
                    alert(data);
                }
            });
        }

        /**
         * ==================================================
         *              Add and update Stacks
         * ==================================================
         */
        stackDialog = $("#stack-form").dialog({
            autoOpen: false,
            height: 250,
            width: 350,
            modal: true,
            buttons: [
                {
                    text: lang.add,
                    id: "stack-add-btn",
                    click: function () {
                        save(stackDialog, jsObject.stack_save);
                    },
                    class: "button"
                },
                {
                    text: lang.cancel,
                    click: function () {
                        $(this).dialog("close");
                    },
                    class: "button gray"
                }
            ],
            create: function () {
                $(this).parent().children(".ui-dialog-titlebar").append('<span class="edit-bar"></span>');
            },
            close: function () {
                $(this).parent().find(".ui-dialog-titlebar .edit-bar").html('');
                stackForm[ 0 ].reset();
                $('#stack-add-btn').button('option', 'label', lang.add);
            }
        });
        stackForm = stackDialog.find("form").on("submit", function (event) {
            event.preventDefault();
            save(stackDialog, jsObject.stack_save);
        });

        $("a.create-stack").on("click", function () {
            stackDialog.dialog("open");
        });

        $("a.edit-stack").on("click", function () {
            var stack = $(this).data('stack');
            $.ajax({
                url: jsObject.stack_get_url + stack,
                method: 'GET',
                success: function (response) {
                    stackDialog.find('input[name="id"]').val(response.entry.id);
                    stackDialog.find('input[name="name"]').val(response.entry.name);
                    stackDialog.find('input[name="position"]').val(response.entry.position);
                    $('#stack-add-btn').button('option', 'label', lang.update);
                    var edit_bar = "<a href='#' data-url='" + jsObject.stack_archive + response.entry.id + "' class='btn-archive'><i class='fa fa-archive' aria-hidden='true'></i></a> \n\
                                    <a href='#' data-url='" + jsObject.stack_delete + response.entry.id + "' class='btn-delete'><i class='fa fa-trash' aria-hidden='true'></i></a>";
                    stackDialog.parent().find(".ui-dialog-titlebar .edit-bar").html(edit_bar);
                    stackDialog.dialog("open");
                }
            });
        });


        /**
         * ==================================================
         *              Add Cards
         * ==================================================
         */

        cardDialog = $("#card-add-form").dialog({
            autoOpen: false,
            height: 220,
            width: 350,
            modal: true,
            buttons: [
                {
                    text: lang.add,
                    id: "card-add-btn",
                    click: function () {
                        save(cardDialog, jsObject.card_add_url);
                    },
                    class: "button"
                },
                {
                    text: lang.cancel,
                    click: function () {
                        $(this).dialog("close");
                    },
                    class: "button gray"
                }
            ],
            create: function () {
                $(this).parent().children(".ui-dialog-titlebar").append('<span class="edit-bar"></span>');
            },
            close: function () {
                cardForm[ 0 ].reset();
                cardForm.find('input[name="stack"]').val("");
                $('#card-add-btn').button('option', 'label', lang.add);
                $(this).parent().find(".ui-dialog-titlebar .edit-bar").html('');
            }
        });

        cardForm = cardDialog.find("form").on("submit", function (event) {
            event.preventDefault();
            save(cardDialog, jsObject.card_add_url);
        });

        $("a.create-card").on("click", function () {
            var stack_id = $(this).data('stack');
            cardForm.find('input[name="stack"]').val(stack_id);
            cardDialog.dialog("open");
        });

        $(".board-card").on("click", function () {
            var card = $(this).data('card');
            $.ajax({
                url: jsObject.card_get_url + card,
                method: 'GET',
                success: function (response) {
                    cardDialog.find('input[name="id"]').val(response.entry.id);
                    cardDialog.find('input[name="title"]').val(response.entry.title);
                    cardDialog.find('input[name="position"]').val(response.entry.position);
                    cardDialog.find('input[name="stack"]').val(response.entry.stack);
                    $('#card-add-btn').button('option', 'label', lang.update);
                    var edit_bar = "<a href='#' data-url='" + jsObject.card_archive + response.entry.id + "' class='btn-archive'><i class='fa fa-archive' aria-hidden='true'></i></a> \n\
                                    <a href='#' data-url='" + jsObject.card_delete + response.entry.id + "' class='btn-delete'><i class='fa fa-trash' aria-hidden='true'></i></a>";
                    cardDialog.parent().find(".ui-dialog-titlebar .edit-bar").html(edit_bar);
                    cardDialog.dialog("open");
                }
            });
        });

        /**
         * ==================================================
         *              Sort stacks and cards
         * ==================================================
         */
        $(".stack-wrapper").sortable({
            items: ".stack",
            start: function (event, ui) {
                ui.item.removeClass('stack-border');
            },
            stop: function (event, ui) {
                ui.item.addClass('stack-border');
            },
            update: function (event, ui) {
                var data = $(this).sortable('serialize');
                $.ajax({
                    data: data,
                    type: 'POST',
                    url: jsObject.stack_position_url
                });
            }
        }).disableSelection();

        $(".card-wrapper").sortable({
            connectWith: ".card-wrapper",
            dropOnEmpty: true,
            update: function (event, ui) {
                var data = $(this).sortable('serialize');
                $.ajax({
                    data: data,
                    type: 'POST',
                    url: jsObject.card_position_url
                });
            },
            // Move card to another stack
            receive: function (event, ui) {
                var stack = $(this).data('stack');
                var card = ui.item.data('card');
                $.ajax({
                    data: {'card': card, 'stack': stack},
                    type: 'POST',
                    url: jsObject.card_movestack_url
                });
            }
        }).disableSelection();

        /**
         * ==================================================
         *              Archive
         * ==================================================
         */
        $('body').on('click', '.btn-archive', function (e) {
            e.preventDefault();
            var url = $(this).data('url');
            if (!confirm(lang.really_archive)) {
                return false;
            }
            $.ajax({
                url: url,
                method: 'POST',
                success: function (response) {
                    window.location.reload();
                },
                error: function (data) {
                    alert(data);
                }
            });
        });


    });
})(jQuery);
