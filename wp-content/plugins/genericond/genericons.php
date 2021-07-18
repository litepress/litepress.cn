<?php
/*
Plugin Name: Genericon'd
Plugin URI: https://halfelf.org/plugins/genericond
Description: Use the Genericon icons within WordPress. Icons can be inserted using either HTML or a shortcode.
Version: 4.0.5
Author: Mika Epstein (Ipstenu)
Author URI: https://ipstenu.org/
Author Email: ipstenu@halfelf.org

	Credits: Forked plugin code from Rachel Baker's Font Awesome for WordPress plugin - https://github.com/rachelbaker/Font-Awesome-WordPress-Plugin

	License: GPLv2

	Copyright (C) 2013 - 2016 Mika Epstein (ipstenu@halfelf.org)

	This file is part of Genericon'd, a plugin for WordPress.

	The Genericon'd Plugin is free software: you can redistribute it and/or
	modify it under the terms of the GNU General Public License as published
	by the Free Software Foundation, either version 2 of the License, or
	(at your option) any later version.

	Genericons itself is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by the
	Free Software Foundation; either version 2 of the License, or (at your option)
	any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.


*/

class GenericonsHELF {

	// Holds option data.
	static $gen_ver = '4.0.5'; // Plugin version so I can be lazy
	static $option_defaults = array(
		'sprites'         => 'yes',
		'minified'        => 'no',
		'fonts'           => 'no',
		'genericons'      => 'no',
		'genericons-neue' => 'yes',
		'social-logos'    => 'yes',
	);
	
	static $options;
	
	static $urls = array(
		'genericons'      => 'https://github.com/Automattic/genericons',
		'genericons-neue' => 'https://github.com/Automattic/genericons-neue',
		'social-logos'    => 'https://github.com/Automattic/social-logos',
	);
	
	public function __construct() {
		add_action( 'init', array( &$this, 'init' ) );
		add_action( 'admin_init', array( &$this, 'admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'add_settings_menu'));

		// Set defaults for later use
		self::$options = get_option( 'genericons_options', self::$option_defaults );
	}

	/**
	 * Admin init Callback
	 *
	 * @since 4.0
	 */
	function admin_init() {	
		// Add link to settings from plugins listing page
		add_filter( 'plugin_action_links', array( &$this, 'add_settings_link'), 10, 2 );
		// Add donate link to plugin meta
		add_filter('plugin_row_meta', array( &$this, 'plugin_links'), 10, 2);

		// Enqueue scripts for admins
		add_action( 'admin_enqueue_scripts', array( &$this, 'register_plugin_styles' ) );
		// Enqueue styles for admins
		add_action( 'admin_enqueue_scripts', array( &$this, 'register_admin_styles' ) );

		// Admin notices
		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );

		// Register Settings
		$this->register_settings();
	}

	/**
	 *  Init Callback (front end)
	 *
	 * @since 1.0
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( &$this, 'register_plugin_styles' ) );
		add_shortcode( 'genericon', array( &$this, 'setup_shortcode' ) );
		add_filter( 'widget_text', 'do_shortcode' );
	}

	/**
	 *  Admin Notices
	 *
	 * @since 4.0
	 */
	function admin_notices() {
		
		if ( self::$options['genericons-neue'] == 'no' && self::$options['genericons'] == 'no' && $options['social-logos'] == 'no' ) {
			echo '<div class="notice notice-error"><p>';
				printf( __( 'You have no Genericons options set so no icons will load. Please <a href="%s">change your settings</a> to either Genericons Neue or Classic Genericons.', 'genericond' ), admin_url( 'themes.php?page=genericons' ) );
			echo '</p></div>';	
		}
	}

	/**
	 *  Register Plugin Styles
	 *
	 * @since 1.0
	 */
	public function register_plugin_styles() {	   
		wp_enqueue_style( 'genericond', plugins_url( 'css/genericond.css', __FILE__ , '', self::$gen_ver ) );
		
		if ( self::$options['genericons'] == 'yes' ) {
			// If classic genericons, then we use fonts and ONLY genericons.
			wp_register_style('genericons', plugins_url('icons/genericons/genericons/genericons.css', __FILE__, false, '', self::$gen_ver) );
			wp_enqueue_style('genericons');
		} else {
			// If we're not using classic, we have some decisions
			if ( self::$options['fonts'] == 'yes' ) {
				// Use Fonts (default NO)
				wp_register_style('genericons-neue', plugins_url('icons/genericons-neue/icon-font/Genericons-Neue.css', __FILE__, false, '', self::$gen_ver) );
				wp_register_style('social-logos', plugins_url('icons/social-logos/icon-font/social-logos.css', __FILE__, false, '', self::$gen_ver) );
			}

			if ( self::$options['social-logos'] == 'yes' ) {
				wp_enqueue_style('social-logos');
			}
			if ( self::$options['genericons-neue'] == 'yes' ) {
				wp_enqueue_style('genericons-neue');
			}
		}
	}

	function register_admin_styles() {
			wp_register_style( 'genericondExampleStyles', plugins_url( 'css/example.css', __FILE__ , '', self::$gen_ver ) );
	}

	function add_admin_styles() {
		wp_enqueue_style( 'genericondExampleStyles' );
	}

	/**
	 * Shortcode
	 * 
	 * @since 1.0
	 */
	public function setup_shortcode( $params ) {
		$attributes = shortcode_atts( array(
			'icon'   => '',
			'size'   => '',
			'color'  => '',
			'rotate' => '',
			'repeat' => '1',
			'title'  => ''
		), $params );

		// Set Icon Types
		if ( self::$options['genericons'] == 'yes' ) {
			$icon_type = 'genericons';
		} else {
			// Check for files to make sure they exist and where.			
			if ( self::$options['genericons-neue'] == 'yes' && self::$options['social-logos'] == 'yes' ) {
				if ( file_exists( plugin_dir_path(__FILE__).'/icons/genericons-neue/svg/'.$attributes['icon'].'.svg' ) ) {
					$icon_type = 'genericons-neue';
				} elseif ( file_exists( plugin_dir_path(__FILE__).'/icons/social-logos/svg/'.$attributes['icon'].'.svg' ) ) {
					$icon_type = 'social-logos';
				} else {
					$icon_type = 'genericons-neue';
					$attributes['icon'] = 'stop';
				}
			} elseif ( self::$options['genericons-neue'] == 'yes' && self::$options['social-logos'] == 'no' ) {
				$icon_type = 'genericons-neue';
				if ( !file_exists( plugin_dir_path(__FILE__).'/icons/genericons-neue/svg/'.$attributes['icon'].'.svg' ) ) {
					$attributes['icon'] = 'stop';
				}
			} elseif ( $options['genericons-neue'] == 'no' && $options['social-logos'] == 'yes' ) {
				$icon_type = 'social-logos';
				if ( !file_exists( plugin_dir_path(__FILE__).'/icons/social-logos/svg/'.$attributes['icon'].'.svg' ) ) {
					$attributes['icon'] = 'wordpress';
				}
			}
		}

		// Resizing
		$icon_size = 'genericond-'.$icon_type.'-';
		if ( !empty($attributes['size']) && isset($attributes['size']) && in_array($attributes['size'], array('2x', '3x', '4x', '5x', '6x')) ) {
			$icon_size .= $attributes['size'];
		}
		else {
			$icon_size .= "1x";
		}

		// Color
		$icon_color = "fill:";
		if ( isset($attributes['color']) && !empty($attributes['color']) ) {
			$icon_color .= $attributes['color'];
		}
		else {
			$icon_color .= 'inherit';
		}
		$icon_color .= ";";

		// Rotate
		$icon_rotate = 'genericond-rotate-normal';
		if ( !empty($attributes['rotate']) && isset($attributes['rotate']) && in_array($attributes['rotate'], array('90', '180', '270')) ) {
			$icon_rotate = 'genericond-rotate-'.$attributes['rotate'];
		} 
		if ( !empty($attributes['rotate']) && isset($attributes['rotate']) && in_array($attributes['rotate'], array('flip-horizontal', 'flip-vertical')) )  {
			$icon_rotate = 'genericond-'.$attributes['rotate'];
		}

		// Title
		$icon_title = $attributes['icon'];
		if ( isset($attributes['title']) && !empty($attributes['title']) ) {
			$icon_title = $attributes['title'];
		}

		// Build the Genericon!
		$icon_styles = $icon_color; // In case I add more later? Hope I never have to, but...

		$external = true;
		if ( self::$options['sprites'] == 'no' ) {
			$external = false;
		}

		$icon = $this->get_genericond( $attributes['icon'], '', $external, $icon_title, $icon_size, $icon_styles, $icon_rotate, $attributes['repeat'], $icon_type); 

		return $icon;
	}

	/**
	 * This allows us to get the SVG code and return as a variable
	 * Usage: get_genericond( 'name-of-icon' );
	 * 
	 * @since 4.0
	 */
	public function get_genericond( $name, $id = null, $external = true, $title = null, $size, $styles, $rotate, $repeat  = '1', $type = 'genericons-neue' ) {		
		$genericons_inject_sprite = null;
		$output = null;
	
		// Generate an attribute string for the SVG.
		$attr = 'class="genericon genericond-'. $type .' '. $type .'-'. $name .' '. $size .' '. $rotate .'"';
		$attr .= ' style="' . $styles . '"';
	
		// If the user has passed a unique ID, output it.
		if ( $id ) {
			$attr .= ' id="' . $id . '"';
		}
		
		// Use the icon name as the title if the user hasn't set one.
		if ( ! $title ) {
			$title = $name;
		} 
	
		// Specify the icon is presentational.
		// Output a title and role for screen readers.
		if ( 'none' === $title ) {
			$attr .= ' role="presentation"';
		} else { 
			$attr .= ' title="' . $title . '"';
			$attr .= ' role="img" aria-labelledby="title"';
		}
	
		// Print the SVG tag.
		$return = '<svg ' . $attr . '>';
	
		if ( $external ) : // Default behavior; caches better.
			$return .= '<use xlink:href="' . esc_url( plugin_dir_url( __FILE__ ) ) .'/icons/'.$type.'/svg-sprite/'.$type.'.svg#' . $name . '" />';
	
		else : // Use internal method if specified.
			$return .= '<use xlink:href="#' . $name . '" />';
			$genericons_inject_sprite = true;
		endif;

		$return .= '</svg>';

		// Repeat the icon if needed
		for ($i = 1 ; $i <= $repeat ; ++$i) {
			$output .= $return;
		}

	 return $output;
	}

	/**
	 * Admin Menu Callback
	 *
	 * @since 1.0
	 */
	public function add_settings_menu() {
		$page = add_theme_page(__('Genericon\'d', 'genericond'), __('Genericon\'d', 'genericond'), 'edit_posts', 'genericons', array($this, 'settings_page'));
		add_action( 'admin_print_styles-' . $page, array( &$this, 'add_admin_styles') );
		}

	/**
	 * Register Admin Settings
	 *
	 * @since 4.0
	 */
	function register_settings() {
		register_setting( 'genericons_settings', 'genericons_options', array( $this, 'genericons_sanitize' ) );

		// The main section
		add_settings_section( 'genericond-settings', 'Genericon\'d Custom Settings', array( &$this, 'genericond_settings_callback'), 'genericond-custom-settings' );

		// The Fields
		add_settings_field( 'genericon-neue', 'Genericons Neue', array( &$this, 'genericons_neue_callback'), 'genericond-custom-settings', 'genericond-settings' );
		add_settings_field( 'social-logos', 'Social Logos', array( &$this, 'social_logos_callback'), 'genericond-custom-settings', 'genericond-settings' );
		add_settings_field( 'legacy-fonts', 'Legacy Fonts', array( &$this, 'legacy_fonts_callback'), 'genericond-custom-settings', 'genericond-settings' );
		add_settings_field( 'classic-genericons', 'Classic Genericons', array( &$this, 'classic_genericons_callback'), 'genericond-custom-settings', 'genericond-settings' );		
	}

	/**
	 * Genericon'd Custtom Settings Callback
	 *
	 * @since 4.0
	 */
	function genericond_settings_callback() {
		printf( __('It is recommended to use both <a href="%1$s">Genericon Neue</a> and <a href="%2$s">Social Logos</a> in order to reproduce the "feel" of <a href="%3$s">Classic Genericons</a>. If you find you need to support IE users, try using Legacy Fonts.', 'genericons'), esc_url(self::$urls['genericons-neue']), esc_url(self::$urls['social-logos']), esc_url(self::$urls['genericons']) );
	}

	/**
	 * Genericon'd Custtom Settings: Classic Genericons Callback
	 *
	 * @since 4.0
	 */
	function classic_genericons_callback() {
		?>
		<input type="checkbox" id="genericons_options[genericons]" name="genericons_options[genericons]" value="yes" <?php 
			echo disabled( self::$options['genericons-neue'], 'yes' );	
			echo disabled( self::$options['social-logos'], 'yes' );
			echo checked( self::$options['genericons'], 'yes', true ); 
		?> >
		<?php
	}

	/**
	 * Genericon'd Custtom Settings: Genericons Neue Callback
	 *
	 * @since 4.0
	 */
	function genericons_neue_callback() {
		?>
		<input type="checkbox" id="genericons_options[genericons-neue]" name="genericons_options[genericons-neue]" value="yes" <?php 
			echo disabled( self::$options['genericons'], 'yes' );	
			echo checked( self::$options['genericons-neue'], 'yes', true ); 
		?> >
		<?php
	}	

	/**
	 * Genericon'd Custtom Settings: Social Logos Callback
	 *
	 * @since 4.0
	 */
	function social_logos_callback() {
		?>
		<input type="checkbox" id="genericons_options[social-logos]" name="genericons_options[social-logos]" value="yes"  <?php 
			echo disabled( self::$options['genericons'], 'yes' );	
			echo checked( self::$options['social-logos'], 'yes', true ); 
		?> >
		<?php
	}	

	/**
	 * Genericon'd Custtom Settings: Legacy Fonts Callback
	 *
	 * @since 4.0
	 */
	function legacy_fonts_callback() {
		?>
		<input type="checkbox" id="genericons_options[fonts]" name="genericons_options[fonts]" value="yes" <?php 
			echo disabled( self::$options['genericons'], 'yes' );
			echo checked( self::$options['fonts'], 'yes', true ); 
		?> >
		<?php
	}

	/**
	 * Options sanitization and validation
	 *
	 * @param $input the input to be sanitized
	 * @since 2.0
	 */
	function genericons_sanitize( $input ) {
	
			foreach (self::$options as $key=>$value) {
				if ( !isset($input[$key]) || is_null( $input[$key] ) || $input[$key] == '0' ) {
					$output[$key] = 'no';
				} else {
					$output[$key] = sanitize_text_field($input[$key]);
				}
			}
		
		// If classic, we disable social and neue. If we disable classic, we force neue.
		if ( $output['genericons'] == 'yes' ) {
			$output['social-logos']    = 'no';
			$output['genericons-neue'] = 'no';
		}

		// Reverse! If social or neue are active, kill classic:
		if ( $output['social-logos'] == 'yes' || $output['genericons-neue'] == 'yes' ) {
			$output['genericons'] = 'no';
		}
				
		// Hardcoded for now - these will be options later
		$output['sprites']  = 'yes';
		$output['minified'] = 'no';
	
		return $output;
	}

	/**
	 * Settings Page
	 *
	 * @since 1.0
	 */
 	function settings_page() {
		?>
		<div class="wrap">

			<h1><?php printf( __( 'Genericon\'d %s Usage', 'genericond' ), self::$gen_ver); ?></h1>
			<?php settings_errors(); ?>
			<p><?php printf( __('As of version 4.0, Genericon\'d defaults to using modern SVGs instead of fonts and combines the <a href="%1$s">Genericon Neue</a> icon pack as well as <a href="%2$s">Social Logos</a> to ensure your old code keeps working. If SVGs won\'t work for your site, you can either use <a href="%3$s">classic Genericons</a> or the legacy font packs. Be aware, those options will slow your site. It is not recommended to keep this enabled unless your circumstances require it.', 'genericons'), esc_url(self::$urls['genericons-neue']), esc_url(self::$urls['social-logos']),esc_url(self::$urls['genericons']) );  ?></p>

			<div id="content">
				<div id="glyph">
				<form method="post" action="options.php">
				<?php
					settings_fields( 'genericons_settings' );
					do_settings_sections( 'genericond-custom-settings' );
					submit_button();
				?>
				</form>
				</div>
			
				<div class="description">
					<h2><?php _e( 'Usage Examples:', 'genericond' ); ?></h2>
					<p><?php _e( 'Basic:', 'genericond' ); ?><code>&#091;genericon icon=twitter&#093;</code></p>
					<p><?php _e( 'Color:', 'genericond' ); ?> <code>&#091;genericon icon=twitter color=#4099FF&#093;</code></p>
					<p><?php _e( 'Size:', 'genericond' ); ?> <code>&#091;genericon icon=facebook size=4x&#093;</code></p>
					<p><?php _e( 'Repeat:', 'genericond' ); ?> <code>&#091;genericon icon=star repeat=3&#093;</code></p>
					<p><?php _e( 'Flip:', 'genericond' ); ?> <code>&#091;genericon icon=twitter rotate=flip-horizontal&#093;</code></p>
				</div>
			</div>

			<div class="clear"></div>
				
			<h3><?php _e( 'Genericons Neue', 'genericond' ); ?></h3>

			<p><?php _e( 'The following are all the included Genericons Neue icons, with the name listed below.', 'genericond' ); ?></p>

			<div id="icons"><div id="iconlist">
				<?php
				$imagepath = plugin_dir_path(__FILE__).'/icons/genericons-neue/svg-min/';
				foreach( glob( $imagepath.'*' ) as $filename ){
					$name  = str_replace( $imagepath, '' , $filename );
					$name  = str_replace( '.svg', '', $name );	
					$image = $this->get_genericond( $name, '', true, $name, '1x', '', '', '1', 'genericons-neue');
					echo '<span role="img" class="genericond-icon">' . $image . $name .'</span>';
				}
				?>
			</div></div>

			<hr>

			<h3><?php _e( 'Social Logos', 'genericond' ); ?></h3>

			<p><?php _e( 'The following are all the included Social Logo icons, with the name listed below.', 'genericond' ); ?></p>

			<div id="icons"><div id="iconlist">
				<?php
				$imagepath = plugin_dir_path(__FILE__).'/icons/social-logos/svg-min/';
				foreach( glob( $imagepath.'*' ) as $filename ){
					$name  = str_replace( $imagepath, '' , $filename );
					$name  = str_replace( '.svg', '', $name );
					$image = $this->get_genericond( $name, '', true, $name, '1x', '', '', '1', 'social-logos');
					echo '<span role="img" class="genericond-icon">' . $image . $name .'</span>';
				}
				?>
			</div></div>
		</div>
		<div class="clear"></div>
		<?php
	}

	/**
	 * Add donate links on plugin listing
	 *
	 * @since 1.0
	 */
	public function plugin_links($links, $file) {
		if ($file == plugin_basename(__FILE__)) {
				$donate_link = '<a href="https://ko-fi.com/A236CEN/">Donate</a>';
				$links[] = $donate_link;
		}
		return $links;
	}

	/**
	 * Add settings link on plugin
	 *
	 * @since 4.0
	 */
	function add_settings_link( $links, $file ) {
		if ( plugin_basename( __FILE__ ) == $file ) {
			$settings_link = '<a href="' . admin_url( 'themes.php?page=genericons' ) .'">' . __( 'Settings', 'genericond' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

}

new GenericonsHELF();