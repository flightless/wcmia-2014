<?php

namespace WCMIA_2014;
use Traversable;
use WP_Error;

/**
 * Class UserListImporter
 */
class UserListImporter {
	/** @var Traversable */
	private $list = NULL;

	/** @var MessageCollection */
	private $messages = NULL;

	public function __construct( Traversable $list ) {
		$this->list = $list;
		$this->messages = new MessageCollection();
	}

	public function import_list() {
		foreach ( $this->list as $row ) {
			$user = new PendingUser($row[0], $row[1], $row[2]);
			$user_id = $user->save();
			$this->log_message($user_id, $user);
		}
		return $this->messages;
	}

	/**
	 * @param int|WP_Error $response
	 * @param PendingUser $user
	 *
	 * @return void
	 */
	private function log_message( $response, $user ) {

		if ( is_wp_error($response) ) {
			$this->log_error_message( $response, $user );
		} else {
			$this->log_success_message( $response, $user );
		}
	}

	/**
	 * @param WP_Error $error
	 * @param PendingUser $user
	 *
	 * @return void
	 */
	private function log_error_message( $error, $user ) {
		if ( $error->get_error_code() == 'import_skipped' ) {
			$this->messages->add($error->get_error_message());
		} else {
			$this->messages->add( sprintf( __( 'Error creating user %s (%s). "%s"', 'wordsesh' ), $user->get_username(), $user->get_email_address(), $error->get_error_message() ), 'error' );
		}
	}

	/**
	 * @param int $user_id
	 * @param PendingUser $user
	 *
	 * @return void
	 */
	private function log_success_message( $user_id, $user ) {
		$this->messages->add(sprintf( __( 'Created new user %s (%s) with user ID %d', 'wordsesh' ), $user->get_username(), $user->get_email_address(), $user_id ));
	}
}
 