(function($) {



	var IcyInstagram = (function() {



		var mediaOpen = false;



		var hotspots = ( typeof icyig_hs_contents === 'object' && icyig_hs_contents !== null ) ? icyig_hs_contents : {};



		var image_meta = ( typeof icyig_image_meta === 'object' && icyig_image_meta !== null ) ? icyig_image_meta : {};



		var _events = function() {

			/**

			 * Open the lightbox

			 */

			$('.icyig-container .icyig-thumb-container').on('click', function(e) {

				e.preventDefault();



				if( mediaOpen !== false )

					return false;



				var thumb = $(this).children('.icyig-thumb');



				_showDetail(thumb);

			});





			/**

			 * Close the lightbox

			 */

			$('.icyig-detail').on('click', function(e) {

				var target = jQuery( e.target );



				if( target.is('.icyig-detail' ) ) {

					e.preventDefault();

					_closeDetail();

				}

			});



			$('.icyig-close-detail').on('click', function(e) {

				e.preventDefault();

				_closeDetail();

			});



			$('.icyig-detail').on('mouseover', '.hotspot-link-container a', function(e) {

				var hotspot = $(this).attr('data-hotspot');



				$('.icyig-hotspot[data-hotspot=' + hotspot + ']').addClass('active');

			}).on('mouseout', '.hotspot-link-container a', function(e) {

				$('.icyig-hotspot').removeClass('active');

			});

		};





		var _addSocialLinks = function() {

			if( $('.icyig-detail .icyig-social-link-container').length > 0 ) {

				$('.icyig-detail .icyig-social-link-container').each(function() {

					var network = $(this).attr('data-network');

					var link = $(this).children('a');

					var instagramLink = encodeURIComponent( image_meta[mediaOpen].link );

					var caption = image_meta[mediaOpen].caption;

					var image = encodeURIComponent( image_meta[mediaOpen].image );



					if( network != '' && typeof network !== 'undefined' ) {

						switch( network ) {

							case 'facebook' :

								link.attr('href', 'https://www.facebook.com/sharer/sharer.php?u=' + instagramLink);

								break;

							case 'twitter' :

								var captionLength = 130 - image_meta[mediaOpen].link.length;

								if( caption && typeof caption !== 'undefined' ) {
									if( caption.length > captionLength ) {
										text = caption.substr(0, captionLength) + '...';
									}else {
										text = caption;
									}
								}else {
									text = '';
								}

								link.attr('href', 'https://www.twitter.com/share?url=' + instagramLink + '&text=' + text + '&related=' + image_meta[mediaOpen].user.username );

								break;

							case 'google' :

								link.attr('href', 'https://plus.google.com/share?url=' + instagramLink );

								break;

							case 'pinterest' :

								link.attr('href', 'https://www.pinterest.com/pin/create/button/?url=' + instagramLink + '&media=' + image + '&description=' + caption );

								break;

							case 'instagram' :

								link.attr('href', image_meta[mediaOpen].link );

								break;

							case 'email' :

								link.attr('href', 'mailto:?body=' + caption + '%0D%0A' + instagramLink);

								break;

						}

					}

				});

			}

			

		};





		var _showDetail = function( media ) {

			$media = $(media);



			mediaOpen = $media.attr('data-image-id');



			_addSocialLinks();





			$('.icyig-detail').removeClass('hidden');

			$('.icyig-detail .icyig-image-container').html( '<img src="' + $media.attr('data-image-src') + '" class="icyig-image-full" />' );



			var imageID = $media.attr('data-image-id');

			var caption = '';

			if( image_meta[imageID].caption && typeof image_meta[imageID].caption !== 'undefined' ) {
				caption = image_meta[imageID].caption;
			}

			var dc = [], i = 0;



			dc[i++] = '<div style="display: none !important;" class="instagram-share" data-link="' + $media.attr('data-link') + '" data-caption="' + $media.attr('data-caption') + '"></div>';



			dc[i++] = '<div class="icyig-meta-container"><span class="icyig-likes">' + image_meta[imageID].likes + ' likes</span><span class="icyig-timeago">' + image_meta[imageID].timestamp + '</span></div>';



			dc[i++] = '<div class="icyig-caption"><span class="icyig-caption-user"><a href="https://www.instagram.com/' + image_meta[imageID].user.username + '" target="_blank">' + image_meta[imageID].user.username + '</a></span> ' + caption + '</div>';



			// Check if the image is listed in the array of images

			// that have hotspots

			if( imageID in hotspots ) {

				if( typeof hotspots[imageID].hotspots === 'object' ) {

					var imageHotspots = hotspots[imageID].hotspots;



					// Loop through the hotspots

					var c = 1;

					for( var property in imageHotspots ) {

					    if( imageHotspots.hasOwnProperty( property ) ) {



					    	// Append the hotspots to the image

					        $('.icyig-detail .icyig-image-container').append( '<a class="icyig-hotspot" href="' + imageHotspots[property].url + '" target="_blank" data-hotspot="' + property + '" style="top: ' + imageHotspots[property].yPos + '%; left: ' + imageHotspots[property].xPos + '%;">' + c + '</a>' );



					        dc[i++] = '<div class="hotspot-link-container"><a href="' + imageHotspots[property].url + '" target="_blank" data-hotspot="' + property + '"><span class="hs-count">' + c + '</span>' + imageHotspots[property].title + '</a></div>';

					        c++;

					    }

					}

				}

			}



			$('.icyig-detail .icyig-content-container').append( dc.join('') );

		};



		var _closeDetail = function() {

			mediaOpen = false;



			$('.icyig-detail').addClass('hidden');



			$('.icyig-content-container, .icyig-image-container').html('');

		};



		return {

			init : function() {

				return _events();

			}

		}



	})(jQuery);



	IcyInstagram.init();



})(jQuery);