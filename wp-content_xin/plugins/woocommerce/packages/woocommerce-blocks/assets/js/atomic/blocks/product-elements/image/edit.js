/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { createInterpolateElement } from '@wordpress/element';
import { getAdminLink } from '@woocommerce/settings';
import {
	Disabled,
	PanelBody,
	ToggleControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControl as ToggleGroupControl,
	// eslint-disable-next-line @wordpress/no-unsafe-wp-apis
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import Block from './block';
import withProductSelector from '../shared/with-product-selector';
import { BLOCK_TITLE, BLOCK_ICON } from './constants';

const Edit = ( { attributes, setAttributes } ) => {
	const { showProductLink, imageSizing, showSaleBadge, saleBadgeAlign } =
		attributes;

	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody
					title={ __( 'Content', 'woocommerce' ) }
				>
					<ToggleControl
						label={ __(
							'Link to Product Page',
							'woocommerce'
						) }
						help={ __(
							'Links the image to the single product listing.',
							'woocommerce'
						) }
						checked={ showProductLink }
						onChange={ () =>
							setAttributes( {
								showProductLink: ! showProductLink,
							} )
						}
					/>
					<ToggleControl
						label={ __(
							'Show On-Sale Badge',
							'woocommerce'
						) }
						help={ __(
							'Overlay a "sale" badge if the product is on-sale.',
							'woocommerce'
						) }
						checked={ showSaleBadge }
						onChange={ () =>
							setAttributes( {
								showSaleBadge: ! showSaleBadge,
							} )
						}
					/>
					{ showSaleBadge && (
						<ToggleGroupControl
							label={ __(
								'Sale Badge Alignment',
								'woocommerce'
							) }
							value={ saleBadgeAlign }
							onChange={ ( value ) =>
								setAttributes( { saleBadgeAlign: value } )
							}
						>
							<ToggleGroupControlOption
								value="left"
								label={ __(
									'Left',
									'woocommerce'
								) }
							/>
							<ToggleGroupControlOption
								value="center"
								label={ __(
									'Center',
									'woocommerce'
								) }
							/>
							<ToggleGroupControlOption
								value="right"
								label={ __(
									'Right',
									'woocommerce'
								) }
							/>
						</ToggleGroupControl>
					) }
					<ToggleGroupControl
						label={ __(
							'Image Sizing',
							'woocommerce'
						) }
						help={ createInterpolateElement(
							__(
								'Product image cropping can be modified in the <a>Customizer</a>.',
								'woocommerce'
							),
							{
								a: (
									// eslint-disable-next-line jsx-a11y/anchor-has-content
									<a
										href={ `${ getAdminLink(
											'customize.php'
										) }?autofocus[panel]=woocommerce&autofocus[section]=woocommerce_product_images` }
										target="_blank"
										rel="noopener noreferrer"
									/>
								),
							}
						) }
						value={ imageSizing }
						onChange={ ( value ) =>
							setAttributes( { imageSizing: value } )
						}
					>
						<ToggleGroupControlOption
							value="full-size"
							label={ __(
								'Full Size',
								'woocommerce'
							) }
						/>
						<ToggleGroupControlOption
							value="cropped"
							label={ __(
								'Cropped',
								'woocommerce'
							) }
						/>
					</ToggleGroupControl>
				</PanelBody>
			</InspectorControls>
			<Disabled>
				<Block { ...attributes } />
			</Disabled>
		</div>
	);
};

export default withProductSelector( {
	icon: BLOCK_ICON,
	label: BLOCK_TITLE,
	description: __(
		'Choose a product to display its image.',
		'woocommerce'
	),
} )( Edit );
