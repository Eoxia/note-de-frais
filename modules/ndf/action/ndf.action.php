<?php
/**
 * Classe gérant les actions NDF
 *
 * @author eoxia
 * @since 1.0.0.0
 * @version 1.0.0.0
 * @copyright 2017 Eoxia
 * @package ndf
 * @subpackage action
 */

namespace note_de_frais;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Classe gérant les actions NDF
 */
class NDF_Action {

	public function __construct() {
		add_action( 'wp_ajax_open_note_de_frais', array( $this, 'callback_open_note_de_frais' ) );
		add_action( 'wp_ajax_modify_note_de_frais', array( $this, 'callback_modify_note_de_frais' ) );
		add_action( 'wp_ajax_modify_note_de_frais', array( $this, 'callback_add_note_de_frais' ) );
	}

	public function callback_open_note_de_frais() {
		check_ajax_referer( 'open_note_de_frais' );

		$group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : -1;

		ob_start();
		NDF_Class::g()->display( $group_id );
		$response = ob_get_clean();

		wp_send_json_success( array(
			'namespace' => 'noteDeFrais',
			'module' => 'NDF',
			'callback_success' => 'openNdf',
			'view' => $response,
		) );
	}

	public function callback_modify_note_de_frais() {
		check_ajax_referer( 'modify_note_de_frais' );

		$group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : -1;
		NDF_Class::g()->update( $_POST );

		wp_send_json_success( array(
			'namespace' => '',
			'module' => '',
			'callback_success' => '',
			'html' => NDF_Class::g()->display( $group_id ),
		) );
	}

	public function callback_add_note_de_frais() {
		check_ajax_referer( 'add_note_de_frais' );

		$group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : -1;
		NDF_Class::g()->create( $_POST );

		wp_send_json_success( array(
			'namespace' => '',
			'module' => '',
			'callback_success' => '',
			'response' => NDF_Class::g()->display( $group_id ),
		) );
	}
}

new NDF_Action();
