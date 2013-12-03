<?php


namespace WCMIA_2014;
use SplFileObject;

class UserImporter {
	const MENU_SLUG = 'wcmia_userimporter';
	/** @var UserImporter  */
	private static $instance = NULL;
	private $uploaded_file_path = '';

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

		$importer = new UserListImporter($file);
		$messages = $importer->import_list();
		$messages->save();

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