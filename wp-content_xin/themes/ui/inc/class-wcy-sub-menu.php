<?php
class WCY_Sub_Menu extends Walker_Nav_Menu {

	function start_lvl(&$output, $depth = 0, $args = null) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent<ul class=\"dropdown-menu\">";
	}

	function end_lvl(&$output, $depth = 0, $args = null) {
		$indent = str_repeat("\t", $depth);
		$output .= "$indent</ul>";
	}

}
