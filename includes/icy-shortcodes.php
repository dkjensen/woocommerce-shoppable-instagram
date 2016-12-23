<?php

if( ! function_exists( 'icyig_display_feed' ) ) :

function icyig_display_feed( $atts, $content = '' ) {
	global $wcsi;
	
	/**
	 * Number of images to display
	 */
	$display_num = isset( Icy_Instagram_Feed::$settings['display_num'] ) ? (int) Icy_Instagram_Feed::$settings['display_num'] : 10;
	$display_num = ( $display_num <= 20 && $display_num >= 1 ) ? $display_num : 10;
	
	/**
	 * Instagram images
	 */
	$media = $wcsi->getMedia( $display_num );
	if( ! empty( $media ) ) {
		/**
		 * Hotspots data
		 */
		$media_data 	  = get_option( 'icyig_media_data_' . $wcsi->getToken() );
		$data 			  = Icy_Instagram_Feed::parse_data( json_decode( json_encode( $media_data ), true ) );
		$social_links     = isset( Icy_Instagram_Feed::$settings['enabled_social'] ) ? Icy_Instagram_Feed::$settings['enabled_social'] : array();
		$hoverstate 	  = ! empty( Icy_Instagram_Feed::$settings['hoverstate'] ) ? '<div class="thumb-hoverstate"><span class="icyicon-shop"></span><br />' . apply_filters( 'icyig_hoverstate_text', __( 'Shop Now' ) ) . '</div>' : '';
		$nav_menu         = Icy_Instagram_Feed::social_links();
		$icyig_image_meta = array();

		ob_start();

		print '<div class="icyig-container">';
			print '<div class="icyig-detail hidden">
				       <div class="icyig-detail-container">
				           <div class="icyig-detail-row">
				               <div class="dc-left">
				                   <div class="icyig-image-container"></div>
				               </div>
				               <div class="dc-right">
				                   <div class="icyig-content-container"></div>
				                   ' . $nav_menu . '
				                   <div class="icyig-close-detail">
					                   <svg id="icyig-detail-box" viewBox="0 0 31.7 31.7" xmlns="http://www.w3.org/2000/svg" style="enable-background:new 0 0 31.7 31.7" x="0px" xml:space="preserve" y="0px" version="1.1">
										   <line class="st0" y1=".7" x1=".7" y2="31" x2="31"/>
										   <line class="st0" y1="31" x1=".7" y2=".7" x2="31"/>
									   </svg>
								   </div>
				               </div>
				           </div>
				       </div>
				   </div>';

			foreach( $media as $entry ) {

				printf( '<div class="icyig-thumb-container">' . $hoverstate . '<div class="icyig-thumb" style="background-image: url(%1$s);" data-image-src="%1$s" data-image-id="%2$s"></div></div>', $entry->images->standard_resolution->url, $entry->id );

				/**
				 * Generate an array of media for JS access
				 */
				$icyig_image_meta[ $entry->id ] = apply_filters( 'icyig_image_meta', array(
					'caption'   => isset( $entry->caption->text ) ? $entry->caption->text : '',
					'user'      => array(
						'username'   => $entry->user->username,
						'picture'    => $entry->user->profile_picture,
						'id'         => $entry->user->id,
					),
					'timestamp' => human_time_diff( $entry->created_time, current_time( 'timestamp' ) ),
					'likes'     => $entry->likes->count,
					'link'      => $entry->link,
					'tags'      => $entry->tags,
					'image'     => $entry->images->standard_resolution->url
				) );

			}

			print '<script>var icyig_hs_contents = ' . json_encode( $data ) . '; var icyig_image_meta = ' . json_encode( $icyig_image_meta ) . ';</script>';

		print '</div>';

	}elseif( isset( $media->meta->error_type ) ) {
		print $media->meta->error_message;
	}

	$content = ob_get_contents();

	ob_end_clean();

	return $content;
}

endif;
add_shortcode( 'woocommerce_shoppable_instagram', 'icyig_display_feed' );
add_shortcode( 'woocommerce_instagram_feed', 'icyig_display_feed' );