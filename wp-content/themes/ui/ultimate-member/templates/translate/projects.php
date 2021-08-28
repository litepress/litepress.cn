<?php
$args = $args ?? array();


foreach ( $args as $item ):?>
    name:<?php echo $item['name'] ?><br/>
<?php endforeach; ?>
