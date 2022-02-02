/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { without } from 'lodash';
import { Icon, stonks } from '@woocommerce/icons';
import { createBlock, registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import Block from './block';
import sharedAttributes, {
	sharedAttributeBlockTypes,
} from '../../utils/shared-attributes';

registerBlockType( 'woocommerce/product-best-sellers', {
	title: __( 'Best Selling Products', 'woocommerce' ),
	icon: {
		src: <Icon srcElement={ stonks } />,
		foreground: '#7f54b3',
	},
	category: 'woocommerce',
	keywords: [ __( 'WooCommerce', 'woocommerce' ) ],
	description: __(
		'Display a grid of your all-time best selling products.',
		'woocommerce'
	),
	supports: {
		align: [ 'wide', 'full' ],
		html: false,
	},
	example: {
		attributes: {
			isPreview: true,
		},
	},
	attributes: {
		...sharedAttributes,
	},

	transforms: {
		from: [
			{
				type: 'block',
				blocks: without(
					sharedAttributeBlockTypes,
					'woocommerce/product-best-sellers'
				),
				transform: ( attributes ) =>
					createBlock(
						'woocommerce/product-best-sellers',
						attributes
					),
			},
		],
	},

	/**
	 * Renders and manages the block.
	 *
	 * @param {Object} props Props to pass to block.
	 */
	edit( props ) {
		return <Block { ...props } />;
	},

	save() {
		return null;
	},
} );
