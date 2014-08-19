<?php

class Babble_Top_Level_Domain_Locale extends Babble_Locale {

	public function __construct( $previous ) {
		remove_action( 'admin_init', array( $previous, 'admin_init' ) );
		remove_action( 'admin_notices', array( $previous, 'admin_notices' ) );
		remove_action( 'parse_request', array( $previous, 'parse_request_early' ), 0 );
		remove_action( 'pre_comment_on_post', array( $previous, 'pre_comment_on_post' ) );
		remove_filter( 'body_class', array( $previous, 'body_class' ) );
		remove_filter( 'locale', array( $previous, 'set_locale' ) );
		remove_filter( 'mod_rewrite_rules', array( $previous, 'mod_rewrite_rules' ) );
		remove_filter( 'post_class', array( $previous, 'post_class' ), null, 3 );
		remove_filter( 'pre_update_option_rewrite_rules', array( $previous, 'internal_rewrite_rules_filter' ) );
		remove_filter( 'query_vars', array( $previous, 'query_vars' ) );

		parent::__construct();
	}

	/**
	 * Hooks the WP parse_request action 
	 *
	 * FIXME: Should I be extending and replacing the WP class?
	 *
	 * @param object $wp The WP object, passed by reference (so no need to return)
	 * @return void
	 **/
	public function parse_request_early( WP $wp ) {
		// Otherwise, simply set the lang for this request
		$wp->query_vars[ 'lang' ] = $this->content_lang;
		$wp->query_vars[ 'lang_url_prefix' ] = $this->url_prefix;
	}

	/**
	 * Hooks the WP locale filter to switch locales whenever we gosh darned want.
	 *
	 * @param string $locale The locale 
	 * @return string The locale
	 **/
	public function set_locale( $locale ) {
		global $wp_rewrite, $bbl_languages;
		
		// Deal with the special case of wp-comments-post.php
		if ( false !== stristr( $_SERVER[ 'REQUEST_URI' ], 'wp-comments-post.php' ) ) {
			if ( $comment_post_ID = ( isset( $_POST[ 'comment_post_ID' ] ) ) ? (int) $_POST[ 'comment_post_ID' ] : false ) {
				$this->set_content_lang( bbl_get_post_lang_code( $comment_post_ID ) );
				return $this->content_lang;
			}
		}
		
		if ( is_admin() ) {
			if ( isset( $this->interface_lang ) )
				return $this->interface_lang;
		} else {
			if ( isset( $this->content_lang ) )
				return $this->content_lang;
		}


		if ( is_admin() ) {
			// @FIXME: At this point a mischievous XSS "attack" could set a user's admin area language for them
			if ( isset( $_POST[ 'interface_lang' ] ) ) {
				$this->set_interface_lang( $_POST[ 'interface_lang' ] );
			} else {
				// $current_user = wp_get_current_user();
				if ( $lang = $this->get_cookie_interface_lang() ) {
					$this->set_interface_lang( $lang );
				}
			}
			// @FIXME: At this point a mischievous XSS "attack" could set a user's content language for them
			if ( isset( $_GET[ 'lang' ] ) ) {
				$this->set_content_lang( $_GET[ 'lang' ] );
			} else {
				// $current_user = wp_get_current_user();
				if ( $lang = $this->get_cookie_content_lang() ) {
					$this->set_content_lang( $lang );
				}
			}
		} 
		else { // Front end
			$lang_code = array_search( $_SERVER['HTTP_HOST'], Babble_Top_Level_Domain::$languages );

			if( $lang_code ) {
				$this->set_content_lang_from_prefix( $lang_code );
			}

			if ( $lang = $this->get_cookie_content_lang() ) {
				$this->set_interface_lang( $lang );
			}
		}


		if ( ! isset( $this->content_lang ) || ! $this->content_lang ) {
			$this->set_content_lang( bbl_get_default_lang_code() );
		}

		if ( ! isset( $this->interface_lang ) || ! $this->interface_lang ) {
			$this->set_interface_lang( bbl_get_default_lang_code() );
		}

		if ( is_admin() )
			return $this->interface_lang;
		else
			return $this->content_lang;
	}


	/**
	 * Hooks the WP home_url action 
	 * 
	 * Hackity hack: this function is attached with add_filter within
	 * the query_vars filter and the pre_comment_on_post action.
	 * @TODO: Can't remember why this is attached like this… investigate.
	 *
	 * @param string $url The URL 
	 * @param string $path The path 
	 * @param string $orig_scheme The original scheme 
	 * @param int $blog_id The ID of the blog 
	 * @return string The URL
	 **/
	public function home_url( $url, $path ) {
		if( ! bbl_is_default_lang() ) {
			$lang = bbl_get_current_lang();
			$url  = Babble_Top_Level_Domain::$languages[ $lang->url_prefix ];

			if ( is_ssl() && ! is_admin() ) {
				$url = 'https://' . $url;
			}
			else {
				$url = 'http://' . $url;
			}

			if ( $path && is_string( $path ) ) {
				$url .= '/' . ltrim( $path, '/' );
			}
		}

		return $url;
	}

	public function internal_rewrite_rules_filter( $rules ) {
		return $rules;
	}

}