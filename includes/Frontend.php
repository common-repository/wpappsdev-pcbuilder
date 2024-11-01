<?php

namespace WPAppsDev\PCBU;

/**
 * The frontend class.
 */
class Frontend {
	/**
	 * Initialize the class.
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'maybe_register_session' ], 99 );
		add_action( 'wp_footer', [ $this, 'add_custom_css_code' ] );
		add_action( 'init', [ $this, 'load_share_configuration_data'] );
	}

	/**
	 * Set WC session for non-login user.
	 *
	 * @return void
	 */
	public function maybe_register_session() {
		if ( ! is_user_logged_in() && ! headers_sent() ) {
			if ( isset( WC()->session ) && is_callable( [ WC()->session, 'has_session' ] ) ) {
				if ( ! WC()->session->has_session() ) {
					WC()->session->set_customer_session_cookie( true );
				}
			}
		}
	}

	/**
	 * Add custom css code in footer.
	 *
	 * @return void
	 */
	public function add_custom_css_code() {
		echo '<style>
			body.woocommerce-account ul li.woocommerce-MyAccount-navigation-link.woocommerce-MyAccount-navigation-link--saved-configurations a::before {
				content: "\f472";
				font-family: dashicons;
			}
		</style>';
	}

	/**
	 * Load share configuration data to the builder settings.
	 *
	 * @return void
	 */
	public function load_share_configuration_data() {
		if ( ! isset( $_GET['share_key'] ) ) {
			return;
		}

		$share_key     = $_GET['share_key'];
		$configuration = wpadpcbu_process()->configurations->get_config_by_share_key( $share_key );

		if ( is_null( $configuration ) ) {
			return;
		}

		$data = json_decode( $configuration->configurations, true );

		$items = [];
		$total = 0;

		foreach ( $data as $component => $product_id ) {
			$_product   = wc_get_product( $product_id );
			$image_url  = wp_get_attachment_url( get_post_thumbnail_id( $product_id ) );
			$image_html = sprintf( '<img width="80" height="80" src="%s" class="attachment-80x80 size-80x80" alt="" loading="lazy">', esc_url( $image_url ) );

			// Add component product to pc builder items data.
			$items[ $component ] = [
				'id'     => $product_id,
				'name'   => $_product->get_name(),
				'price'  => $_product->get_price(),
				'fprice' => wc_price( $_product->get_price() ),
				'image'  => $image_html,
			];

			// Calculate price total.
			$total += $_product->get_price();
		}

		// Set pc builder data.
		$data = [
			'items' => $items,
			'total' => $total,
		];

		WC()->session->set( 'wpadpcbu_pc_builder_data', $data );

		wp_safe_redirect( get_builder_page() );
	}
}
