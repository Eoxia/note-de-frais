<?php namespace note_de_frais;

if ( ! defined( 'ABSPATH' ) ) { exit; }

class Document_Class extends \eoxia\Post_Class {
	protected $model_name   				= '\note_de_frais\document_model';
	protected $post_type    				= 'attachment';
	public $attached_taxonomy_type  = 'attachment_category';
	protected $meta_key    					= '_wpdigi_document';
	protected $before_put_function = array();
	protected $after_get_function = array();

	public $mime_type_link = array(
		'application/vnd.oasis.opendocument.text' => '.odt',
		'application/zip' => '.zip',
	);
	/**
	 * Instanciation de la gestion des document imprimés / Instanciate printes document
	 */
	protected function construct() {
	}

	/**
	* Récupères le chemin vers le dossier digirisk dans wp-content/uploads
	*
	* @param string $path_type (Optional) Le type de path
	*
	* @return string Le chemin vers le document
	*/
	public function get_digirisk_dir_path( $path_type = 'basedir' ) {
		$upload_dir = wp_upload_dir();
		return str_replace( '\\', '/', $upload_dir[ $path_type ] ) . '/digirisk';
	}

	/**
	 * Création d'un fichier odt a partir d'un modèle de document donné et d'un modèle de donnée / Create an "odt" file from a given document model and a data model
	 *
	 * @param string $model_path Le chemin vers le fichier modèle a utiliser pour la génération / The path to model file to use for generate the final document
	 * @param array $document_content Un tableau contenant le contenu du fichier a écrire selon l'élément en cours d'impression / An array with the content for building file to print
	 * @param object $element L'élément courant sur lequel on souhaite générer un document / Current element where the user want to generate a file for
	 *
	 */
	public function generate_document( $model_path, $document_content, $document_name ) {
		// if ( !is_string( $model_path ) || !is_array( $document_content ) || !is_string( $document_name ) ) {
		// 	return false;
		// }

		$response = array(
			'status'	=> false,
			'message'	=> '',
			'link'		=> '',
		);

		require_once( PLUGIN_NOTE_DE_FRAIS_PATH . '/core/external/odtPhpLibrary/odf.php');

		$digirisk_directory = $this->get_digirisk_dir_path();
		$document_path = $digirisk_directory . '/' . $document_name;

		$config = array(
			'PATH_TO_TMP' => $digirisk_directory . '/tmp',
		);
		if( !is_dir( $config[ 'PATH_TO_TMP' ] ) ) {
			wp_mkdir_p( $config[ 'PATH_TO_TMP' ] );
		}

		/**	On créé l'instance pour la génération du document odt / Create instance for document generation */
		@ini_set( 'memory_limit', '256M' );
		$DigiOdf = new \DigiOdf( $model_path, $config );

		/**	Vérification de l'existence d'un contenu a écrire dans le document / Check if there is content to put into file	*/
		if ( !empty( $document_content ) ) {
			/**	Lecture du contenu à écrire dans le document / Read the content to write into document	*/
			foreach ( $document_content as $data_key => $data_value ) {
				$DigiOdf = $this->set_document_meta( $data_key, $data_value, $DigiOdf );
			}
		}

		/**	Vérification de l'existence du dossier de destination / Check if final directory exists	*/
		if( !is_dir( dirname( $document_path ) ) ) {
			wp_mkdir_p( dirname( $document_path ) );
		}

		/**	Enregistrement du document sur le disque / Save the file on disk	*/
		$DigiOdf->saveToDisk( $document_path );

		/**	Dans le cas ou le fichier a bien été généré, on met a jour les informations dans la base de données / In case the file have been saved successfully, save information into database	*/
		if ( is_file( $document_path ) ) {
			$response[ 'status' ] = true;
			$response[ 'success' ] = true;
			$response[ 'link' ] = $document_path;
		}

		return $response;
	}

	/**
	* Ecris dans le document ODT
	*
	* @param string $data_key La clé dans le ODT.
	* @param string $data_value La valeur de la clé.
	* @param object $current_odf Le document courant
	*
	* @return object Le document courant
	*/
	public function set_document_meta( $data_key, $data_value, $current_odf ) {
		// if ( !is_string( $data_key ) || !is_string( $data_value ) || !is_object( $current_odf ) ) {
		// 	return false;
		// }
		/**	Dans le cas où la donnée a écrire est une valeur "simple" (texte) / In case the data to write is a "simple" (text) data	*/
		if ( !is_array( $data_value ) ) {
			$current_odf->setVars( $data_key, stripslashes( $data_value ), true, 'UTF-8' );
		}
		else if ( is_array( $data_value ) && isset( $data_value[ 'type' ] ) && !empty( $data_value[ 'type' ] ) ) {
			switch ( $data_value[ 'type' ] ) {

				case 'picture':
					$current_odf->setImage( $data_key, $data_value[ 'value' ], ( !empty( $data_value[ 'option' ] ) && !empty( $data_value[ 'option' ][ 'size' ] ) ? $data_value[ 'option' ][ 'size' ] : 0 ) );
					break;

				case 'segment':
					$segment = $current_odf->setdigiSegment( $data_key );

					if ( $segment && is_array( $data_value[ 'value' ] ) ) {
						foreach ( $data_value[ 'value' ] as $segment_detail ) {
							foreach ( $segment_detail as $segment_detail_key => $segment_detail_value ) {
								if ( is_array( $segment_detail_value ) && array_key_exists( 'type', $segment_detail_value ) && ( 'sub_segment' == $segment_detail_value[ 'type' ] ) ) {
									foreach ( $segment_detail_value[ 'value' ] as $sub_segment_data ) {
										foreach ( $sub_segment_data as $sub_segment_data_key => $sub_segment_data_value ) {
											$segment->$segment_detail_key = $this->set_document_meta( $sub_segment_data_key, $sub_segment_data_value, $segment );
										}
									}
								}
								else {
									$segment = $this->set_document_meta( $segment_detail_key, $segment_detail_value, $segment );
								}
							}

							$segment->merge();
						}

						$current_odf->mergedigiSegment( $segment );
					}
					unset( $segment );
					break;
			}
		}

		return $current_odf;
	}


	/**
	 * Create the document into database and call the generation function / Création du document dans la base de données puis appel de la fonction de génération du fichier
	 *
	 * @param object $element The element to create the document for / L'élément pour lequel il faut créer le document
	 * @param array $document_type The document's categories / Les catégories auxquelles associer le document généré
	 * @param array $document_meta Datas to write into the document template / Les données a écrire dans le modèle de document
	 *
	 * @return object The result of document creation / le résultat de la création du document
	 */
	public function create_document( $element, $document_meta ) {
  	/**	Définition de la partie principale du nom de fichier / Define the main part of file name	*/
  	$main_title_part = $element->title;


  	/**	Enregistrement de la fiche dans la base de donnée / Save sheet into database	*/
  	$response[ 'filename' ] = mysql2date( 'Ymd', current_time( 'mysql', 0 ) ) . '_' . sanitize_title( str_replace( ' ', '_', $main_title_part ) ) . '.odt';
  	$document_args = array(
			'post_content'	=> '',
			'post_status'	=> 'inherit',
			'post_author'	=> get_current_user_id(),
			'post_date'		=> current_time( 'mysql', 0 ),
			'post_title'	=> basename( 'test', '.odt' ),
  	);


  	/**	On créé le document / Create the document	*/
  	$filetype = 'unknown';


		$path = 'document/' . $element->id . '/test.odt';
		$document_creation = $this->generate_document( str_replace( '\\', '/', PLUGIN_NOTE_DE_FRAIS_PATH . 'core/assets/document_template/ndf.odt' ), $document_meta, $path );


		$response[ 'id' ] = wp_insert_attachment( $document_args, $this->get_digirisk_dir_path() . '/' . $path, $element->id );

		$attach_data = wp_generate_attachment_metadata( $response['id'], $this->get_digirisk_dir_path() . '/' . $path );
		wp_update_attachment_metadata( $response['id'], $attach_data );

  	/**	On met à jour les informations concernant le document dans la base de données / Update data for document into database	*/
  	$document_args = array(
			'id'										=> $response[ 'id' ],
			'title'									=> basename( $response[ 'filename' ], '.odt' ),
			'parent_id'							=> $element->id,
			'author_id'							=> get_current_user_id(),
			'date'									=> current_time( 'mysql', 0 ),
			'mime_type'							=> !empty( $filetype[ 'type' ] ) ? $filetype['type'] : $filetype,
			'document_meta' 				=> $document_meta,
			'status'								=> 'inherit'
  	);
		Document_Class::g()->update( $document_args );

		return $response;
	}
}

document_class::g();
