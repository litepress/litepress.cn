<?php

ini_set( 'display_errors', 1 );
error_reporting( E_ERROR | E_WARNING | E_PARSE );

require_once("../../API/qqConnectAPI.php");

$qc = new QC();
$qc->qq_login();
