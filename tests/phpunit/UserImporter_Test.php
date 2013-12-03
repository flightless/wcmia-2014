<?php

namespace WCMIA_2014;

/**
 * Class UserImporter_Test
 */
class UserImporter_Test extends UserImporter_UnitTestCase {
	public function test_plugin_loaded() {
		$instance = UserImporter::instance();
		$this->assertInstanceOf(__NAMESPACE__.'\UserImporter', $instance);
	}
}
 