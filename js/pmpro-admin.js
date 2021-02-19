/**
 * Show a system prompt before redirecting to a URL.
 * Used for delete links/etc.
 * @param	text	The prompt, i.e. are you sure?
 * @param	url		The url to redirect to.
 */
function pmpro_askfirst( text, url ) {
	var answer = window.confirm( text );

	if ( answer ) {
		window.location = url;
	}
}

/**
 * Deprecated in v2.1
 * In case add-ons/etc are expecting the non-prefixed version.
 */
if ( typeof askfirst !== 'function' ) {
    function askfirst( text, url ) {
        return pmpro_askfirst( text, url );
    }
}

/*
 * Toggle elements with a specific CSS class selector.
 * Used to hide/show sub settings when a main setting is enabled.
 * @since v2.1
 */
function pmpro_toggle_elements_by_selector( selector, checked ) {
	if( checked === undefined ) {
		jQuery( selector ).toggle();
	} else if ( checked ) {
		jQuery( selector ).show();
	} else {
		jQuery( selector ).hide();
	}
}

/*
 * Find inputs with a custom attribute pmpro_toggle_trigger_for,
 * and bind change to toggle the specified elements.
 * @since v2.1
 */
jQuery(document).ready(function() {
	jQuery( 'input[pmpro_toggle_trigger_for]' ).change( function() {		
		pmpro_toggle_elements_by_selector( jQuery( this ).attr( 'pmpro_toggle_trigger_for' ), jQuery( this ).prop( 'checked' ) );
	});
});

/** JQuery to hide the notifications. */
jQuery(document).ready(function(){
	jQuery(document).on( 'click', '.pmpro-notice-button.notice-dismiss', function() {
		var notification_id = jQuery( this ).val();

		var postData = {
			action: 'pmpro_hide_notice',
			notification_id: notification_id
		}

		jQuery.ajax({
			type: "POST",
			data: postData,
			url: ajaxurl,
			success: function( response ) {
				///console.log( notification_id );
				jQuery('#'+notification_id).hide();
			}
		})
	
	});
});

/*
 * Create Webhook button for Stripe on the payment settings page.
 */
jQuery(document).ready(function() {
	// Check that we are on payment settings page.
	if ( ! jQuery( '#stripe_publishablekey' ).length || ! jQuery( '#stripe_secretkey' ).length || ! jQuery( '#pmpro_stripe_create_webhook' ).length ) {
		return;
	}

    // Disable the webhook buttons if the API keys aren't complete yet.
    jQuery('#stripe_publishablekey,#stripe_secretkey').bind('change keyup', function() {
        pmpro_stripe_check_api_keys();
    });    
    pmpro_stripe_check_api_keys();
    
    // AJAX call to create webhook.
    jQuery('#pmpro_stripe_create_webhook').click(function(event){
        event.preventDefault();
                
		var postData = {
			action: 'pmpro_stripe_create_webhook',
            secretkey: jQuery('#stripe_secretkey').val(),
		}

		jQuery.ajax({
			type: "POST",
			data: postData,
			url: ajaxurl,
			success: function( response ) {
				response = jQuery.parseJSON( response );
                ///console.log( response );
                
                jQuery( '#pmpro_stripe_webhook_notice' ).parent('div').removeClass('error')
                jQuery( '#pmpro_stripe_webhook_notice' ).parent('div').removeClass('notice-success')
                
                if ( response.notice ) {
                    jQuery('#pmpro_stripe_webhook_notice').parent('div').addClass(response.notice);
                }
                if ( response.message ) {
                    jQuery('#pmpro_stripe_webhook_notice').html(response.message);
                }
                if ( response.success ) {
                    jQuery('#pmpro_stripe_create_webhook').hide();
                }
			}
		})
    });
    
    // AJAX call to delete webhook.
    jQuery('#pmpro_stripe_delete_webhook').click(function(event){
        event.preventDefault();
                
		var postData = {
			action: 'pmpro_stripe_delete_webhook',
            secretkey: jQuery('#stripe_secretkey').val(),
		}

		jQuery.ajax({
			type: "POST",
			data: postData,
			url: ajaxurl,
			success: function( response ) {
				response = jQuery.parseJSON( response );
                ///console.log( response );
                
                jQuery( '#pmpro_stripe_webhook_notice' ).parent('div').removeClass('error')
                jQuery( '#pmpro_stripe_webhook_notice' ).parent('div').removeClass('notice-success')
                
                if ( response.notice ) {
                    jQuery('#pmpro_stripe_webhook_notice').parent('div').addClass(response.notice);
                }
                if ( response.message ) {
                    jQuery('#pmpro_stripe_webhook_notice').html(response.message);
                }
                if ( response.success ) {
                    jQuery('#pmpro_stripe_create_webhook').show();
                }				
			}
		})
	});

	// AJAX call to rebuild webhook.
    jQuery('#pmpro_stripe_rebuild_webhook').click(function(event){
        event.preventDefault();
                
		var postData = {
			action: 'pmpro_stripe_rebuild_webhook',
            secretkey: jQuery('#stripe_secretkey').val(),
		}

		jQuery.ajax({
			type: "POST",
			data: postData,
			url: ajaxurl,
			success: function( response ) {
				response = jQuery.parseJSON( response );
                ///console.log( response );
                
                jQuery( '#pmpro_stripe_webhook_notice' ).parent('div').removeClass('error')
                jQuery( '#pmpro_stripe_webhook_notice' ).parent('div').removeClass('notice-success')
                
                if ( response.notice ) {
                    jQuery('#pmpro_stripe_webhook_notice').parent('div').addClass(response.notice);
                }
                if ( response.message ) {
                    jQuery('#pmpro_stripe_webhook_notice').html(response.message);
                }
                if ( response.success ) {
                    jQuery('#pmpro_stripe_create_webhook').hide();
                }				
			}
		})
    });
});

// Disable the webhook buttons if the API keys aren't complete yet.
function pmpro_stripe_check_api_keys() {    
    if( jQuery('#stripe_publishablekey').val().length > 0 && jQuery('#stripe_secretkey').val().length > 0 ) {
        jQuery('#pmpro_stripe_create_webhook').removeClass('disabled');
        jQuery('#pmpro_stripe_create_webhook').addClass('button-secondary');
    } else {            
        jQuery('#pmpro_stripe_create_webhook').removeClass('button-secondary');
        jQuery('#pmpro_stripe_create_webhook').addClass('disabled');
    }
}

/** JQuery to hide the notifications. */
jQuery(document).ready(function(){
	// Check that we are on payment settings page.
	if ( ! jQuery( '#log_slug' ).length || ! jQuery( '#log_output' ).length || ! jQuery( '#log_clear' ).length || ! jQuery( '#log_refresh' ).length ) {
		return;
	}

	var log_slug = jQuery( '#log_slug' ).html();
	var entries  = [];

	function pmpro_update_log_ouptut() {
		var output = '';
		var selected_tag = jQuery( '#log_filter' ).val();
		for (var i = entries.length-1; i >= 0; i--) {
			if ( selected_tag && ! entries[i].tags.includes( selected_tag ) ) {
				// Hidden by filter.
				continue;
			}
			output += '----------------\n';
			output += 'Logged on ' + entries[i].timestamp + '\n';
			output += 'Tags: ' + entries[i].tags.join( ', ' ) + '\n';
			output += 'Message:\n' + entries[i].message + '\n\n';
		}
		jQuery( '#log_output' ).val( output );
	}

	jQuery( '#log_filter' ).change( function() {
		pmpro_update_log_ouptut();
	});

	function pmpro_refresh_log() {
		var postData = {
			action: 'pmpro_log_get_entries_json',
			log_slug: log_slug,
		}

		jQuery.ajax({
			type: "POST",
			data: postData,
			url: ajaxurl,
			success: function( response ) {
				entries = jQuery.parseJSON( response );
				pmpro_update_log_ouptut();
			}
		});
	}
	pmpro_refresh_log();

	jQuery( '#log_refresh' ).click( function() {
		pmpro_refresh_log();
	});

	jQuery( '#log_clear' ).click( function() {
		var postData = {
			action: 'pmpro_log_clear',
			log_slug: log_slug,
		}
		jQuery.ajax({
			type: "POST",
			data: postData,
			url: ajaxurl,
			success: function( response ) {
				entries = jQuery.parseJSON( response );
				pmpro_update_log_ouptut();
			}
		});
	});
});