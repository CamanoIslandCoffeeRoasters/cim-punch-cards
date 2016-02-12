<?php
/*
Plugin Name: CIM Punch Cards
Description: Manages Punch Cards
Author: tobinfekkes
Author URI: http://tobinfekkes.com
Version: 0.1
*/

define( 'PUNCHCARD_PLUGIN_FILE', __FILE__ );
define( 'PUNCHCARD_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ));

$plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
require($plugin_path.'/classes/Punch_Card.class.php');
require($plugin_path.'/classes/Punch_Card_Install.class.php');

if ( ! class_exists('Punch_Cards')) {

    class Punch_Cards {

        public function __construct() {

            $this->punch_card = new Punch_Card();
            $this->install    = new Punch_Card_Install();

            add_action( 'init', array(&$this, 'include_template_functions'), 20 );

            add_action('plugins_loaded', array( &$this, 'punch_cards_css_and_js'), 10);
        }


        public function include_template_functions() {
            add_action( 'admin_menu', 'punch_cards_menu_pages' );

            function punch_cards_menu_pages() {
                add_menu_page( 'Punch Cards', 'Punch Cards', 'manage_options', 'punch-cards', 'punch_cards', 'dashicons-admin-users');
            }

            function punch_cards() {
                $plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
                $template = $plugin_path . '/admin/punch-cards.php';
                include($template);
            }
        }


        public function punch_cards_css_and_js() {
                wp_register_style('punch_cards_css', plugins_url('css/punch-cards.css', __FILE__));
                wp_enqueue_style('punch_cards_css');

                wp_register_script('punch_cards_js', plugins_url('js/punch-cards.js', __FILE__));
                wp_enqueue_script('punch_cards_js');

                wp_register_script('data-tables', plugins_url('js/jquery.dataTables.min.js', __FILE__));
                wp_enqueue_script('data-tables');
        }
    }

    $GLOBALS['punch_cards'] = new Punch_Cards();
}
?>
