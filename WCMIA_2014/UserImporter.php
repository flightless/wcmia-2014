<?php


namespace WCMIA_2014;

class UserImporter {
	const MENU_SLUG = 'wcmia_userimporter';
	/** @var UserImporter  */
	private static $instance = NULL;

	/** @var AdminPage */
	private $admin_page = NULL;

	/** @var SubmissionHandler */
	private $submission_handler = NULL;

	private function __construct() {
		$this->setup_admin_page();
		$this->setup_submission_handler();
	}

	private function setup_admin_page() {
		$this->admin_page = new AdminPage();
		add_action( 'admin_menu', array( $this->admin_page, 'register' ), 10, 0 );
	}

	private function setup_submission_handler() {
		$this->submission_handler = new SubmissionHandler($_POST, $_FILES);

		if ( $this->submission_handler->submission_detected() ) {
			add_action( 'load-tools_page_'.AdminPage::MENU_SLUG, array( $this->submission_handler, 'handle_import_request' ), 10, 0 );
		}
	}

	public static function instance() {
		if ( !isset(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}