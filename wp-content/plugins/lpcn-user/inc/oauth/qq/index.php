<?php

add_action( 'init', function () {
	if ( '/user/oauth/qq' === $_SERVER['REQUEST_URI'] ) {
		$qc = new QC();
		$qc->qq_login();
		exit;
	}
} );
