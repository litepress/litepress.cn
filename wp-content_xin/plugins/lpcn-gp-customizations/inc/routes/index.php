<?php

namespace LitePress\GlotPress\Customizations\Inc\Routes;

use GP_Route_Project;

class Index extends GP_Route_Project {

	public function index() {
		$this->tmpl( 'index', get_defined_vars() );
	}

}
