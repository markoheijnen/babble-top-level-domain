<?php

class Babble_Top_Level_Domain_Url_Replacer {

	public function __construct() {
		add_filter( 'bbl_switch_admin_generic_link', array( $this, 'correct_base' ), 10, 2 );
		add_filter( 'bbl_switch_front_page_link', array( $this, 'correct_base' ), 10, 2 );
	}

	public function correct_base( $href, $lang ) {
		global $bbl_locale;

		if( isset( Babble_Top_Level_Domain::$languages[ $lang->url_prefix ] ) )
			$href = Babble_Top_Level_Domain::$languages[ $lang->url_prefix ];

		return $href;
	}

}