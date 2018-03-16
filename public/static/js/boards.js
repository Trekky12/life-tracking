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
                    "class": "button"
                },
                {
                    text: lang.cancel,
                    click: function () {
                        $(this).dialog("close");
                    },
                    "class": "button gray"
                }
            ],
            create: function () {
                $(this).parent().children(".ui-dialog-titlebar").append('<span class="edit-bar"></span>');
            },
            open: function (event, ui) {
                // Do not autofocus on first element when on mobile
                if ($('.menu-toggle').css('display') !== 'none') {
                    $(this).parent().focus();
                }
            },
            close: function () {
                $('#stack-add-btn').button('option', 'label', lang.add);

                stackForm[ 0 ].reset();
                stackForm.find('input[type="hidden"].reset-field').val('');

                $(this).parent().find(".ui-dialog-titlebar .edit-bar").html('');
            }
        });
        stackForm = stackDialog.find("form").on("submit", function (event) {
            event.preventDefault();
            save(stackDialog, jsObject.stack_save);
        });

        $("a.create-stack").on("click", function () {
            stackDialog.dialog("open");
        });

        $(".stack-header").on("click", function () {
            var stack = $(this).data('stack');
            $.ajax({
                url: jsObject.stack_get_url + stack,
                method: 'GET',
                success: function (response) {
                    stackDialog.find('input[name="id"]').val(response.entry.id);
                    stackDialog.find('input[name="name"]').val(response.entry.name);
                    stackDialog.find('input[name="position"]').val(response.entry.position);
                    $('#stack-add-btn').button('option', 'label', lang.update);
                    var edit_bar = "<a href='#' data-url='" + jsObject.stack_archive + response.entry.id + "' data-archive='" + response.entry.archive + "' class='btn-archive'><i class='fa fa-archive' aria-hidden='true'></i></a> \n\
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
            width: 550,
            modal: true,
            //@see https://stackoverflow.com/a/31322508
            //width: $(window).width() > 550 ? 550 : 'auto',
            buttons: [
                {
                    text: lang.add,
                    id: "card-add-btn",
                    click: function () {
                        save(cardDialog, jsObject.card_save);
                    },
                    "class": "button"
                },
                {
                    text: lang.cancel,
                    click: function () {
                        $(this).dialog("close");
                    },
                    "class": "button gray"
                }
            ],
            open: function () {
                // expand textarea
                var $textarea = cardDialog.find('textarea[name="description"]');
                if ($textarea.val() !== '') {
                    $textarea.height($textarea[0].scrollHeight);
                } else {
                    $textarea.height("auto");
                }

                // Do not autofocus on first element when on mobile
                if ($('.menu-toggle').css('display') !== 'none') {
                    $(this).parent().focus();
                }
            },
            create: function () {
                $(this).parent().children(".ui-dialog-titlebar").append('<span class="edit-bar"></span>');
            },
            close: function () {
                $('#card-add-btn').button('option', 'label', lang.add);

                cardForm[ 0 ].reset();
                cardForm.find('input[type="hidden"].reset-field').val('');

                $(this).parent().find(".ui-dialog-titlebar .edit-bar").html('');

                cardForm.find('.show-sibling').removeClass('hidden');
                cardForm.find('.hidden-field').addClass('hidden');

                cardDialog.find('textarea[name="description"]').height("auto");

                cardDialog.find('#createdBy').html("");
                cardDialog.find('#createdOn').html("");
                cardDialog.find('#changedBy').html("");
                cardDialog.find('#changedOn').html("");
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
                    cardDialog.find('input[name="archive"]').val(response.entry.archive);

                    if (response.entry.date) {
                        var datefield = cardDialog.find('input[name="date"]');
                        datefield.val(response.entry.date);
                        cardDialog.find('input.date-display').datepicker("setDate", moment(response.entry.date).toDate());

                        datefield.parent().siblings('.show-sibling').addClass('hidden');
                        datefield.parent().removeClass('hidden');
                    }
                    if (response.entry.time) {
                        var timefield = cardDialog.find('input[name="time"]');
                        timefield.val(response.entry.time);

                        timefield.parent().siblings('.show-sibling').addClass('hidden');
                        timefield.parent().removeClass('hidden');
                    }
                    if (response.entry.description) {
                        var descrfield = cardDialog.find('textarea[name="description"]');
                        descrfield.val(response.entry.description);

                        descrfield.parent().siblings('.show-sibling').addClass('hidden');
                        descrfield.parent().removeClass('hidden');
                    }

                    cardDialog.find('select[name="users[]"]').val(response.entry.users);

                    cardDialog.find('#createdBy').html(response.entry.createdBy);
                    cardDialog.find('#createdOn').html(moment(response.entry.createdOn).format(i18n.dateformatJSFull));
                    cardDialog.find('#changedBy').html(response.entry.changedBy);
                    cardDialog.find('#changedOn').html(moment(response.entry.changedOn).format(i18n.dateformatJSFull));

                    var users = cardDialog.find('.avatar-small, .avatar-small');

                    $.each(users, function (idx, user) {
                        var user_id = $(user).data('user');
                        if (jQuery.inArray(user_id, response.entry.users) !== -1) {
                            $(user).addClass('selected');
                        } else {
                            $(user).removeClass('selected');
                        }
                    });

                    cardDialog.find('select[name="labels[]"]').val(response.entry.labels);


                    $('#card-add-btn').button('option', 'label', lang.update);
                    var edit_bar = "<a href='#' data-url='" + jsObject.card_archive + response.entry.id + "' data-archive='" + response.entry.archive + "' class='btn-archive'><i class='fa fa-archive' aria-hidden='true'></i></a> \n\
                                    <a href='#' data-url='" + jsObject.card_delete + response.entry.id + "' class='btn-delete'><i class='fa fa-trash' aria-hidden='true'></i></a>";
                    cardDialog.parent().find(".ui-dialog-titlebar .edit-bar").html(edit_bar);

                    $('select#card-label-list').trigger('chosen:updated');

                    cardDialog.dialog("open");
                }
            });

        });

        // show hidden fields on card-dialog
        $('.show-sibling').on('click', function (e) {
            $(this).addClass('hidden');
            $(this).siblings('.hidden-field').removeClass('hidden').find('input, textarea').focus();
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
                    "class": "button"
                },
                {
                    text: lang.cancel,
                    click: function () {
                        $(this).dialog("close");
                    },
                    "class": "button gray"
                }
            ],
            create: function () {
                $(this).parent().children(".ui-dialog-titlebar").append('<span class="edit-bar"></span>');
            },
            open: function (event, ui) {
                // Do not autofocus on first element when on mobile
                if ($('.menu-toggle').css('display') !== 'none') {
                    $(this).parent().focus();
                }
            },
            close: function () {
                $('#label-add-btn').button('option', 'label', lang.add);

                labelForm[ 0 ].reset();
                labelForm.find('input[type="hidden"].reset-field').val('');

                $(this).parent().find(".ui-dialog-titlebar .edit-bar").html('');
                $(this).find('.color-wrapper').css("background-color", 'black');
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
                    labelDialog.find('input[name="background_color"]').val(response.entry.background_color);
                    labelDialog.find('input[name="background_color"]').parent('.color-wrapper').css("background-color", response.entry.background_color);
                    labelDialog.find('input[name="text_color"]').val(response.entry.text_color);
                    labelDialog.find('input[name="text_color"]').parent('.color-wrapper').css("background-color", response.entry.text_color);

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
            var is_archived = $(this).data('archive');
            if (is_archived === 1) {
                if (!confirm(lang.undo_archive)) {
                    return false;
                }
            } else {
                if (!confirm(lang.really_archive)) {
                    return false;
                }
            }
            $.ajax({
                url: url,
                method: 'POST',
                data: {'archive': is_archived ? 0 : 1},
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
        $('#card-form .avatar').on('click', function (e) {
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

        $('.avatar').disableSelection();

        // Expand textarea on input
        $("textarea").on("input change", function () {
            $(this).height("auto").height($(this)[0].scrollHeight);
        });


        mobileFunctions();
        $(window).resize(function () {
            mobileFunctions();
        });


        $('#sidebar-toggle').on('click', function () {
            if ($('.menu-toggle').css('display') !== 'none') {
                $(this).parent().toggleClass('mobile-visible');
                // mobile visible means desktop visible
                // default is visible so remove possible hidden class
                $(this).parent().removeClass('desktop-hidden');
            } else {
                $(this).parent().toggleClass('desktop-hidden');
                // desktop visible means mobile hidden
                // default is hidden so remove possible visible class
                $(this).parent().removeClass('mobile-visible');
            }
        });


        // replace color picker placeholder with chosen color
        $(document).on('change', 'input[type="color"]', function () {
            $(this).parent('.color-wrapper').css('background-color', "" + $(this).val());
        });

        function mobileFunctions() {
            //@see https://stackoverflow.com/a/31322508
            cardDialog.dialog({
                width: $(window).width() > 550 ? 550 : 'auto'
            });

            if ($('.menu-toggle').css('display') !== 'none') {
                $(".stack-wrapper").sortable({
                    axis: false
                });
            } else {
                $(".stack-wrapper").sortable({
                    axis: "x"
                });
            }
        }

        $("select#card-label-list").chosen({
            width: "100%",
            disable_search: true,
            placeholder_text_multiple: lang.labels
        });

        /**
         * Stick sidebar to top when scrolling
         */
        function sidebarAdjustments() {
            var $sidebar = $('.sidebar');
            if ($(window).scrollTop() < $('#masthead').outerHeight()) {
                $sidebar.css("padding-top", $('#masthead').outerHeight() - $(window).scrollTop());
            } else {
                $sidebar.css("padding-top", 0);
            }
        }
        sidebarAdjustments();
        $(window).scroll(function () {
            sidebarAdjustments();
        });

        /**
         * Show archived items?
         */
        $('#checkboxArchivedItems').on('click', function (e) {
            $.ajax({
                url: jsObject.set_archive,
                method: 'POST',
                data: {'state': $(this).is(":checked") ? 1 : 0},
                success: function (response) {
                    window.location.reload();
                },
                error: function (data) {
                    alert(data);
                }
            });
        });


        $('.avatar-small').tooltip();

        /**
         * Auto Update page
         */
        setInterval(function () {
            var isOpenStack = stackDialog.dialog("isOpen");
            var isOpenCard = cardDialog.dialog("isOpen");
            var isOpenLabel = labelDialog.dialog("isOpen");

            if (!isOpenStack === true && !isOpenCard === true && !isOpenLabel === true) {
                window.location.reload();
            }
        }, 30000);

    });
})(jQuery);