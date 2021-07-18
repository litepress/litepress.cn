<?php

/**
 * TODO:WCY 去除内容转义，否则这个老傻逼会把tab内容中的&lt和&gt;转义为<>。
 */
$content = wpautop( str_replace( '\\', '', $content ) );

/**
 * 为the_content过滤器多设置一个产品ID参数
 */
$content = apply_filters( 'the_content', $content, get_the_ID() );
?>

<div class="tab-editor-container ywtm_content_tab"> <?php echo do_shortcode( $content ); ?></div>
