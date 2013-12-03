<?php

namespace WCMIA_2014;

/**
 * Class MessageCollection_Test
 */
class MessageCollection_Test extends UserImporter_UnitTestCase {
	public function test_add_message() {
		$messages = new MessageCollection();
		$messages->add( 'test message 1' );
		$messages->add( 'test message 2' );

		$this->assertCount( 2, $messages->get('update') );
		$this->assertContains( 'test message 1', $messages->get('update') );
	}

	public function test_message_groups() {
		$messages = new MessageCollection();
		$messages->add( 'test update' );
		$messages->add( 'test error', 'error' );

		$this->assertCount( 1, $messages->get('update') );
		$this->assertContains( 'test update', $messages->get('update') );

		$this->assertCount( 1, $messages->get('error') );
		$this->assertContains( 'test error', $messages->get('error') );
	}

	public function test_format_message_list() {
		$messages = new MessageCollection();
		$messages->add( 'test message 1' );
		$messages->add( 'test message 2' );

		$html = $messages->to_html( 'update' );
		$this->assertTag( array(
			'tag' => 'li',
			'ancestor' => array( 'tag' => 'ul' ),
			'content' => 'test message 1',
		), $html );
	}

	public function test_empty_message_list() {
		$messages = new MessageCollection();
		$messages->add( 'test message 1' );
		$messages->add( 'test message 2' );

		$html = $messages->to_html( 'error' );
		$this->assertEmpty($html);
	}

	public function test_save() {
		$this->clear_settings_errors();

		$messages = new MessageCollection();
		$messages->add( 'test update' );
		$messages->add( 'test error', 'error' );
		$messages->save();

		$saved_in_memory = get_settings_errors();
		$this->assertCount(2, $saved_in_memory);

		$saved_in_db = get_transient( 'settings_errors' );
		$this->assertCount(2, $saved_in_db);

		$this->clear_settings_errors();
	}
}
 