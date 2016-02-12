jQuery(document).ready(function($){
    safeUrl = '/wp-content/plugins/cim-punch-cards/js/ajax/get-punch-cards.php';
    var table = $('#punch-cards-table').DataTable( {
        "dom": '<".dt-search-box"f>t<"F"ip>',
        "processing": true,
        "serverSide": true,
        "ajax": safeUrl,
    } );

    $('#punch-cards-table_filter').bind('ajaxStart', function(){
        $('#punch-cards-table_filter label').after("<span style='margin-left:8px;' id='search_punch_cards'>Loading . . .&nbsp&nbsp&nbsp</span>");
            }).bind('ajaxStop', function(){
        $('#search_punch_cards').remove();
    });

    $('#add_punch_card_form').on('submit', function() {
        setTimeout(function() {
            table.ajax.reload();
        },1000);
    });

    $('.dt-search-box input').attr('placeholder', 'Search Punch Cards');

    $('#punch-cards-table').on('click', '.add, .remove, .complete', function(){
        row = $(this).parent('div');
        id = $(this).parent('div').attr('id');
        card_action = ($(this).hasClass('add') == true) ? 'add' : ($(this).hasClass('remove') == true) ? 'remove' : 'complete';

        punches = $(this).parent('div').attr('data-punches');
        // Check if the punches are going below 0
        if ((punches == 0 && card_action == 'remove')) {
            return;
        }

        safeUrl = "/wp-admin/admin-ajax.php";
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: safeUrl,
            data: {action: 'update_punch_card', card_action: card_action, card_id: id },
        })
        .done(function(response) {
            $(row).html(response.punches_html);
            if (card_action == "complete") {
                table.ajax.reload();
            }
        });
    });

    $('#add_punch_card_form').on('submit', function(event) {
        event.preventDefault();

        safeUrl = "/wp-admin/admin-ajax.php";
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: safeUrl,
            data: $(this).serialize(),
        })
        .done(function(response) {
            $('#TB_closeWindowButton').click();
            $('#add_punch_card_form input[name^="card"]').val('');
        });
    });
});
