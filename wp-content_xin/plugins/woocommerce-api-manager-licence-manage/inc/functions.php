<?php

namespace LitePress\LM\Inc;

function a() {

}

/**
 * 输出分页条
 */
function pagination( $total, $totalpages, $paged ) {
	$first_page = add_query_arg( array( 'paged' => 1 ) );
	$last_page  = add_query_arg( array( 'paged' => $totalpages ) );
	$prev_page  = add_query_arg( array( 'paged' => $paged > 1 ? $paged - 1 : 1 ) );
	$next_page  = add_query_arg( array( 'paged' => $paged < $totalpages ? $paged + 1 : $totalpages ) );

	echo '<div class="tablenav-pages"><span class="displaying-num">共' . $total . '个项目</span><span class="pagination-links">';

	if ( $paged > 1 ) {
		echo <<<html
<a class="first-page button" href="{$first_page}">
  <span class="screen-reader-text">首页</span><span aria-hidden="true">«</span>
</a> 
html;

		echo <<<html
<a class="prev-page button" href="{$prev_page}">
  <span class="screen-reader-text">上一页</span><span aria-hidden="true">‹</span>
</a> 
html;
	} else {
		echo <<<html
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span> 
html;

		echo <<<html
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span> 
html;
	}

	echo <<<html
<span class="paging-input">
    第<label for="current-page-selector" class="screen-reader-text">当前页</label>
    <input class="current-page" id="current-page-selector" type="text" name="paged" value="{$paged}" size="4" aria-describedby="table-paging">
    <span class="tablenav-paging-text">页，共<span class="total-pages">{$totalpages}</span>页</span>
</span> 
html;

	if ( $paged < $totalpages ) {
		echo <<<html
<a class="next-page button" href="{$next_page}">
  <span class="screen-reader-text">下一页</span><span aria-hidden="true">›</span>
</a> 
html;

		echo <<<html
<a class="last-page button" href="{$last_page}">
  <span class="screen-reader-text">尾页</span><span aria-hidden="true">»</span>
</a> 
html;
	} else {
		echo <<<html
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span> 
html;

		echo <<<html
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span> 
html;
	}

	echo '</span></div>';
}
