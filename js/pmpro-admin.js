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

/*
 * Add JavaScript for levels page.
 */
jQuery(document).ready(function ($) {
	"use strict";

	var add_new_elem = $('#add-new-group');
	var gn_element = $("#groupname");

	add_new_elem.insertAfter("h2 .add-new-h2");

	add_new_elem.click(function () {
		var dialog = $("#addeditgroupdialog").dialog({
			autoOpen: false,
			title: "Add Group",
			modal: true,
			buttons: {
				"Add": function () {
					if (gn_element.val().length > 0) {
						var groupname = $("#groupname").val();
						var allowmult = 0;
						if ( $("#groupallowmult:checked").length > 0 ) {
							allowmult = 1;
						}
						dialog.dialog("close");
						$.post(ajaxurl, {
							action: "pmpro_add_level_group",
							name: groupname,
							mult: allowmult
						}, function () {
							window.location = pmpro.settings.level_page_url;
						});
					}
				},
				"Cancel": function () {
					dialog.dialog("close");
				}
			}
		});
		dialog.dialog("open");
	});

	$(".editgrpbutt").click(function () {

		var groupid = parseInt($(this).attr("data-groupid"), 10);
		var allow_multi = $("#groupallowmult");

		var $current_text  = $(this).closest('th').find('h2').text();
		var $current_multi = $(this).closest('th').find('.pmpro-allow-multi').val();

		window.console.log("Text for row: " + $current_text );

		if (groupid > 0) {
			gn_element.val($current_text);

			if (parseInt($current_multi) > 0) {
				allow_multi.attr('checked', true);
			} else {
				allow_multi.attr('checked', false);
			}

			var dialog = $("#addeditgroupdialog").dialog({
				autoOpen: false,
				title: "Edit Group",
				modal: true,
				buttons: {
					"Edit": function () {

						var element_val = gn_element.val();

						if (element_val.length > 0) {
							var groupname = element_val;
							var allowmult = 0;
							if ( $("#groupallowmult:checked").length > 0 ) {
								allowmult = 1;
							}
							dialog.dialog("close");
							$.post(ajaxurl, {
								action: "pmpro_edit_level_group",
								group: groupid,
								name: groupname,
								mult: allowmult
							}, function () {
								window.location = pmpro.settings.level_page_url;
							});
						}
					},
					"Cancel": function () {
						dialog.dialog("close");
					}
				}
			});
			dialog.dialog("open");
		}
	});
	$(".delgroupbutt").click(function () {
		var groupid = parseInt($(this).attr("data-groupid"), 10);
		if (groupid > 0) {
			var answer = window.confirm(pmpro.lang.confirm_delete);
			if (true === answer) {
				$.post(
					ajaxurl,
					{
						action: "pmpro_delete_level_group",
						group: groupid
					},
					function () {
						window.location.reload(true);
					}
				);
			}
		}
	});

	// Return a helper with preserved width of cells
	// from http://www.foliotek.com/devblog/make-table-rows-sortable-using-jquery-ui-sortable/
	var fixHelper = function (e, ui) {
		ui.children().each(function () {
			$(this).width($(this).width());
		});
		return ui;
	};

	$("table.membership-levels").sortable({
		helper: fixHelper,
		update: update_level_and_group_order
	});


	$("table.membership-levels tbody").sortable({
		items: "tr.levelrow",
		helper: fixHelper,
		placeholder: 'testclass',
		forcePlaceholderSize: true,
		connectWith: "tbody",
		update: update_level_and_group_order
	});

	function update_level_and_group_order(event, ui) {
		console.log('moved');
		var groupsnlevels = [];
		$("tbody").each(function () {
			var groupid = $(this).attr('data-groupid');
			var curlevels = [];
			$(this).children("tr.levelrow").each(function () {
				curlevels.push(parseInt($("td.levelid", this).text(), 10));
			});
			groupsnlevels.push({group: groupid, levels: curlevels});
		});

		var data = {
			action: 'pmpro_update_level_and_group_order',
			neworder: groupsnlevels
		};
		$.post(ajaxurl, data, function (response) {
		});
	}
});