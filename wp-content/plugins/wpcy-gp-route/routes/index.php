<?php

class WPCY_Route_Index extends GP_Route_Project {

    public function index() {
        $this->tmpl( 'index', get_defined_vars() );
    }

}
