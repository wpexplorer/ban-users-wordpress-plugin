<?php
/*
Plugin Name: Ban Users
Plugin URI: http://www.wpexplorer.com/how-to-ban-a-wordpress-user/
Description: Allows you to ban users
Author: Remi Corson
Version: 1.0
Author URI: http://www.wpexplorer.com
*/

/**
 * Admin init
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/
function rc_admin_init(){
	// Edit user profile
	add_action( 'edit_user_profile', 'rc_edit_user_profile' );
	add_action( 'edit_user_profile_update', 'rc_edit_user_profile_update' );
	
}
add_action('admin_init', 'rc_admin_init' );


/**
 * Adds custom checkbox to user edition page
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/
function rc_edit_user_profile() {
	if ( !current_user_can( 'edit_users' ) ) {
		return;
	}
	
	global $user_id;
	
	// User cannot disable itself
	$current_user = wp_get_current_user();
	$current_user_id = $current_user->ID;
	if ( $current_user_id == $user_id ) {
		return;
	}
	?>
	<table class="form-table">
	<tr>
		<th scope="row">Ban User</th>
		<td>
		<label for="rc_ban">
			<input name="rc_ban" type="checkbox" id="rc_ban" <?php if ( get_user_option( 'rc_banned', $user_id, false ) ) echo ' checked="checked"'; ?> />Ban this user</label>
		</td>
	</tr>
	</table>
	<?php
}


/**
 * Save custom checkbox
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/
function rc_edit_user_profile_update() {
			
	if ( !current_user_can( 'edit_users' ) ) {
		return;
	}
	
	global $user_id;
	
	// User cannot disable itself
	$current_user    = wp_get_current_user();
	$current_user_id = $current_user->ID;
	if ( $current_user_id == $user_id ) {
		return;
	}
	
	// Lock
	if( isset( $_POST['rc_ban'] ) && $_POST['rc_ban'] = 'on' ) {
		rc_ban_user( $user_id );
	} else { // Unlock
		rc_unban_user( $user_id );
	}
	
}


/**
 * Ban user
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/
function rc_ban_user( $user_id ) {
	
	$old_status = rc_is_user_banned( $user_id );
	
	// Update status
	if ( !$old_status ) {
		update_user_option( $user_id, 'rc_banned', true, false );
	}
}


/**
 * Un-ban user
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/
function rc_unban_user( $user_id ) {

	$old_status = rc_is_user_banned( $user_id );
	
	// Update status
	if ( $old_status ) {
		update_user_option( $user_id, 'rc_banned', false, false );
	}
}


/**
 * Checks if a user is already banned
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/
function rc_is_user_banned( $user_id ) {
	return get_user_option( 'rc_banned', $user_id, false );
}


/**
 * Check if user is locked while login process
 *
 * @access      public
 * @since       1.0 
 * @return      void
*/
function rc_authenticate_user( $user ) {

	if ( is_wp_error( $user ) ) {
		return $user;
	}
	
	// Return error if user account is banned
	$banned = get_user_option( 'rc_banned', $user->ID, false );
	if ( $banned ) {
		return new WP_Error( 'rc_banned', __('<strong>ERROR</strong>: This user account is disabled.', 'rc') );
	}
	
	return $user;
}
add_filter( 'wp_authenticate_user', 'rc_authenticate_user', 1 );
