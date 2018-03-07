(function ($) {

    $(document).ready(function () {


        var stackDialog, stackForm, cardDialog, cardForm, labelDialog, labelForm;

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
            //height: 250,
            autoResize: true,
            width: 330,
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

        cardDialog = $("#card-form").dialog({
            autoOpen: false,
            //height: 650,
            autoResize: true,
            //width: 550,
            modal: true,
            //@see https://stackoverflow.com/a/31322508
            width: $(window).width() > 550 ? 550 : 'auto',
            buttons: [
                {
                    text: lang.add,
                    id: "card-add-btn",
                    click: function () {
                        save(cardDialog, jsObject.card_save);
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
            open: function () {
                var $textarea = cardDialog.find('textarea[name="description"]');
                if ($textarea.val() !== '') {
                    $textarea.height($textarea[0].scrollHeight);
                } else {
                    $textarea.height("auto");
                }
            },
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
            save(cardDialog, jsObject.card_save);
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
                    cardDialog.find('input[name="date"]').val(response.entry.date);
                    if (response.entry.date) {
                        cardDialog.find('input.date-display').datepicker("setDate", moment(response.entry.date).toDate());
                    }
                    cardDialog.find('input[name="time"]').val(response.entry.time);
                    cardDialog.find('textarea[name="description"]').val(response.entry.description);

                    cardDialog.find('select[name="users[]"]').val(response.entry.users);

                    var users = cardDialog.find('.avatar-small');

                    $.each(users, function (idx, user) {
                        var user_id = $(user).data('user');

                        if (jQuery.inArray(user_id, response.entry.users) !== -1) {
                            $(user).addClass('selected');
                        } else {
                            $(user).removeClass('selected');
                        }

                    });


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
         *              Add/edit labels
         * ==================================================
         */
        labelDialog = $("#label-form").dialog({
            autoOpen: false,
            //height: 250,
            autoResize: true,
            width: 330,
            modal: true,
            buttons: [
                {
                    text: lang.add,
                    id: "label-add-btn",
                    click: function () {
                        save(labelDialog, jsObject.label_save);
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
                labelForm[ 0 ].reset();
                $('#label-add-btn').button('option', 'label', lang.add);
            }
        });
        labelForm = labelDialog.find("form").on("submit", function (event) {
            event.preventDefault();
            save(labelDialog, jsObject.label_save);
        });

        $("a.create-label").on("click", function () {
            labelDialog.dialog("open");
        });

        $("a.edit-label").on("click", function () {
            var label = $(this).data('label');
            $.ajax({
                url: jsObject.label_get_url + label,
                method: 'GET',
                success: function (response) {
                    labelDialog.find('input[name="id"]').val(response.entry.id);
                    labelDialog.find('input[name="name"]').val(response.entry.name);
                    labelDialog.find('input[name="color"]').val(response.entry.color);
                    labelDialog.find('input[name="color"]').parent('.color-wrapper').css("background-color", response.entry.color);
                    $('#label-add-btn').button('option', 'label', lang.update);
                    var edit_bar = "<a href='#' data-url='" + jsObject.label_delete + response.entry.id + "' class='btn-delete'><i class='fa fa-trash' aria-hidden='true'></i></a>";
                    labelDialog.parent().find(".ui-dialog-titlebar .edit-bar").html(edit_bar);
                    labelDialog.dialog("open");
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
            axis: "x",
            tolerance: 'pointer',
            helper: 'clone',
            handle: ".stack-header",
            cursor: "move",
            containment: 'parent',
            start: function (event, ui) {
                ui.helper.removeClass('stack-border');
            },
            stop: function (event, ui) {
                //ui.item.addClass('stack-border');
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
            helper: 'clone', // do not fire click events @see https://stackoverflow.com/a/2977904
            placeholder: "card-placeholder",
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


        /**
         * Select user on avatar click in hidden multi-select
         */
        $('#card-form .avatar-small').on('click', function (e) {
            var user_id = $(this).data('user');
            var option = $("#card-form select#users option[value='" + user_id + "']");
            if (option.prop("selected")) {
                option.prop("selected", false);
                $(this).removeClass('selected');
            } else {
                option.prop("selected", true);
                $(this).addClass('selected');
            }
        });

        $('.add-user-to-card').on('click', function (e) {
            e.preventDefault();
            $(this).siblings('.dropdown-content').css("display", "block");
        });

        $('.avatar-small').disableSelection();

        $("textarea").on("input change", function () {
            $(this).height("auto").height($(this)[0].scrollHeight);
        });

        //$('.card-date').html(moment($('.card-date')).format(i18n.dateformatJS));

        mobileFunctions();
        $(window).resize(function () {
            mobileFunctions();
        });


        $('#sidebar-toggle').on('click', function () {
            $(this).parent().toggleClass('small');
        });

            
        // replace color picker placeholder with chosen color
        $(document).on('change', 'input[type="color"]', function () {
            $(this).parent('.color-wrapper').css('background-color', "" + $(this).val());
        });
        
        function mobileFunctions(){
            cardDialog.dialog({
                width: $(window).width() > 550 ? 550 : 'auto'
            });
            if ($('.menu-toggle').css('display') !== 'none') {
                $('.sidebar').addClass('small');
                $(".stack-wrapper").sortable({
                    axis: false
                });
            }else{
                $('.sidebar').removeClass('small');
                $(".stack-wrapper").sortable({
                    axis: "x"
                });
            }
        }
    });
})(jQuery);