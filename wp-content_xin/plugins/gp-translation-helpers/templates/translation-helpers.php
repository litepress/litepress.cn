<td colspan="3" class="translation-helpers">
	<nav>
		<ul class="helpers-tabs">
			<?php
			$is_first_class = 'current';
			foreach ( $sections as $section ) {
				// TODO: printf.
				echo "<li class='{$is_first_class}' data-tab='{$section['id']}'>" . esc_html( $section['title'] ) . '<span class="count"></span></li>'; // WPCS: XSS OK.
				$is_first_class = '';
			}
			?>
		</ul>
	</nav>
	<?php
	$is_first_class = 'current';
	foreach ( $sections as $section ) {
		printf( '<div class="%s helper %s" id="%s">', esc_attr( $section['classname'] ), esc_attr( $is_first_class ), esc_attr( $section['id'] ) );
		if ( $section['has_async_content'] ) {
			echo '<div class="async-content"></div>';
		}
		echo $section['content'];
		echo '</div>';
		$is_first_class = '';
	}
		?>
</td>
