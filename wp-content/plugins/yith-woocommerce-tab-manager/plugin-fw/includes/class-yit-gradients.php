<?php
/**
 * YITH Plugin Gradients Class.
 *
 * @class   YIT_Gradients
 * @package YITH\PluginFramework\Classes
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YIT_Gradients' ) ) {

	/**
	 * YIT_Gradients class.
	 *
	 * @deprecated 3.5
	 */
	class YIT_Gradients {
		/**
		 * An array of colors to use for a gradient.
		 *
		 * @var     array
		 */
		public $colors_gradient = array();

		/**
		 * Set property
		 *
		 * @param string $key   The key.
		 * @param mixed  $value The value.
		 *
		 * @internal param array $colors_gradient
		 */
		public function set( $key, $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			}
		}

		/**
		 * Get property
		 *
		 * @param string $key The key.
		 *
		 * @return mixed
		 */
		public function get( $key ) {
			if ( property_exists( $this, $key ) ) {
				return $this->{$key};
			}

			return false;
		}

		/**
		 * Add a color to use in a gradient.
		 *
		 * @param string $color    The color.
		 * @param int    $position The position.
		 */
		public function add_color_gradient( $color, $position ) {
			$the_color['color']    = $color;
			$the_color['position'] = $position;

			array_push( $this->colors_gradient, $the_color );
		}

		/**
		 * Generate the CSS code for a gradient.
		 *
		 * @param string $role      The role.
		 * @param string $direction The direction.
		 *
		 * @return string|bool
		 */
		public function gradient( $role, $direction ) {
			if ( ! empty( $this->colors_gradient ) ) {

				$css = array(
					'old'        => $this->make_old_gradient( $this->colors_gradient[0]['color'] ), // Old browsers support.
					'ff3'        => $this->make_modern_gradient( $this->colors_gradient, $direction, 'moz' ), // Firefox 3.6+ support.
					'chr_saf4'   => $this->make_chr_saf4_gradient( $this->colors_gradient, $direction ), // Chrome and safari4+ support.
					'chr10_saf5' => $this->make_modern_gradient( $this->colors_gradient, $direction, 'webkit' ), // Chrome10+ and safari5+ support.
					'opera'      => $this->make_modern_gradient( $this->colors_gradient, $direction, 'o' ), // Opera11.10+ support.
					'ie10'       => $this->make_modern_gradient( $this->colors_gradient, $direction, 'ms' ), // Internet explorer 10+ support.
					'w3c'        => $this->make_modern_gradient( $this->colors_gradient, $direction, 'w3c' ), // W3c support.
					'ie6_9'      => $this->make_ie6_gradient( $this->colors_gradient, $direction ), // Ie6-9 support.
				);

				$css = $role . '{' . implode( ';', $css ) . '}';

				$this->colors_gradient = array();

				return $css;
			}

			return '';
		}

		/**
		 * Reverse a gradient. This method should be used only before calling ::make_gradient(). Otherwise it will not works.
		 */
		public function reverse_gradient() {
			$colors_gradient       = array_reverse( $this->get( 'colors_gradient' ) );
			$colors_gradient_count = count( $colors_gradient );
			for ( $i = 0; $i < $colors_gradient_count; $i ++ ) {
				$colors_gradient[ $i ]['position'] = 100 - $colors_gradient[ $i ]['position'];
			}

			$this->set( 'colors_gradient', $colors_gradient );
		}

		/**
		 * Generate the CSS code for a gradient.
		 *
		 * @param string $role      The role.
		 * @param string $direction The direction.
		 *
		 * @return string|bool
		 */
		public function get_gradient( $role, $direction ) {
			return $this->gradient( $role, $direction );
		}

		/**
		 * Generate the CSS code for a gradient.
		 *
		 * @param string $role      The role.
		 * @param string $direction The direction.
		 */
		public function the_gradient( $role, $direction ) {
			echo esc_html( $this->get_gradient( $role, $direction ) );
		}

		/**
		 * Generate the CSS code for a gradient.
		 *
		 * @param string $role      The role.
		 * @param string $from      From color.
		 * @param string $to        To color.
		 * @param string $direction The direction.
		 *
		 * @return string|bool
		 */
		public function gradient_from_to( $role, $from, $to, $direction ) {

			$colors = array(
				array(
					'color'    => $from,
					'position' => 0,
				),
				array(
					'color'    => $to,
					'position' => 100,
				),
			);

			$this->set( 'colors_gradient', $colors );

			return $this->get_gradient( $role, $direction );
		}

		/**
		 * Generate the CSS code for a gradient.
		 *
		 * @param string     $role      The role.
		 * @param string     $color     The color.
		 * @param string     $direction The direction.
		 * @param int|string $factor    The factor.
		 *
		 * @return string|bool
		 */
		public function gradient_darker( $role, $color, $direction, $factor = 30 ) {

			$colors = array(
				array(
					'color'    => $color,
					'position' => 0,
				),
				array(
					'color'    => $this->hex_darker( $color, $factor ),
					'position' => 100,
				),
			);

			$this->set( 'colors_gradient', $colors );

			return $this->get_gradient( $role, $direction );
		}

		/**
		 * Generate the CSS code for a gradient.
		 *
		 * @param string     $role      The role.
		 * @param string     $color     The color.
		 * @param string     $direction The direction.
		 * @param int|string $factor    The factor.
		 *
		 * @return string|bool
		 * @since  1.0
		 */
		public function gradient_lighter( $role, $color, $direction, $factor = 30 ) {

			$colors = array(
				array(
					'color'    => $color,
					'position' => 0,
				),
				array(
					'color'    => $this->hex_lighter( $color, $factor ),
					'position' => 100,
				),
			);

			$this->set( 'colors_gradient', $colors );

			return $this->get_gradient( $role, $direction );
		}

		/**
		 * Generate the CSS code for a gradient that not supports gradients (add only a background color).
		 *
		 * @param string $color The color.
		 *
		 * @return string|bool
		 */
		private function make_old_gradient( $color ) {
			return 'background:' . $color;
		}

		/**
		 * Generate the CSS code for a gradient in IE6-9.
		 *
		 * @param array  $colors    The colors.
		 * @param string $direction The direction.
		 *
		 * @return string|bool
		 */
		private function make_ie6_gradient( $colors, $direction ) {
			$start  = $colors[0]['color'];
			$end    = $colors[ count( $colors ) - 1 ]['color'];
			$type   = 'horizontal' === $direction ? 1 : 0;
			$params = implode(
				',',
				array(
					"startColorstr='{$start}'",
					"endColorstr='{$end}'",
					"GradientType={$type}",
				)
			);

			return "filter:progid:DXImageTransform.Microsoft.gradient({$params})";
		}

		/**
		 * Make the CSS 3 for a gradient in modern browsers( FF3.6+, Chrome, Safari5+, Opera11.10+, IE10+ )
		 *
		 * @param array  $colors    The colors.
		 * @param string $direction The direction.
		 * @param string $browser   The browser.
		 *
		 * @return string
		 */
		private function make_modern_gradient( $colors, $direction, $browser ) {
			$css = 'background:';

			// Add the browser suffix.
			if ( 'w3c' !== $browser ) {
				$browser = '-' . $browser . '-';
			} else {
				$browser = '';
			}

			switch ( $direction ) {
				case 'vertical':
					$css .= $browser . 'linear-gradient(top,';
					break;
				case 'horizontal':
					$css .= $browser . 'linear-gradient(left,';
					break;
				case 'diagonal-bottom':
					$css .= $browser . 'linear-gradient(-45deg,';
					break;
				case 'diagonal-top':
					$css .= $browser . 'linear-gradient(45deg,';
					break;
				case 'radial':
					$css .= $browser . 'radial-gradient(center, ellipse cover,';
					break;
			}

			foreach ( $colors as $stop ) {
				$css .= $stop['color'] . ' ' . $stop['position'] . '%, ';
			}

			$css = rtrim( $css );
			$css = rtrim( $css, ',' );

			$css .= ')';

			return $css;
		}

		/**
		 * Make the CSS 3 for a gradient in Chrome and Safari 4+
		 *
		 * @param array  $colors    The colors.
		 * @param string $direction The direction.
		 *
		 * @return string
		 */
		private function make_chr_saf4_gradient( $colors, $direction ) {
			$css = 'background:';

			switch ( $direction ) {
				case 'vertical':
					$css .= '-webkit-gradient(linear,left top,left bottom,';
					break;
				case 'horizontal':
					$css .= '-webkit-gradient(linear,left top,right top,';
					break;
				case 'diagonal-bottom':
					$css .= '-webkit-gradient(linear,left top,right bottom,';
					break;
				case 'diagonal-top':
					$css .= '-webkit-gradient(linear,left bottom,right top,';
					break;
				case 'radial':
					$css .= '-webkit-gradient(radial,center center, 0px, center center, 100%,';
					break;
			}

			foreach ( $colors as $stop ) {
				$css .= 'color-stop(' . $stop['position'] . '%, ' . $stop['color'] . '), ';
			}

			$css = rtrim( $css );
			$css = rtrim( $css, ',' );

			$css .= ')';

			return $css;
		}

		/**
		 * Return an instance of the model called
		 *
		 * @param string $class The name of class that I want the instance.
		 *
		 * @return mixed
		 * @deprecated 3.5 | use YIT_Gradients::get_model instead
		 */
		public function getModel( $class ) {
			return $this->get_model( $class );
		}


		/**
		 * Return an instance of the model called
		 *
		 * @param string $class The name of class that I want the instance.
		 *
		 * @return mixed
		 * @since  3.5
		 */
		public function get_model( $class ) {
			return class_exists( 'YIT_Registry' ) ? YIT_Registry::get_instance()->$class : false;
		}


		/**
		 * Return a color darker then $color.
		 *
		 * @param string $color  The color.
		 * @param int    $factor The factor.
		 *
		 * @return  string
		 */
		public function hex_darker( $color, $factor = 30 ) {
			$color = str_replace( '#', '', $color );

			$base['R'] = hexdec( substr( $color, 0, 2 ) );
			$base['G'] = hexdec( substr( $color, 2, 2 ) );
			$base['B'] = hexdec( substr( $color, 4, 2 ) );

			$color = '#';

			foreach ( $base as $k => $v ) {
				$amount      = $v / 100;
				$amount      = round( $amount * $factor );
				$new_decimal = $v - $amount;

				$new_hex_component = dechex( $new_decimal );

				if ( strlen( $new_hex_component ) < 2 ) {
					$new_hex_component = '0' . $new_hex_component;
				}

				$color .= $new_hex_component;
			}

			return $color;
		}

		/**
		 * Return a color lighter then $color.
		 *
		 * @param string $color  The color.
		 * @param int    $factor The factor.
		 *
		 * @return  string
		 */
		public function hex_lighter( $color, $factor = 30 ) {
			$color = str_replace( '#', '', $color );

			$base['R'] = hexdec( $color[0] . $color[1] );
			$base['G'] = hexdec( $color[2] . $color[3] );
			$base['B'] = hexdec( $color[4] . $color[5] );

			$color = '#';

			foreach ( $base as $k => $v ) {
				$amount      = 255 - $v;
				$amount      = $amount / 100;
				$amount      = round( $amount * $factor );
				$new_decimal = $v + $amount;

				$new_hex_component = dechex( $new_decimal );

				if ( strlen( $new_hex_component ) < 2 ) {
					$new_hex_component = '0' . $new_hex_component;
				}

				$color .= $new_hex_component;
			}

			return $color;
		}

		/**
		 * Detect if we must use a color darker or lighter then the background.
		 *
		 * @param string $color The color.
		 * @param string $dark  The dark color.
		 * @param string $light The light color.
		 *
		 * @return  string
		 * @since   1.0
		 * @author  Andrea Grillo <andrea.grillo@yithemes.com>
		 */
		public function light_or_dark( $color, $dark = '#000000', $light = '#FFFFFF' ) {
			$hex = str_replace( '#', '', $color );

			$c_r        = hexdec( substr( $hex, 0, 2 ) );
			$c_g        = hexdec( substr( $hex, 2, 2 ) );
			$c_b        = hexdec( substr( $hex, 4, 2 ) );
			$brightness = ( ( $c_r * 299 ) + ( $c_g * 587 ) + ( $c_b * 114 ) ) / 1000;

			return ( $brightness > 155 ) ? $dark : $light;
		}

		/**
		 * Detect if we must use a color darker or lighter then the background.
		 *
		 * @param string $hex the Color.
		 *
		 * @return array Array with the rgb values.
		 */
		public function hex2rgb( $hex ) {
			$hex = str_replace( '#', '', $hex );

			if ( 3 === strlen( $hex ) ) {
				$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
				$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
				$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
			} else {
				$r = hexdec( substr( $hex, 0, 2 ) );
				$g = hexdec( substr( $hex, 2, 2 ) );
				$b = hexdec( substr( $hex, 4, 2 ) );
			}
			$rgb = array( $r, $g, $b );

			return $rgb;
		}

		/**
		 * Magic getter method
		 *
		 * @param string $prop The property.
		 *
		 * @return mixed
		 */
		public function __get( $prop ) {
			$value = null;
			if ( 'request' === $prop ) {
				if ( class_exists( 'YIT_Request' ) && ( ! isset( $this->request ) || ! $this->request instanceof YIT_Request ) ) {
					$value         = YIT_Registry::get_instance()->request;
					$this->request = $value;
				}
			}

			return $value;
		}
	}
}
