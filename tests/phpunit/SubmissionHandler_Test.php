<?php

namespace WCMIA_2014;

/**
 * Class SubmissionHandler_Test
 */
class SubmissionHandler_Test extends UserImporter_UnitTestCase {
	public function test_detect_request() {
		$handler = new SubmissionHandler(array(), array());
		$this->assertFalse($handler->submission_detected());

		$handler = new SubmissionHandler(array('wcmianonce' => 'test'), array('import_file' => array('tmp_name' => 'test')));
		$this->assertTrue($handler->submission_detected());
	}

	public function test_valid_request() {
		$handler = new SubmissionHandler(array('wcmianonce' => 'test'), array('import_file' => array('tmp_name' => 'test')));
		$this->assertFalse($handler->valid_submission());

		$handler = new SubmissionHandler(array('wcmianonce' => wp_create_nonce('userimport')), array('import_file' => array('tmp_name' => 'test')));
		$this->assertFalse($handler->valid_submission());

		$handler = new SubmissionHandler(array('wcmianonce' => 'test'), array('import_file' => array('tmp_name' => $this->get_test_csv_file_path())));
		$this->assertFalse($handler->valid_submission());

		$handler = new SubmissionHandler(array('wcmianonce' => wp_create_nonce('userimport')), array('import_file' => array('tmp_name' => $this->get_test_csv_file_path())));
		$this->assertTrue($handler->valid_submission());
	}

	public function test_import() {
		$this->clear_settings_errors();
		$this->assertEmpty(get_transient('settings_errors'));
		$this->assertNull(username_exists('gentoo'));

		$handler = new SubmissionHandler(array('wcmianonce' => wp_create_nonce('userimport')), array('import_file' => array('tmp_name' => $this->get_test_csv_file_path())));
		$handler->do_import();

		$this->assertNotNull(username_exists('gentoo'));
		$this->assertNotNull(username_exists('king'));
		$this->assertNotNull(username_exists('galapagos'));

		$this->assertNotEmpty(get_transient('settings_errors'));
	}

}
 