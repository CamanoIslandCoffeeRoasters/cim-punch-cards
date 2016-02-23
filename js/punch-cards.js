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
            form.find('input[name="action"]').val('add_punch_card');
        });
    });

    $('#punch-cards-table').on('click', '.punch-card-meta', function() {
        card_id = $(this).data('card_id');
        card_name = $(this).text();
        safeUrl = '/wp-admin/admin-ajax.php';
        $.ajax({
            url: safeUrl,
            data: { action: 'get_punch_card_meta', card_id:card_id },
            dataType: 'JSON',
            type: 'POST'
        })
        .done(function(response) {
            $('#TB_title .punch-card-title').empty();
            $('#TB_title').prepend('<div class="punch-card-title" data-card-id="'+card_id+'"><span>&nbsp;'+card_name+'</span><a class="edit-punch-card" href"">&nbsp;&nbsp;edit</a><span class="delete-punch-card"><a href"">Delete Card</a></span></div>');
            $('#punch_card_meta_table tbody tr').remove();
            if (response.punches == 'true') {
                $.each(response.punches_meta, function(key, value) {
                    $('#punch_card_meta_table tbody').append('<tr><td>'+value.punch_number+'</td><td>'+value.punch_dates+'</td></tr>');
                });
            }else{
                $('#punch_card_meta_table tbody').append('<tr><td colspan="2">No punches for '+card_name+'</td></tr>');
            }
        });
    });

    $('body').on('click', '.delete-punch-card', function() {
        card_id = $(this).parent('div').data('card-id');
        safeUrl = '/wp-admin/admin-ajax.php';
        $.ajax({
            url: safeUrl,
            data: { action: 'delete_punch_card', card_id:card_id },
            dataType: 'JSON',
            type: 'POST'
        })
        .done(function(response) {
            $('#TB_closeWindowButton').click();
            setTimeout(function() {
                table.ajax.reload();
            },1000);
        });
    });
    $('body').on('click', '.edit-punch-card', function(){
        card_id = $(this).parent('div').data('card-id');
        punch_card = $('#punch_card_'+card_id).parents('tr');

        card_name = punch_card.find('td:nth-child(2)').text();
        card_email = punch_card.find('td:nth-child(3)').text();
        card_phone = punch_card.find('td:nth-child(4)').text();
        card_phone = card_phone.replace(/[\W_]+/g, "");
        form = $('#add_punch_card_form');
        form.find('#name').val(card_name);
        form.append('<input name="card[card_id]" type="hidden" value="'+card_id+'" />');
        form.find('input[name="action"]').val('edit_punch_card');
        form.find('#email').val(card_email);
        form.find('#phone').val(card_phone);
        $('#add_punch_card_form').replaceWith(form);
        $('#TB_closeWindowButton').click();

        setTimeout(function() {
            $('.add-new-punch-card').click();
        },250);
    });
});
