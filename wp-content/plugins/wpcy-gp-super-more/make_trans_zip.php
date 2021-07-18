<?php
$id = htmlentities(trim($_GET['id']),ENT_QUOTES,"UTF-8");

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-includes/wp-db.php');

do_action('traduttore.generate_zip', $id);

echo 'ok';
