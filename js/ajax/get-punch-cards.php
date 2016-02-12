<?php
    require( $_SERVER['DOCUMENT_ROOT'].'/wp-load.php' );
    /*
     * DataTables server-side processing script for Subscriptions Tables.
     *
     */
    global $wpdb;
    // DB table to use
    $table = $wpdb->prefix . 'punch_cards';

    // Table's primary key
    $primaryKey = 'card_id';

    // Array of database columns which should be read and sent back to DataTables.
    // The `db` parameter represents the column name in the database, while the `dt`
    // parameter represents the DataTables column identifier. In this case simple
    // indexes
    $columns = array(
        array( 'db' => 'card_id',      'dt' => 0 ),
        array( 'db' => 'card_name',    'dt' => 1 ),
        array( 'db' => 'card_email',   'dt' => 2 ),
        array( 'db' => 'card_phone',   'dt' => 3,
               'formatter' => function($phone) {
                   return Punch_Card::format_phone($phone);
               } ),
        array( 'db' => 'card_punches', 'dt' => 4,
               'formatter' => function( $d, $row ) {
                   return Punch_Card::get_punches_html($row[0], $d);
               } ),
        array( 'db' => 'card_completed', 'dt' => 5)
    );

    // SQL server connection information
    $sql_details = array(
        'user' => DB_USER,
        'pass' => DB_PASSWORD,
        'db'   => DB_NAME,
        'host' => DB_HOST
    );


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP
     * server-side, there is no need to edit below this line.
     */

    require( 'ssp.class.php' );

    echo json_encode(
        SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
    );


    die();
?>
