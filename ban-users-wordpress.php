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
 * Adds custom checkbox to user edition page
 *
 * @access      public
 * @since       1.0
 *
 * @param object $user
 *
 * @return void
 */

function rc_edit_user_profile( $user ) {

	// Proper authentication

	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}

	// Do not show on user's own edit screen

	if ( get_current_user_id() == $user->ID ) {
		return;
	}

	?>
	<table class="form-table">
		<tr>
			<th scope="row">Ban User</th>
			<td>
			<label for="rc_ban">
				<input name="rc_ban" type="checkbox" id="rc_ban" <?php
					checked( rc_is_user_banned( $user->ID ), TRUE )?> value="1">
					Ban this user</label>
			</td>
		</tr>
	</table>
	<?php
}

add_action( 'edit_user_profile', 'rc_edit_user_profile' );


/**
 * Save custom checkbox
 *
 * @access      public
 * @since       1.0
 *
 * @param int $user_id
 *
 * @return      void
 */

function rc_edit_user_profile_update( $user_id ) {

	// Proper authentication

	if ( ! current_user_can( 'edit_users' ) ) {
		return;
	}

	// Do not show on user's own edit screen

	if ( get_current_user_id() == $user_id ) {
		return;
	}

	if ( empty( $_POST['rc_ban'] ) ) {

		// Unlock

		rc_unban_user( $user_id );
	} else {

		// Lock

		rc_ban_user( $user_id );
	}
	
}

add_action( 'edit_user_profile_update', 'rc_edit_user_profile_update' );


/**
 * Ban user
 *
 * @access      public
 * @since       1.0
 *
 * @param int $user_id
 *
 * @return      void
 */

function rc_ban_user( $user_id ) {

	// Update status

	if ( ! rc_is_user_banned( $user_id ) ) {
		update_user_option( $user_id, 'rc_banned', TRUE, FALSE );
	}
}


/**
 * Un-ban user
 *
 * @access      public
 * @since       1.0
 *
 * @param int $user_id
 *
 * @return      void
 */

function rc_unban_user( $user_id ) {

	// Update status

	if ( rc_is_user_banned( $user_id ) ) {
		update_user_option( $user_id, 'rc_banned', FALSE, FALSE );
	}
}


/**
 * Checks if a user is already banned
 *
 * @access      public
 * @since       1.0
 *
 * @param int $user_id
 *
 * @return bool
 */

function rc_is_user_banned( $user_id ) {
	return get_user_option( 'rc_banned', $user_id );
}


/**
 * Check if user is locked while login process
 *
 * @access      public
 * @since       1.0
 *
 * @param $user
 * @param $password
 *
 * @return WP_Error, object
 */

function rc_authenticate_user( $user, $password ) {

	if ( is_wp_error( $user ) ) {
		return $user;
	}
	
	// Return error if user account is banned

	if ( get_user_option( 'rc_banned', $user->ID, FALSE ) ) {

		return new WP_Error(
			'rc_banned',
			__( '<strong>ERROR</strong>: This user account is disabled.', 'rc' )
		);
	}
	
	return $user;
}

add_filter( 'wp_authenticate_user', 'rc_authenticate_user', 10, 2 );
