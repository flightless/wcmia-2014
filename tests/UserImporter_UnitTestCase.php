<?php

namespace WCMIA_2014;
use WP_UnitTestCase;

/**
 * Class UserImporter_UnitTestCase
 *
 * A base class for all unit test for the User Importer plugin
 */
abstract class UserImporter_UnitTestCase extends WP_UnitTestCase {
	protected function get_test_csv_file_path() {
		return __DIR__ . '/data/user_list.csv';
	}

	protected function clear_settings_errors() {
		global $wp_settings_errors;
		$wp_settings_errors = array();
		delete_transient( 'settings_errors' );
	}
}
