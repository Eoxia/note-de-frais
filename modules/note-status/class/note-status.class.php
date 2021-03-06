<?php
/**
 * Classe gérant les statuts des notes.
 *
 * @author eoxia
 * @since 1.4.0
 * @version 1.4.0
 * @copyright 2017 Eoxia
 * @package Frais.pro
 */

namespace frais_pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe gérant les actions des types de note des notes de frais.
 */
class Note_Status_Class extends \eoxia\Term_Class {

	/**
	 * Nom du modèle à utiliser
	 *
	 * @var string
	 */
	protected $model_name = '\frais_pro\Note_Status_Model';

	/**
	 * Nom de la meta stockant les donnée
	 *
	 * @var string
	 */
	protected $meta_key = 'fp_note_status';

	/**
	 * Nom de la taxonomie par défaut
	 *
	 * @var string
	 */
	protected $type = 'fp_note_status';

	/**
	 * Base de l'url pour la REST API.
	 *
	 * @var string
	 */
	protected $base = 'note_status';

	/**
	 * Nom du statut à afficher.
	 *
	 * @var array
	 *
	 * @todo nécessite un transfert
	 */
	public $status = array();

	/**
	 * Le ou les statuts pour lesquels on ne peut plus modifier les notes
	 *
	 * @var array
	 *
	 * @todo nécessite un transfert
	 */
	public $closed_status = array();

	/**
	 * Définition des statuts
	 */
	public function construct() {
		$this->associate_post_types = Note_Class::g()->get_type();
		parent::construct();
	}

	/**
	 * Initialise le tableau des status de note.
	 *
	 * @since 1.4.0
	 * @version 1.4.0
	 *
	 * @return void
	 */
	public function init_status_note() {
		$this->status = array(
			array(
				'name'              => __( 'In progress', 'frais-pro' ),
				'old_slug'          => 'En cours',
				'is_default'        => true,
				'special_treatment' => '',
				'color'             => '#898de5',
			),
			array(
				'name'              => __( 'Validated', 'frais-pro' ),
				'old_slug'          => 'Validée',
				'is_default'        => false,
				'special_treatment' => '',
				'color'             => '#139bf2',
			),
			array(
				'name'              => __( 'Payed', 'frais-pro' ),
				'old_slug'          => 'Payée',
				'is_default'        => false,
				'special_treatment' => 'closed',
				'color'             => '#47e58e',
			),
			array(
				'name'              => __( 'Refused', 'frais-pro' ),
				'old_slug'          => 'Refusée',
				'is_default'        => false,
				'special_treatment' => '',
				'color'             => '#e05353',
			),
		);
	}

	/**
	 * Create default note statuses.
	 *
	 * @since 1.4.0
	 * @version 1.4.0
	 *
	 * @return void
	 */
	public function create_default_statuses() {
		$this->init_status_note();

		if ( ! empty( $this->status ) ) {
			// Utilisé pour déclarer la taxonomie à l'activation du plugin. L'action "init" n'est pas lancée à ce moment là.
			$this->callback_init();

			foreach ( $this->status as $category_data ) {
				$category_slug = sanitize_title( $category_data['name'] );
				$tax           = get_term_by( 'slug', $category_slug, $this->get_type(), ARRAY_A );

				if ( ! empty( $tax['term_id'] ) && is_int( $tax['term_id'] ) ) {
					$category_data['id'] = $tax['term_id'];
				}

				$category_data['slug'] = $category_slug;

				$this->update( $category_data );
			}
		}
	}


	/**
	 * Récupères la liste des status possible pour les notes de frais
	 *
	 * @since 1.4.0
	 * @version 1.4.0
	 *
	 * @return array (Voir au dessus).
	 */
	public function get_statuses() {
		return $this->status;
	}

	/**
	 * Affiches le dropdown des status de note.
	 *
	 * @since 1.4.0
	 *
	 * @param Note_Model $note La note en elle même.
	 *
	 * array['class']          string Classe supplémentaire pour personnalisé le dropdown. (optional)
	 * array['current_screen'] string Page courante. (optional)
	 *
	 * @param  array   $args          (Voir au dessus).
	 *
	 * @return void
	 */
	public function display( $note, $args = array() ) {
		$note_status = 0;

		if ( null !== $note ) {
			$note_status = $note->data['current_status']->data['id'];
		}

		$args['class']          = ! empty( $args['class'] ) ? sanitize_text_field( $args['class'] ) : '';
		$args['current_screen'] = ! empty( $args['current_screen'] ) ? sanitize_text_field( $args['current_screen'] ) : '';

		$default_status = null;
		$status_list    = apply_filters( 'fp_filter_note_status_list', $this->get(), $args );

		if ( ! empty( $status_list ) ) {
			foreach ( $status_list as $status ) {
				if ( $status->data['is_default'] && empty( $default_status ) ) {
					$default_status = $status;
				}

				if ( $status->data['id'] === $note_status ) {
					$default_status = $status;
				}
			}
		}

		\eoxia\View_Util::exec( 'frais-pro', 'note-status', 'dropdown', array(
			'note'           => $note,
			'status_list'    => $status_list,
			'status_id'      => $note_status,
			'default_status' => $default_status,
			'args'           => $args,
		) );
	}
}

Note_Status_Class::g();
