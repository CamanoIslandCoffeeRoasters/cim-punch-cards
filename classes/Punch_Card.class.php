<?php

class Punch_Card {

	public function __construct() {

        add_action('init', array (&$this, 'plugins_loaded') );

	}

	public function plugins_loaded() {

		add_action( 'wp_ajax_edit_punch_card', 'edit_punch_card_ajax' );
        add_action( 'wp_ajax_nopriv_edit_punch_card', 'edit_punch_card_ajax' );

		function edit_punch_card_ajax() {
			global $wpdb;
			$card_data = array();

			foreach ($_POST['card'] as $key => $value ) {
				$card_data[$key] = $value;
			}
			$card_id = $card_data['card_id'];

			unset($card_data['card_id']);

			$wpdb->update($wpdb->prefix.'punch_cards', $card_data, array('card_id' => $card_id));
			echo json_encode($card_data);

			wp_die();
		}

		add_action( 'wp_ajax_add_punch_card', 'add_punch_card_ajax' );
        add_action( 'wp_ajax_nopriv_add_punch_card', 'add_punch_card_ajax' );

		function add_punch_card_ajax() {
			$card_data = array();

			foreach ($_POST['card'] as $key => $value ) {
				$card_data[$key] = $value;
			}

			echo Punch_Card::add_punch_card($card_data);

			wp_die();
		}

		add_action( 'wp_ajax_update_punch_card', 'update_punch_card_ajax' );
        add_action( 'wp_ajax_nopriv_update_punch_card', 'update_punch_card_ajax' );

		function update_punch_card_ajax() {

	        global $wpdb;
	        $punch_cards_table = $wpdb->prefix . "punch_cards";
			$punch_cards_meta_table = $wpdb->prefix . "punch_cards_meta";

	 	   	$card_action = $_POST['card_action'];
	 	   	$card_id     = $_POST['card_id'];
			$punches_array = array();

			$punch_card = Punch_Card::get_punch_card($card_id);
			$punches = (int) $punch_card->card_punches;

			switch ($card_action) {
				case 'add':
					$punches++;
					break;
				case 'remove':
					$punches--;
					break;
				case 'complete':
					$punches = 0;
					$punches_array['card_completed'] = ( (int) $punch_card->card_completed) + 1;
					break;

				default:
					$punches = 0;
					break;
			}
			$punches_array['card_punches'] = $punches;

			$wpdb->update(
			     $punch_cards_table,
			     $punches_array,
			     array('card_id' => $card_id)
			);

 			$wpdb->insert(
 			     $punch_cards_meta_table,
 			     array('card_id' => $card_id,
				 	   'punch_number' => $punches,
				 	   'punch_date'	=> current_time('mysql')
			 	 )
 			);

			 echo json_encode(
						 array(
								 'action'        => $card_action,
								 'card_id'       => $card_id,
								 'punches'       => $punches,
								 'punches_html'  => Punch_Card::get_punches_html($card_id, $punches)
							 )
			 );

			 wp_die();
	 	}

		add_action( 'wp_ajax_get_punch_card_meta', 'get_punch_card_meta_ajax' );
        add_action( 'wp_ajax_nopriv_get_punch_card_meta', 'get_punch_card_meta_ajax' );

		function get_punch_card_meta_ajax() {
			global $wpdb;

			$punch_cards_meta_table = $wpdb->prefix .'punch_cards_meta';
			$card_id = $_POST['card_id'];

			$punches_meta = $wpdb->get_results("SELECT punch_number, DATE_FORMAT(punch_date,'%M %D, %Y') as punch_dates
												FROM $punch_cards_meta_table
												WHERE card_id = $card_id
												ORDER BY punch_date DESC"
											);

			$punches = (count($punches_meta) > 0) ? 'true' : 'false';
			echo json_encode(
						array(
							'punches_meta' => $punches_meta,
							'punches' => $punches
						)
					);
			wp_die();
		}

		add_action( 'wp_ajax_delete_punch_card', 'delete_punch_card_ajax' );
        add_action( 'wp_ajax_nopriv_delete_punch_card', 'delete_punch_card_ajax' );

		function delete_punch_card_ajax() {
			global $wpdb;

			$card_id = $_POST['card_id'];
			$punch_cards_table = $wpdb->prefix .'punch_cards';
			$punch_cards_meta_table = $wpdb->prefix .'punch_cards_meta';

			if (!$card_id) return;
			// Set status column to false
			$wpdb->update($punch_cards_table, array('card_status' => 0), array('card_id' => $card_id));

			wp_die();
		}
	}

	public static function add_punch_card($card_array) {
		if (!$card_array) return;

		global $wpdb;
		$table = $wpdb->prefix .'punch_cards';

		return $wpdb->insert(
				$table,
				$card_array
			);
	} // End add_punch_card method

    public static function get_punch_card($id) {
        global $wpdb;
        $table = $wpdb->prefix . 'punch_cards';

        $punch_card = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE card_id = %d", $id));

        return $punch_card;
    } // End get_punch_card method

    public static function get_punches_html($id = 0, $punches = 0) {
       $punches_html = '';
	   $button = '<span class="noselect dashicons dashicons-%s"></span>';

	   $minus = sprintf($button, "minus remove" );

	   if ($punches == 6) {
		   $plus = sprintf($button, "yes complete");
	   } else {
		   $plus = sprintf($button, "plus add");
	   }

       for ($x=0; $x < 6; $x++) {
           $stars = ($x < $punches) ? "filled" : "empty";
           $punches_html .= "<span style='font-size:1.8em;margin: 0 2px;' class='dashicons dashicons-star-$stars'></span>";
       }

       $response = sprintf("<div id='%d' data-punches='%d'>%s %s %s</div>", $id, $punches, $minus, $punches_html, $plus);

       return $response;
   } // End get_punches method

   public static function update_punches($id = 0, $action = '') {
       if (!$id) return;

       global $wpdb;
       $table = $wpdb->prefix . "punch_cards";

	   $action = $_POST['action'];
	   $card_id     = $_POST['card_id'];

	   echo json_encode(
	                       array(
	                           'action'        => $action,
	                           'card_id'       => $card_id,
	                           'punches'       => $punches,
	                           'punches_html'  => Punch_Card::get_punches_html($card_id, $punches)
	                       )
	                   );


       $punches = self::get_punch_card($id)->card_punches;
       $punches = ($action == "add") ? $punches + 1 : $punches - 1;

       $wpdb->update(
                $table,
                array('card_punches' => $punches),
                array('card_id' => $id)
            );
        return $punches;
   } // End update_punches method

	public static function format_phone($phone) {
	// If we have not entered a phone number just return empty
	if (empty($phone)) {
		return '';
	}

	// Strip out any extra characters that we do not need only keep letters and numbers
	$phone = preg_replace('/\D/', "", $phone);

	// If we have a number longer than 11 digits cut the string down to only 11
	// This is also only ran if we want to limit only to 11 characters
	if (strlen($phone)>11) {
		$phone = substr($phone, 0, 11);
	}

	// Perform phone number formatting here
	if (strlen($phone) == 7) {
		return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
	} elseif (strlen($phone) == 10) {
		return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "($1) $2-$3", $phone);
	} elseif (strlen($phone) == 11) {
		return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1($2) $3-$4", $phone);
	}

	// Return original phone if not 7, 10 or 11 digits long
	return $phone;
	}

} // End Get_Punch_Card class
?>
