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

    $('#punch-cards-table').on('click', '.punch-card-meta', function() {
        card_id = $(this).data('card_id');
        card_name = $(this).text();
        console.log(card_name);
        safeUrl = '/wp-admin/admin-ajax.php';
        $.ajax({
            url: safeUrl,
            data: { action: 'get_punch_card_meta', card_id:card_id },
            dataType: 'JSON',
            type: 'POST'
        })
        .done(function(response) {
            $('#TB_title').empty().append('<span style="font-size:15px;font-weight:900;margin-top:3px;">&nbsp;'+card_name+'</span>');
            $('#punch_card_meta_table tbody tr').remove();
            console.log(response);
            if (response.punches == 'true') {
                $.each(response.punches_meta, function(key, value) {
                    $('#punch_card_meta_table tbody').append('<tr><td>'+value.punch_number+'</td><td>'+value.punch_dates+'</td></tr>');
                });
            }else{
                $('#punch_card_meta_table tbody').append('<tr><td colspan="2">No punches for '+card_name+'</td></tr>');
            }
        });
    });
});
