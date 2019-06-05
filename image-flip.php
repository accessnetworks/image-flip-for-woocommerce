<?php
/**
Plugin Name: Image Flip for WooCommerce
Plugin URI: https://github.com/accessnetworks/image-flip-for-woocommerce
Version: 0.0.1
Description: Adds a secondary image on product archives that is revealed on hover. Perfect for displaying front/back shots of clothing and other products.
Author: Access Networks
Author URI: https://www.accessca.com
Text Domain: image-flip-woocommerce
Domain Path: /languages/

License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

@package image-flip-for-woocommerce

Based on plugin by jameskoster (https://jameskoster.co.uk)
 */

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {


	/**
	 * Image Flipper class exists.
	 */
	if ( ! class_exists( 'Image_Flip_WooCommerce' ) ) {

		/**
		 * Image_Flip_WooCommerce class.
		 */
		class Image_Flip_WooCommerce {

			/**
			 * __construct function.
			 *
			 * @access public
			 * @return void
			 */
			public function __construct() {
				add_action( 'init', array( $this, 'init' ) );
				add_action( 'wp_enqueue_scripts', array( $this, 'image_flip_styles' ) );
				add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'woocommerce_template_loop_second_product_thumbnail' ), 11 );
				add_filter( 'post_class', array( $this, 'product_has_gallery' ) );
			}

			/**
			 * Init.
			 *
			 * @access public
			 */
			public function init() {
				load_plugin_textdomain( 'image-flip-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
			}

			/**
			 * Styles.
			 *
			 * @access public
			 */
			public function image_flip_styles() {
				if ( apply_filters( 'image_flip_styles', true ) ) {
					wp_enqueue_style( 'image-flip-styles', plugins_url( '/assets/css/style.min.css', __FILE__ ), array(), '0.0.1', 'all' );
				}
			}

			/**
			 * Add class if Product has Gallery.
			 *
			 * @access public
			 * @param mixed $classes Classes.
			 */
			public function product_has_gallery( $classes ) {
				global $product;

				$post_type = get_post_type( get_the_ID() );

				if ( ! is_admin() ) {

					if ( 'product' === $post_type ) {

						$attachment_ids = $this->get_gallery_image_ids( $product );

						if ( $attachment_ids ) {
							$classes[] = 'pif-has-gallery';
						}
					}
				}

				return $classes;
			}

			/**
			 * Loop Second Product Thumbnail.
			 *
			 * @access public
			 */
			public function woocommerce_template_loop_second_product_thumbnail() {

				global $product, $woocommerce;

				$attachment_ids = $this->get_gallery_image_ids( $product );

				if ( $attachment_ids ) {
					$attachment_ids     = array_values( $attachment_ids );
					$secondary_image_id = $attachment_ids['0'];

					$secondary_image_alt   = get_post_meta( $secondary_image_id, '_wp_attachment_image_alt', true );
					$secondary_image_title = get_the_title( $secondary_image_id );

					echo wp_get_attachment_image(
						$secondary_image_id,
						'shop_catalog',
						'',
						array(
							'class' => 'secondary-image attachment-shop-catalog wp-post-image wp-post-image--secondary',
							'alt'   => $secondary_image_alt,
							'title' => $secondary_image_title,
						)
					);
				}
			}

			/**
			 * Get Gallery Image IDs.
			 *
			 * @access public
			 * @param mixed $product Product.
			 */
			public function get_gallery_image_ids( $product ) {

				if ( ! is_a( $product, 'WC_Product' ) ) {
					return;
				}

				if ( is_callable( 'WC_Product::get_gallery_image_ids' ) ) {
					return $product->get_gallery_image_ids();
				} else {
					return $product->get_gallery_attachment_ids();
				}
			}

		}

		$image_flip_woocommerce = new Image_Flip_WooCommerce();
	}
}
