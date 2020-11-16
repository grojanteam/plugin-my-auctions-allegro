<?php
declare(strict_types=1);
/**
 * My Auctions Allegro
 * @Author Luke Grochal (Grojan Team)
 * @Author URI https://grojanteam.pl
 */
defined( 'ABSPATH' ) or die();

class GJMAA_Hook_Product
{
	public function __construct()
	{
		add_action(
			'woocommerce_product_meta_end', [
				$this,
				'add_allegro_link_to_product_meta'
			], 10, 0
		);

		if(is_admin()) {
			add_action(
				'woocommerce_product_options_advanced', [
					$this,
					'add_metabox_field_to_advanced_fields'
				], 10, 0
			);

			add_action( 'woocommerce_process_product_meta', [
				$this,
				'save_advanced_metabox_field'
			], 10, 1 );
		}
	}

	/** Hook to show auctions on Product Page */
	public function add_allegro_link_to_product_meta()
	{
		global $product;

		try {
			if (! (get_post_meta( $product->get_id(), '_allow_to_show_allegro_link', true ) ) ) {
				return;
			}

			$cta = get_post_meta( $product->get_id(), '_cta_to_show_allegro_link', true) ?? 'Show on Allegro';

			$allegroAuctionId = get_post_meta( $product->get_id(), '_allegro_auction_id', true);

			/** @var GJMAA_Helper_Auctions $helper */
			$helper = GJMAA::getHelper('auctions');
			$url = $helper->getAuctionUrl($allegroAuctionId, 'auction_id');

			$this->renderUrl($url, $cta);
		} catch( Exception | Throwable $exception ) {
			error_log( $exception->getMessage() );
		}
	}

	private function renderUrl($url, $cta = 'Show on Allegro')
	{
		echo '<div class="allegro_link"><a target="_blank" href="'.$url.'">'.__($cta, GJMAA_TEXT_DOMAIN) .'</a></div>';
	}

	public function add_metabox_field_to_advanced_fields()
	{
		global $post, $thepostid, $product_object;

		GJMAA::getView( 'product_metabox.phtml', 'admin', [
			'product_object' => $product_object,
			'post'           => $post,
			'thepostid'      => $thepostid
		] );
	}

	public function save_advanced_metabox_field( $product_id ) {
		if ( isset( $_REQUEST['_allow_to_show_allegro_link'] ) ) {
			update_post_meta( $product_id, '_allow_to_show_allegro_link', $_REQUEST['_allow_to_show_allegro_link'] );
		}

		if ( isset( $_REQUEST['_cta_to_show_allegro_link'] ) ) {
			update_post_meta( $product_id, '_cta_to_show_allegro_link', $_REQUEST['_cta_to_show_allegro_link'] );
		}
	}
}