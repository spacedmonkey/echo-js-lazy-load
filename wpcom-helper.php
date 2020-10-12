<?php
/**
 * WPCOM helpers.
 *
 * @package   Echo_Js_Lazy_Load
 * @author    Jonathan Harris <jon@spacedmonkey.co.uk>
 * @license   GPL-2.0+
 * @link      http://www.spacedmonkey.com/
 * @copyright 2015 Spacedmonkey
 */

add_filter( 'echo_js_lazy_load_enabled', 'wpcom_vip_disable_lazyload_on_mobile' );

/**
 * Disable echo lazy loading on mobile.
 *
 * @param boolean $enabled Enabled.
 *
 * @return false
 */
function wpcom_vip_disable_lazyload_on_mobile( $enabled ) {
	if ( function_exists( 'jetpack_is_mobile' ) && jetpack_is_mobile() ) {
		$enabled = false;
	}

	if ( class_exists( 'Jetpack_User_Agent_Info' ) && Jetpack_User_Agent_Info::is_ipad() ) {
		$enabled = false;
	}

	return $enabled;
}
