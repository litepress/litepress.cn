/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import PropTypes from 'prop-types';
import { ToggleControl } from '@wordpress/components';

/**
 * A combination of toggle controls for content visibility in product grids.
 *
 * @param {Object}            props          Incoming props for the component.
 * @param {function(any):any} props.onChange
 * @param {Object}            props.settings
 */
const GridContentControl = ( { onChange, settings } ) => {
	const { image, button, price, rating, title } = settings;
	// If `image` is undefined, that might be because it's a block that was
	// created before the `image` attribute existed, so we default to true.
	const imageIsVisible = image !== false;
	return (
		<>
			<ToggleControl
				label={ __( 'Product image', 'woocommerce' ) }
				help={
					imageIsVisible
						? __(
								'Product image is visible.',
								'woocommerce'
						  )
						: __(
								'Product image is hidden.',
								'woocommerce'
						  )
				}
				checked={ imageIsVisible }
				onChange={ () =>
					onChange( { ...settings, image: ! imageIsVisible } )
				}
			/>
			<ToggleControl
				label={ __( 'Product title', 'woocommerce' ) }
				help={
					title
						? __(
								'Product title is visible.',
								'woocommerce'
						  )
						: __(
								'Product title is hidden.',
								'woocommerce'
						  )
				}
				checked={ title }
				onChange={ () => onChange( { ...settings, title: ! title } ) }
			/>
			<ToggleControl
				label={ __( 'Product price', 'woocommerce' ) }
				help={
					price
						? __(
								'Product price is visible.',
								'woocommerce'
						  )
						: __(
								'Product price is hidden.',
								'woocommerce'
						  )
				}
				checked={ price }
				onChange={ () => onChange( { ...settings, price: ! price } ) }
			/>
			<ToggleControl
				label={ __( 'Product rating', 'woocommerce' ) }
				help={
					rating
						? __(
								'Product rating is visible.',
								'woocommerce'
						  )
						: __(
								'Product rating is hidden.',
								'woocommerce'
						  )
				}
				checked={ rating }
				onChange={ () => onChange( { ...settings, rating: ! rating } ) }
			/>
			<ToggleControl
				label={ __(
					'Add to Cart button',
					'woocommerce'
				) }
				help={
					button
						? __(
								'Add to Cart button is visible.',
								'woocommerce'
						  )
						: __(
								'Add to Cart button is hidden.',
								'woocommerce'
						  )
				}
				checked={ button }
				onChange={ () => onChange( { ...settings, button: ! button } ) }
			/>
		</>
	);
};

GridContentControl.propTypes = {
	/**
	 * The current title visibility.
	 */
	settings: PropTypes.shape( {
		image: PropTypes.bool.isRequired,
		button: PropTypes.bool.isRequired,
		price: PropTypes.bool.isRequired,
		rating: PropTypes.bool.isRequired,
		title: PropTypes.bool.isRequired,
	} ).isRequired,
	/**
	 * Callback to update the layout settings.
	 */
	onChange: PropTypes.func.isRequired,
};

export default GridContentControl;
