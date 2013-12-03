<?php
/*
Plugin Name: WordCamp Miami 2014 Demo - User importer
Plugin URI: https://github.com/flightless/wcmia-2014
Description: Import users from a CSV
Author: Flightless
Author URI: http://flightless.us/
Version: 0.1
*/
/*
Copyright (c) 2014 Flightless, Inc. http://flightless.us/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

namespace WCMIA_2014;
use SplFileObject;

class UserImporter {
	const MENU_SLUG = 'wcmia_userimporter';
	/** @var UserImporter  */
	private static $instance = NULL;
	private $uploaded_file_path = '';
	private $messages = array( 'error' => array(), 'update' => array() );

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'register_admin_page' ), 10, 0 );
		if ( isset($_POST['wcmianonce']) && isset($_FILES['import_file']['tmp_name']) ) {
			$this->uploaded_file_path = $_FILES['import_file']['tmp_name'];
			add_action( 'load-tools_page_'.self::MENU_SLUG, array( $this, 'handle_import_request' ), 10, 0 );
		}
	}

	public function register_admin_page() {
		add_management_page(
			__('Import Users', 'wcmia'),
			__('Import Users', 'wcmia'),
			'edit_users',
			self::MENU_SLUG,
			array( $this, 'display_admin_page' )
		);
	}

	public function display_admin_page() {
		$title = __('Import users', 'wcmia');
		$icon = get_screen_icon('tools');
		$description = '<p class="description">'.__('Import users from a CSV.', 'wcmia').'</p>';

		$nonce = wp_nonce_field('userimport', 'wcmianonce', true, false);
		$file_input = '<input type="file" name="import_file" id="wcmia-user-import-csv-file"/>';
		$button = get_submit_button(__('Import Users', 'wcmia'));
		$form = sprintf('<form method="post" action="%s" enctype="multipart/form-data">%s%s%s</form>', $_SERVER['REQUEST_URI'], $nonce, $file_input, $button);

		$content = $description.$form;

		ob_start();
		settings_errors();
		$messages = ob_get_clean();

		printf( '<div class="wrap">%s<h2>%s</h2>%s%s</div>', $icon, $title, $messages, $content );
	}

	public function handle_import_request() {
		if ( !wp_verify_nonce($_POST['wcmianonce'], 'userimport') ) {
			return;
		}
		if ( !file_exists($this->uploaded_file_path) ) {
			return;
		}
		$file = new SplFileObject($this->uploaded_file_path);
		$file->setFlags(SplFileObject::SKIP_EMPTY|SplFileObject::READ_CSV|SplFileObject::READ_AHEAD|SplFileObject::DROP_NEW_LINE);

		foreach ( $file as $row ) {
			$username = $row[0];
			$email_address = $row[1];
			$password = empty($row[2]) ? wp_generate_password() : $row[2];

			if ( get_user_by('email', $email_address) || get_user_by('login', $username) ) {
				$this->messages['update'][] = sprintf( __( 'Skipped user %s (%s). Username or email address is already taken.', 'wcmia' ), $username, $email_address );
			} else {
				$user_id = wp_create_user($username, $password, $email_address);
				if ( $user_id && !is_wp_error($user_id) ) {
					$this->messages['update'][] = sprintf( __( 'Created new user %s (%s) with user ID %d', 'wcmia' ), $username, $email_address, $user_id );
				} elseif ( is_wp_error($user_id) ) {
					/** @var \WP_Error $user_id */
					$this->messages['error'][] = sprintf( __( 'Error creating user %s (%s). "%s"', 'wcmia' ), $username, $email_address, $user_id->get_error_message() );
				}
			}
		}
		if ( !empty($this->messages['error']) ) {
			$error_message = __('Import Errors', 'wcmia');
			$error_message .= '<ul>';
			foreach ( $this->messages['error'] as $error ) {
				$error_message .= '<li>'.$error.'</li>';
			}
			$error_message .= '</ul>';
			add_settings_error('general', 'import_error', $error_message, 'error');
		}
		if ( !empty($this->messages['update']) ) {
			$update_message = __('Import Complete', 'wcmia');
			$update_message .= '<ul>';
			foreach ( $this->messages['update'] as $update ) {
				$update_message .= '<li>'.$update.'</li>';
			}
			$update_message .= '</ul>';
			add_settings_error('general', 'import_success', $update_message, 'updated');
		}
		set_transient('settings_errors', get_settings_errors(), 30);

		wp_redirect( add_query_arg( array('settings-updated' => 1 ) ) );
		exit();
	}

	public static function instance() {
		if ( !isset(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

add_action( 'plugins_loaded', array( __NAMESPACE__.'\UserImporter', 'instance' ), 10, 0 );