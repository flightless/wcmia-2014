<?php

namespace WCMIA_2014;

/**
 * Class AdminPage
 */
class AdminPage {
	const MENU_SLUG = 'wcmia_userimporter';

	public function register() {
		add_management_page(
			__('Import Users', 'wcmia'),
			__('Import Users', 'wcmia'),
			'edit_users',
			self::MENU_SLUG,
			array( $this, 'display' )
		);
	}

	public function display() {
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
}
 