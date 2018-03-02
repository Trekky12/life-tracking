(function ($) {

    $(document).ready(function () {
        var stackAddDialog, stackAddForm, cardAddDialog, cardAddForm;

        function addStack() {
            $.ajax({
                url: jsObject.stack_add_url,
                method: 'POST',
                data: stackAddForm.serialize(),
                success: function (response) {
                    window.location.reload();
                },
                error: function (data) {
                    alert(data);
                }
            });
        }

        stackAddDialog = $("#stack-add-form").dialog({
            autoOpen: false,
            height: 220,
            width: 350,
            modal: true,
            buttons: [
                {
                    text: lang.add,
                    click: function () {
                        addStack();
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
            close: function () {
                stackAddForm[ 0 ].reset();
            }
        });
        stackAddForm = stackAddDialog.find("form").on("submit", function (event) {
            event.preventDefault();
            addStack();
        });

        $("a.create-stack").on("click", function () {
            stackAddDialog.dialog("open");
        });
        
        /**
         * Add Card
         */
        
        function addCard() {
            $.ajax({
                url: jsObject.card_add_url,
                method: 'POST',
                data: cardAddForm.serialize(),
                success: function (response) {
                    window.location.reload();
                },
                error: function (data) {
                    alert(data);
                }
            });
        }
        cardAddDialog = $("#card-add-form").dialog({
            autoOpen: false,
            height: 220,
            width: 350,
            modal: true,
            buttons: [
                {
                    text: lang.add,
                    click: function () {
                        addCard();
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
            close: function () {
                cardAddForm[ 0 ].reset();
                cardAddForm.find('input[name="stack"]').val("");
            }
        });
        
        cardAddForm = cardAddDialog.find("form").on("submit", function (event) {
            event.preventDefault();
            addCard();
        });
        
        $("a.create-card").on("click", function () {
            var stack_id = $(this).data('stack');
            cardAddForm.find('input[name="stack"]').val(stack_id);
            cardAddDialog.dialog("open");
        });

        /**
         * Sortable stacks
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
        });

        /**
         * Sortable cards in and over stacks 
         */
        $(".stack").sortable({
            items: ".board-card",
            connectWith: ".stack",
            update: function (event, ui) {
                var data = $(this).sortable('serialize');
                $.ajax({
                    data: data,
                    type: 'POST',
                    url: jsObject.card_position_url
                });
            }
        });
    });
})(jQuery);
