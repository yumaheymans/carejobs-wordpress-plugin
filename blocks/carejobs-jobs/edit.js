/**
 * Editor script for the CareJobs Healthcare Jobs block.
 *
 * No build step: it uses the wp.* globals WordPress already loads. The list
 * itself is rendered by PHP (ServerSideRender), so the editor shows exactly
 * what visitors will see. The lists of kinds, states and sort orders come from
 * the plugin through window.carejobsHjEditor.
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.element || ! wp.blockEditor || ! wp.components || ! wp.i18n ) {
		return;
	}

	var TEXT_DOMAIN = 'carejobs-healthcare-jobs';
	var data = window.carejobsHjEditor || { kinds: [], states: [], sorts: [] };

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;
	var __ = wp.i18n.__;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var TextControl = wp.components.TextControl;
	var SelectControl = wp.components.SelectControl;
	var ToggleControl = wp.components.ToggleControl;
	var RangeControl = wp.components.RangeControl;
	var ServerSideRender = wp.serverSideRender;

	if ( ServerSideRender && ServerSideRender.default ) {
		ServerSideRender = ServerSideRender.default;
	}

	// Props that silence spacing and size warnings on current WordPress and are ignored on older ones.
	var LOOK = { __nextHasNoMarginBottom: true, __next40pxDefaultSize: true };

	function withLook( props ) {
		for ( var key in LOOK ) {
			if ( Object.prototype.hasOwnProperty.call( LOOK, key ) ) {
				props[ key ] = LOOK[ key ];
			}
		}
		return props;
	}

	function withAll( label, options ) {
		return [ { value: '', label: label } ].concat( options );
	}

	/**
	 * A text field that saves when it loses focus or Enter is pressed, so typing
	 * does not ask CareJobs.Now for a new list on every keystroke.
	 */
	function CommitText( props ) {
		var state = useState( props.value );
		var value = state[ 0 ];
		var setValue = state[ 1 ];

		useEffect(
			function () {
				setValue( props.value );
			},
			[ props.value ]
		);

		function commit() {
			if ( value !== props.value ) {
				props.onCommit( value );
			}
		}

		return el(
			TextControl,
			withLook( {
				label: props.label,
				help: props.help,
				value: value,
				onChange: setValue,
				onBlur: commit,
				onKeyDown: function ( event ) {
					if ( 'Enter' === event.key ) {
						commit();
					}
				},
			} )
		);
	}

	function Edit( props ) {
		var attributes = props.attributes;
		var setAttributes = props.setAttributes;
		var blockProps = useBlockProps();

		return el(
			Fragment,
			null,
			el(
				InspectorControls,
				null,
				el(
					PanelBody,
					{ title: __( 'Job filters', TEXT_DOMAIN ), initialOpen: true },
					el(
						SelectControl,
						withLook( {
							label: __( 'Kind of job', TEXT_DOMAIN ),
							value: attributes.kind,
							options: withAll( __( 'All kinds of jobs', TEXT_DOMAIN ), data.kinds ),
							onChange: function ( value ) {
								setAttributes( { kind: value } );
							},
						} )
					),
					el(
						SelectControl,
						withLook( {
							label: __( 'State', TEXT_DOMAIN ),
							value: attributes.state,
							options: withAll( __( 'All states', TEXT_DOMAIN ), data.states ),
							onChange: function ( value ) {
								setAttributes( { state: value } );
							},
						} )
					),
					el( CommitText, {
						label: __( 'Keywords', TEXT_DOMAIN ),
						help: __( 'Words every job must carry in its title, place or department, such as ICU, night or travel.', TEXT_DOMAIN ),
						value: attributes.query,
						onCommit: function ( value ) {
							setAttributes( { query: value } );
						},
					} ),
					el(
						ToggleControl,
						withLook( {
							label: __( 'Remote jobs only', TEXT_DOMAIN ),
							checked: !! attributes.remote,
							onChange: function ( value ) {
								setAttributes( { remote: !! value } );
							},
						} )
					),
					el(
						TextControl,
						withLook( {
							label: __( 'Minimum hourly pay in US dollars (optional)', TEXT_DOMAIN ),
							type: 'number',
							min: 0,
							max: 1000,
							value: attributes.minPay ? String( attributes.minPay ) : '',
							onChange: function ( value ) {
								var number = parseInt( value, 10 );
								setAttributes( { minPay: number > 0 ? Math.min( number, 1000 ) : 0 } );
							},
						} )
					)
				),
				el(
					PanelBody,
					{ title: __( 'List', TEXT_DOMAIN ), initialOpen: true },
					el( CommitText, {
						label: __( 'Title (optional)', TEXT_DOMAIN ),
						value: attributes.title,
						onCommit: function ( value ) {
							setAttributes( { title: value } );
						},
					} ),
					el(
						RangeControl,
						withLook( {
							label: __( 'Number of jobs', TEXT_DOMAIN ),
							value: attributes.limit,
							min: 1,
							max: 25,
							onChange: function ( value ) {
								setAttributes( { limit: value || 10 } );
							},
						} )
					),
					el(
						SelectControl,
						withLook( {
							label: __( 'Order', TEXT_DOMAIN ),
							value: attributes.sort,
							options: data.sorts,
							onChange: function ( value ) {
								setAttributes( { sort: value } );
							},
						} )
					),
					el(
						ToggleControl,
						withLook( {
							label: __( 'Open links in a new tab', TEXT_DOMAIN ),
							checked: !! attributes.newTab,
							onChange: function ( value ) {
								setAttributes( { newTab: !! value } );
							},
						} )
					)
				)
			),
			el(
				'div',
				blockProps,
				el( ServerSideRender, {
					block: 'carejobs/healthcare-jobs',
					attributes: attributes,
				} )
			)
		);
	}

	wp.blocks.registerBlockType( 'carejobs/healthcare-jobs', {
		edit: Edit,
		save: function () {
			return null;
		},
	} );
} )( window.wp );
