<?php
/**
 * Vue principale de l'ajout des champs des utilisateurs
 *
 * @package Eoxia\Plugin
 *
 * @since 1.0.0
 * @version 1.2.0
 */

namespace note_de_frais;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<h2><?php esc_html_e( 'User informations for Frais.pro', 'note-de-frais' ); ?></h2>

<table class="form-table">
	<tbody>
		<tr class="user-marque-wrap">
			<th><label for="marque">Marque</label></th>
			<td><input type="text" name="marque" id="marque" value="<?php echo esc_attr( $user->marque ); ?>" class="regular-text ltr"></td>
		</tr>

		<tr class="user-marque-wrap">
			<th><label for="chevaux">Chevaux</label></th>
			<td>
				<select name="chevaux" id="chevaux">
				<?php

				if ( ! empty( \eoxia\Config_Util::$init['note-de-frais']->chevaux ) ) :
					foreach ( \eoxia\Config_Util::$init['note-de-frais']->chevaux as $chevaux ) :
						?>
						<option value="<?php echo esc_attr( $chevaux ); ?>" <?php selected( $chevaux, $user->chevaux, true ); ?>><?php echo esc_html( $chevaux ); ?></option>
						<?php
					endforeach;
				endif;
				?>
			</select>
			</td>
		</tr>

		<tr class="user-marque-wrap">
			<th><label for="prixkm">Prix/km</label></th>
			<td><input type="text" name="prixkm" id="prixkm" value="<?php echo esc_attr( $user->prixkm ); ?>" class="regular-text ltr"></td>
		</tr>

		<tr class="user-marque-wrap">
			<th><label for="marque">Carte grise</label></th>
			<td class="eox-note-frais"><?php echo do_shortcode( '[wpeo_upload id="' . $user->id . '" model_name="/note_de_frais/User_Class"]' ); ?></td>
		</tr>

		<?php if ( ( get_current_user_id() !== $user->id ) || ( 1 === get_current_user_id() ) ) : ?>
		<tr class="user-marque-wrap">
			<th><label for="ndf_admin">L'utilisateur peut tout voir</label></th>
			<td><input type="checkbox" name="ndf_admin" id="ndf_admin" value="1" <?php checked( $user->ndf_admin, true, true ); ?>></td>
		</tr>
	<?php endif; ?>
	</tbody>
</table>
