<?php


namespace WCMIA_2014;
use SplFileObject;

class UserImporter {
	const MENU_SLUG = 'wcmia_userimporter';
	/** @var UserImporter  */
	private static $instance = NULL;

	/** @var AdminPage */
	private $admin_page = NULL;
	private $uploaded_file_path = '';

	private function __construct() {
		$this->setup_admin_page();
		if ( isset($_POST['wcmianonce']) && isset($_FILES['import_file']['tmp_name']) ) {
			$this->uploaded_file_path = $_FILES['import_file']['tmp_name'];
			add_action( 'load-tools_page_'.self::MENU_SLUG, array( $this, 'handle_import_request' ), 10, 0 );
		}
	}

	private function setup_admin_page() {
		$this->admin_page = new AdminPage();
		add_action( 'admin_menu', array( $this->admin_page, 'register' ), 10, 0 );
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