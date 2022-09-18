<?php
require_once dirname( __FILE__ ) . '/TnCode.class.php';
$tn  = new TnCode();
if($tn->check()){
	$_SESSION['tncode_check'] = true;
    echo "ok";
}else{
	$_SESSION['tncode_check'] = false;
    echo "error";
}
