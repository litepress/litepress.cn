<?php

add_action( 'init', function () {
	if ( '/auth/oauth/qq' === $_SERVER['REQUEST_URI'] ) {
		$qc = new QC();
		$qc->qq_login();
		exit;
	}
} );
