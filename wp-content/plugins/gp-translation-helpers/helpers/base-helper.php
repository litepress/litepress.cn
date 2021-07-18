<?php

/**
 * Class GP_Translation_Helper
 *
 * Base class, extended by all other Helpers.
 */
class GP_Translation_Helper {

	public $assets_dir;
	public $data;

	/**
	 * GP_Translation_Helper constructor.
	 *
	 * Will throw a LogicException if the title property is not set.
	 */
	public final function __construct() {
		$this->assets_dir = dirname( dirname( __FILE__ ) ) . '/helpers-assets/' ;

		$required_properties = array(
			'title',
		);

		foreach ( $required_properties as $prop ) {
			if ( ! isset( $this->{$prop} ) ) {
				throw new LogicException( get_class( $this ) . ' must have a property ' . $prop );
			}
		}

		if ( method_exists( $this, 'after_constructor' ) ) {
			$this->after_constructor();
		}
	}

	/**
	 * Sets the data coming from the route.
	 * i.e original_id, path, tec
	 * @param array $args
	 */
	public function set_data( $args ) {
		$this->data = $args;
	}

	/**
	 * Get the priority of a helper. Defaults to 1 if not set.
	 * @return int
	 */
	public function get_priority() {
		return isset( $this->priority ) ? $this->priority : 1;
	}

	/**
	 * Does a helper also loads content asynchronously?
	 * Defaults to false, but uses the class property if set.
	 *
	 * @return bool
	 */
	public function has_async_content() {
		return isset( $this->has_async_content ) ? $this->has_async_content : false;
	}

	/**
	 * Get the class name for the helper div.
	 *
	 * @return string
	 */
	public function get_div_classname() {
		if ( isset( $this->classname ) ) {
			return $this->classname;
		}

		return sanitize_html_class( str_replace( '_' , '-', strtolower( get_class( $this ) ) ), 'default-translation-helper' );
	}

	/**
	 * Get the html id for the div
	 * @return string
	 */
	public function get_div_id() {
		$div_id = $this->get_div_classname() . '-' . $this->data['original_id'];

		if ( isset( $this->data['translation_id'] ) ) {
			$div_id .= '-' . $this->data['translation_id'];
		}

		return $div_id;
	}

	/**
	 * Return the title of the helper
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Should the helper be active?
	 * Overwrite in the inheriting class to make this vary depending on class args.
	 *
	 * @return bool
	 */
	public function activate() {
		return true;
	}

	/**
	 * Set the count of items returned by the helper.
	 *
	 * @param mixed $list
	 */
	public function set_count( $list ) {
		if ( is_array( $list ) ) {
			$this->count = count( $list );
		} else {
			$this->count = $list ? 1 : 0;
		}
	}

	/**
	 * Get the number of items returned by the helper.
	 *
	 * @return int
	 */
	public function get_count() {
		return isset( $this->count ) ? $this->count : 0;
	}

	/**
	 * Content/string to return when a helper has no results.
	 *
	 * @return string
	 */
	public function empty_content() {
		return 'No results found.';
	}

	/**
	 * Default callback to render items returned by the helper.
	 *
	 * @param array $items
	 *
	 * @return string
	 */
	public function async_output_callback( $items ) {
		$output = '<ul>';
		foreach ( $items as $item ) {
			$output .= '<li>' . $item . '</li>';
		}
		$output .= '</ul>';
		return $output;
	}

	/**
	 * Get content that is returned asynchronously.
	 *
	 * @return string
	 */
	public function get_async_output() {
		$items = $this->get_async_content();
		$this->set_count( $items );

		if ( ! $items ) {
			return $this->empty_content();
		}

		$output = $this->async_output_callback( $items );
		return $output;
	}

	/**
	 * Get the (non-async) output for the helper.
	 *
	 * @return string
	 */
	public function get_output() {
		return '<div class="loading">Loading&hellip;</div>';
	}

	/**
	 * Get additional css required by the helper
	 *
	 * @return bool|string
	 */
	public function get_css() {
		return false;
	}

	/**
	 * Get additional js required by the helper
	 *
	 * @return bool|string
	 */
	public function get_js() {
		return false;
	}
}