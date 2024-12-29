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

		add_action( 'admin_enqueue_scripts', array( $this, 'om_admin_scripts' ) );
	}

	function om_admin_scripts( $hook_suffix ) {
		$version = time();

		if ( 'post.php' === $hook_suffix ) {
			wp_enqueue_media(); // Enqueue media library, recommended, might work even without enqueueing.
			wp_enqueue_script( 'our-metabox', plugin_dir_url( __FILE__ ) . 'assets/admin/js/our-metabox.js', array( 'jquery' ), $version, true );
			wp_enqueue_style( 'our-metabox', plugin_dir_url( __FILE__ ) . 'assets/admin/css/admin.css', array(), $version );
		}
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

		$colors = array( 'red', 'blue', 'green', 'yellow', 'magenta', 'purple', 'black' );

		wp_nonce_field( 'location', 'location_nonce' );
		?>
		<label for="location"><?php _e( 'Post location', 'our-metabox' ); ?></label>
		<input type="text" name="location" id="location" value="<?php echo esc_attr( $location_meta ); ?>" class="regular-text">

		<br>

		<label for="location"><?php _e( 'City', 'our-metabox' ); ?></label>
		<input type="text" name="city" id="city" value="<?php echo esc_attr( $city ); ?>" class="regular-text">
		<br>
		<label for="is_favorite"> <?php _e( 'Is favorite post', 'our-metabox' ); ?></label>
		<input type="checkbox" name="is_favorite" id="is_favorite" value="1" <?php echo esc_attr( $checked ); ?>>
		<br>
		<label for="colors"><?php _e( 'Choose colors', 'our-metabox' ); ?></label>
		<!-- Colors checkboxes -->
		<?php

		$colorrs = get_post_meta( $post->ID, 'colors', true );

		foreach ( $colors as $color ) :
			$checked = is_array( $colorrs ) && in_array( $color, $colorrs ) ? 'checked' : '';
			?>
			<label for="<?php echo esc_attr( $color ); ?>">
				<input type="checkbox" name="colors[]" id="<?php echo $color; ?>" value="<?php echo $color; ?>" <?php echo esc_html( $checked ); ?>>
				<?php echo esc_html( $color ); ?>
			</label>
			<?php
			endforeach;
		?>

		<label for="work_from"><?php _e( 'Work', 'our-metabox' ); ?></label>
		<select name="work_from" id="work_from">
			<option value="home" <?php selected( get_post_meta( $post->ID, 'work_from', true ), 'home' ); ?>><?php _e( 'Home', 'our-metabox' ); ?></option>
			<option value="office" <?php selected( get_post_meta( $post->ID, 'work_from', true ), 'office' ); ?>><?php _e( 'Office', 'our-metabox' ); ?></option>
		</select>

		<br>

		<!-- Radio button -->
		<label for="another_colors">
		<?php _e( 'Choose colors', 'our-metabox' ); ?>
		</label>
		<br>
		<!-- Use the color array above to render radio buttons -->
		<?php
		foreach ( $colors as $color ) :
			$checked = get_post_meta( $post->ID, 'another_colors', true ) === $color ? 'checked' : '';
			?>
		<label for="<?php echo esc_attr( $color ); ?>">
		<input type="radio" name="another_colors" id="<?php echo esc_attr( $color ); ?>" value="<?php echo esc_attr( $color ); ?>" <?php echo esc_html( $checked ); ?>>
			<?php echo esc_html( $color ); ?>
		</label>
			<?php endforeach; ?>
			<br>
			<br>
			<!-- Image box -->
			<div class="custom-image-box">
				<label for="image"><?php _e( 'Image', 'our-metabox' ); ?></label>
				<button class="button" id="upload_image">Upload Image</button>
			</div>
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

		// Save colors data
		$colors = isset( $_POST['colors'] ) ? array_map( 'sanitize_text_field', $_POST['colors'] ) : array();
		update_post_meta( $post_id, 'colors', $colors );

		// Save select data
		$work_from = isset( $_POST['work_from'] ) ? sanitize_text_field( $_POST['work_from'] ) : '';
		update_post_meta( $post_id, 'work_from', $work_from );

		// Save radio data
		$another_colors = isset( $_POST['another_colors'] ) ? sanitize_text_field( $_POST['another_colors'] ) : '';
		update_post_meta( $post_id, 'another_colors', $another_colors );
	}
}

new OurMetabox();
