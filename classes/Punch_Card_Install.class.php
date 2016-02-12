<?php
    class Punch_Card_Install {

        public function __construct() {
            register_activation_hook(PUNCHCARD_PLUGIN_FILE, array($this, 'punch_card_install'));
        }

        public function punch_card_install() {
            global $wpdb;

            $punch_card_table_name = $wpdb->prefix . 'punch_cards';

            $punch_card_sql = "CREATE TABLE $punch_card_table_name (
                                `card_id`       int(15) NOT NULL AUTO_INCREMENT,
                                `card_name`     varchar(255) DEFAULT NULL,
                                `card_email`    text DEFAULT NULL,
                                `card_phone`    varchar(255) DEFAULT NULL,
                                `card_punches`  int(10) DEFAULT NULL,
                                `card_completed`int(10) DEFAULT NULL,
                                UNIQUE KEY (`card_id`)
                            );";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($punch_card_sql);


        }
    }

?>
