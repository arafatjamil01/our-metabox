<?php
/**
 * Plugin Name:       Our Metabox
 * Plugin URI:        https://github.com/arafatjamil01/our-metabox
 * Description:       Metabox Plugin.
 * Version:           1.0
 * Author:            Arafat Jamil
 * Author URI:        https://github.com/arafatjamil01
 * License:           GPL v2 or later
 * Text Domain:       our-metabox
 * Domain Path:       /languages/
 */


class OurMetabox {
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'save_post', array( $this, 'save_metabox' ) );
	}

	public function is_secured( $nonce_field, $action, $post_id ) {

		$nonce = isset( $_POST[ $nonce_field ] ) ? $_POST[ $nonce_field ] : '';

		if ( '' == $nonce ) {
			return false;
		}

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			return false;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return false;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		return true;
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'our-metabox', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function add_metabox() {
		add_meta_box(
			'location',
			__( 'Location', 'our-metabox' ),
			array( $this, 'render_metabox' ),
			array( 'post', 'pages' ),
			'normal',
			'high'
		);
	}

	public function render_metabox( $post ) {
		$location_meta = get_post_meta( $post->ID, 'location', true );
		$city          = get_post_meta( $post->ID, 'city', true );
		$is_favorite   = get_post_meta( $post->ID, 'is_favorite', true );
		$checked       = $is_favorite ? 'checked' : '';

		echo '<pre>';
		print_r( $is_favorite );
		echo '</pre>';

		wp_nonce_field( 'location', 'location_nonce' );
		?>
		<label for="location"><?php _e( 'Post location', 'our-metabox' ); ?></label>
		<input type="text" name="location" id="location" value="<?php echo esc_attr( $location_meta ); ?>" class="regular-text">

		<br>

		<label for="location"><?php _e( 'City', 'our-metabox' ); ?></label>
		<input type="text" name="city" id="city" value="<?php echo esc_attr( $city ); ?>" class="regular-text">

		<label for="is_favorite"> <?php _e( 'Is favorite post', 'our-metabox' ); ?></label>
		<input type="checkbox" name="is_favorite" id="is_favorite" value="1" <?php echo esc_attr( $checked ); ?>>

		<?php
	}

	public function save_metabox( $post_id ) {
		if ( ! $this->is_secured( 'location_nonce', 'location', $post_id ) ) {
			return $post_id;
		}

		if ( isset( $_POST['location'] ) ) {
			update_post_meta( $post_id, 'location', sanitize_text_field( $_POST['location'] ) );
		}

		if ( isset( $_POST['city'] ) ) {
			update_post_meta( $post_id, 'city', sanitize_text_field( $_POST['city'] ) );
		}

		$is_fav = 0;
		if ( isset( $_POST['is_favorite'] ) && ! empty( $_POST['is_favorite'] ) ) {
			$is_fav = 1;
			update_post_meta( $post_id, 'is_favorite', $is_fav );
		}

		// Save checkbox data
		if ( ! isset( $_POST['is_favorite'] ) ) {
			update_post_meta( $post_id, 'is_favorite', 0 );
		}

	}
}

new OurMetabox();
