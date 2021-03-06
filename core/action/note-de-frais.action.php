<?php
/**
 * Initialise les fichiers .config.json
 *
 * @package Eoxia\Plugin
 *
 * @since 1.0.0
 * @version 1.4.0
 */

namespace frais_pro;
use \eoxia\Custom_Menu_Handler as CMH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialise les scripts JS et CSS du Plugin
 * Ainsi que le fichier MO
 */
class Note_De_Frais_Action {

	/**
	 * Le constructeur ajoute les actions WordPress suivantes:
	 * admin_enqueue_scripts (Pour appeler les scripts JS et CSS dans l'admin)
	 * admin_print_scripts (Pour appeler les scripts JS en bas du footer)
	 * plugins_loaded (Pour appeler le domaine de traduction)
	 */
	public function __construct() {
		// Initialises ses actions que si nous sommes sur une des pages réglés dans le fichier digirisk.config.json dans la clé "insert_scripts_pages".
		$page = ( ! empty( $_REQUEST['page'] ) ) ? sanitize_text_field( $_REQUEST['page'] ) : '';
		$post = ( ! empty( $_REQUEST['post'] ) ) ? intval( $_REQUEST['post'] ) : '';

		if ( in_array( $page, \eoxia\Config_Util::$init['frais-pro']->insert_scripts_pages_css, true ) && empty( $post ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'callback_admin_enqueue_scripts_css' ), 11 );
		}

		if ( empty( $page ) || ( in_array( $page, \eoxia\Config_Util::$init['frais-pro']->insert_scripts_pages_js, true ) && empty( $post ) ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'callback_before_admin_enqueue_scripts_js' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'callback_admin_enqueue_scripts_js' ), 11 );
		}

		add_action( 'init', array( $this, 'callback_plugins_loaded' ) );
		add_action( 'init', array( $this, 'callback_init' ), 11 );
		add_action( 'admin_init', array( $this, 'redirect_to' ) );
		add_action( 'admin_menu', array( $this, 'callback_admin_menu' ), 12 );

		add_action( 'wp_ajax_fp_modal_profil', array( $this, 'open_modal_profil' ) );
	}

	/**
	 * Initialise les fichiers JS inclus dans WordPress (jQuery, wp.media et thickbox)
	 *
	 * @return void nothing
	 *
	 * @since 1.0.0
	 * @version 1.3.0
	 */
	public function callback_before_admin_enqueue_scripts_js() {
		wp_enqueue_media();
		add_thickbox();
	}

	/**
	 * Initialise le fichier style.min.css et backend.min.js du plugin Frais Pro.
	 *
	 * @return void nothing
	 *
	 * @since 1.0.0
	 * @version 1.4.0
	 */
	public function callback_admin_enqueue_scripts_js() {
		wp_enqueue_script( 'frais-pro-math', PLUGIN_NOTE_DE_FRAIS_URL . 'core/assets/js/math.min.js', array(), \eoxia\Config_Util::$init['frais-pro']->version, false );
		wp_enqueue_script( 'frais-pro-script', PLUGIN_NOTE_DE_FRAIS_URL . 'core/assets/js/backend.min.js', array( 'jquery' ), \eoxia\Config_Util::$init['frais-pro']->version, false );
		wp_localize_script( 'frais-pro-script', 'fraisPro', array(
			'updateDataUrlPage'        => 'admin_page_' . \eoxia\Config_Util::$init['frais-pro']->update_page_url,
			'confirmMarkAsPayed'       => __( 'Are you sur you want to mark as payed? You won\'t be able to change anything after this action.', 'frais-pro' ),
			'confirmUpdateManagerExit' => __( 'Your data are being updated. If you confirm that you want to leave this page, your data could be corrupted', 'frais-pro' ),
			'noteStatusInProgress'     => __( 'In progress', 'frais-pro' ),
			'noteStatusInValidated'    => __( 'Validated', 'frais-pro' ),
			'noteStatusInPayed'        => __( 'Payed', 'frais-pro' ),
			'noteStatusInRefused'      => __( 'Refused', 'frais-pro' ),
			'lineStatusInvalid'        => __( 'Invalid line', 'frais-pro' ),
			'lineStatusValid'          => __( 'Valid line', 'frais-pro' ),
			'loader'                   => '<img src=' . admin_url( '/images/loading.gif' ) . ' />',
			'updateInProgress'         => __( 'Update in progress...', 'frais-pro' ),
			'updateDone'               => __( 'Note saved', 'frais-pro' ),
			'lineAffectedSuccessfully' => __( 'Lines have been successfully assigned', 'frais-pro' ),
		) );
	}

	/**
	 * Initialise le fichier style.min.css et backend.min.js du plugin Frais Pro.
	 *
	 * @return void nothing
	 *
	 * @since 1.0.0
	 * @version 1.3.0
	 */
	public function callback_admin_enqueue_scripts_css() {
		wp_register_style( 'frais-pro-style', PLUGIN_NOTE_DE_FRAIS_URL . 'core/assets/css/style.css', array(), \eoxia\Config_Util::$init['frais-pro']->version );
		wp_enqueue_style( 'frais-pro-style' );
	}

	/**
	 * Initialise le fichier MO
	 *
	 * @since 1.0.0
	 * @version 1.2.0
	 */
	public function callback_plugins_loaded() {
		load_plugin_textdomain( 'frais-pro', false, PLUGIN_NOTE_DE_FRAIS_DIR . '/core/assets/languages/' );
	}

	/**
	 * Appel la méthode pour initialiser les données par défaut.
	 *
	 * @since 1.4.0
	 * @version 1.4.0
	 *
	 * @return void
	 */
	public function callback_init() {
		Note_De_Frais_Class::g()->init_default_data();
	}

	/**
	 * Permet de rediriger l'utilisateur vers la page de frais pro.
	 *
	 * @since 1.5.0
	 */
	public function redirect_to() {
		/*$_pos = strlen( $_SERVER[ 'REQUEST_URI' ] ) - strlen( '/wp-admin/' );
		if ( strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) !== false && strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) == $_pos ) {
				wp_redirect( admin_url( 'admin.php?page=frais-pro' ) );
				die();
		}*/
	}

	/**
	 * Définition du menu dans l'administration de WordPress pour Frais Pro.
	 *
	 * @since 1.0.0
	 * @version 1.4.0
	 */
	public function callback_admin_menu() {
		CMH::register_container( 'Frais.pro', 'Frais.pro', 'manage_options', 'frais-pro' );
		CMH::add_logo( 'frais-pro', PLUGIN_NOTE_DE_FRAIS_URL . '/core/assets/images/icone-fond-blanc.png', admin_url( 'admin.php?page=frais-pro' ) );

		CMH::register_menu( 'frais-pro', __( 'Frais.pro', 'digirisk' ), __( 'Frais.pro', 'digirisk' ), 'manage_options', \eoxia\Config_Util::$init['frais-pro']->slug, array( Note_De_Frais_Class::g(), 'display' ), 'fa fa-home' );
		CMH::register_menu( \eoxia\Config_Util::$init['frais-pro']->slug, __( 'Edit note', 'digirisk' ), __( 'Edit note', 'digirisk' ), 'manage_options', \eoxia\Config_Util::$init['frais-pro']->slug . '-edit', array( Note_De_Frais_Class::g(), 'display' ), 'fa fa-home', 'hidden' );
	}

	public function open_modal_profil() {
		check_ajax_referer( 'open_modal_profil' );

		$user = User_Class::g()->get( array(
			'id' => get_current_user_id(),
		), true );

		ob_start();
		\eoxia\View_Util::exec( 'frais-pro', 'core', 'modal-profile', array(
			'data' => $user->data,
		) );

		wp_send_json_success( array(
			'view'        => ob_get_clean(),
			'modal_title' => __( 'User profile', 'frais-pro' ),
		) );
	}

}

new Note_De_Frais_Action();
