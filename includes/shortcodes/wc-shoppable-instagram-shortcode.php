<?php

if( ! function_exists( 'icyig_display_feed' ) ) :

function icyig_display_feed( $atts, $content = '' ) {
	global $wcsi;

	$options = get_option( 'icy_instagram_config_settings' );

	wp_enqueue_script( 'wcsi-frontend-script' );
	wp_enqueue_style( 'wcsi-frontend-style' );
	
	/**
	 * Number of images to display
	 */
	$display_num = isset( $options['display_num'] ) ? (int) $options['display_num'] : 10;
	
	/**
	 * Instagram images
	 */
	$media = $wcsi->getMedia( $display_num );

	if( ! empty( $media ) ) {
		/**
		 * Hotspots data
		 */
		$hotspots 	  = get_option( 'icyig_media_data_' . $wcsi->getToken() );

		var_dump( $hotspots );

		$data 			  = $wcsi->parse_data( json_decode( json_encode( $hotspots ), true ) );

		$hoverstate 	  = ! empty( $options['hoverstate'] ) ? '<div class="thumb-hoverstate"><span class="icyicon-shop"></span><br />' . apply_filters( 'icyig_hoverstate_text', __( 'Shop Now' ) ) . '</div>' : '';
		

		ob_start();
		?>

		<div class="icyig-container">
			<div class="icyig-detail hidden">
				<div class="icyig-detail-container">
					<div class="icyig-detail-row">
						<div class="dc-left">
							<div class="icyig-image-container"></div>
						</div>
						<div class="dc-right">
							<div class="icyig-content-container"></div>
							<?php if( ! empty( $options['enabled_social'] ) && is_array( $options['enabled_social'] ) ) : ?>

								<div class="icyig-social-menu">
									<ul class="icyig-social-list">

									<?php
									foreach( $options['enabled_social'] as $social => $enabled ) :
										if( $enabled != 1 )
											continue;
									?>

									<li class="icyig-social-link-container <?php print esc_attr( $social ); ?>" data-network="<?php print esc_attr( $social ); ?>"><a href="" target="_blank" class="icyig-social-link"></a></li>
									<?php endforeach; ?>

									</ul>
								</div>

							<?php endif; ?>
							<div class="icyig-close-detail">
								<svg id="icyig-detail-box" viewBox="0 0 31.7 31.7" xmlns="http://www.w3.org/2000/svg" style="enable-background:new 0 0 31.7 31.7" x="0px" xml:space="preserve" y="0px" version="1.1">
									<line class="st0" y1=".7" x1=".7" y2="31" x2="31"/>
									<line class="st0" y1="31" x1=".7" y2=".7" x2="31"/>
								</svg>
							</div>
						</div>
					</div>
				</div>
			</div>

			<?php

			$icyig_image_meta = array();

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

			?>

			<script>var icyig_hs_contents = <?php print json_encode( $data ); ?>; var icyig_image_meta = <?php print json_encode( $icyig_image_meta ); ?>;</script>

		</div><!-- .icyig-container -->

		<?php
		$content = ob_get_contents();

		ob_end_clean();

	}elseif( isset( $media->meta->error_type ) ) {
		return sprintf( '<p>%s</p>', esc_html( $media->meta->error_message ) );
	}

	return $content;
}

endif;
add_shortcode( 'woocommerce_shoppable_instagram', 'icyig_display_feed' );
add_shortcode( 'woocommerce_instagram_feed', 'icyig_display_feed' );