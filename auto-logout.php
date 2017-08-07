<?php
/*
Plugin Name: Auto Logout
Plugin URI: http://wordpress.org/extend/plugins/auto-logout/
Description: This plugin automatically logs out the user after a period of idle time. The time period can be configured from admin end general settings page.
Version: 1.0
Author: Rajasekaran M
Author URI: http://wordpress.org/extend/plugins/auto-logout/
*/

register_activation_hook( __FILE__, 'al_activate' );
register_deactivation_hook( __FILE__, 'al_deactivate' );

function al_activate() {
	if( get_option( 'al_idleTimeDuration' ) ) {
		update_option( 'al_idleTimeDuration', 1*60*60 );		
	} else {
		add_option( 'al_idleTimeDuration', 1*60*60 );
	}
	al_updateLastActiveTime();	
}

function al_deactivate() {
	delete_option( 'al_idleTimeDuration' );
}

function al_updateLastActiveTime() {
	if( is_user_logged_in() ) {
		update_usermeta( get_current_user_id(), 'al_lastActiveTime', time() );
	}
}

add_action('wp_login', 'al_updateLoginTime', 1 );
function al_updateLoginTime( $username = '' ) {
	$user_id = get_userdatabylogin( $username )->ID;
	if ($user_id != null && $user_id > 0) {
		update_usermeta( $user_id, 'al_lastActiveTime', time() );
	}
}
add_action('get_header', 'al_processOnPageLoad', 1 );
add_action('admin_init', 'al_processOnPageLoad', 1 );
function al_processOnPageLoad() {
	if( is_user_logged_in() ) {
		$lastActivityTime = al_getLastActiveTime();
		$idleTimeDuration = get_option( 'al_idleTimeDuration' ) * 60;
		if( $lastActivityTime + $idleTimeDuration < time() ) {
			wp_logout();
			wp_redirect( wp_login_url() );
		} else {
			al_updateLastActiveTime();
		}
	}
}

function al_getLastActiveTime() {
	if (is_user_logged_in()) {
		return (int) get_usermeta( get_current_user_id(), 'al_lastActiveTime' );
	} else {
		return 0;
	}
}

add_action( 'admin_init', 'al_settingSection' );
function al_settingSection() {
 	add_settings_field('al_idleTimeDuration', 'Auto Logout Duration', 'al_idleTimeHandle', 'general');
 	register_setting('general','al_idleTimeDuration');
}

function al_idleTimeHandle() {
	echo '<input name="al_idleTimeDuration" id="al_idleTimeDuration" type="text" value="'.get_option('al_idleTimeDuration').'" maxlength="4" size="5" /> Minutes';
}
