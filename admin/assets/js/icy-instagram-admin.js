(function($) {





	var icyHotSpot = (function() {

		var overlay 			= jQuery('.ig-img-overlay');				// Overlay container
		var hsContainer 		= jQuery('.ig-img-overlay .ig-img');		// Hotspot overlay image container
		var hscContainer 		= jQuery('.ig-img-overlay .ig-img-hsc');
		var imgContainer 		= jQuery('.ig-img-overlay .ig-img-c');
		var productSelect 		= jQuery('.ig-hotspot-settings');			// Enhanced product selector
		var currentlyEditingImg = false;									// The image ID we are editing
		var currentlyEditing 	= false;									// Are we currently editing a hotspot?
		var hsList = {};


		/**
		 * Generates a random string for Hotspot ID
		 */
		var _generatehsID = function() {
		    var text = "";
		    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

		    for( var i = 0; i < 16; i++ ) {
		        text += possible.charAt( Math.floor( Math.random() * possible.length ) );
		    }
		    return text;
		}


		/**
		 * Retrieve the existing Hotspots from the database
		 */
		var _getHotSpots = function() {
			jQuery.post(ajaxurl, {
				'action' : 'icyig_get_hotspots',
			}, function(response) {
				if( response !== false && response != 'false' ) {
					try {
						hsList = JSON.parse( response );
					}catch(e) {
						hsList = {};
					}
				}else {
					hsList = {};
				}
			});
		}


		var _populateHotSpots = function( imageID ) {
			if( imageID in hsList ) {
				var imageHotspots = hsList[imageID].hotspots;

				for( var property in imageHotspots ) {
				    if( imageHotspots.hasOwnProperty( property ) ) {
				    	if( imageHotspots[property].product_id == '' || typeof imageHotspots[property].product_id === 'undefined' )
				    		continue;

						hscContainer.append( '<div class="ig-hotspot" data-hsid="' + property + '" style="left: ' + imageHotspots[property].xPos + '%; top: ' + imageHotspots[property].yPos + '%;"></div>' );
				    }
				}
			}
		}


		/**
		 * Populates the overlay with the image and other data
		 */
		var _populateOverlayData = function( data ) {
			if( data == '' || typeof data === 'undefined' )
				return false;

			imgContainer.html( '<img src="' + data.image + '" />' );
			hscContainer.html( '' );

			_populateHotSpots( data.id );
		}


		var _init = function() {

			_getHotSpots();

			/**
			 * Actions to take when closing Thickbox
			 */
			jQuery(window).on('tb_unload', function(e) {
				currentlyEditingImg = false;
				jQuery('input[name=ig-hotspot-product]').select2('close');
				productSelect.hide();
			});

			
			jQuery('.ig-img-settings-action').on('click', function(e) {
				if( currentlyEditingImg != false )
					return false;

				var $parent = jQuery(this).closest('.ig-image');
				var $data   = $parent.find('.ig-img-data');

				currentlyEditingImg = $parent.attr('data-image-id');

				_populateOverlayData({ id : $parent.attr('data-image-id'), image : $data.attr('data-image') });
			});


			hsContainer.on('click', function(e) {
				var target = jQuery( e.target );

				// Add HotSpot
				if( target.is('img' ) && currentlyEditing === false ) {
					icyHotSpot.addHotSpot(e);
					return;
				}

				// Edit HotSpot
				if( target.is('.ig-hotspot' ) ) {
					icyHotSpot.editHotSpot( target.attr('data-hsid') );
					return;
				}

				// Save Hotspot
				if( target.is('.ig-save-hotspot' ) || ( target.is('img' ) && currentlyEditing !== false ) ) {
					icyHotSpot.saveHotSpot();
					return;
				}

				// Delete Hotspot
				if( target.is('.ig-close-hotspot' ) || ( target.is('img' ) && currentlyEditing !== false ) ) {
					icyHotSpot.deleteHotSpot();
					return;
				}
			});
		}


		return {

			init: function() {
				if( ! jQuery().select2 )
					return false;
				
				return _init();
			},


			/**
			 * Function fires on click action to set a hotspot
			 */
			addHotSpot: function(e) {
				if( currentlyEditing != false )
					return;

				var xPos, yPos;

				// Get click position
				// 12 is the offset for the pointer (width is 24px of hotspot)
				xPos = (e.offsetX - 12) / jQuery(e.target).width() * 100;
				yPos = (e.offsetY - 12) / jQuery(e.target).height() * 100;

				// Use timestamp for HotSpot ID
				var hsID = _generatehsID();

				hscContainer.append( '<div class="ig-hotspot" data-hsid="' + hsID + '" style="left: ' + xPos.toFixed(4) + '%; top: ' + yPos.toFixed(4) + '%;"></div>' );

				if( ! (currentlyEditingImg in hsList ) ) {
					hsList[currentlyEditingImg] = {
						hotspots : {}
					};
				}

				hsList[currentlyEditingImg].hotspots[hsID] = {
					xPos : xPos.toFixed(4),
					yPos : yPos.toFixed(4),
				};

				icyHotSpot.editHotSpot( hsID );
			},


			editHotSpot: function( hsID ) {
				currentlyEditing = hsID;

				var hs = jQuery('.ig-hotspot[data-hsid=' + hsID + ']');

				hs.addClass('is-editing');

				productSelect.removeClass('enhanced');

				jQuery('input[name=ig-hotspot-product]').removeClass('enhanced');

				productSelect.css({ left: hs.css('left'), top: hs.css('top') }).show();

				var product_id = hsList[currentlyEditingImg].hotspots[hsID].product_id;

				jQuery('input[name=ig-hotspot-product]').val(product_id).attr('data-selected', 'Product ID: ' + product_id);

				jQuery( document.body ).trigger('wc-enhanced-select-init');
			},


			saveHotSpot: function() {
				if( currentlyEditing == false )
					return false;

				currentImage = hsList[currentlyEditingImg];

				if( currentlyEditingImg in hsList ) {
					if( currentlyEditing in currentImage.hotspots ) {
						var product = jQuery('input[name=ig-hotspot-product]').val();

						hsList[currentlyEditingImg].hotspots[currentlyEditing].product_id = product;
					}else {
						console.error('Instagram Feed: Hotspot does not exist');
					}
				}else {
					console.error('Instagram Feed: Image does not exist');
				}

				icyHotSpot.closeHotSpot();
			},


			deleteHotSpot: function() {
				jQuery('.ig-hotspot[data-hsid=' + currentlyEditing + ']').remove();
				jQuery('input[name=ig-hotspot-product]').select2('close');

				productSelect.hide();

				if( currentlyEditingImg in hsList ) {
					if( currentlyEditing in hsList[currentlyEditingImg].hotspots ) {
						delete hsList[currentlyEditingImg].hotspots[currentlyEditing];
					}
				}

				icyHotSpot.updateDB();

				currentlyEditing = false;
			},


			closeHotSpot: function() {
				icyHotSpot.updateDB();

				currentlyEditing = false;

				jQuery('input[name=ig-hotspot-product]').select2('data', null);

				jQuery('.ig-hotspot').removeClass('is-editing');
				productSelect.hide();
			},


			updateDB: function() {
				var value = JSON.stringify(hsList);
				var data  = {
					'action' : 'icyig_update',
					'data' : value
				};

				jQuery.post(ajaxurl, data, function(response) {
					console.log('Got this from the server: ' + response);
				});
			}
		}

	}(jQuery));

	icyHotSpot.init();

})(jQuery);





