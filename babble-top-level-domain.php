<?php
/*
Plugin Name: Babble: Top level domain
Plugin URI: 
Description: 
Author: 
Version: 1.0
Author URI:
*/

include 'url-replacer.php';

class Babble_Top_Level_Domain {
	public static $languages = array();

	/**
	 * Loads all classes and hooks
	 *
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'set_languages' ) );
		add_action( 'plugins_loaded', array( $this, 'overwrite_babble_core' ) );

		new Babble_Top_Level_Domain_Url_Replacer;
	}

	public function set_languages() {
		// Need better handeling. Like setting the default
		self::$languages['nl'] = 'domain.nl';
	}

	public function overwrite_babble_core() {
		global $bbl_locale;
		include 'locale.php';
		$bbl_locale = new Babble_Top_Level_Domain_Locale( $bbl_locale );
	}

}

$GLOBAL['babble_top_level_domain'] = new Babble_Top_Level_Domain;