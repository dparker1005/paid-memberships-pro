( function ( wp ) {
	const { __ } = wp.i18n;
	const { registerPlugin } = wp.plugins;
	const { PluginDocumentSettingPanel } = wp.editPost;
	const { Component } = wp.element;
	const { Spinner, CheckboxControl } = wp.components;

	const { withSelect, withDispatch } = wp.data;
	const { compose } = wp.compose;

    const all_levels = [{ value: 0, label: "Non-Members" }].concat( pmpro.all_level_values_and_labels );


    /**
     * TODO: We need to change this to query the pmpro_memberships_pages table insetead of post meta.
     * This will probably require creating new endpoints in the PMPro REST API.
     */
	const RestrictionSelectControl = compose(
		withDispatch( function ( dispatch, props ) {
			return {
				setMetaValue: function ( value ) {
					dispatch( 'core/editor' ).editPost( {
						meta: { [ props.metaKey ]: value },
					} );
				},
			};
		} ),
		withSelect( function ( select, props ) {
			return {
				metaValue:
					select( 'core/editor' ).getEditedPostAttribute( 'meta' )[
						props.metaKey
					],
			};
		} )
	)( function ( props ) {
		const level_checkboxes = all_levels.map(
			( level ) => {
				return (
					<CheckboxControl
						key={ level.value }
						label={ level.label }
						//checked={ props.metaValue.includes( level.value ) }
                        checked={false}
						onChange={ () => {
							let newValue = [...props.metaValue];
							if ( newValue.includes( product.id ) ) {
								newValue = newValue.filter(
									( item ) => item !== product.id
								);
							} else {
								newValue.push( product.id )
							}
							props.setMetaValue( newValue );
						} }
					/>
				)
			}
		);
		return (
			<fragment>
				{
					level_checkboxes.length > 6 ? (
						<div className="pmpro-scrollable-div">
							{ level_checkboxes }
						</div>
					) : (
						level_checkboxes
					)
				}
			</fragment>
		);
	} );

	class PMProPostSidebar extends Component {
		render() {
			return (
				<PluginDocumentSettingPanel name="pmpro-post-sidebar-panel" title={ __( 'Require Membership', 'paid-memberships-pro' ) } >
					<RestrictionSelectControl />
				</PluginDocumentSettingPanel>
			);
		}
	}

	registerPlugin( 'pmpro-post-sidebar', {
		icon: 'lock',
		render: PMProPostSidebar,
	} );
} )( window.wp );
