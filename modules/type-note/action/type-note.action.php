<?php
/**
 * Classe gérant les actions des types de note des notes de frais.
 *
 * @author eoxia
 * @since 1.2.0
 * @version 1.3.0
 * @copyright 2017 Eoxia
 * @package NDF
 */

namespace note_de_frais;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe gérant les actions des types de note des notes de frais.
 */
class Type_Note_Action {

	/**
	 * Le constructeur
	 *
	 * @since 1.2.0
	 * @version 1.2.0
	 */
	public function __construct() {
		// add_action( 'admin_menu', array( $this, 'callback_admin_menu' ), 99 );
	}

	/**
	 * Ajoutes un sous menu "Categories" qui renvoie vers la page pour créer les catégories de NDF.
	 *
	 * @since 1.2.0
	 * @version 1.2.0
	 *
	 * @return void
	 */
	public function callback_admin_menu() {
		add_submenu_page( 'frais-pro', __( 'Note types', 'frais-pro' ), __( 'Note types', 'frais-pro' ), 'manage_options', 'edit-tags.php?taxonomy=' . Type_Note_Class::g()->get_taxonomy() );
	}

}

new Type_Note_Action();